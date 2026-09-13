<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsAfwProtectionLevelValue: int
{
	case HecpResponseSignOnly = 1;
	case HecpResponseSignAndEncrypt = 2;
}
