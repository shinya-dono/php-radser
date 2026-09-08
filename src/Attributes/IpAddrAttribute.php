<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;
use Shinya\PhpRadser\Support\Missing;

/**
 * IPv4 address.
 */
abstract class IpAddrAttribute extends RawAttribute
{
	/**
	 * $value is this attribute's raw 4-byte wire bytes, not a dotted-decimal string.
	 */
	#[Override]
	public static function hydrate(mixed $value): static
	{
		if (!is_string($value) || 4 !== strlen($value)) {
			return new static(Missing::instance());
		}

		$ip = inet_ntop($value);

		return new static(false !== $ip ? $ip : Missing::instance());
	}

	#[Override]
	public function dehydrate(): string|null
	{
		if ($this->value instanceof Missing) {
			return null;
		}

		$raw = inet_pton($this->value);

		return false !== $raw ? $raw : null;
	}

	#[Override]
	public function validate(): void
	{
		if (!$value = $this->read()) {
			$this->throwInvalid('ip-v4-address', 'null');
		}

		if (!filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$this->throwInvalid('ip-v4-address', $value);
		}
	}
}
