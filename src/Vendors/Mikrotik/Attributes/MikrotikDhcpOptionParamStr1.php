<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * first free-form string parameter passed through to the RouterOS DHCP option set named by
 * Mikrotik-DHCP-Option-Set (see also {@see MikortikDhcpOptionParamStr2}).
 */
class MikrotikDhcpOptionParamStr1 extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-DHCP-Option-Param-STR1';
	}

	#[Override]
	public static function type(): int
	{
		return 24;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
