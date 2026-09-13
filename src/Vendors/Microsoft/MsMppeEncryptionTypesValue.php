<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsMppeEncryptionTypesValue: int
{
	case Rc440bitAllowed = 1;
	case Rc4128bitAllowed = 2;
	case Rc440or128BitAllowed = 6;
}
