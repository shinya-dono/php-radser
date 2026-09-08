<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * wireless encryption key RouterOS assigns to the client, matching the algorithm in
 * {@see MikrotikWirelessEncAlgo}.
 */
class MikrotikWirelessEncKey extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Wireless-Enc-Key';
	}

	#[Override]
	public static function type(): int
	{
		return 7;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
