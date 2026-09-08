<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * VLAN ID RouterOS should tag this wireless client's traffic with; paired with
 * {@see MikrotikWirelessVlanidtype} for the tag type.
 *
 * @example 100
 */
class MikrotikWirelessVlanid extends IntegerAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Wireless-VLANID';
	}

	#[Override]
	public static function type(): int
	{
		return 26;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
