<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Support;

use SensitiveParameter;
use Random\RandomException;
use InvalidArgumentException;
use Shinya\PhpRadser\Contracts\Message;
use Shinya\PhpRadser\Rfc2865\Attributes\UserName;
use Shinya\PhpRadser\Exceptions\RadiusRuntimeException;
use Shinya\PhpRadser\Vendors\Microsoft\Attributes\MsChapError;
use Shinya\PhpRadser\Vendors\Microsoft\Attributes\MsChap2Success;
use Shinya\PhpRadser\Vendors\Microsoft\Attributes\MsChapResponse;
use Shinya\PhpRadser\Vendors\Microsoft\Attributes\MsChap2Response;
use Shinya\PhpRadser\Vendors\Microsoft\Attributes\MsChapChallenge;

/**
 * MS-CHAP v1 (RFC 2433) and v2 (RFC 2759), as RFC 2548 carries them in RADIUS. Both are keyed on
 * the NT hash rather than the password, so a user store can keep ntHash() instead of plaintext.
 * Not covered: password change (MS-CHAP-CPW-*), LM-only responses and MPPE keys.
 *
 * @see \Shinya\PhpRadser\Tests\Support\MsChapTest
 */
class MsChap
{
	/**
	 * RFC 2759 8.3 NtPasswordHash: MD4 over the password in UTF-16LE.
	 */
	public static function ntHash(#[SensitiveParameter] string $password): string
	{
		return hash('md4', mb_convert_encoding($password, 'UTF-16LE', 'UTF-8'), binary: true);
	}

	/**
	 * whether the request's MS-CHAP-Response answers its MS-CHAP-Challenge for this NT hash. A
	 * response carrying only the LM half (flags 0) is refused.
	 *
	 * @throws RadiusRuntimeException
	 * @throws InvalidArgumentException $ntHash is not 16 bytes - most likely the password itself
	 */
	public static function verifyV1(Message $message, #[SensitiveParameter] string $ntHash): bool
	{
		// ident (1) + flags (1) + LM-Response (24) + NT-Response (24)
		$response = $message->get(MsChapResponse::class)->read();
		$challenge = $message->get(MsChapChallenge::class)->read();
		if (null === $response || null === $challenge || 50 !== strlen($response) || 8 !== strlen($challenge) || 1 !== ord($response[1])) {
			return false;
		}

		return hash_equals(static::challengeResponse($challenge, $ntHash), substr($response, 26, 24));
	}

	/**
	 * the MS-CHAP2-Success to push on the Access-Accept when the request's MS-CHAP2-Response is
	 * right for this NT hash, null when it is not. It is not optional: it is how the server proves
	 * it knew the password too, and a client that gets an Accept without one hangs up anyway.
	 *
	 * @throws RadiusRuntimeException
	 * @throws InvalidArgumentException $ntHash is not 16 bytes - most likely the password itself
	 */
	public static function verifyV2(Message $message, #[SensitiveParameter] string $ntHash): MsChap2Success|null
	{
		// ident (1) + flags (1) + Peer-Challenge (16) + reserved (8) + NT-Response (24)
		$response = $message->get(MsChap2Response::class)->read();
		$authenticatorChallenge = $message->get(MsChapChallenge::class)->read();
		if (null === $response || null === $authenticatorChallenge || 50 !== strlen($response) || 16 !== strlen($authenticatorChallenge)) {
			return null;
		}

		// RFC 2759 8.2 hashes the user name without any 'DOMAIN\' in front of it
		$userName = array_last(explode('\\', (string) $message->get(UserName::class)->read()));
		$challenge = substr(sha1(substr($response, 2, 16).$authenticatorChallenge.$userName, binary: true), 0, 8);

		$ntResponse = static::challengeResponse($challenge, $ntHash);
		if (!hash_equals($ntResponse, substr($response, 26, 24))) {
			return null;
		}

		// RFC 2759 8.7 GenerateAuthenticatorResponse
		$digest = sha1(hash('md4', $ntHash, binary: true).$ntResponse.'Magic server to client signing constant', binary: true);
		$digest = sha1($digest.$challenge.'Pad to make it do more than one iteration', binary: true);

		return MsChap2Success::make($response[0].'S='.strtoupper(bin2hex($digest)));
	}

	/**
	 * the MS-CHAP-Error to push on the Access-Reject, in whichever MS-CHAP version the request
	 * used. $code is the Windows error: 691 bad credentials, 646 restricted logon hours, 647
	 * account disabled, 648 password expired, 649 no dial-in permission. Retry is always off.
	 *
	 * @throws RandomException
	 */
	public static function error(Message $message, int $code = 691): MsChapError
	{
		if ($message->has(MsChap2Response::class)) {
			// RFC 2759 6: a v2 client expects a fresh challenge and the version even with R=0
			return MsChapError::make(sprintf('%sE=%d R=0 C=%s V=3', static::ident($message->get(MsChap2Response::class)->read()), $code, strtoupper(bin2hex(random_bytes(16)))));
		}

		return MsChapError::make(sprintf('%sE=%d R=0', static::ident($message->get(MsChapResponse::class)->read()), $code));
	}

	/**
	 * the ident byte an MS-CHAP reply attribute has to echo from the response it answers.
	 */
	protected static function ident(string|null $response): string
	{
		return str_pad(substr((string) $response, 0, 1), 1, "\0");
	}

	/**
	 * RFC 2759 8.5 ChallengeResponse: the NT hash zero-padded to 21 bytes, cut into three 7-byte
	 * DES keys, each encrypting the 8-byte challenge.
	 *
	 * @throws RadiusRuntimeException
	 * @throws InvalidArgumentException
	 */
	protected static function challengeResponse(string $challenge, #[SensitiveParameter] string $ntHash): string
	{
		// the likely mistake is handing over the password, which would just never verify - say so
		if (16 !== strlen($ntHash)) {
			throw new InvalidArgumentException('an MS-CHAP check takes the 16-byte NT hash - pass MsChap::ntHash($password), not the password');
		}

		$response = '';
		foreach (str_split(str_pad($ntHash, 21, "\0"), 7) as $key) {
			$response .= static::des($key, $challenge);
		}

		return $response;
	}

	/**
	 * single DES over one 8-byte block, with the 56-bit key MS-CHAP hands over as 7 bytes. OpenSSL 3
	 * only offers plain DES through its legacy provider, but EDE3 with one key three times over is
	 * the same cipher and is in the default provider.
	 *
	 * @throws RadiusRuntimeException
	 */
	protected static function des(#[SensitiveParameter] string $key7, string $block): string
	{
		// spread the 56 key bits over 8 bytes, 7 bits each; the low bit is parity and DES ignores it
		$bits = (int) hexdec(bin2hex($key7));
		$key = '';
		for ($i = 0; $i < 8; ++$i) {
			$key .= chr((($bits >> (49 - 7 * $i)) & 0x7F) << 1);
		}

		$encrypted = openssl_encrypt($block, 'des-ede3-ecb', $key.$key.$key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
		if (false === $encrypted) {
			throw new RadiusRuntimeException('openssl could not run DES: '.openssl_error_string());
		}

		return $encrypted;
	}
}
