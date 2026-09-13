<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsNetworkAccessServerTypeValue: int
{
	case Unspecified = 0;
	case TerminalServerGateway = 1;
	case RemoteAccessServer = 2;
	case DhcpServer = 3;
	case WirelessAccessPoint = 4;
	case Hra = 5;
	case HcapServer = 6;
}
