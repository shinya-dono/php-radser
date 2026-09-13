<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2869;

enum PromptValue: int
{
	case NoEcho = 0;
	case EchoAttribute = 1;
}
