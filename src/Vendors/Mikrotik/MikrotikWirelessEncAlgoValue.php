<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Mikrotik;

enum MikrotikWirelessEncAlgoValue: int
{
	case NoEncryption = 0;
	case _40BitWep = 1;
	case _104BitWep = 2;
	case AesCcm = 3;
	case Tkip = 4;
}
