<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * lower signal-strength bound (dBm) the client must stay above; RouterOS disconnects the client
 * if its signal drops below this.
 *
 * @example '-85'
 */
class MikrotikWirelessMinsignal extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Wireless-Minsignal';
	}

	#[Override]
	public static function type(): int
	{
		return 28;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
