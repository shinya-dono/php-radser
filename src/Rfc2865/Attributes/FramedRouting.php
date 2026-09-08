<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Rfc2865\FramedRoutingValue;

/**
 * routing method the NAS should use on the user's interface, e.g. none, broadcast, listen, or
 * broadcast-and-listen.
 *
 * @extends EnumAttribute<FramedRoutingValue>
 */
class FramedRouting extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Framed-Routing';
	}

	#[Override]
	public static function type(): int
	{
		return 10;
	}

	#[Override]
	protected static function enum(): string
	{
		return FramedRoutingValue::class;
	}
}
