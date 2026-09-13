<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

class MsOldArapPassword extends OctetsAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-Old-ARAP-Password';
	}

	#[Override]
	public static function type(): int
	{
		return 19;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
