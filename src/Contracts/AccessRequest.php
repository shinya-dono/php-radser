<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Contracts;

use Override;
use Random\RandomException;

/**
 * RFC 2865 3: an Access-Request carries a freshly random Request Authenticator instead of one
 * derived from its own contents. It is the nonce the reply and any hidden attributes are keyed
 * to, which leaves nothing in the packet to verify. Status-Server (code 12, RFC 5997 3) works the
 * same way - map it to this class.
 */
class AccessRequest extends Message
{
	/**
	 * inbound, the seed is whatever arrived; outbound, a fresh nonce, memoised by the parent so
	 * the hidden attributes and the header agree on it.
	 *
	 * @throws RandomException
	 */
	#[Override]
	public function authenticatorSeed(): string
	{
		return $this->authenticatorSeed ??= '' !== $this->authenticator ? $this->authenticator : random_bytes(16);
	}

	/**
	 * the seed is the authenticator here, there is nothing to sign. Verification compares this
	 * against what arrived and so passes by construction, which is the correct answer for a
	 * packet with nothing in it to check.
	 *
	 * @throws RandomException
	 */
	#[Override]
	public function signedAuthenticator(string $header, string $attributeBytes): string
	{
		return $this->authenticatorSeed();
	}
}
