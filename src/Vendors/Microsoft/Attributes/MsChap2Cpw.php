<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

class MsChap2Cpw extends OctetsAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-CHAP2-CPW';
	}

	#[Override]
	public static function type(): int
	{
		return 27;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
