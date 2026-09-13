<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsMppeEncryptionPolicyValue: int
{
	case EncryptionAllowed = 1;
	case EncryptionRequired = 2;
}
