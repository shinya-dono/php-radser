<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865;

enum FramedRoutingValue: int
{
	case None = 0;
	case Broadcast = 1;
	case Listen = 2;
	case BroadcastListen = 3;
}
