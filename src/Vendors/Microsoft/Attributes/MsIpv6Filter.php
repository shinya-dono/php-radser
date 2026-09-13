<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

class MsIpv6Filter extends OctetsAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-IPv6-Filter';
	}

	#[Override]
	public static function type(): int
	{
		return 51;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
