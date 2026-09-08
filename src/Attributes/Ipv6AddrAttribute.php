<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;
use Shinya\PhpRadser\Support\Missing;

/**
 * IPv6 address.
 */
abstract class Ipv6AddrAttribute extends RawAttribute
{
	/**
	 * $value is this attribute's raw 16-byte wire bytes, not a colon-hex string.
	 */
	#[Override]
	public static function hydrate(mixed $value): static
	{
		if (!is_string($value) || 16 !== strlen($value)) {
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
			$this->throwInvalid('ip-v6-address', 'null');
		}

		if (!filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			$this->throwInvalid('ip-v6-address', $value);
		}
	}
}
