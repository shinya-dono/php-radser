<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * identifier of the endpoint the user connected to, e.g. the DNIS number dialed or the access
 * point's MAC address for wireless.
 *
 * @example '00-10-A4-23-19-C0'
 */
class CalledStationId extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Called-Station-Id';
	}

	#[Override]
	public static function type(): int
	{
		return 30;
	}
}
