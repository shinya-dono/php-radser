<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Exceptions;

use Shinya\PhpRadser\Contracts\Message;

/**
 * a peer answered a request we are not waiting on - usually harmless noise: a duplicate of an
 * answer we already took, or one that arrived after we gave up on it and handed its identifier
 * back to the pool.
 */
class UnexpectedReplyException extends RadiusRuntimeException
{
	public function __construct(
		public readonly Message $packet,
	) {
		parent::__construct(sprintf('peer %s sent a %s for identifier %d, which no request is waiting on', $packet->getPeer()->ip, $packet->describe(), $packet->getIdentifier()));
	}
}
