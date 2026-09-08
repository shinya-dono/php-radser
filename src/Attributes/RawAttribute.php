<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;
use Shinya\PhpRadser\Support\Missing;
use Shinya\PhpRadser\Contracts\Attribute;

/**
 * shared base for attribute types that are just an opaque wire string (string, octets, ipaddr,
 * ipv6addr, ipv6prefix, ifid, ether, abinary, ipv4prefix).
 *
 * @see \Shinya\PhpRadser\Tests\Attributes\RawAttributeTest
 */
abstract class RawAttribute extends Attribute
{
	final protected function __construct(
		protected string|Missing $value,
	) {}

	public static function make(string $value): static
	{
		return new static($value);
	}

	#[Override]
	public static function hydrate(mixed $value): static
	{
		return new static(is_string($value) ? $value : Missing::instance());
	}

	#[Override]
	public function filled(): bool
	{
		return !$this->value instanceof Missing;
	}

	#[Override]
	public function read(): string|null
	{
		return $this->value instanceof Missing ? null : $this->value;
	}

	#[Override]
	public function dehydrate(): string|null
	{
		return $this->value instanceof Missing ? null : $this->value;
	}
}
