<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

/**
 * opaque data the server sends in an Access-Challenge that the client must echo back unmodified
 * in the follow-up Access-Request, letting the server resume a multi-step authentication exchange.
 */
class State extends OctetsAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'State';
	}

	#[Override]
	public static function type(): int
	{
		return 24;
	}
}
