<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

class MsQuarantineSessionTimeout extends IntegerAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-Quarantine-Session-Timeout';
	}

	#[Override]
	public static function type(): int
	{
		return 37;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
