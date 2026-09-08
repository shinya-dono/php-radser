<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * whether RouterOS should skip 802.1X re-authentication for this wireless client (1 = skip, 0 =
 * require).
 *
 * @example 1
 */
class MikrotikWirelessSkipDot1x extends IntegerAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Wireless-Skip-Dot1x';
	}

	#[Override]
	public static function type(): int
	{
		return 5;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
