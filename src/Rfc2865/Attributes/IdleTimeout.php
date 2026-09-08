<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * maximum number of consecutive seconds of idle connection allowed before the NAS terminates the
 * session.
 *
 * @example 600
 */
class IdleTimeout extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Idle-Timeout';
	}

	#[Override]
	public static function type(): int
	{
		return 28;
	}
}
