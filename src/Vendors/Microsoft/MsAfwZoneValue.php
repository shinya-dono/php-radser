<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsAfwZoneValue: int
{
	case MsAfwZoneBoundaryPolicy = 1;
	case MsAfwZoneUnprotectedPolicy = 2;
	case MsAfwZoneProtectedPolicy = 3;
}
