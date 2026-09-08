<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2866\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * number of seconds the user has received service for, sent in Accounting-Request
 * Stop/Interim-Update.
 *
 * @example 1800
 */
class AcctSessionTime extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Acct-Session-Time';
	}

	#[Override]
	public static function type(): int
	{
		return 46;
	}
}
