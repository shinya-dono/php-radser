<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * name of the filter list the NAS should apply to this user's traffic; the filter itself lives in
 * NAS configuration, not in this attribute.
 *
 * @example 'std-firewall'
 */
class FilterId extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Filter-Id';
	}

	#[Override]
	public static function type(): int
	{
		return 11;
	}
}
