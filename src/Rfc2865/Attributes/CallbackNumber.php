<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * dialing string the NAS should use to call the user back, sent when Service-Type requests a
 * callback service.
 *
 * @example '+15551234567'
 */
class CallbackNumber extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Callback-Number';
	}

	#[Override]
	public static function type(): int
	{
		return 19;
	}
}
