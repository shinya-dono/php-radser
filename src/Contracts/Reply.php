<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Contracts;

use Override;

/**
 * a message whose authenticator is keyed to some other message's rather than to its own contents
 * - an Access-Accept/Reject/Challenge or Accounting-Response we send back, and the CoA/Disconnect
 * Ack/Nak a NAS sends us in answer to a request this library started. Server routes an inbound
 * one to whichever Channel::sendAsync() is awaiting it and never to a Handler, so what is and
 * isn't a reply is decided by this type, not by a list of codes.
 */
class Reply extends Message
{
	/**
	 * for an outbound reply the $authenticator it was built with is the request's own
	 * ({@see Message::reply()}), which is exactly the seed RFC 2865 3 wants. An inbound one is verified
	 * against the authenticator RequestQueue kept for the request we sent, so this is not
	 * consulted on that path.
	 */
	#[Override]
	public function authenticatorSeed(): string
	{
		return $this->authenticator;
	}
}
