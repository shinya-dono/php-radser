<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Contracts;

use Closure;

/**
 * registered on Server; receives every inbound message except replies (CoA/Disconnect Ack/Nak,
 * which only go to whichever sendAsync() is awaiting them) - check $message->getPacketCode() to
 * see what arrived.
 */
interface Handler
{
	/**
	 * @param Closure():void $stopBubbling stop dispatching message to other handlers
	 */
	public function handle(Message $message, Channel $channel, Closure $stopBubbling): void;
}
