<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * physical or virtual port number of the NAS that is authenticating the user.
 *
 * @example 20
 */
class NasPort extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'NAS-Port';
	}

	#[Override]
	public static function type(): int
	{
		return 5;
	}
}
