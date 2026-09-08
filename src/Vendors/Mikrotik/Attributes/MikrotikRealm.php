<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * authentication realm to associate with the client, used by RouterOS to route the request to the
 * right upstream RADIUS domain.
 *
 * @example 'example.net'
 */
class MikrotikRealm extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Realm';
	}

	#[Override]
	public static function type(): int
	{
		return 9;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
