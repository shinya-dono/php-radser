<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

/**
 * CHAP-Id byte followed by the 16-octet CHAP response, the user's answer to the CHAP challenge;
 * used instead of User-Password for CHAP authentication.
 */
class ChapPassword extends OctetsAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'CHAP-Password';
	}

	#[Override]
	public static function type(): int
	{
		return 3;
	}
}
