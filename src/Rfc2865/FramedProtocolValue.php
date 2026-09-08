<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865;

enum FramedProtocolValue: int
{
	case Ppp = 1;
	case Slip = 2;
	case Arap = 3;
	case GandalfSlml = 4;
	case XylogicsIpxSlip = 5;
	case X75Synchronous = 6;
}
