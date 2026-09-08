<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * whether RouterOS should forward traffic between this wireless client and other clients on the
 * same access point (1 = allowed, 0 = blocked).
 *
 * @example 0
 */
class MikrotikWirelessForward extends IntegerAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Wireless-Forward';
	}

	#[Override]
	public static function type(): int
	{
		return 4;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
