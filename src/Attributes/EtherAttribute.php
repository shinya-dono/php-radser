<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;
use Shinya\PhpRadser\Support\Missing;

/**
 * ethernet MAC address.
 */
abstract class EtherAttribute extends RawAttribute
{
	/**
	 * $value is this attribute's raw 6-byte wire bytes, not a colon-hex string.
	 */
	#[Override]
	public static function hydrate(mixed $value): static
	{
		if (!is_string($value) || 6 !== strlen($value)) {
			return new static(Missing::instance());
		}

		return new static(implode(':', str_split(bin2hex($value), 2)));
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

	#[Override]
	public function validate(): void
	{
		if (!$value = $this->read()) {
			$this->throwInvalid('mac-address', 'null');
		}

		if (!filter_var($value, FILTER_VALIDATE_MAC)) {
			$this->throwInvalid('mac-address', $value);
		}
	}
}
