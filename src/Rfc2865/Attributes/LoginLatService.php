<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * LAT service to connect the user to when Login-Service is LAT.
 *
 * @example 'FINANCE'
 */
class LoginLatService extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Login-LAT-Service';
	}

	#[Override]
	public static function type(): int
	{
		return 34;
	}
}
