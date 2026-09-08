<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Vendors\Mikrotik\MikrotikWirelessVlanidtypeValue;

/**
 * tag type used for the VLAN ID in {@see MikrotikWirelessVlanid}, e.g. 802.1Q or 802.1ad (Q-in-Q).
 *
 * @extends EnumAttribute<MikrotikWirelessVlanidtypeValue>
 */
class MikrotikWirelessVlanidtype extends EnumAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Wireless-VLANIDtype';
	}

	#[Override]
	public static function type(): int
	{
		return 27;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}

	#[Override]
	protected static function enum(): string
	{
		return MikrotikWirelessVlanidtypeValue::class;
	}
}
