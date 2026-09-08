<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * total number of bytes (download + upload combined) the client is allowed before RouterOS
 * terminates the session; the low 32 bits of the limit, paired with
 * {@see MikrotikTotalLimitGigawords} for the high bits.
 */
class MikrotikTotalLimit extends IntegerAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Total-Limit';
	}

	#[Override]
	public static function type(): int
	{
		return 17;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
