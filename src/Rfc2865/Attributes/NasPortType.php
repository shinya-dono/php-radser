<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Rfc2865\NasPortTypeValue;

/**
 * physical type of the NAS port used to authenticate the user, e.g. Async, ISDN, Virtual,
 * Wireless-802.11.
 *
 * @extends EnumAttribute<NasPortTypeValue>
 */
class NasPortType extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'NAS-Port-Type';
	}

	#[Override]
	public static function type(): int
	{
		return 61;
	}

	#[Override]
	protected static function enum(): string
	{
		return NasPortTypeValue::class;
	}
}
