<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser;

use React\EventLoop\Loop;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Shinya\PhpRadser\Contracts\Channel;
use Shinya\PhpRadser\Contracts\Message;
use Shinya\PhpRadser\Exceptions\RadiusRuntimeException;
use Shinya\PhpRadser\Exceptions\RequestTimedOutException;

/**
 * the requests we have sent one peer and not yet had an answer to. Keyed by the identifier they
 * went out with - that byte is the only correlation RADIUS gives us, so one queue holds at most
 * 256 requests in flight at a time.
 */
class RequestQueue
{
	/**
	 * identifier => the authenticator we sent, which is what the answer is signed over, and who is
	 * waiting on it.
	 *
	 * @var array<int, array{0: string, 1: Deferred<array{Message, Channel}>}>
	 */
	protected array $pending = [];

	/**
	 * @param Peer $peer whose requests these are, for the exceptions this raises
	 * @param float $timeout seconds to wait for an answer before giving up on a request and
	 *                       handing its identifier back to the pool
	 */
	public function __construct(
		public readonly Peer $peer,
		public readonly float $timeout = 5.0,
	) {}

	/**
	 * claim an identifier no request to this peer is currently waiting on.
	 *
	 * @return int<0, 255>
	 *
	 * @throws RadiusRuntimeException
	 */
	public function nextIdentifier(): int
	{
		// note: lowest free wins, so an answered identifier is reusable immediately - a stale
		// reply to the previous holder is caught by the authenticator check in Server. Cycle
		// through the space instead if a NAS turns up that suppresses duplicates on identifier alone
		for ($identifier = 0; $identifier < 256; ++$identifier) {
			if (!isset($this->pending[$identifier])) {
				return $identifier;
			}
		}

		throw new RadiusRuntimeException(sprintf('all 256 identifiers for peer %s are in flight', $this->peer->ip));
	}

	/**
	 * park a sent request until its answer arrives or $timeout elapses; the returned promise
	 * rejects with a RequestTimedOutException, so callers have to handle it.
	 *
	 * @return PromiseInterface<array{Message, Channel}>
	 */
	public function defer(int $identifier, string $requestAuthenticator): PromiseInterface
	{
		/** @var Deferred<array{Message, Channel}> $deferred */
		$deferred = new Deferred();

		$this->pending[$identifier] = [$requestAuthenticator, $deferred];

		$timer = Loop::addTimer(
			$this->timeout,
			function () use ($identifier, $deferred): void
			{
				unset($this->pending[$identifier]);

				$deferred->reject(new RequestTimedOutException($this->peer, $identifier, $this->timeout));
			},
		);

		return $deferred->promise()->finally(
			static function () use ($timer): void
			{
				Loop::cancelTimer($timer);
			},
		);
	}

	/**
	 * the authenticator we sent with request $identifier, or null if we aren't waiting on that
	 * identifier - an answer is only ours if it verifies against it.
	 */
	public function pendingAuthenticator(int $identifier): string|null
	{
		return $this->pending[$identifier][0] ?? null;
	}

	public function resolve(Message $message, Channel $channel): void
	{
		if (!$pending = $this->pending[$key = $message->getIdentifier()] ?? null) {
			return;
		}

		unset($this->pending[$key]);

		$pending[1]->resolve([$message, $channel]);
	}
}
