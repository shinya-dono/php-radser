<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;
use Shinya\PhpRadser\Support\Missing;

/**
 * IPv6 prefix, with mask.
 */
abstract class Ipv6PrefixAttribute extends RawAttribute
{
	/**
	 * $value is this attribute's raw wire bytes (1 reserved byte, 1 prefix-length byte, then the
	 * address), not an "address/prefixLength" string.
	 */
	#[Override]
	public static function hydrate(mixed $value): static
	{
		if (!is_string($value) || strlen($value) < 2) {
			return new static(Missing::instance());
		}

		$prefixLength = ord($value[1]);
		$address = str_pad(substr($value, 2), 16, "\0");
		$ip = inet_ntop($address);

		return new static(false !== $ip ? "{$ip}/{$prefixLength}" : Missing::instance());
	}

	#[Override]
	public function dehydrate(): string|null
	{
		if ($this->value instanceof Missing) {
			return null;
		}

		$parts = explode('/', $this->value, 2);
		if (2 !== count($parts)) {
			return null;
		}

		[$address, $prefixLength] = $parts;
		$raw = inet_pton($address);
		if (false === $raw) {
			return null;
		}

		return "\0".pack('C', (int) $prefixLength).str_pad($raw, 16, "\0");
	}

	#[Override]
	public function validate(): void
	{
		if (!$value = $this->read()) {
			$this->throwInvalid('ip-v6-prefix');
		}

		$parts = explode('/', $value, 2);

		if (!filter_var($parts[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			$this->throwInvalid('ip-v6-prefix', $value);
		}

		if (!isset($parts[1])) {
			$this->throwInvalid('ip-v6-prefix', $value);
		}

		$subnet = (int) $parts[1];

		if ($subnet > 128 || $subnet < 1) {
			$this->throwInvalid('ip-v6-prefix', $value);
		}
	}
}
