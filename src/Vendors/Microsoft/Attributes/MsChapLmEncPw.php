<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

class MsChapLmEncPw extends OctetsAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-CHAP-LM-Enc-PW';
	}

	#[Override]
	public static function type(): int
	{
		return 5;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
