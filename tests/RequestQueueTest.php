<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Tests;

use Throwable;
use React\EventLoop\Loop;
use Shinya\PhpRadser\Peer;
use PHPUnit\Framework\TestCase;
use Shinya\PhpRadser\PacketCode;
use Shinya\PhpRadser\RequestQueue;
use React\Datagram\SocketInterface;
use Shinya\PhpRadser\Contracts\Reply;
use Shinya\PhpRadser\Contracts\Channel;
use Shinya\PhpRadser\Contracts\Message;
use PHPUnit\Framework\Attributes\CoversNothing;
use Shinya\PhpRadser\Exceptions\RadiusRuntimeException;
use Shinya\PhpRadser\Exceptions\RequestTimedOutException;

/**
 * @internal
 */
#[CoversNothing]
final class RequestQueueTest extends TestCase
{
	public function testIdentifiersAreNotHandedOutTwiceWhileInFlight(): void
	{
		$requestQueue = new RequestQueue(new Peer('10.0.0.1', 'secret'), timeout: 0.01);

		$first = $requestQueue->nextIdentifier();
		$requestQueue->defer($first, str_repeat("\x01", 16))->catch(static fn (Throwable $throwable): null => null);

		$this->assertNotSame($first, $requestQueue->nextIdentifier());
		$this->assertSame(str_repeat("\x01", 16), $requestQueue->pendingAuthenticator($first));
		$this->assertNull($requestQueue->pendingAuthenticator(($first + 1) % 256));
	}

	public function testRunningOutOfIdentifiersFailsLoudly(): void
	{
		$requestQueue = new RequestQueue(new Peer('10.0.0.1', 'secret'), timeout: 0.01);

		for ($sent = 0; $sent < 256; ++$sent) {
			$requestQueue->defer($requestQueue->nextIdentifier(), str_repeat("\0", 16))->catch(static fn (Throwable $throwable): null => null);
		}

		$this->expectException(RadiusRuntimeException::class);

		$requestQueue->nextIdentifier();
	}

	/**
	 * a lost answer has to hand its identifier back, or a peer bleeds the 256-wide space one
	 * dropped packet at a time.
	 */
	public function testATimedOutRequestFreesItsIdentifier(): void
	{
		$requestQueue = new RequestQueue(new Peer('10.0.0.1', 'secret'), timeout: 0.01);

		$identifier = $requestQueue->nextIdentifier();
		$rejection = null;
		$requestQueue->defer($identifier, str_repeat("\0", 16))->catch(static function (Throwable $throwable) use (&$rejection): null
		{
			$rejection = $throwable;

			return null;
		});

		Loop::addTimer(0.05, static fn (): null => null); // let every pending timer drain
		Loop::run();

		$this->assertInstanceOf(RequestTimedOutException::class, $rejection);
		$this->assertSame($identifier, $rejection->identifier);
		$this->assertNull($requestQueue->pendingAuthenticator($identifier));
	}

	public function testResolveOnlyFiresTheRequestWithThatIdentifier(): void
	{
		$requestQueue = new RequestQueue($peer = new Peer('10.0.0.1', 'secret'), timeout: 0.01);
		$channel = new Channel($peer, new Message($peer, PacketCode::CoaRequest), $this->createStub(SocketInterface::class), $requestQueue);

		$resolved = null;
		$requestQueue->defer(7, str_repeat("\0", 16))->then(static function (array $answer) use (&$resolved): null
		{
			$resolved = $answer[0];

			return null;
		});

		$requestQueue->resolve(new Reply($peer, PacketCode::CoaAck, 9), $channel);
		$this->assertNotInstanceOf(Message::class, $resolved);

		$requestQueue->resolve($reply = new Reply($peer, PacketCode::CoaAck, 7), $channel);
		$this->assertSame($reply, $resolved);
		$this->assertNull($requestQueue->pendingAuthenticator(7)); // and the identifier goes back to the pool
	}
}
