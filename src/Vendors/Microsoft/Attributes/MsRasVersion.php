<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

class MsRasVersion extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-RAS-Version';
	}

	#[Override]
	public static function type(): int
	{
		return 18;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
