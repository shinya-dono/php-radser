<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2866\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Rfc2866\AcctStatusTypeValue;

/**
 * marks what kind of accounting event this record represents, e.g. Start, Stop, Interim-Update,
 * Accounting-On, Accounting-Off.
 *
 * @extends EnumAttribute<AcctStatusTypeValue>
 */
class AcctStatusType extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Acct-Status-Type';
	}

	#[Override]
	public static function type(): int
	{
		return 40;
	}

	#[Override]
	protected static function enum(): string
	{
		return AcctStatusTypeValue::class;
	}
}
