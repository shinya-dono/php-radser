<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * name of the RouterOS IPv6 address pool to delegate a prefix from for DHCPv6 prefix delegation.
 *
 * @example 'ipv6-pd-pool'
 */
class MikrotikDelegatedIpv6Pool extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Delegated-IPv6-Pool';
	}

	#[Override]
	public static function type(): int
	{
		return 22;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}
}
