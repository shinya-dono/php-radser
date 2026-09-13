<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsQuarantineStateValue: int
{
	case FullAccess = 0;
	case Quarantine = 1;
	case Probation = 2;
}
