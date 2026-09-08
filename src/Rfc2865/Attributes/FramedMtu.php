<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * maximum transmission unit, in bytes, to configure for the user's framed connection.
 *
 * @example 1500
 */
class FramedMtu extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Framed-MTU';
	}

	#[Override]
	public static function type(): int
	{
		return 12;
	}
}
