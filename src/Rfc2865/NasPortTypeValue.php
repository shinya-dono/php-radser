<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865;

enum NasPortTypeValue: int
{
	case Async = 0;
	case Sync = 1;
	case Isdn = 2;
	case IsdnV120 = 3;
	case IsdnV110 = 4;
	case Virtual = 5;
	case Piafs = 6;
	case HdlcClearChannel = 7;
	case X25 = 8;
	case X75 = 9;
	case G3Fax = 10;
	case Sdsl = 11;
	case AdslCap = 12;
	case AdslDmt = 13;
	case Idsl = 14;
	case Ethernet = 15;
	case Xdsl = 16;
	case Cable = 17;
	case WirelessOther = 18;
	case Wireless80211 = 19;
}
