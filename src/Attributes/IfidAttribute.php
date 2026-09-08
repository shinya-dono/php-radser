<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;
use Shinya\PhpRadser\Support\Missing;

/**
 * interface id (hex:hex:hex:hex).
 */
abstract class IfidAttribute extends RawAttribute
{
	/**
	 * $value is this attribute's raw 8-byte wire bytes, not a colon-hex string.
	 */
	#[Override]
	public static function hydrate(mixed $value): static
	{
		if (!is_string($value) || 8 !== strlen($value)) {
			return new static(Missing::instance());
		}

		return new static(implode(':', str_split(bin2hex($value), 4)));
	}

	#[Override]
	public function dehydrate(): string|null
	{
		if ($this->value instanceof Missing) {
			return null;
		}

		$raw = hex2bin(str_replace(':', '', $this->value));

		return false !== $raw ? $raw : null;
	}
}
