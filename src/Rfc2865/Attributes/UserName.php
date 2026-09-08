<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * name of the user being authenticated; present in nearly every Access-Request.
 *
 * @example 'jdoe'
 */
class UserName extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'User-Name';
	}

	#[Override]
	public static function type(): int
	{
		return 1;
	}
}
