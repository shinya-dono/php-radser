<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

class MsChapCpw1 extends OctetsAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-CHAP-CPW-1';
	}

	#[Override]
	public static function type(): int
	{
		return 3;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
