<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * upper signal-strength bound (dBm) the client must stay under; RouterOS disconnects the client
 * if its signal rises above this.
 *
 * @example '-50'
 */
class MikrotikWirelessMaxsignal extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Wireless-Maxsignal';
	}

	#[Override]
	public static function type(): int
	{
		return 29;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
