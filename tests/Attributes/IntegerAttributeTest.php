<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Tests\Attributes;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Shinya\PhpRadser\Rfc2865\Attributes\NasPort;
use PHPUnit\Framework\ExpectationFailedException;
use Shinya\PhpRadser\Exceptions\InvalidAttributeValueException;

/**
 * @internal
 */
#[CoversNothing]
final class IntegerAttributeTest extends TestCase
{
	/**
	 * @throws ExpectationFailedException
	 */
	public function testHydrateAndRead(): void
	{
		$nasPort = NasPort::hydrate(pack('N', 42));

		$this->assertSame(42, $nasPort->read());
		$this->assertSame(pack('N', 42), $nasPort->dehydrate());
	}

	/**
	 * @throws ExpectationFailedException
	 */
	public function testHydrateNonIntIsMissing(): void
	{
		$nasPort = NasPort::hydrate('not-an-int');

		$this->assertFalse($nasPort->filled());
		$this->assertNull($nasPort->read());
	}

	/**
	 * @throws InvalidAttributeValueException
	 */
	public function testValidateRejectsOutOfRangeValue(): void
	{
		$nasPort = NasPort::make(-1);

		$this->expectException(InvalidAttributeValueException::class);

		$nasPort->validate();
	}

	/**
	 * the top of the range has to be 0xFFFFFFFF exactly - a bound that lets a wider value through
	 * hands pack('N') something it silently truncates.
	 *
	 * @throws InvalidAttributeValueException
	 */
	public function testValidateAcceptsTheLargestUint32(): void
	{
		NasPort::make(0xFFFFFFFF)->validate();

		$this->expectException(InvalidAttributeValueException::class);

		NasPort::make(0xFFFFFFFF + 1)->validate();
	}
}
