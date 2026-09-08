<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Contracts;

use Shinya\PhpRadser\Peer;

/**
 * an attribute the sender hid behind the shared secret, per the dictionary's 'encrypt=' flag; the
 * raw bytes read() hands back are ciphertext. Message injects the peer and the request
 * authenticator right after hydration - the two halves of the key - so getPlainText() can unwrap
 * it.
 */
interface EncryptedAttribute
{
	public function setPeer(Peer $peer): void;

	public function setAuthenticator(string $authenticator): void;
}
