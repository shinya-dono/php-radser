<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Vendors\Mikrotik\MikrotikWirelessEncAlgoValue;

/**
 * wireless encryption algorithm RouterOS should use for the client, e.g. WEP-40, WEP-104, TKIP,
 * or AES-CCM.
 *
 * @extends EnumAttribute<MikrotikWirelessEncAlgoValue>
 */
class MikrotikWirelessEncAlgo extends EnumAttribute implements VendorAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Mikrotik-Wireless-Enc-Algo';
	}

	#[Override]
	public static function type(): int
	{
		return 6;
	}

	#[Override]
	public static function vendorId(): int
	{
		return 14988;
	}

	#[Override]
	protected static function enum(): string
	{
		return MikrotikWirelessEncAlgoValue::class;
	}
}
