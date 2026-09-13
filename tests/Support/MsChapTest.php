<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Tests\Support;

use Shinya\PhpRadser\Peer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shinya\PhpRadser\PacketCode;
use Shinya\PhpRadser\Support\MsChap;
use Shinya\PhpRadser\Contracts\AccessRequest;
use PHPUnit\Framework\Attributes\CoversNothing;
use Shinya\PhpRadser\Rfc2865\Attributes\UserName;
use Shinya\PhpRadser\Vendors\Microsoft\Attributes\MsChap2Success;
use Shinya\PhpRadser\Vendors\Microsoft\Attributes\MsChapResponse;
use Shinya\PhpRadser\Vendors\Microsoft\Attributes\MsChap2Response;
use Shinya\PhpRadser\Vendors\Microsoft\Attributes\MsChapChallenge;

/**
 * the v2 cases run on the RFC 2759 9.2 test vectors - radclient cannot generate MS-CHAPv2, so
 * those are the only independent answers there are.
 *
 * @internal
 */
#[CoversNothing]
final class MsChapTest extends TestCase
{
	private const string AUTHENTICATOR_CHALLENGE = '5b5d7c7d7b3f2f3e3c2c602132262628';

	private const string PEER_CHALLENGE = '21402324255e262a28295f2b3a337c7e';

	private const string NT_RESPONSE = '82309ecd8d708b5ea08faa3981cd83544233114a3d85d6df';

	public function testNtHashMatchesTheRfc2759Vector(): void
	{
		$this->assertSame('44ebba8d5312b8d611474411f56989ae', bin2hex(MsChap::ntHash('clientPass')));
	}

	/**
	 * captured off the wire from radclient 3.2.10 sending `MS-CHAP-Password = hunter2`.
	 */
	public function testV1ResponseVerifies(): void
	{
		$accessRequest = $this->v1Request();

		$this->assertTrue(MsChap::verifyV1($accessRequest, MsChap::ntHash('hunter2')));
		$this->assertFalse(MsChap::verifyV1($accessRequest, MsChap::ntHash('hunter3')));
	}

	public function testV2ResponseVerifiesAndProvesTheServerKnewThePassword(): void
	{
		$success = MsChap::verifyV2($this->v2Request('User'), MsChap::ntHash('clientPass'));

		$this->assertInstanceOf(MsChap2Success::class, $success);
		$this->assertSame("\x07S=407A5589115FD0D6209F510FE9C04566932CDA56", $success->read());
	}

	public function testV2HashesTheUserNameWithoutItsDomain(): void
	{
		$this->assertInstanceOf(MsChap2Success::class, MsChap::verifyV2($this->v2Request('CORP\User'), MsChap::ntHash('clientPass')));
	}

	public function testV2WrongPasswordIsRefused(): void
	{
		$this->assertNull(MsChap::verifyV2($this->v2Request('User'), MsChap::ntHash('serverPass')));
	}

	public function testErrorAnswersInTheVersionTheRequestUsed(): void
	{
		$this->assertMatchesRegularExpression('/^\x07E=691 R=0 C=[0-9A-F]{32} V=3$/', (string) MsChap::error($this->v2Request('User'))->read());
		$this->assertSame("\x00E=648 R=0", MsChap::error($this->v1Request(), 648)->read());
	}

	/**
	 * handing over the password where the NT hash goes would otherwise just never verify.
	 */
	public function testAPasswordInPlaceOfTheNtHashIsRefusedLoudly(): void
	{
		$this->expectException(InvalidArgumentException::class);

		MsChap::verifyV1($this->v1Request(), 'hunter2');
	}

	private function v1Request(): AccessRequest
	{
		return new AccessRequest(
			new Peer('127.0.0.1', 'testing123'),
			PacketCode::AccessRequest,
			authenticator: random_bytes(16),
			vendorAttributes: [MsChapResponse::vendorId() => [
				MsChapChallenge::type() => (string) hex2bin('60dcdacebd9cb19d'),
				MsChapResponse::type()  => (string) hex2bin('00010000000000000000000000000000000000000000000000007b6e58354d628a48cef24e42e9dc154beab371176f8ba96a'),
			]],
		);
	}

	private function v2Request(string $userName): AccessRequest
	{
		return new AccessRequest(
			new Peer('127.0.0.1', 'testing123'),
			PacketCode::AccessRequest,
			authenticator: random_bytes(16),
			standardAttributes: [UserName::type() => $userName],
			vendorAttributes: [MsChap2Response::vendorId() => [
				MsChapChallenge::type()  => (string) hex2bin(self::AUTHENTICATOR_CHALLENGE),
				MsChap2Response::type()  => "\x07\x00".hex2bin(self::PEER_CHALLENGE).str_repeat("\0", 8).hex2bin(self::NT_RESPONSE),
			]],
		);
	}
}
