<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsIdentityTypeValue: int
{
	case MachineHealthCheck = 1;
	case IgnoreUserLookupFailure = 2;
}
