<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2866\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * number of octets received from the user over the course of the session, sent in
 * Accounting-Request Stop/Interim-Update.
 */
class AcctInputOctets extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Acct-Input-Octets';
	}

	#[Override]
	public static function type(): int
	{
		return 42;
	}
}
