<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * name of the RouterOS firewall address-list the client's address should be added to for the
 * duration of the session.
 *
 * @example 'hotspot-authenticated'
 */
class MikrotikAddressList extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Address-List';
	}

	#[Override]
	public static function type(): int
	{
		return 19;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
