<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Tests;

use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * drives the real server process with the system radclient binary: actual UDP, actual
 * freeradius-generated packets, actual replies.
 *
 * @internal
 */
#[CoversNothing]
final class E2eRadiusClientTest extends TestCase
{
	private const string SECRET = 'testing123';

	private const int AUTH_PORT = 18120;

	private const int ACCT_PORT = 18130;

	/**
	 * @var resource|null
	 */
	private $server;

	private string|null $errorLog = null;

	public function testAccessRequestForKnownUserIsAccepted(): void
	{
		$output = $this->radiusCommand(self::AUTH_PORT, 'auth', "User-Name = alice\nUser-Password = hunter2\nNAS-IP-Address = 127.0.0.1\n");

		$this->assertStringContainsString('Access-Accept', $output);
		$this->assertStringContainsString('hello alice', $output);
	}

	public function testAccessRequestForUnknownUserIsRejected(): void
	{
		$output = $this->radiusCommand(self::AUTH_PORT, 'auth', "User-Name = mallory\nUser-Password = hunter2\nNAS-IP-Address = 127.0.0.1\n");

		$this->assertStringContainsString('Access-Reject', $output);
	}

	/**
	 * radclient hides the password per RFC 2865 5.2, so this only passes if PacketCodec actually
	 * reverses that stream.
	 */
	public function testAccessRequestWithWrongPasswordIsRejected(): void
	{
		$output = $this->radiusCommand(self::AUTH_PORT, 'auth', "User-Name = alice\nUser-Password = wrong\nNAS-IP-Address = 127.0.0.1\n");

		$this->assertStringContainsString('Access-Reject', $output);
	}

	/**
	 * a handler that throws costs the packet that triggered it and nothing else - otherwise any
	 * registered NAS could stop the service with one request - and the server hands what it
	 * caught to the errorHandler it was built with.
	 */
	public function testAThrowingHandlerIsReportedAndDoesNotTakeTheServerDown(): void
	{
		$this->radiusCommand(self::AUTH_PORT, 'auth', "User-Name = boom\nUser-Password = hunter2\nNAS-IP-Address = 127.0.0.1\n");

		$output = $this->radiusCommand(self::AUTH_PORT, 'auth', "User-Name = alice\nUser-Password = hunter2\nNAS-IP-Address = 127.0.0.1\n");

		$this->assertStringContainsString('Access-Accept', $output);
		$this->assertStringContainsString('handler blew up on purpose', (string) file_get_contents((string) $this->errorLog));
	}

	/**
	 * an answer to a request we never sent is reported and dropped, not acted on - radclient
	 * can't emit a CoA-Ack, so this one goes out as raw bytes.
	 */
	public function testAnUnmatchedReplyIsReportedAndDropped(): void
	{
		$socket = stream_socket_client(sprintf('udp://127.0.0.1:%d', self::AUTH_PORT));
		$this->assertIsResource($socket);

		fwrite($socket, pack('CCn', 44, 99, 20).str_repeat("\0", 16)); // CoA-Ack, identifier 99
		fclose($socket);

		$output = $this->radiusCommand(self::AUTH_PORT, 'auth', "User-Name = alice\nUser-Password = hunter2\nNAS-IP-Address = 127.0.0.1\n");

		$this->assertStringContainsString('Access-Accept', $output); // and the server is still up
		$this->assertStringContainsString('no request is waiting on', (string) file_get_contents((string) $this->errorLog));
	}

	/**
	 * RFC 2866 4.1: an Accounting-Request that isn't signed with the shared secret is discarded,
	 * so a spoofed source address can't write a peer's session records.
	 */
	public function testAccountingRequestSignedWithTheWrongSecretIsRejected(): void
	{
		$output = shell_exec(sprintf(
			'printf %s | radclient -x -t 1 -r 1 127.0.0.1:%d acct %s 2>&1',
			escapeshellarg("User-Name = alice\nAcct-Status-Type = Start\nNAS-IP-Address = 127.0.0.1\n"),
			self::ACCT_PORT,
			escapeshellarg('not-the-secret'),
		));

		$this->assertStringContainsString('No reply', (string) $output);
		$this->assertStringContainsString('not signed with our shared secret', (string) file_get_contents((string) $this->errorLog));
	}

	public function testAccountingRequestIsAcknowledged(): void
	{
		$output = $this->radiusCommand(self::ACCT_PORT, 'acct', "User-Name = alice\nAcct-Status-Type = Start\nAcct-Session-Id = \"e2e-001\"\nNAS-IP-Address = 127.0.0.1\n");

		$this->assertStringContainsString('Accounting-Response', $output);
	}

	protected function setUp(): void
	{
		if ('' === trim((string) shell_exec('command -v radclient')) || '0' === trim((string) shell_exec('command -v radclient'))) {
			$this->markTestSkipped('radclient is not installed');
		}

		$command = sprintf(
			'exec php %s %d %d %s 30',
			escapeshellarg(__DIR__.'/e2e/server.php'),
			self::AUTH_PORT,
			self::ACCT_PORT,
			escapeshellarg(self::SECRET),
		);

		$this->errorLog = tempnam(sys_get_temp_dir(), 'radser-errors-');
		$this->assertIsString($this->errorLog);

		$pipes = [];
		$server = proc_open($command, [1 => ['file', '/dev/null', 'w'], 2 => ['file', $this->errorLog, 'w']], $pipes);
		$this->assertIsResource($server);
		$this->server = $server;

		$this->waitForPort(self::AUTH_PORT);
		$this->waitForPort(self::ACCT_PORT);
	}

	protected function tearDown(): void
	{
		if (is_resource($this->server)) {
			proc_terminate($this->server, SIGKILL);
			proc_close($this->server);
		}

		$this->server = null;

		if (null !== $this->errorLog) {
			@unlink($this->errorLog);
			$this->errorLog = null;
		}
	}

	/**
	 * radclient verifies the reply authenticator itself, so a bad signature surfaces here as a
	 * failure rather than an accept.
	 */
	private function radiusCommand(int $port, string $type, string $attributes): string
	{
		$command = sprintf(
			'printf %s | radclient -x -t 2 -r 1 127.0.0.1:%d %s %s 2>&1',
			escapeshellarg($attributes),
			$port,
			$type,
			escapeshellarg(self::SECRET),
		);

		return (string) shell_exec($command);
	}

	private function waitForPort(int $port): void
	{
		for ($attempt = 0; $attempt < 100; ++$attempt) {
			if (str_contains((string) shell_exec('ss -lun 2>/dev/null'), ":{$port}")) {
				return;
			}

			usleep(50_000);
		}

		$this->fail("server did not start listening on udp/{$port}");
	}
}
