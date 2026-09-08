<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;

/**
 * 8-bit unsigned integer.
 */
abstract class ByteAttribute extends IntegerLikeAttribute
{
	#[Override]
	protected static function unpackWire(string $raw): int|null
	{
		return self::unpackUint('C', 1, $raw);
	}

	#[Override]
	protected static function packWire(int $value): string
	{
		return pack('C', $value);
	}

	#[Override]
	protected function validateInteger(int $value): void
	{
		if ($value > 255 || $value < 0) {
			$this->throwInvalid('uint8', (string) $value);
		}
	}
}
