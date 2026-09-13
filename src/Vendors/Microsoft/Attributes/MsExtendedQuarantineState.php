<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Vendors\Microsoft\MsExtendedQuarantineStateValue;

/**
 * @extends EnumAttribute<MsExtendedQuarantineStateValue>
 */
class MsExtendedQuarantineState extends EnumAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-Extended-Quarantine-State';
	}

	#[Override]
	public static function type(): int
	{
		return 57;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}

	#[Override]
	protected static function enum(): string
	{
		return MsExtendedQuarantineStateValue::class;
	}
}
