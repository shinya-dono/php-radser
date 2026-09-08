<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * identifier of the endpoint the user connected from, e.g. the calling party's phone number (ANI)
 * or the client's MAC address for wireless.
 *
 * @example '00-10-A4-13-9A-21'
 */
class CallingStationId extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Calling-Station-Id';
	}

	#[Override]
	public static function type(): int
	{
		return 31;
	}
}
