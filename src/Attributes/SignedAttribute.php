<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;

/**
 * 32-bit signed integer.
 */
abstract class SignedAttribute extends IntegerLikeAttribute
{
	#[Override]
	protected static function unpackWire(string $raw): int|null
	{
		$unsigned = self::unpackUint('N', 4, $raw);

		return null === $unsigned || $unsigned <= 0x7FFFFFFF ? $unsigned : $unsigned - 0x100000000;
	}

	#[Override]
	protected static function packWire(int $value): string
	{
		return pack('N', $value < 0 ? $value + 0x100000000 : $value);
	}

	#[Override]
	protected function validateInteger(int $value): void
	{
		if ($value < -0x80000000 || $value > 0x7FFFFFFF) {
			$this->throwInvalid('int32', (string) $value);
		}
	}
}
