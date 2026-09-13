<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsAcctEapTypeValue: int
{
	case Md5 = 4;
	case Otp = 5;
	case GenericTokenCard = 6;
	case Tls = 13;
}
