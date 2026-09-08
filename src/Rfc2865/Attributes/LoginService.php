<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Rfc2865\LoginServiceValue;

/**
 * service the NAS should use to connect the user to the login host, e.g. Telnet, Rlogin, TCP
 * Clear, or LAT; only meaningful with Service-Type = Login.
 *
 * @extends EnumAttribute<LoginServiceValue>
 */
class LoginService extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Login-Service';
	}

	#[Override]
	public static function type(): int
	{
		return 15;
	}

	#[Override]
	protected static function enum(): string
	{
		return LoginServiceValue::class;
	}
}
