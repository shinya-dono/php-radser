<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Vendors\Microsoft\MsAfwZoneValue;

/**
 * @extends EnumAttribute<MsAfwZoneValue>
 */
class MsAfwZone extends EnumAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-AFW-Zone';
	}

	#[Override]
	public static function type(): int
	{
		return 48;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}

	#[Override]
	protected static function enum(): string
	{
		return MsAfwZoneValue::class;
	}
}
