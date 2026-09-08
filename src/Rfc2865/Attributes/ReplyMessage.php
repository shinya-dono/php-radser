<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * text to be displayed to the user; may be sent in Access-Accept, Access-Reject, or
 * Access-Challenge, and repeated to build multi-line messages.
 *
 * @example 'Your account expires in 3 days'
 */
class ReplyMessage extends StringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Reply-Message';
	}

	#[Override]
	public static function type(): int
	{
		return 18;
	}
}
