<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

class MsLinkDropTimeLimit extends IntegerAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-Link-Drop-Time-Limit';
	}

	#[Override]
	public static function type(): int
	{
		return 15;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
