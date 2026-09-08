<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2866;

enum AcctTerminateCauseValue: int
{
	case UserRequest = 1;
	case LostCarrier = 2;
	case LostService = 3;
	case IdleTimeout = 4;
	case SessionTimeout = 5;
	case AdminReset = 6;
	case AdminReboot = 7;
	case PortError = 8;
	case NasError = 9;
	case NasRequest = 10;
	case NasReboot = 11;
	case PortUnneeded = 12;
	case PortPreempted = 13;
	case PortSuspended = 14;
	case ServiceUnavailable = 15;
	case Callback = 16;
	case UserError = 17;
	case HostRequest = 18;
}
