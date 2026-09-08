<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Exceptions;

class InvalidAttributeValueException extends RadiusRuntimeException
{
	public function __construct(string $identifier, string $expected, string $actual)
	{
		parent::__construct(sprintf("unable to validate attribute '%s': expected '%s', got '%s'", $identifier, $expected, $actual));
	}
}
