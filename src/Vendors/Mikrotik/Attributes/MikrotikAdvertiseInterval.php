<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * interval, in seconds, between IPv6 router advertisements RouterOS sends to the client.
 *
 * @example 600
 */
class MikrotikAdvertiseInterval extends IntegerAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Advertise-Interval';
	}

	#[Override]
	public static function type(): int
	{
		return 13;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
