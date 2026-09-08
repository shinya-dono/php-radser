<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * shared key RouterOS uses for wireless mesh/Nstreme-dual (MPKey) peer authentication with this
 * client.
 */
class MikrotikWirelessMpkey extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Wireless-MPKey';
	}

	#[Override]
	public static function type(): int
	{
		return 20;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
