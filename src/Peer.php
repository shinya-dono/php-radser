<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser;

use SensitiveParameter;

/**
 * who we are talking to and the secret we share with them - a value, with no bearing on what is
 * currently in flight ({@see RequestQueue}), which is what makes it cheap to copy per request.
 */
class Peer
{
	/**
	 * where this peer is reachable right now: the ephemeral port a request came from, once Server
	 * has seen one, and the CoA port until then.
	 */
	public protected(set) string $address;

	/**
	 * where the peer listens for the requests we start - CoA, Disconnect, PoD.
	 */
	public protected(set) string $coaAddress;

	/**
	 * @param string $ip the peer's source address, which is what selects it on an inbound packet
	 * @param string $secret the secret shared with this peer
	 * @param int $port the port the peer listens on for the requests we start - CoA, Disconnect, PoD
	 */
	public function __construct(
		public readonly string $ip,
		#[SensitiveParameter]
		protected readonly string $secret,
		public readonly int $port = 3799,
	) {
		$this->address = static::endpoint($this->ip, $this->port);
		$this->coaAddress = $this->address;
	}

	/**
	 * 'ip:port' is only unambiguous for v4 - a v6 address is full of colons, so it has to be
	 * bracketed before the port is appended.
	 */
	protected static function endpoint(string $ip, int $port): string
	{
		if (false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			return "[{$ip}]:{$port}";
		}

		return "{$ip}:{$port}";
	}

	/**
	 * the same peer, pinned to the port a request actually arrived from - that is where its reply
	 * has to go, which is never the CoA port it listens on.
	 */
	public function reachedFrom(int $port): static
	{
		return clone ($this, ['address' => static::endpoint($this->ip, $port)]);
	}

	/**
	 * RFC 2865 3: a packet's authenticator is MD5 over the packet with the shared secret appended.
	 */
	public function sign(string $content): string
	{
		return md5($content.$this->secret, binary: true);
	}

	/**
	 * hide a value the way RFC 2865 5.2 wants it hidden: null-pad to a multiple of 16, then XOR
	 * each block with MD5(secret + previous ciphertext block), the first block seeded with $seed
	 * - the request authenticator, or the 16 zero bytes on a CoA, whichever the packet was built
	 * around.
	 *
	 * @param string $seed 16 bytes the far side can reproduce
	 */
	public function cipher(#[SensitiveParameter] string $plaintext, string $seed): string
	{
		$ciphertext = '';
		$previous = $seed;
		$padded = str_pad($plaintext, max(16, (int) ceil(strlen($plaintext) / 16) * 16), "\0");

		foreach (str_split($padded, 16) as $block) {
			$previous = $block ^ md5($this->secret.$previous, binary: true);
			$ciphertext .= $previous;
		}

		return $ciphertext;
	}

	/**
	 * undo cipher(); both directions chain on the ciphertext block, so this is not the same walk
	 * backwards. Note the secret goes in front here, unlike sign() - RFC 2865 genuinely specifies
	 * the two orders differently.
	 */
	public function decipher(string $ciphertext, string $seed): string
	{
		$plaintext = '';
		$previous = $seed;

		foreach (str_split($ciphertext, 16) as $block) {
			$plaintext .= $block ^ md5($this->secret.$previous, binary: true);
			$previous = $block;
		}

		return rtrim($plaintext, "\0");
	}
}
