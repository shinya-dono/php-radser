<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * WPA/WPA2 pre-shared key RouterOS assigns to this wireless client.
 */
class MikrotikWirelessPsk extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Wireless-PSK';
	}

	#[Override]
	public static function type(): int
	{
		return 16;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
