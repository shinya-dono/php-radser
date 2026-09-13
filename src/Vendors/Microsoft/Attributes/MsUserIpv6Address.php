<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\Ipv6AddrAttribute;

class MsUserIpv6Address extends Ipv6AddrAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-User-IPv6-Address';
	}

	#[Override]
	public static function type(): int
	{
		return 62;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
