<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

/**
 * CHAP challenge sent by the NAS to a PPP CHAP peer; carried alongside CHAP-Password when the
 * challenge doesn't fit in the Access-Request Authenticator.
 */
class ChapChallenge extends OctetsAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'CHAP-Challenge';
	}

	#[Override]
	public static function type(): int
	{
		return 60;
	}
}
