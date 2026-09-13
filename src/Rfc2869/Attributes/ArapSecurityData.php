<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2869\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

class ArapSecurityData extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'ARAP-Security-Data';
	}

	#[Override]
	public static function type(): int
	{
		return 74;
	}
}
