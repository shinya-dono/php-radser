<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * name of the RouterOS switch/bridge filter rule set to apply to the client's port.
 */
class MikrotikSwitchingFilter extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Switching-Filter';
	}

	#[Override]
	public static function type(): int
	{
		return 30;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
