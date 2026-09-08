<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * URL RouterOS advertises to the client, e.g. a hotspot redirect or captive portal landing page.
 *
 * @example 'https://example.net/welcome'
 */
class MikrotikAdvertiseUrl extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Advertise-URL';
	}

	#[Override]
	public static function type(): int
	{
		return 12;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
