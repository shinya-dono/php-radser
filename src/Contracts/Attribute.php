<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Contracts;

use Shinya\PhpRadser\Exceptions\InvalidAttributeValueException;

/**
 * one RADIUS attribute: the wire type it occupies, and the conversion between its raw bytes and
 * a PHP value. Instances are cheap and stateless apart from that value.
 */
abstract class Attribute
{
	/**
	 * get standard identifier for attribute.
	 *
	 * @return non-empty-string
	 */
	abstract public static function identifier(): string;

	/**
	 * numeric RADIUS wire type; for a vendor attribute this is the vendor-scoped sub-type carried
	 * inside the Vendor-Specific (26) attribute, not 26 itself.
	 *
	 * @return int<0, 255>
	 */
	abstract public static function type(): int;

	/**
	 * hydrate a new instance of attribute from raw value.
	 */
	abstract public static function hydrate(mixed $value): static;

	/**
	 * get current hydrated value of this attribute.
	 */
	abstract public function read(): mixed;

	/**
	 * the current value in wire-ready form - raw bytes - or null if unfilled/Missing.
	 */
	abstract public function dehydrate(): string|null;

	/**
	 * check if the attribute value is filled.
	 */
	abstract public function filled(): bool;

	/**
	 * validate current value.
	 *
	 * @return void return void on success, an exception is thrown on error
	 *
	 * @throws InvalidAttributeValueException
	 */
	public function validate(): void {}

	/**
	 * throw an InvalidAttributeValueException for current attribute.
	 *
	 * @throws InvalidAttributeValueException
	 */
	protected function throwInvalid(string $expected, string $actual = "''"): never
	{
		throw new InvalidAttributeValueException(static::identifier(), $expected, $actual);
	}
}
