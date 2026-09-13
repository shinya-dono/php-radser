<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Vendors\Microsoft;

enum MsArapPwChangeReasonValue: int
{
	case JustChangePassword = 1;
	case ExpiredPassword = 2;
	case AdminRequiresPasswordChange = 3;
	case PasswordTooShort = 4;
}
