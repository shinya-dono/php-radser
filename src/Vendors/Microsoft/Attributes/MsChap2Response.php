<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

/**
 * an MS-CHAPv2 client's answer to MS-CHAP-Challenge: ident, flags, its own 16-octet challenge,
 * 8 reserved octets and the 24-octet NT response.
 *
 * @see \Shinya\PhpRadser\Support\MsChap::verifyV2()
 */
class MsChap2Response extends OctetsAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-CHAP2-Response';
	}

	#[Override]
	public static function type(): int
	{
		return 25;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
