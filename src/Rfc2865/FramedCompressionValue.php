<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc2865;

enum FramedCompressionValue: int
{
	case None = 0;
	case VanJacobsonTcpIp = 1;
	case IpxHeaderCompression = 2;
	case StacLzs = 3;
}
