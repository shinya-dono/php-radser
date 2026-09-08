<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2866\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Rfc2866\AcctTerminateCauseValue;

/**
 * why the session ended, e.g. User-Request, Idle-Timeout, Lost-Carrier; sent in the accounting
 * Stop record.
 *
 * @extends EnumAttribute<AcctTerminateCauseValue>
 */
class AcctTerminateCause extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Acct-Terminate-Cause';
	}

	#[Override]
	public static function type(): int
	{
		return 49;
	}

	#[Override]
	protected static function enum(): string
	{
		return AcctTerminateCauseValue::class;
	}
}
