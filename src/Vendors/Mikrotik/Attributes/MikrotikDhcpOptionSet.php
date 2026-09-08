<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * name of the RouterOS DHCP option set to hand to the client, parameterized by
 * Mikrotik-DHCP-Option-Param-STR1/STR2.
 *
 * @example 'pxe-boot'
 */
class MikrotikDhcpOptionSet extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-DHCP-Option-Set';
	}

	#[Override]
	public static function type(): int
	{
		return 23;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
