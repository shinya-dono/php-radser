<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Tests\Attributes;

use Shinya\PhpRadser\Peer;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\ExpectationFailedException;
use Shinya\PhpRadser\Rfc2865\Attributes\UserPassword;
use Shinya\PhpRadser\Exceptions\RadiusRuntimeException;

/**
 * @internal
 */
#[CoversNothing]
final class EncryptedStringAttributeTest extends TestCase
{
	private const string AUTHENTICATOR = '0123456789abcdef';

	/**
	 * @throws ExpectationFailedException
	 * @throws RadiusRuntimeException
	 */
	public function testMakeHoldsPlaintextAndHidesItForTheWire(): void
	{
		$userPassword = UserPassword::make('foobar');
		$userPassword->setPeer($peer = new Peer('127.0.0.1', 'testing123'));
		$userPassword->setAuthenticator(self::AUTHENTICATOR);

		$this->assertSame('foobar', $userPassword->getPlainText());

		$onTheWire = $userPassword->dehydrate();
		$this->assertIsString($onTheWire);
		$this->assertNotSame('foobar', $onTheWire);
		$this->assertSame(16, strlen($onTheWire)); // null-padded up to one block

		// what we hid is what the other side reveals
		$received = UserPassword::hydrate($onTheWire);
		$received->setPeer($peer);
		$received->setAuthenticator(self::AUTHENTICATOR);

		$this->assertSame('foobar', $received->getPlainText());
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws RadiusRuntimeException
	 */
	public function testHydratedValueIsPassedThroughUntouched(): void
	{
		$userPassword = UserPassword::hydrate($raw = str_repeat("\x2a", 16));

		$this->assertSame($raw, $userPassword->read());
		$this->assertSame($raw, $userPassword->dehydrate());
		$this->assertNull($userPassword->getPlainText()); // no peer injected, no key
	}

	/**
	 * @throws RadiusRuntimeException
	 */
	public function testDehydrateRefusesToSendAHandBuiltValueInTheClear(): void
	{
		$this->expectException(RadiusRuntimeException::class);

		UserPassword::make('foobar')->dehydrate();
	}
}
