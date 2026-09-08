<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\IpAddrAttribute;

/**
 * IPX network number to configure for the user's session.
 */
class FramedIpxNetwork extends IpAddrAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Framed-IPX-Network';
	}

	#[Override]
	public static function type(): int
	{
		return 23;
	}
}
