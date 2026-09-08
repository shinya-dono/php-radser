<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc5176\Attributes;

use Override;
use Shinya\PhpRadser\Rfc5176\ErrorCauseValue;
use Shinya\PhpRadser\Attributes\EnumAttribute;

/**
 * why a CoA-Request or Disconnect-Request was rejected (NAK'd), e.g. Session-Context-Not-Found,
 * Unsupported-Attribute.
 *
 * @extends EnumAttribute<ErrorCauseValue>
 */
class ErrorCause extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Error-Cause';
	}

	#[Override]
	public static function type(): int
	{
		return 101;
	}

	#[Override]
	protected static function enum(): string
	{
		return ErrorCauseValue::class;
	}
}
