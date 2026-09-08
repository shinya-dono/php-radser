<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser;

/**
 * in-memory table of known peers, keyed by IP. Override get() to look them up somewhere else.
 */
class PeerRegistry
{
	/**
	 * @var array<array-key, Peer> keyed by self::key(); array-key rather than string because PHP
	 *                             files a numeric-looking key as an int whatever we hand it
	 */
	protected array $clients = [];

	public function register(Peer $peer): void
	{
		$this->clients[$this->key($peer->ip)] = $peer;
	}

	/**
	 * the peer registered for this source address, or null if it is not one we know.
	 */
	public function get(string $ip): Peer|null
	{
		return $this->clients[$this->key($ip)] ?? null;
	}

	/**
	 * one v6 address has many spellings - '::1', '0:0:0:0:0:0:0:1' and '::0001' are the same host
	 * - so peers are filed under the packed form rather than whatever text they were registered
	 * with; anything that isn't an IP is left alone.
	 */
	private function key(string $ip): string
	{
		$packed = inet_pton($ip);

		return false === $packed ? $ip : 'ip:'.bin2hex($packed);
	}
}
