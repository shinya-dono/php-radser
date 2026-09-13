<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Support;

use SensitiveParameter;
use Shinya\PhpRadser\Contracts\Message;
use Shinya\PhpRadser\Rfc2865\Attributes\ChapPassword;
use Shinya\PhpRadser\Rfc2865\Attributes\ChapChallenge;

/**
 * RFC 2865 5.3 CHAP: the NAS never sees the password, only MD5 over the CHAP ident, the password
 * and the challenge - so checking one takes the password in the clear, no stored hash will do.
 *
 * @see \Shinya\PhpRadser\Tests\Support\ChapTest
 */
class Chap
{
	/**
	 * whether the request's CHAP-Password answers the challenge for $password. The challenge is
	 * CHAP-Challenge when the NAS sent one, and the Request Authenticator when it did not.
	 */
	public static function verify(Message $message, #[SensitiveParameter] string $password): bool
	{
		// CHAP ident (1) + MD5 response (16)
		$response = $message->get(ChapPassword::class)->read();
		if (null === $response || 17 !== strlen($response)) {
			return false;
		}

		$challenge = $message->get(ChapChallenge::class)->read() ?? $message->getAuthenticator();

		return hash_equals(md5($response[0].$password.$challenge, binary: true), substr($response, 1));
	}
}
