<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

/**
 * LAT group codes the user is authorized to use, encoded as the 256-bit bitmap DEC's LAT
 * documentation defines; used with Login-Service = LAT.
 */
class LoginLatGroup extends OctetsAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Login-LAT-Group';
	}

	#[Override]
	public static function type(): int
	{
		return 36;
	}
}
