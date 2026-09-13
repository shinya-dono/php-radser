<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2869\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

class ConfigurationToken extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Configuration-Token';
	}

	#[Override]
	public static function type(): int
	{
		return 78;
	}
}
