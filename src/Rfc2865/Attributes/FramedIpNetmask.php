<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IpAddrAttribute;

/**
 * IP netmask to configure for the user when they're acting as a router to a single host (e.g.
 * over SLIP), used to build a static route on the NAS.
 *
 * @example '255.255.255.0'
 */
class FramedIpNetmask extends IpAddrAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Framed-IP-Netmask';
	}

	#[Override]
	public static function type(): int
	{
		return 9;
	}
}
