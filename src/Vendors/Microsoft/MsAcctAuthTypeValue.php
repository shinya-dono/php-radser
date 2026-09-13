<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsAcctAuthTypeValue: int
{
	case Pap = 1;
	case Chap = 2;
	case MsChap1 = 3;
	case MsChap2 = 4;
	case Eap = 5;
}
