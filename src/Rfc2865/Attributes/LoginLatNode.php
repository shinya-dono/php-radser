<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * LAT node to connect the user to when Login-Service is LAT.
 *
 * @example 'VAX1'
 */
class LoginLatNode extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Login-LAT-Node';
	}

	#[Override]
	public static function type(): int
	{
		return 35;
	}
}
