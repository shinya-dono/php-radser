<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * AppleTalk network number to probe for and allocate to the user for the session; can be sent
 * more than once to list candidate networks.
 */
class FramedAppletalkNetwork extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Framed-AppleTalk-Network';
	}

	#[Override]
	public static function type(): int
	{
		return 38;
	}
}
