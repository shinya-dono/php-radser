<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * name of a place to be called by the NAS for callback, used when Service-Type requests Callback
 * Login/Framed and Callback-Number alone isn't sufficient.
 *
 * @example 'lab_router'
 */
class CallbackId extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Callback-Id';
	}

	#[Override]
	public static function type(): int
	{
		return 20;
	}
}
