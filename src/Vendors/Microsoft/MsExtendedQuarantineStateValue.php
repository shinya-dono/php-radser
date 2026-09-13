<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsExtendedQuarantineStateValue: int
{
	case Transition = 1;
	case Infected = 2;
	case Unknown = 3;
	case NoData = 4;
}
