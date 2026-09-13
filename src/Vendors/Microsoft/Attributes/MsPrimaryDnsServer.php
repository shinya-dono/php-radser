<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IpAddrAttribute;

class MsPrimaryDnsServer extends IpAddrAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-Primary-DNS-Server';
	}

	#[Override]
	public static function type(): int
	{
		return 28;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
