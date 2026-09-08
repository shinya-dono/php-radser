<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * string identifying the NAS originating the request, used as an alternative to NAS-IP-Address
 * when the NAS has no fixed address.
 *
 * @example 'nas01.example.net'
 */
class NasIdentifier extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'NAS-Identifier';
	}

	#[Override]
	public static function type(): int
	{
		return 32;
	}
}
