<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2866\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * seconds the NAS has been trying to send this accounting record, can be added to the record's
 * timestamp to recover when it actually happened.
 *
 * @example 3
 */
class AcctDelayTime extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Acct-Delay-Time';
	}

	#[Override]
	public static function type(): int
	{
		return 41;
	}
}
