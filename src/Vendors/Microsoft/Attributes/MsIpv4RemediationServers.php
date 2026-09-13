<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft\Attributes;

use Override;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\OctetsAttribute;

class MsIpv4RemediationServers extends OctetsAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'MS-IPv4-Remediation-Servers';
	}

	#[Override]
	public static function type(): int
	{
		return 52;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 311;
	}
}
