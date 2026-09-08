<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;

/**
 * 16-bit unsigned integer.
 */
abstract class ShortAttribute extends IntegerLikeAttribute
{
	#[Override]
	protected static function unpackWire(string $raw): int|null
	{
		return self::unpackUint('n', 2, $raw);
	}

	#[Override]
	protected static function packWire(int $value): string
	{
		return pack('n', $value);
	}

	#[Override]
	protected function validateInteger(int $value): void
	{
		if ($value < 0 || $value > 0xFFFF) {
			$this->throwInvalid('uint16', (string) $value);
		}
	}
}
