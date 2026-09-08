<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IpAddrAttribute;

/**
 * IP address of the NAS originating the request; one of NAS-IP-Address or NAS-Identifier must be
 * present in every Access-Request.
 *
 * @example '203.0.113.5'
 */
class NasIpAddress extends IpAddrAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'NAS-IP-Address';
	}

	#[Override]
	public static function type(): int
	{
		return 4;
	}
}
