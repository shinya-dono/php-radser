<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Attributes;

use Override;
use Shinya\PhpRadser\Peer;
use Shinya\PhpRadser\Contracts\EncryptedAttribute;
use Shinya\PhpRadser\Exceptions\RadiusRuntimeException;

/**
 * text hidden with the RFC 2865 5.2 stream ('encrypt=1' in the dictionary); the stream itself is
 * keyed on the shared secret, so it lives on Peer - this class only tracks which side of it the
 * value it holds is currently on.
 *
 * @see \Shinya\PhpRadser\Rfc2865\Attributes\UserPassword
 */
abstract class EncryptedStringAttribute extends StringAttribute implements EncryptedAttribute
{
	protected Peer|null $peer = null;

	protected string $authenticator = '';

	/**
	 * true when the bytes we hold came off the wire and are still hidden; false for a value
	 * handed to make(), which is plaintext already.
	 */
	protected bool $hidden = false;

	#[Override]
	public static function hydrate(mixed $value): static
	{
		$attribute = parent::hydrate($value);
		$attribute->hidden = true;

		return $attribute;
	}

	#[Override]
	public function setPeer(Peer $peer): void
	{
		$this->peer = $peer;
	}

	#[Override]
	public function setAuthenticator(string $authenticator): void
	{
		$this->authenticator = $authenticator;
	}

	/**
	 * the value in the clear: unwrapped if it came off the wire, as-given if it was built with
	 * make(); null if absent, or if it arrived hidden and no peer was ever injected to unwrap it
	 * with. Deliberately not on EncryptedAttribute - the return type belongs to the value family,
	 * not to being encrypted.
	 */
	public function getPlainText(): string|null
	{
		$value = $this->read();

		if (null === $value || !$this->hidden) {
			return $value; // built by hand, never hidden in the first place
		}

		if (!$this->peer instanceof Peer) {
			return null; // hydrated outside a Message, so there is no key to undo it with
		}

		return $this->peer->decipher($value, $this->authenticator);
	}

	/**
	 * refuses to hand back a plaintext value the caller built by hand - this attribute is only
	 * ever legal on the wire hidden, and silently sending the password in the clear is worse than
	 * failing the send.
	 *
	 * @throws RadiusRuntimeException
	 */
	#[Override]
	public function dehydrate(): string|null
	{
		$value = $this->read();

		if (null === $value || $this->hidden) {
			return $value; // already in wire form
		}

		if (!$this->peer instanceof Peer) {
			throw new RadiusRuntimeException(sprintf("attribute '%s' holds a plaintext value and no peer to hide it with, refusing to send it in the clear", static::identifier()));
		}

		return $this->peer->cipher($value, $this->authenticator);
	}
}
