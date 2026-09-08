<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * maximum number of seconds of service to be provided before the session is terminated.
 *
 * @example 3600
 */
class SessionTimeout extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Session-Timeout';
	}

	#[Override]
	public static function type(): int
	{
		return 27;
	}
}
