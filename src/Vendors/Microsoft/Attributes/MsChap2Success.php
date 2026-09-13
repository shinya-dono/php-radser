<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

/**
 * the server's proof it knew the password too, required on an MS-CHAPv2 Access-Accept: the
 * response's ident followed by "S=" and 40 hex digits.
 *
 * @see \Shinya\PhpRadser\Support\MsChap::verifyV2()
 */
class MsChap2Success extends OctetsAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-CHAP2-Success';
	}

	#[Override]
	public static function type(): int
	{
		return 26;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
