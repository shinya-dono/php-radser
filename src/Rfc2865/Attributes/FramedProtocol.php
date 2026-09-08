<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Rfc2865\FramedProtocolValue;

/**
 * framing to be used for framed access, e.g. PPP or SLIP; sent by the NAS to request a protocol
 * and returned by the server to confirm/override it.
 *
 * @extends EnumAttribute<FramedProtocolValue>
 */
class FramedProtocol extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Framed-Protocol';
	}

	#[Override]
	public static function type(): int
	{
		return 7;
	}

	#[Override]
	protected static function enum(): string
	{
		return FramedProtocolValue::class;
	}
}
