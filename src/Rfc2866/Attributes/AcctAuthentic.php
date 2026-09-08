<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2866\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Rfc2866\AcctAuthenticValue;

/**
 * how the user was authenticated, e.g. by RADIUS, locally on the NAS, or by some other remote
 * mechanism.
 *
 * @extends EnumAttribute<AcctAuthenticValue>
 */
class AcctAuthentic extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Acct-Authentic';
	}

	#[Override]
	public static function type(): int
	{
		return 45;
	}

	#[Override]
	protected static function enum(): string
	{
		return AcctAuthenticValue::class;
	}
}
