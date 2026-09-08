<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Exceptions;

use Shinya\PhpRadser\Contracts\Message;

/**
 * a packet is not signed with the secret we share with the peer it claims to be from - either the
 * two ends disagree about that secret, or somebody is sending on the peer's behalf. Worth
 * surfacing either way.
 */
class InvalidAuthenticatorException extends RadiusRuntimeException
{
	public function __construct(
		public readonly Message $packet,
	) {
		parent::__construct(sprintf('%s from peer %s for identifier %d is not signed with our shared secret', $packet->describe(), $packet->getPeer()->ip, $packet->getIdentifier()));
	}
}
