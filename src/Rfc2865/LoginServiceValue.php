<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865;

enum LoginServiceValue: int
{
	case Telnet = 0;
	case Rlogin = 1;
	case TcpClear = 2;
	case Portmaster = 3;
	case Lat = 4;
	case X25Pad = 5;
	case X25T3pos = 6;
	case TcpClearQuiet = 8;
}
