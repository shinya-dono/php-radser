<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * name of the RouterOS PPP/hotspot user group to place the client in, controlling which local
 * group policy (rate limits, address pool) applies.
 *
 * @example 'default'
 */
class MikrotikGroup extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Group';
	}

	#[Override]
	public static function type(): int
	{
		return 3;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
