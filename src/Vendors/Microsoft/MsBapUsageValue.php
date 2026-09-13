<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsBapUsageValue: int
{
	case NotAllowed = 0;
	case Allowed = 1;
	case Required = 2;
}
