<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

class MsLinkUtilizationThreshold extends IntegerAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-Link-Utilization-Threshold';
	}

	#[Override]
	public static function type(): int
	{
		return 14;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
