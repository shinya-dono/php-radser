<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

/**
 * why an MS-CHAP login was refused, on the Access-Reject: the response's ident followed by
 * "E=<windows error> R=<retry>", plus "C=<challenge> V=3" for v2.
 *
 * @see \Shinya\PhpRadser\Support\MsChap::error()
 */
class MsChapError extends StringAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-CHAP-Error';
	}

	#[Override]
	public static function type(): int
	{
		return 2;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
