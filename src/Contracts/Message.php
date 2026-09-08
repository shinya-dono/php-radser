<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Contracts;

use Shinya\PhpRadser\Peer;
use Shinya\PhpRadser\Support\PacketCodec;

/**
 * a single RADIUS message, inbound or outbound. An inbound message (built by Server from a
 * decoded datagram) carries raw wire attributes, read lazily via get()/has(). An outbound message
 * (built by the developer) is assembled by push()ing the Attribute instances to send, then handed
 * to Channel.
 *
 * This base class is what any packet code we have no specific behaviour for decodes to, so an
 * unknown code is still a message a handler can read - subclass it and register the subclass in
 * PacketCodec::$knownMessageTypes only when a code needs to behave differently; see Reply and
 * AccessRequest.
 */
class Message
{
	/**
	 * memoised, because an Access-Request's seed is random and the hidden attributes and the
	 * header have to be built around the same 16 bytes.
	 */
	protected string|null $authenticatorSeed = null;

	/**
	 * @var array<class-string<Attribute>, Attribute>
	 */
	private array $hydratedAttributes = [];

	/**
	 * @var Attribute[]
	 */
	private array $pushed = [];

	/**
	 * @param int<0, 255> $identifier
	 * @param int $packetCode wire packet type; {@see PacketCode} for the named ones
	 * @param string $authenticator raw 16-byte authenticator, '' if this message wasn't received off the wire
	 * @param array<int, string> $standardAttributes wire type => raw bytes
	 * @param array<int, array<int, string>> $vendorAttributes vendor id => wire type => raw bytes
	 */
	public function __construct(
		public readonly Peer $peer,
		public readonly int $packetCode,
		public readonly int $identifier = 0,
		public readonly string $authenticator = '',
		public readonly array $standardAttributes = [],
		public readonly array $vendorAttributes = [],
	) {}

	public function getPeer(): Peer
	{
		return $this->peer;
	}

	public function getPacketCode(): int
	{
		return $this->packetCode;
	}

	/**
	 * this message's type, for a human reading a log line - the class we resolved the code to
	 * says more than the number, and an unnamed code still gets one.
	 */
	public function describe(): string
	{
		$segments = explode('\\', static::class);

		return sprintf('%s(code %d)', end($segments), $this->packetCode);
	}

	/**
	 * an answer to this message, keyed to its authenticator and identifier the way RFC 2865 3
	 * wants a reply keyed - which is the only way to build one that Channel::send() will accept.
	 */
	public function reply(int $packetCode): Reply
	{
		return new Reply($this->peer, $packetCode, $this->identifier, $this->authenticator);
	}

	/**
	 * the 16 bytes this packet's authenticator is computed over and its hidden attributes are
	 * keyed to. Plain 16 zero bytes for a request that derives its authenticator from its own
	 * contents, which is everything except an Access-Request ({@see AccessRequest}) and a reply
	 * ({@see Reply}).
	 */
	public function authenticatorSeed(): string
	{
		return $this->authenticatorSeed ??= PacketCodec::ZERO_AUTHENTICATOR;
	}

	/**
	 * the authenticator that actually goes on the wire: RFC 2865 3, MD5 over the packet with the
	 * seed in the authenticator's place and the shared secret appended. Also what an inbound
	 * packet is verified against, so a subclass that overrides this decides both how its packets
	 * are signed and how they are checked.
	 */
	public function signedAuthenticator(string $header, string $attributeBytes): string
	{
		return $this->peer->sign($header.$this->authenticatorSeed().$attributeBytes);
	}

	/**
	 * @return int<0, 255>
	 */
	public function getIdentifier(): int
	{
		return $this->identifier;
	}

	public function getAuthenticator(): string
	{
		return $this->authenticator;
	}

	/**
	 * queue an attribute to be sent when this message is handed to Channel::send()/sendAsync().
	 */
	public function push(Attribute $attribute): static
	{
		$this->pushed[] = $attribute;

		return $this;
	}

	/**
	 * @return iterable<Attribute>
	 */
	public function pushedAttributes(): iterable
	{
		yield from $this->pushed;
	}

	/**
	 * resolve an attribute from this message; it may be empty, {@see has()}.
	 *
	 * @template T of Attribute
	 *
	 * @param class-string<T> $attribute
	 */
	public function get(string $attribute): Attribute
	{
		return $this->hydratedAttributes[$attribute] ??= $this->hydrateAttribute($attribute);
	}

	/**
	 * check if this message has the given attribute.
	 *
	 * @param class-string<Attribute> $attribute
	 */
	public function has(string $attribute): bool
	{
		if (is_a($attribute, VendorAttribute::class, allow_string: true)) {
			return array_key_exists($attribute::type(), $this->vendorAttributes[$attribute::vendorId()] ?? []);
		}

		return array_key_exists($attribute::type(), $this->standardAttributes);
	}

	/**
	 * @template T of Attribute
	 *
	 * @param class-string<T> $attribute
	 */
	private function hydrateAttribute(string $attribute): Attribute
	{
		$hydrated = $attribute::hydrate($this->rawAttributeValue($attribute));

		if ($hydrated instanceof EncryptedAttribute) {
			$hydrated->setPeer($this->peer);
			$hydrated->setAuthenticator($this->authenticator);
		}

		return $hydrated;
	}

	/**
	 * @template T of Attribute
	 *
	 * @param class-string<T> $attribute
	 */
	private function rawAttributeValue(string $attribute): string|null
	{
		if (is_a($attribute, VendorAttribute::class, allow_string: true)) {
			return $this->vendorAttributes[$attribute::vendorId()][$attribute::type()] ?? null;
		}

		return $this->standardAttributes[$attribute::type()] ?? null;
	}
}
