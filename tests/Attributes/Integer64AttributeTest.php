<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Tests\Attributes;

use Override;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\ExpectationFailedException;
use Shinya\PhpRadser\Attributes\Integer64Attribute;
use Shinya\PhpRadser\Exceptions\InvalidAttributeValueException;

/**
 * no dictionary we ship declares an integer64 attribute, so the family is exercised through a
 * concrete one defined here.
 *
 * @internal
 */
#[CoversNothing]
final class Integer64AttributeTest extends TestCase
{
	/**
	 * @return iterable<string, array{string, string}>
	 */
	public static function values(): iterable
	{
		yield 'zero' => ['0', "\0\0\0\0\0\0\0\0"];

		yield 'one' => ['1', "\0\0\0\0\0\0\0\1"];

		yield 'largest uint32' => ['4294967295', "\0\0\0\0\xFF\xFF\xFF\xFF"];

		yield 'first value needing the high half' => ['4294967296', "\0\0\0\1\0\0\0\0"];

		yield 'largest PHP int' => ['9223372036854775807', "\x7F\xFF\xFF\xFF\xFF\xFF\xFF\xFF"];

		yield 'one past the largest PHP int' => ['9223372036854775808', "\x80\0\0\0\0\0\0\0"];

		yield 'largest uint64' => [Integer64Attribute::MAX, "\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF"];
	}

	/**
	 * the last three cases are the point of the whole class: an int-backed implementation reads
	 * them back negative.
	 *
	 * @throws ExpectationFailedException
	 */
	#[DataProvider(methodName: 'values')]
	public function testRoundTripsTheFullUnsignedRange(string $decimal, string $wire): void
	{
		$this->assertSame($wire, Integer64Test::make($decimal)->dehydrate());
		$this->assertSame($decimal, Integer64Test::hydrate($wire)->read());
	}

	/**
	 * @throws ExpectationFailedException
	 */
	public function testMakeAcceptsAnInt(): void
	{
		$integer64Test = Integer64Test::make(42);

		$this->assertTrue($integer64Test->filled());
		$this->assertSame('42', $integer64Test->read());
	}

	/**
	 * @throws ExpectationFailedException
	 */
	public function testHydrateRejectsTheWrongByteLength(): void
	{
		$integer64Test = Integer64Test::hydrate("\0\0\0\1");

		$this->assertFalse($integer64Test->filled());
		$this->assertNull($integer64Test->read());
		$this->assertNull($integer64Test->dehydrate());
	}

	/**
	 * @throws ExpectationFailedException
	 */
	public function testAValuePastTheRangeHasNoWireForm(): void
	{
		$integer64Test = Integer64Test::make(bcadd(Integer64Attribute::MAX, '1'));

		$this->assertNull($integer64Test->dehydrate());
	}

	/**
	 * @throws InvalidAttributeValueException
	 */
	public function testValidateRejectsANegativeValue(): void
	{
		$this->expectException(InvalidAttributeValueException::class);

		Integer64Test::make(-1)->validate();
	}

	/**
	 * @throws InvalidAttributeValueException
	 */
	public function testValidateAcceptsTheLargestUint64(): void
	{
		Integer64Test::make(Integer64Attribute::MAX)->validate();

		$this->expectException(InvalidAttributeValueException::class);

		Integer64Test::make(bcadd(Integer64Attribute::MAX, '1'))->validate();
	}
}

/**
 * @internal
 *
 * @coversNothing
 */
final class Integer64Test extends Integer64Attribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Integer64-Test';
	}

	#[Override]
	public static function type(): int
	{
		return 200;
	}
}
