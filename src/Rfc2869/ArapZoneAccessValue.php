<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2869;

enum ArapZoneAccessValue: int
{
	case DefaultZone = 1;
	case ZoneFilterInclusive = 2;
	case ZoneFilterExclusive = 4;
}
