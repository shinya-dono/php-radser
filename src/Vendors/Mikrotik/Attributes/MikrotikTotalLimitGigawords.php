<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * upper 32 bits of Mikrotik-Total-Limit, i.e. the number of times that 32-bit counter has wrapped
 * past 4GiB; combine as (gigawords << 32) + limit to get the full combined in+out byte limit.
 */
class MikrotikTotalLimitGigawords extends IntegerAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Total-Limit-Gigawords';
	}

	#[Override]
	public static function type(): int
	{
		return 18;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
