<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * comment RouterOS sets on the wireless registration-table entry created for this client.
 */
class MikrotikWirelessComment extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Wireless-Comment';
	}

	#[Override]
	public static function type(): int
	{
		return 21;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
