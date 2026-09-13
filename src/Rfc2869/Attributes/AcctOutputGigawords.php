<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2869\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

class AcctOutputGigawords extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Acct-Output-Gigawords';
	}

	#[Override]
	public static function type(): int
	{
		return 53;
	}
}
