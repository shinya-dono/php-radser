<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Exceptions;

use Shinya\PhpRadser\Peer;

/**
 * a request we sent went unanswered long enough that we gave up on it and handed its identifier
 * back to the pool; this is what the promise from Channel::sendAsync() rejects with.
 */
class RequestTimedOutException extends RadiusRuntimeException
{
	public function __construct(
		public readonly Peer $peer,
		public readonly int $identifier,
		public readonly float $timeout,
	) {
		parent::__construct(sprintf('peer %s did not answer request %d within %.1fs', $peer->ip, $identifier, $timeout));
	}
}
