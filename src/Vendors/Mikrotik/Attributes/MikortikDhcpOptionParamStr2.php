<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * second free-form string parameter passed through to the RouterOS DHCP option set named by
 * Mikrotik-DHCP-Option-Set (identifier keeps the vendor's own "Mikortik" typo, see also
 * {@see MikrotikDhcpOptionParamStr1}).
 */
class MikortikDhcpOptionParamStr2 extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikortik-DHCP-Option-Param-STR2';
	}

	#[Override]
	public static function type(): int
	{
		return 25;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
