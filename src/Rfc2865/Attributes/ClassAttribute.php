<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

/**
 * opaque data sent by the server in an Access-Accept; the NAS must echo it back unmodified in
 * every Accounting-Request for the session, letting the server correlate accounting records with
 * the authentication that started them.
 */
class ClassAttribute extends OctetsAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Class';
	}

	#[Override]
	public static function type(): int
	{
		return 25;
	}
}
