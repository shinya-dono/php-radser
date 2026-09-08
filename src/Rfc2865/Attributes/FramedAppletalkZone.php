<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * AppleTalk default zone to be used for the user's session.
 *
 * @example 'Engineering'
 */
class FramedAppletalkZone extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Framed-AppleTalk-Zone';
	}

	#[Override]
	public static function type(): int
	{
		return 39;
	}
}
