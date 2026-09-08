<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Rfc2865\LoginTcpPortValue;

/**
 * TCP port the user should be connected to when Login-Service is Telnet, Rlogin, or TCP Clear.
 *
 * @extends EnumAttribute<LoginTcpPortValue>
 */
class LoginTcpPort extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Login-TCP-Port';
	}

	#[Override]
	public static function type(): int
	{
		return 16;
	}

	#[Override]
	protected static function enum(): string
	{
		return LoginTcpPortValue::class;
	}
}
