<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2869\Attributes;

use Override;
use Shinya\PhpRadser\Rfc2869\PromptValue;
use Shinya\PhpRadser\Attributes\EnumAttribute;

/**
 * @extends EnumAttribute<PromptValue>
 */
class Prompt extends EnumAttribute
{
	#[Override]
	public static function identifier(): string
	{
		return 'Prompt';
	}

	#[Override]
	public static function type(): int
	{
		return 76;
	}

	#[Override]
	protected static function enum(): string
	{
		return PromptValue::class;
	}
}
