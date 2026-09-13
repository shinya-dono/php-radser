<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

class MsTsgDeviceRedirection extends IntegerAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-TSG-Device-Redirection';
	}

	#[Override]
	public static function type(): int
	{
		return 63;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
