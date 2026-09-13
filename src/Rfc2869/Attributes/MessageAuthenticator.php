<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2869\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

/**
 * HMAC-MD5 over the whole packet, keyed on the shared secret (RFC 3579 3.2). PacketCodec computes
 * and verifies it itself - pushing one by hand does nothing.
 */
class MessageAuthenticator extends OctetsAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Message-Authenticator';
	}

	#[Override]
	public static function type(): int
	{
		return 80;
	}
}
