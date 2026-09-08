<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Contracts;

use Shinya\PhpRadser\Peer;
use Random\RandomException;
use Shinya\PhpRadser\RequestQueue;
use React\Datagram\SocketInterface;
use React\Promise\PromiseInterface;
use Shinya\PhpRadser\Support\PacketCodec;
use Shinya\PhpRadser\Exceptions\RadiusRuntimeException;

/**
 * the way back to one peer over the socket its packet arrived on: send() answers the request in
 * hand, sendAsync() starts a new one and waits for the peer's Ack or Nak.
 */
class Channel
{
	/**
	 * @param Peer $peer the sender, on the port this request actually came from - a reply goes
	 *                   back to that ephemeral port, not to the CoA port the peer listens on
	 * @param Message $request the inbound message this channel is replying to, for its identifier()/authenticator()
	 * @param RequestQueue $requests what this peer owes us an answer to, for sendAsync()
	 * @param PacketCodec $packetCodec wire codec used to frame outbound packets
	 */
	public function __construct(
		protected Peer $peer,
		protected Message $request,
		protected SocketInterface $socket,
		protected RequestQueue $requests,
		protected PacketCodec $packetCodec = new PacketCodec(),
	) {}

	/**
	 * fire-and-forget send, used to reply to an inbound Access-Request/Accounting-Request. Takes
	 * a Reply and not any Message because a reply's authenticator is keyed to the request's, and
	 * Message::reply() is what puts it there - hand-building one with `new Message(...)` would
	 * sign it against 16 zero bytes and the NAS would drop it.
	 *
	 * @throws RadiusRuntimeException
	 * @throws RandomException a Reply that seeds itself with a nonce, which none of the built-in ones do
	 */
	public function send(Reply $reply): void
	{
		$this->socket->send(
			$this->packetCodec->encode($reply, $this->request->getIdentifier()),
			$this->peer->address,
		);
	}

	/**
	 * send a message and wait for the peer's Ack/Nak, used for a CoA/Disconnect request.
	 *
	 * @return PromiseInterface<array{Message, Channel}>
	 *
	 * @throws RadiusRuntimeException
	 * @throws RandomException
	 */
	public function sendAsync(Message $message): PromiseInterface
	{
		$identifier = $this->requests->nextIdentifier();

		$bytes = $this->packetCodec->encode($message, $identifier);

		// the authenticator the codec settled on lives at a fixed offset in what it just built,
		// and the queue needs it to tell our answer apart from anything else on that identifier
		$promise = $this->requests->defer($identifier, substr($bytes, 4, 16));

		$this->socket->send($bytes, $this->peer->coaAddress);

		return $promise;
	}
}
