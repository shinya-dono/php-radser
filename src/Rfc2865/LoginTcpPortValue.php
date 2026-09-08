<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865;

enum LoginTcpPortValue: int
{
	case Telnet = 23;
	case Rlogin = 513;
	case Rsh = 514;
}
