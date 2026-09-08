<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Rfc2865\TerminationActionValue;

/**
 * what the NAS should do when the specified service completes, e.g. simply terminate, or re-issue
 * a new Access-Request.
 *
 * @extends EnumAttribute<TerminationActionValue>
 */
class TerminationAction extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Termination-Action';
	}

	#[Override]
	public static function type(): int
	{
		return 29;
	}

	#[Override]
	protected static function enum(): string
	{
		return TerminationActionValue::class;
	}
}
