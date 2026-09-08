<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2866;

enum AcctAuthenticValue: int
{
	case Radius = 1;
	case Local = 2;
	case Remote = 3;
	case Diameter = 4;
}
