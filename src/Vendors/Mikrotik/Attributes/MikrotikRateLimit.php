<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * RouterOS simple-queue rate limit string for the client, in the form "rx-rate/tx-rate" with
 * optional burst/priority fields.
 *
 * @example '2M/10M'
 */
class MikrotikRateLimit extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Rate-Limit';
	}

	#[Override]
	public static function type(): int
	{
		return 8;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
