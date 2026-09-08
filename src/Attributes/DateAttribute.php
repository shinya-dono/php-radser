<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;
use DateTimeImmutable;
use DateMalformedStringException;
use Shinya\PhpRadser\Support\Missing;
use Shinya\PhpRadser\Contracts\Attribute;

/**
 * seconds since January 1, 1970 (32-bit).
 */
abstract class DateAttribute extends Attribute
{
	final protected function __construct(
		protected DateTimeImmutable|Missing $value,
	) {}

	public static function make(DateTimeImmutable $value): static
	{
		return new static($value);
	}

	/**
	 * $value is this attribute's raw 4-byte wire bytes, not a unix timestamp.
	 */
	#[Override]
	public static function hydrate(mixed $value): static
	{
		if (!is_string($value) || 4 !== strlen($value)) {
			return new static(Missing::instance());
		}

		$unpacked = unpack('N', $value);
		$timestamp = false !== $unpacked ? ($unpacked[1] ?? null) : null;
		if (!is_int($timestamp)) {
			return new static(Missing::instance());
		}

		try {
			return new static(new DateTimeImmutable('@'.$timestamp));
		}
		catch (DateMalformedStringException) {
			return new static(Missing::instance());
		}
	}

	#[Override]
	public function filled(): bool
	{
		return !$this->value instanceof Missing;
	}

	#[Override]
	public function read(): DateTimeImmutable|null
	{
		return $this->value instanceof Missing ? null : $this->value;
	}

	#[Override]
	public function dehydrate(): string|null
	{
		return $this->value instanceof Missing ? null : pack('N', $this->value->getTimestamp());
	}
}
