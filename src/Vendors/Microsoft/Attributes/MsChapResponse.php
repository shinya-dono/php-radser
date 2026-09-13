<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

/**
 * an MS-CHAPv1 client's answer to MS-CHAP-Challenge: ident, flags, then the 24-octet LM and NT
 * responses.
 *
 * @see \Shinya\PhpRadser\Support\MsChap::verifyV1()
 */
class MsChapResponse extends OctetsAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-CHAP-Response';
	}

	#[Override]
	public static function type(): int
	{
		return 1;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
