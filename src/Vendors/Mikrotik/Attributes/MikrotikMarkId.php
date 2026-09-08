<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * name RouterOS should use for the connection/routing mark it applies to this client's traffic,
 * used to hook the session into queues or routing rules.
 *
 * @example 'user-jdoe'
 */
class MikrotikMarkId extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Mark-Id';
	}

	#[Override]
	public static function type(): int
	{
		return 11;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
