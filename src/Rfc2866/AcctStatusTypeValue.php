<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2866;

enum AcctStatusTypeValue: int
{
	case Start = 1;
	case Stop = 2;
	case InterimUpdate = 3;
	case AccountingOn = 7;
	case AccountingOff = 8;
	case Failed = 15;
}
