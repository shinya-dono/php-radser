<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

/**
 * opaque data attached by a proxy server; every server forwarding the request must echo it back
 * unmodified in the reply so the proxy can match it.
 */
class ProxyState extends OctetsAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Proxy-State';
	}

	#[Override]
	public static function type(): int
	{
		return 33;
	}
}
