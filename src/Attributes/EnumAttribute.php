<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;
use BackedEnum;
use Shinya\PhpRadser\Support\Missing;
use Shinya\PhpRadser\Contracts\Attribute;

/**
 * shared base for integer-backed attributes with a closed set of named values.
 *
 * @template T of BackedEnum
 *
 * @see \Shinya\PhpRadser\Tests\Attributes\EnumAttributeTest
 */
abstract class EnumAttribute extends Attribute
{
	/**
	 * @param T|Missing $value
	 */
	final protected function __construct(
		protected BackedEnum|Missing $value,
	) {}

	/**
	 * @param T $backedEnum
	 */
	public static function make(BackedEnum $backedEnum): static
	{
		return new static($backedEnum);
	}

	/**
	 * $value is this attribute's raw 4-byte wire bytes, not the enum's backing value directly -
	 * every generated *Value enum is int-backed.
	 *
	 * @return static<T>
	 */
	#[Override]
	public static function hydrate(mixed $value): static
	{
		$parsed = null;

		if (is_string($value) && 4 === strlen($value)) {
			$unpacked = unpack('N', $value);
			$code = false !== $unpacked ? ($unpacked[1] ?? null) : null;
			$parsed = is_int($code) ? static::enum()::tryFrom($code) : null;
		}

		return new static($parsed ?? Missing::instance());
	}

	/**
	 * @return class-string<T>
	 */
	abstract protected static function enum(): string;

	#[Override]
	public function filled(): bool
	{
		return !$this->value instanceof Missing;
	}

	/**
	 * @return T|null
	 */
	#[Override]
	public function read(): BackedEnum|null
	{
		return $this->value instanceof Missing ? null : $this->value;
	}

	#[Override]
	public function dehydrate(): string|null
	{
		return $this->value instanceof Missing ? null : pack('N', (int) $this->value->value);
	}
}
