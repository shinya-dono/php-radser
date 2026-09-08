<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Support;

use React\Datagram\Factory;

/**
 * where the server binds and which event loop it binds on.
 */
readonly class ServerConfig
{
	/**
	 * @param string $host     listen host
	 * @param int $authPort    listen port used for authorization requests
	 * @param int $acctPort    listen port used for accounting requests
	 * @param Factory $factory override for reactphp Datagram client factory
	 */
	public function __construct(
		public string $host = '127.0.0.1',
		public int $authPort = 1812,
		public int $acctPort = 1813,
		public Factory $factory = new Factory(),
	) {}
}
