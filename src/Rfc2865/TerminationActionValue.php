<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865;

enum TerminationActionValue: int
{
	case DefaultAttribute = 0;
	case RadiusRequest = 1;
}
