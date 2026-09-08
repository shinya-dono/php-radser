<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

// note: 'encrypt=1' is only undone on the way in, via getPlainText() - hiding an outbound
// value again (proxying an Access-Request upstream) is unscoped

use Override;
use Shinya\PhpRadser\Attributes\EncryptedStringAttribute;

/**
 * the user's password for PAP-style authentication; it travels hidden behind the shared secret,
 * so read() gives back ciphertext - call getPlainText() for the password the NAS actually sent.
 */
class UserPassword extends EncryptedStringAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'User-Password';
	}

	#[Override]
	public static function type(): int
	{
		return 2;
	}
}
