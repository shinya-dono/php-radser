<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2869\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

class FramedPool extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Framed-Pool';
	}

	#[Override]
	public static function type(): int
	{
		return 88;
	}
}
