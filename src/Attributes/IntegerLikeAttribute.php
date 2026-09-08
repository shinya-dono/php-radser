<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;
use Shinya\PhpRadser\Support\Missing;
use Shinya\PhpRadser\Contracts\Attribute;
use Shinya\PhpRadser\Exceptions\InvalidAttributeValueException;

/**
 * shared base for the integer attribute types that fit a PHP int (integer, byte, short,
 * signed). integer64 does not, and is built on {@see Integer64Attribute} instead.
 */
abstract class IntegerLikeAttribute extends Attribute
{
	final protected function __construct(
		protected int|Missing $value,
	) {}

	public static function make(int $value): static
	{
		return new static($value);
	}

	/**
	 * $value is this attribute's raw wire bytes, not a plain int - each width
	 * (byte/short/integer/signed/integer64) unpacks/packs its own bytes via
	 * unpackWire()/packWire().
	 */
	#[Override]
	public static function hydrate(mixed $value): static
	{
		$parsed = is_string($value) ? static::unpackWire($value) : null;

		return new static($parsed ?? Missing::instance());
	}

	/**
	 * shared unpack-with-length-check used by every width's unpackWire().
	 */
	final protected static function unpackUint(string $format, int $bytes, string $raw): int|null
	{
		if (strlen($raw) !== $bytes) {
			return null;
		}

		$unpacked = unpack($format, $raw);
		$value = false !== $unpacked ? ($unpacked[1] ?? null) : null;

		return is_int($value) ? $value : null;
	}

	/**
	 * parse this width's raw wire bytes into an int, or null if the byte length doesn't match.
	 */
	abstract protected static function unpackWire(string $raw): int|null;

	/**
	 * pack an int into this width's raw wire bytes.
	 */
	abstract protected static function packWire(int $value): string;

	#[Override]
	public function filled(): bool
	{
		return !$this->value instanceof Missing;
	}

	#[Override]
	public function read(): int|null
	{
		return $this->value instanceof Missing ? null : $this->value;
	}

	#[Override]
	public function dehydrate(): string|null
	{
		return $this->value instanceof Missing ? null : static::packWire($this->value);
	}

	#[Override]
	public function validate(): void
	{
		if (!is_int($value = $this->read())) {
			$this->throwInvalid('integer', 'null');
		}

		$this->validateInteger($value);
	}

	/**
	 * @throws InvalidAttributeValueException
	 */
	protected function validateInteger(int $value): void {}
}
