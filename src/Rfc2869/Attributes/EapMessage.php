<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2869\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

class EapMessage extends OctetsAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'EAP-Message';
	}

	#[Override]
	public static function type(): int
	{
		return 79;
	}
}
