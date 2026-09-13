<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsRnapNotQuarantineCapableValue: int
{
	case SohSent = 0;
	case SohNotSent = 1;
}
