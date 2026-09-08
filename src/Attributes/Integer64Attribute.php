<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;
use Shinya\PhpRadser\Support\Missing;
use Shinya\PhpRadser\Contracts\Attribute;
use Shinya\PhpRadser\Exceptions\InvalidAttributeValueException;

/**
 * 64-bit unsigned integer. The top of the range is 18446744073709551615, past what a PHP int can
 * hold, so the value is carried as a decimal string and both directions of the wire conversion go
 * through bcmath - unpacking these 8 bytes into an int would turn every value above PHP_INT_MAX
 * negative, and a byte counter on a long-lived session reaches that range in normal use.
 *
 * @see \Shinya\PhpRadser\Tests\Attributes\Integer64AttributeTest
 */
abstract class Integer64Attribute extends Attribute
{
	/**
	 * the largest value 8 octets can carry.
	 */
	public const string MAX = '18446744073709551615';

	/**
	 * 2 ** 32, the split point between the two halves the wire bytes are read as - each half fits
	 * a PHP int comfortably, their combination is the part that does not.
	 */
	private const string HALF = '4294967296';

	/**
	 * 2 ** 64, subtracted from a value the top bit is set on to get the negative PHP int with the
	 * same eight bytes.
	 */
	private const string WRAP = '18446744073709551616';

	/**
	 * PHP_INT_MAX as a string, so the comparison against it stays exact.
	 */
	private const string SIGNED_MAX = '9223372036854775807';

	final protected function __construct(
		protected string|Missing $value,
	) {}

	/**
	 * an int for the values that fit one, a decimal string for the rest.
	 */
	public static function make(int|string $value): static
	{
		return new static((string) $value);
	}

	/**
	 * $value is this attribute's raw 8-byte wire bytes, not a decimal string.
	 */
	#[Override]
	public static function hydrate(mixed $value): static
	{
		if (!is_string($value) || 8 !== strlen($value)) {
			return new static(Missing::instance());
		}

		$halves = unpack('N2', $value);
		$high = false !== $halves ? ($halves[1] ?? null) : null;
		$low = false !== $halves ? ($halves[2] ?? null) : null;

		if (!is_int($high) || !is_int($low)) {
			return new static(Missing::instance());
		}

		return new static(bcadd(bcmul((string) $high, self::HALF), (string) $low));
	}

	#[Override]
	public function filled(): bool
	{
		return !$this->value instanceof Missing;
	}

	/**
	 * the value as a decimal string, whatever it was built from - comparing two of these with
	 * `==` is a trap once they get large, so reach for bccomp().
	 */
	#[Override]
	public function read(): string|null
	{
		return $this->value instanceof Missing ? null : $this->value;
	}

	/**
	 * a value outside the range 8 octets can carry has no wire form, so nothing is sent for it -
	 * call validate() first if you would rather hear about it.
	 */
	#[Override]
	public function dehydrate(): string|null
	{
		if (null === $inRange = $this->inRange()) {
			return null;
		}

		// pack('J') takes a signed int, so anything above PHP_INT_MAX goes in as the negative
		// number sharing its eight bytes - the same wrap the wire format does, done deliberately
		// rather than let an overflowing cast do it
		return pack('J', bccomp($inRange, self::SIGNED_MAX) > 0 ? (int) bcsub($inRange, self::WRAP) : (int) $inRange);
	}

	/**
	 * @throws InvalidAttributeValueException
	 */
	#[Override]
	public function validate(): void
	{
		if (null === $this->inRange()) {
			$this->throwInvalid('uint64', $this->read() ?? 'null');
		}
	}

	/**
	 * the value if it is a whole number 8 octets can carry; null if it is missing, negative, past
	 * MAX, or not a decimal number at all.
	 *
	 * @return numeric-string|null
	 */
	private function inRange(): string|null
	{
		if ($this->value instanceof Missing || !ctype_digit($this->value)) {
			return null;
		}

		return bccomp($this->value, self::MAX) <= 0 ? $this->value : null;
	}
}
