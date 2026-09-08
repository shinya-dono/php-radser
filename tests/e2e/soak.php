<?php

declare(strict_types = 1);

/**
 * hammers a real server process with a mix of every packet shape it can meet - good requests,
 * ones whose handler throws, replies nobody is waiting on, and garbage - then reports whether
 * its memory settled or kept climbing.
 *
 *   php tests/e2e/soak.php [packets] [batch]
 *
 * a healthy run plateaus: PHP grows its heap early, then holds. A climb that tracks the packet
 * count means something is being kept alive per request.
 */

use Shinya\PhpRadser\Peer;
use Shinya\PhpRadser\PacketCode;
use Shinya\PhpRadser\Support\PacketCodec;
use Shinya\PhpRadser\Contracts\AccessRequest;
use Shinya\PhpRadser\Rfc2865\Attributes\UserName;
use Shinya\PhpRadser\Rfc2865\Attributes\UserPassword;

require dirname(__DIR__, 2).'/vendor/autoload.php';

const SECRET = 'testing123';
const AUTH_PORT = 18220;
const ACCT_PORT = 18221;

$packets = (int) ($argv[1] ?? 20_000);
$batch = (int) ($argv[2] ?? 500);
$lifetime = 30 + (int) (($packets / $batch) / 10);

$memoryLog = (string) tempnam(sys_get_temp_dir(), 'radser-soak-');

$server = proc_open(
	sprintf('exec php %s %d %d %s %d', escapeshellarg(__DIR__.'/server.php'), AUTH_PORT, ACCT_PORT, escapeshellarg(SECRET), $lifetime),
	[1 => ['file', $memoryLog, 'w'], 2 => ['file', '/dev/null', 'w']],
	$pipes,
	env_vars: ['RADSER_MEMORY_LOG' => '1', 'PATH' => (string) getenv('PATH')],
);

if (!is_resource($server)) {
	exit("could not start the server\n");
}

for ($waited = 0; $waited < 100 && !str_contains((string) shell_exec('ss -lun 2>/dev/null'), ':'.AUTH_PORT); ++$waited) {
	usleep(50_000);
}

$codec = new PacketCodec();
$peer = new Peer('127.0.0.1', SECRET);
$socket = stream_socket_client('udp://127.0.0.1:'.AUTH_PORT);

if (!is_resource($socket)) {
	exit("could not open a client socket\n");
}

/**
 * one of each shape, so the exception paths get soaked as hard as the happy one.
 */
$shapes = [
	'accepted'  => static function () use ($codec, $peer): string
	{
		$accessRequest = new AccessRequest($peer, PacketCode::AccessRequest)
			->push(UserName::make('alice'))
			->push(UserPassword::make('hunter2'))
		;

		return $codec->encode($accessRequest, random_int(0, 255));
	},
	'handler throws' => static function () use ($codec, $peer): string
	{
		$accessRequest = new AccessRequest($peer, PacketCode::AccessRequest)->push(UserName::make('boom'));

		return $codec->encode($accessRequest, random_int(0, 255));
	},
	'coa that times out' => static function () use ($codec, $peer): string
	{
		$accessRequest = new AccessRequest($peer, PacketCode::AccessRequest)->push(UserName::make('coa'));

		return $codec->encode($accessRequest, random_int(0, 255));
	},
	'unmatched reply' => static fn (): string => pack('CCn', PacketCode::CoaAck, random_int(0, 255), 20).random_bytes(16),
	'undecodable'     => static fn (): string => random_bytes(random_int(1, 40)),
];

printf("soaking %s with %s packets in batches of %s\n", 'udp://127.0.0.1:'.AUTH_PORT, number_format($packets), number_format($batch));

$sent = 0;
while ($sent < $packets) {
	foreach ($shapes as $shape) {
		for ($i = 0; $i < $batch / count($shapes) && $sent < $packets; ++$i) {
			fwrite($socket, $shape());
			++$sent;
		}
	}

	usleep(20_000); // let the loop drain rather than filling the kernel buffer and dropping
}

fclose($socket);
sleep(2); // one last sampling window

proc_terminate($server, SIGKILL);
proc_close($server);

$samples = [];
foreach (explode("\n", (string) file_get_contents($memoryLog)) as $line) {
	if (4 === count($parts = explode(' ', trim($line)))) {
		$samples[] = [(float) $parts[0], (int) $parts[1], (int) $parts[2], (int) $parts[3]];
	}
}

@unlink($memoryLog);

if (count($samples) < 4) {
	exit("not enough samples - did the server stay up?\n");
}

$first = $samples[0];
$settled = $samples[(int) (count($samples) / 4)]; // after the heap has warmed up
$last = $samples[count($samples) - 1];

$peak = max(array_column($samples, 3));

printf("\n%-28s %12s %12s %12s\n", 'sample', 'in use', 'from OS', 'process RSS');
foreach (['at startup' => $first, 'after warmup' => $settled, 'at the end' => $last] as $label => [$at, $used, $real, $rss]) {
	printf("%-28s %12s %12s %12s\n", $label, number_format($used), number_format($real), number_format($rss));
}

printf("%-28s %12s %12s %12s\n", 'peak', '', '', number_format($peak));

$drift = $last[1] - $settled[1];
printf(
	"\n%s packets over %.1fs\nheap drifted %s%s bytes after warmup (%.4f per packet), RSS %s%s bytes (%.4f per packet)\n",
	number_format($sent),
	$last[0] - $first[0],
	$drift < 0 ? '' : '+',
	number_format($drift),
	$drift / max(1, $sent),
	($rssDrift = ($last[3] - $settled[3])) < 0 ? '' : '+',
	number_format($rssDrift),
	$rssDrift / max(1, $sent),
);
