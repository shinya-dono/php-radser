<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865;

enum ServiceTypeValue: int
{
	case LoginUser = 1;
	case FramedUser = 2;
	case CallbackLoginUser = 3;
	case CallbackFramedUser = 4;
	case OutboundUser = 5;
	case AdministrativeUser = 6;
	case NasPromptUser = 7;
	case AuthenticateOnly = 8;
	case CallbackNasPrompt = 9;
	case CallCheck = 10;
	case CallbackAdministrative = 11;
}
