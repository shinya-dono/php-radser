<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2869\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

class ArapSecurity extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'ARAP-Security';
	}

	#[Override]
	public static function type(): int
	{
		return 73;
	}
}
