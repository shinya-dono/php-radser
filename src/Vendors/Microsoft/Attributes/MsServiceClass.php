<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

class MsServiceClass extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-Service-Class';
	}

	#[Override]
	public static function type(): int
	{
		return 42;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
