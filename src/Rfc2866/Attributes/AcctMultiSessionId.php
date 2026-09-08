<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2866\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * unique identifier shared by all sessions belonging to the same multilink PPP bundle, letting
 * accounting records be grouped together.
 */
class AcctMultiSessionId extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Acct-Multi-Session-Id';
	}

	#[Override]
	public static function type(): int
	{
		return 50;
	}
}
