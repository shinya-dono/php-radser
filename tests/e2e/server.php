<?php

declare(strict_types = 1);

/**
 * standalone server used by E2eRadclientTest: run with
 *   php tests/e2e/server.php <authPort> <acctPort> <secret> <lifetimeSeconds>
 * accepts alice/hunter2, rejects everything else, acks any accounting request, and throws
 * on user 'boom' so the test can check one bad packet doesn't take the daemon down.
 */

use React\EventLoop\Loop;
use Shinya\PhpRadser\Peer;
use Shinya\PhpRadser\Server;
use Shinya\PhpRadser\PacketCode;
use Shinya\PhpRadser\PeerRegistry;
use Shinya\PhpRadser\Contracts\Channel;
use Shinya\PhpRadser\Contracts\Handler;
use Shinya\PhpRadser\Contracts\Message;
use Shinya\PhpRadser\Support\ServerConfig;
use Shinya\PhpRadser\Contracts\ErrorHandler;
use Shinya\PhpRadser\Rfc2865\Attributes\UserName;
use Shinya\PhpRadser\Rfc2865\Attributes\ReplyMessage;
use Shinya\PhpRadser\Rfc2865\Attributes\UserPassword;

require dirname(__DIR__, 2).'/vendor/autoload.php';

[, $authPort, $acctPort, $secret, $lifetime] = $argv;

$registry = new PeerRegistry();
$registry->register(new Peer('127.0.0.1', $secret));

$handler = new class implements Handler
{
	public function handle(Message $message, Channel $channel, Closure $stopBubbling): void
	{
		// soak path: start a request nothing will ever answer, so the pending queue, its timer
		// and its rejected promise all get exercised and then have to be cleaned up
		if ('coa' === $message->get(UserName::class)->read()) {
			$channel
				->sendAsync(new Message($message->getPeer(), PacketCode::DisconnectRequest))
				->catch(static fn (Throwable $throwable): null => null)
			;

			$stopBubbling();

			return;
		}

		if ('boom' === $message->get(UserName::class)->read()) {
			throw new RuntimeException('handler blew up on purpose');
		}

		$code = match ($message->getPacketCode()) {
			PacketCode::AccessRequest     => 'alice' === $message->get(UserName::class)->read() && 'hunter2' === $message->get(UserPassword::class)->getPlainText()
				? PacketCode::AccessAccept
				: PacketCode::AccessReject,
			PacketCode::AccountingRequest => PacketCode::AccountingResponse,
			default                       => null,
		};

		if (null === $code) {
			return;
		}

		$reply = $message->reply($code);
		if (PacketCode::AccountingResponse !== $code) {
			$reply->push(ReplyMessage::hydrate('hello '.$message->get(UserName::class)->read()));
		}

		$channel->send($reply);
		$stopBubbling();
	}
};

$errorHandler = new class implements ErrorHandler
{
	public function handle(Throwable $throwable, string $remoteAddress): void
	{
		fwrite(STDERR, sprintf("dropped a packet from %s: %s\n", $remoteAddress, $throwable->getMessage()));
	}
};

$server = new Server([$handler], $registry, new ServerConfig('127.0.0.1', (int) $authPort, (int) $acctPort), errorHandler: $errorHandler);
$server->start();

Loop::addTimer((float) $lifetime, static fn () => $server->stop());

// soak instrumentation: tests/e2e/soak.php reads these lines back to see whether a long run
// settles or climbs. Off unless asked for, and stdout is /dev/null under the e2e test anyway
if (false !== getenv('RADSER_MEMORY_LOG')) {
	Loop::addPeriodicTimer(0.25, static function (): void
	{
		// RSS as well as PHP's own heap - a leak in an extension or plain fragmentation shows in
		// the first and not the second
		preg_match('/VmRSS:\s+(\d+)/', (string) @file_get_contents('/proc/self/status'), $rss);

		printf("%.3f %d %d %d\n", microtime(as_float: true), memory_get_usage(), memory_get_usage(real_usage: true), 1024 * (int) ($rss[1] ?? 0));
	});
}
