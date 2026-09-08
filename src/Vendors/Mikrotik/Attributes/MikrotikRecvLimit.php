<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * total number of bytes the client is allowed to receive (download) before RouterOS terminates
 * the session; the low 32 bits of the limit, paired with {@see MikrotikRecvLimitGigawords} for the
 * high bits.
 */
class MikrotikRecvLimit extends IntegerAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Recv-Limit';
	}

	#[Override]
	public static function type(): int
	{
		return 1;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
