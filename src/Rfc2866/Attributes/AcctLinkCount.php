<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2866\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * number of links currently part of the multilink session at the time this accounting record is
 * generated.
 */
class AcctLinkCount extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Acct-Link-Count';
	}

	#[Override]
	public static function type(): int
	{
		return 51;
	}
}
