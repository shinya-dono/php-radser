<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IpAddrAttribute;

/**
 * IP address to configure for the user; 255.255.255.255 means the NAS should pick an address from
 * its own pool, 255.255.255.254 means the NAS should accept the address the user requested.
 *
 * @example '192.0.2.10'
 */
class FramedIpAddress extends IpAddrAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Framed-IP-Address';
	}

	#[Override]
	public static function type(): int
	{
		return 8;
	}
}
