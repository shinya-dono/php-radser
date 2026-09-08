<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Tests\Attributes;

use BackedEnum;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\ExpectationFailedException;
use Shinya\PhpRadser\Rfc2866\AcctStatusTypeValue;
use Shinya\PhpRadser\Rfc2866\Attributes\AcctStatusType;

/**
 * @internal
 */
#[CoversNothing]
final class EnumAttributeTest extends TestCase
{
	/**
	 * @throws ExpectationFailedException
	 */
	public function testHydrateParsesKnownValue(): void
	{
		$acctStatusType = AcctStatusType::hydrate(pack('N', 1));

		$this->assertTrue($acctStatusType->filled());
		$this->assertSame(AcctStatusTypeValue::Start, $acctStatusType->read());
		$this->assertSame(pack('N', 1), $acctStatusType->dehydrate());
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws Exception
	 */
	public function testHydrateTreatsUnknownValueAsMissing(): void
	{
		$acctStatusType = AcctStatusType::hydrate(pack('N', 999));

		$this->assertFalse($acctStatusType->filled());
		$this->assertNotInstanceOf(BackedEnum::class, $acctStatusType->read());
		$this->assertNull($acctStatusType->dehydrate());
	}

	/**
	 * @throws ExpectationFailedException
	 */
	public function testMakeBuildsFromEnumCase(): void
	{
		$acctStatusType = AcctStatusType::make(AcctStatusTypeValue::Stop);

		$this->assertSame(AcctStatusTypeValue::Stop, $acctStatusType->read());
	}
}
