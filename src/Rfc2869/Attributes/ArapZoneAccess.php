<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2869\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Rfc2869\ArapZoneAccessValue;

/**
 * @extends EnumAttribute<ArapZoneAccessValue>
 */
class ArapZoneAccess extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'ARAP-Zone-Access';
	}

	#[Override]
	public static function type(): int
	{
		return 72;
	}

	#[Override]
	protected static function enum(): string
	{
		return ArapZoneAccessValue::class;
	}
}
