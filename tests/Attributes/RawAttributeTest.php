<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Tests\Attributes;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\ExpectationFailedException;
use Shinya\PhpRadser\Rfc2865\Attributes\UserName;

/**
 * @internal
 */
#[CoversNothing]
final class RawAttributeTest extends TestCase
{
	/**
	 * @throws ExpectationFailedException
	 */
	public function testHydrateAndRead(): void
	{
		$userName = UserName::hydrate('alice');

		$this->assertTrue($userName->filled());
		$this->assertSame('alice', $userName->read());
		$this->assertSame('alice', $userName->dehydrate());
	}

	/**
	 * @throws ExpectationFailedException
	 */
	public function testHydrateNonStringIsMissing(): void
	{
		$userName = UserName::hydrate(123);

		$this->assertFalse($userName->filled());
		$this->assertNull($userName->read());
		$this->assertNull($userName->dehydrate());
	}
}
