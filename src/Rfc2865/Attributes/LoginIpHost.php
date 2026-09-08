<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IpAddrAttribute;

/**
 * host the user should be connected to when Service-Type is Login; 0.0.0.0 means the NAS should
 * pick the host itself.
 *
 * @example '192.0.2.55'
 */
class LoginIpHost extends IpAddrAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Login-IP-Host';
	}

	#[Override]
	public static function type(): int
	{
		return 14;
	}
}
