<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2869\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\DateAttribute;

class EventTimestamp extends DateAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Event-Timestamp';
	}

	#[Override]
	public static function type(): int
	{
		return 55;
	}
}
