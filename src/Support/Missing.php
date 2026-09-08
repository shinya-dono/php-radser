<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Support;

/**
 * the absence of a value, as distinct from a value that is null - an attribute holds this when
 * the packet did not carry it, which is what lets read() answer null without dehydrate() then
 * putting an empty attribute on the wire.
 */
class Missing
{
	protected static Missing $instance;

	public static function instance(): self
	{
		return self::$instance ??= new self();
	}
}
