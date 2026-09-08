<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865\Attributes;

use Override;
use Shinya\PhpRadser\Attributes\EnumAttribute;
use Shinya\PhpRadser\Rfc2865\ServiceTypeValue;

/**
 * type of service the user has requested or has been provided, e.g. Login, Framed,
 * Callback-Login, Administrative.
 *
 * @extends EnumAttribute<ServiceTypeValue>
 */
class ServiceType extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Service-Type';
	}

	#[Override]
	public static function type(): int
	{
		return 6;
	}

	#[Override]
	protected static function enum(): string
	{
		return ServiceTypeValue::class;
	}
}
