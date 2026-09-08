<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Rfc2865\FramedCompressionValue;

/**
 * compression protocol to use on the link, e.g. Van Jacobson TCP/IP header compression for a PPP
 * or SLIP session.
 *
 * @extends EnumAttribute<FramedCompressionValue>
 */
class FramedCompression extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Framed-Compression';
	}

	#[Override]
	public static function type(): int
	{
		return 13;
	}

	#[Override]
	protected static function enum(): string
	{
		return FramedCompressionValue::class;
	}
}
