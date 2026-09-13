<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Exceptions;

use Shinya\PhpRadser\Contracts\Message;

/**
 * an Access-Request with no Message-Authenticator, from a peer registered to require one
 * ({@see \Shinya\PhpRadser\Peer::$requireMessageAuthenticator}). Its own type so an ErrorHandler
 * can tell a NAS that still needs upgrading apart from a forged packet while rolling that out.
 */
class MissingMessageAuthenticatorException extends InvalidAuthenticatorException
{
	public function __construct(Message $packet)
	{
		parent::__construct($packet, 'carries no Message-Authenticator, and the peer is set to require one');
	}
}
