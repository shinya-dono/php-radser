<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Tests\Support;

use Shinya\PhpRadser\Peer;
use PHPUnit\Framework\TestCase;
use Shinya\PhpRadser\PacketCode;
use Shinya\PhpRadser\Support\Chap;
use Shinya\PhpRadser\Contracts\AccessRequest;
use PHPUnit\Framework\Attributes\CoversNothing;
use Shinya\PhpRadser\Rfc2865\Attributes\ChapPassword;
use Shinya\PhpRadser\Rfc2865\Attributes\ChapChallenge;

/**
 * @internal
 */
#[CoversNothing]
final class ChapTest extends TestCase
{
	/**
	 * captured off the wire from radclient 3.2.10 sending `CHAP-Password = hunter2`, which answers
	 * the Request Authenticator since it sends no CHAP-Challenge.
	 */
	public function testAResponseToTheRequestAuthenticatorVerifies(): void
	{
		$accessRequest = new AccessRequest(
			new Peer('127.0.0.1', 'testing123'),
			PacketCode::AccessRequest,
			authenticator: (string) hex2bin('c46aaa33ce78478fc9c47749e18a0801'),
			standardAttributes: [ChapPassword::type() => (string) hex2bin('76be30652c99d15763cc771f7802091f78')],
		);

		$this->assertTrue(Chap::verify($accessRequest, 'hunter2'));
		$this->assertFalse(Chap::verify($accessRequest, 'hunter3'));
	}

	public function testChapChallengeTakesPrecedenceOverTheRequestAuthenticator(): void
	{
		$challenge = str_repeat("\x42", 16);

		$accessRequest = new AccessRequest(
			new Peer('127.0.0.1', 'testing123'),
			PacketCode::AccessRequest,
			authenticator: random_bytes(16),
			standardAttributes: [
				ChapPassword::type()  => "\x09".md5("\x09".'hunter2'.$challenge, binary: true),
				ChapChallenge::type() => $challenge,
			],
		);

		$this->assertTrue(Chap::verify($accessRequest, 'hunter2'));
	}

	public function testARequestWithoutChapPasswordDoesNotVerify(): void
	{
		$this->assertFalse(Chap::verify(new AccessRequest(new Peer('127.0.0.1', 'testing123'), PacketCode::AccessRequest, authenticator: random_bytes(16)), 'hunter2'));
	}
}
