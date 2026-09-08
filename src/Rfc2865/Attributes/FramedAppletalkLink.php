<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * AppleTalk network number the NAS should use on the point-to-point link with the user, only used
 * when the NAS has no other AppleTalk link assigned.
 */
class FramedAppletalkLink extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Framed-AppleTalk-Link';
	}

	#[Override]
	public static function type(): int
	{
		return 37;
	}
}
