<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * a static route the NAS should install for this user, in the form "dest_ip dest_mask [gateway_ip
 * [metric]]"; can be sent multiple times.
 *
 * @example '192.0.2.0/24 192.0.2.1 1'
 */
class FramedRoute extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Framed-Route';
	}

	#[Override]
	public static function type(): int
	{
		return 22;
	}
}
