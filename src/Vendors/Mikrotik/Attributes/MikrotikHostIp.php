<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IpAddrAttribute;

/**
 * IP address of the hotspot host/gateway the client is bound to.
 *
 * @example '192.168.88.1'
 */
class MikrotikHostIp extends IpAddrAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Host-IP';
	}

	#[Override]
	public static function type(): int
	{
		return 10;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
