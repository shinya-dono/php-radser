<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;

/**
 * 32-bit unsigned integer.
 *
 * @see \Shinya\PhpRadser\Tests\Attributes\IntegerAttributeTest
 */
abstract class IntegerAttribute extends IntegerLikeAttribute
{
	#[Override]
	protected static function unpackWire(string $raw): int|null
	{
		return self::unpackUint('N', 4, $raw);
	}

	#[Override]
	protected static function packWire(int $value): string
	{
		return pack('N', $value);
	}

	#[Override]
	protected function validateInteger(int $value): void
	{
		if ($value < 0 || $value > 0xFFFFFFFF) {
			$this->throwInvalid('uint32', (string) $value);
		}
	}
}
