<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

/**
 * the challenge the NAS put to an MS-CHAP client: 8 octets for v1, 16 for v2.
 *
 * @see \Shinya\PhpRadser\Support\MsChap
 */
class MsChapChallenge extends OctetsAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-CHAP-Challenge';
	}

	#[Override]
	public static function type(): int
	{
		return 11;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
