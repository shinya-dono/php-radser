<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

class MsNewArapPassword extends OctetsAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-New-ARAP-Password';
	}

	#[Override]
	public static function type(): int
	{
		return 20;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
