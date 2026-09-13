<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

class MsUserSecurityIdentity extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-User-Security-Identity';
	}

	#[Override]
	public static function type(): int
	{
		return 40;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
