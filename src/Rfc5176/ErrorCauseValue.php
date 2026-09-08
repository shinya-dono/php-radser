<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Rfc5176;

enum ErrorCauseValue: int
{
	case ResidualContextRemoved = 201;
	case InvalidEapPacket = 202;
	case UnsupportedAttribute = 401;
	case MissingAttribute = 402;
	case NasIdentificationMismatch = 403;
	case InvalidRequest = 404;
	case UnsupportedService = 405;
	case UnsupportedExtension = 406;
	case InvalidAttributeValue = 407;
	case AdministrativelyProhibited = 501;
	case ProxyRequestNotRoutable = 502;
	case SessionContextNotFound = 503;
	case SessionContextNotRemovable = 504;
	case ProxyProcessingError = 505;
	case ResourcesUnavailable = 506;
	case RequestInitiated = 507;
	case MultipleSessionSelectionUnsupported = 508;
}
