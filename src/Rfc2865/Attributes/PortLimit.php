<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IntegerAttribute;

/**
 * maximum number of ports the NAS should provide the user, e.g. for multilink PPP
 * bandwidth-on-demand.
 *
 * @example 2
 */
class PortLimit extends IntegerAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Port-Limit';
	}

	#[Override]
	public static function type(): int
	{
		return 62;
	}
}
