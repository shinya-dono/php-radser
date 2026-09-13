<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

// note: 'encrypt=2' flag ignored, this attribute is generated as plain text - its crypto is unscoped

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

class MsMppeSendKey extends OctetsAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-MPPE-Send-Key';
	}

	#[Override]
	public static function type(): int
	{
		return 16;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
