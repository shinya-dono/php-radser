<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser;

use Closure;
use Throwable;
use React\Datagram\SocketInterface;
use Shinya\PhpRadser\Contracts\Reply;
use Shinya\PhpRadser\Contracts\Channel;
use Shinya\PhpRadser\Contracts\Handler;
use Shinya\PhpRadser\Contracts\Message;
use Shinya\PhpRadser\Support\PacketCodec;
use Shinya\PhpRadser\Support\ServerConfig;
use Shinya\PhpRadser\Contracts\ErrorHandler;
use Shinya\PhpRadser\Exceptions\RadiusRuntimeException;
use Shinya\PhpRadser\Exceptions\UnexpectedReplyException;
use Shinya\PhpRadser\Exceptions\InvalidAuthenticatorException;

/**
 * the entry point: owns the authentication and accounting sockets, decodes and verifies every
 * datagram that arrives on either, and hands what is left to the handlers it was built with.
 * Replies are routed to the RequestQueue awaiting them instead, and never reach a handler.
 */
class Server
{
	protected SocketInterface|null $authSocket = null;

	protected SocketInterface|null $acctSocket = null;

	/**
	 * @var array<array-key, RequestQueue> what each peer still owes us an answer to, kept out of
	 *                                     Peer so a peer stays a value we can copy per request
	 */
	protected array $requestQueues = [];

	/**
	 * @param array<array-key, Handler> $handlers handler instances, each sees every inbound message
	 * @param PeerRegistry $peerRegistry known peers and their shared secrets
	 * @param ServerConfig $serverConfig listen host and ports, and the datagram factory to bind with
	 * @param PacketCodec $packetCodec wire codec used to decode inbound and frame outbound packets
	 * @param ErrorHandler|null $errorHandler told about whatever a handler threw and the address it
	 *                                        came from; leave it null and a packet that blows up is
	 *                                        dropped without a trace, which is rarely what you want
	 *                                        outside tests
	 */
	public function __construct(
		protected array $handlers = [],
		protected PeerRegistry $peerRegistry = new PeerRegistry(),
		protected ServerConfig $serverConfig = new ServerConfig(),
		protected PacketCodec $packetCodec = new PacketCodec(),
		protected ErrorHandler|null $errorHandler = null,
	) {}

	public function start(): void
	{
		$this->bind($this->serverConfig->authPort, function (SocketInterface $socket): void
		{
			$this->authSocket = $socket;
		});

		$this->bind($this->serverConfig->acctPort, function (SocketInterface $socket): void
		{
			$this->acctSocket = $socket;
		});
	}

	public function stop(): void
	{
		$this->authSocket?->close();
		$this->acctSocket?->close();
		$this->authSocket = null;
		$this->acctSocket = null;
	}

	/**
	 * bind one socket and start reading from it. A bind that fails - the port already taken, or
	 * 1812/1813 without the privilege to take them - is handed to the ErrorHandler; with none
	 * registered it is left to reject, which ReactPHP reports as an unhandled rejection rather
	 * than letting a server that never bound look like one that did.
	 *
	 * @param Closure(SocketInterface): void $keep stashes the socket so stop() can close it
	 */
	protected function bind(int $port, Closure $keep): void
	{
		$address = "{$this->serverConfig->host}:{$port}";

		$this->serverConfig->factory
			->createServer($address)
			->then(
				function (SocketInterface $socket) use ($keep): void
				{
					$keep($socket);
					$this->listen($socket);
				},
				null === $this->errorHandler ? null : function (Throwable $throwable) use ($address): void
				{
					$this->errorHandler?->handle($throwable, $address);
				},
			)
		;
	}

	protected function listen(SocketInterface $socket): void
	{
		$socket->on('message', function (string $data, string $remoteAddress, SocketInterface $socket): void
		{
			try {
				$this->receive($data, $remoteAddress, $socket);
			}
			catch (Throwable $throwable) {
				// a handler that throws must not take the daemon down with it - one crafted packet
				// from a registered peer would otherwise stop the whole service
				$this->errorHandler?->handle($throwable, $remoteAddress);
			}
		});
	}

	/**
	 * everything we do with one inbound datagram; anything it throws is caught by the caller and
	 * costs that single packet, nothing more.
	 *
	 * @throws RadiusRuntimeException the datagram isn't a RADIUS packet we can read
	 * @throws UnexpectedReplyException an answer to a request we aren't waiting on
	 * @throws InvalidAuthenticatorException a packet not signed with the peer's secret
	 */
	protected function receive(string $data, string $remoteAddress, SocketInterface $socket): void
	{
		// react hands us 'ip:port', which for v6 is '[::1]:42790' - splitting on the first colon
		// would hand back '[' and quietly lose every v6 peer
		$source = parse_url("udp://{$remoteAddress}");
		if (!is_array($source) || !isset($source['host'], $source['port'])) {
			return;
		}

		if (!$peer = $this->peerRegistry->get(trim($source['host'], '[]'))) {
			return;
		}

		$message = $this->packetCodec->decode($data, $peer);

		$requestQueue = $this->requestsTo($peer);

		// the channel talks to this peer on the port it just reached us from, not the CoA port
		$channel = new Channel($peer->reachedFrom($source['port']), $message, $socket, $requestQueue, $this->packetCodec);

		if (!$message instanceof Reply) {
			// a request carries an authenticator derived from its own contents, and RFC 2866 4.1 says
			// an unverifiable one is to be discarded - without this anyone who can spoof the peer's
			// address can write its accounting records. An Access-Request has nothing in it to check
			// and says so by signing nothing, so this stays a single unconditional call
			if (!$this->packetCodec->verifyRequestAuthenticator($data, $message)) {
				throw new InvalidAuthenticatorException($message);
			}

			$this->dispatch($message, $channel);

			return;
		}

		if (!$requestAuthenticator = $requestQueue->pendingAuthenticator($message->getIdentifier())) {
			throw new UnexpectedReplyException($message);
		}

		if (!$this->packetCodec->verifyReplyAuthenticator($data, $requestAuthenticator, $peer)) {
			throw new InvalidAuthenticatorException($message);
		}

		$requestQueue->resolve($message, $channel);
	}

	/**
	 * one queue per peer, made on first contact - the identifier space it hands out is per peer,
	 * so they must not be shared.
	 */
	protected function requestsTo(Peer $peer): RequestQueue
	{
		return $this->requestQueues[$peer->ip] ??= new RequestQueue($peer);
	}

	protected function dispatch(Message $message, Channel $channel): void
	{
		$stop = false;

		$stopBubbling = static function () use (&$stop): void
		{
			$stop = true;
		};

		foreach ($this->handlers as $handler) {
			$handler->handle($message, $channel, $stopBubbling);
			if ($stop) {
				return;
			}
		}
	}
}
