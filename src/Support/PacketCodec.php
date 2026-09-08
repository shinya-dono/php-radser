<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Support;

use Shinya\PhpRadser\Peer;
use Random\RandomException;
use Shinya\PhpRadser\PacketCode;
use Shinya\PhpRadser\Contracts\Reply;
use Shinya\PhpRadser\Contracts\Message;
use Shinya\PhpRadser\Contracts\Attribute;
use Shinya\PhpRadser\Contracts\AccessRequest;
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Contracts\EncryptedAttribute;
use Shinya\PhpRadser\Exceptions\RadiusRuntimeException;

/**
 * wire framing for a full RADIUS packet: 20-byte header, attribute TLV walk (including
 * Vendor-Specific nesting), and authenticator computation/verification; attribute decoding never
 * resolves a wire type number to a PHP class - it just hands back raw bytes keyed by that number,
 * class resolution happens lazily in Message::get().
 */
class PacketCodec
{
	public const int HEADER_LENGTH = 20;

	public const string ZERO_AUTHENTICATOR = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

	public const int MAX_LENGTH = 4096;

	/**
	 * RFC 2865 5: an attribute's Length is a single octet covering the type and length octets
	 * too, so the value it wraps cannot be longer than this.
	 */
	public const int MAX_ATTRIBUTE_VALUE_LENGTH = 253;

	/**
	 * RFC 2865 5.26: a vendor value additionally carries the 4-byte vendor id and the
	 * vendor-type/vendor-length pair inside that same 253 bytes.
	 */
	public const int MAX_VENDOR_ATTRIBUTE_VALUE_LENGTH = 247;

	/**
	 * the packet codes that decode to something other than a plain Message, because their
	 * authenticator is built differently. Anything not in here - including a code nobody has got
	 * round to naming - decodes to Message and reaches a Handler like any other request, so a
	 * vendor with a code of its own needs no change here.
	 *
	 * @var array<int, class-string<Message>>
	 */
	public const array KNOWN_MESSAGE_TYPES = [
		PacketCode::AccessRequest      => AccessRequest::class,
		PacketCode::AccessAccept       => Reply::class,
		PacketCode::AccessReject       => Reply::class,
		PacketCode::AccessChallenge    => Reply::class,
		PacketCode::AccountingResponse => Reply::class,
		PacketCode::DisconnectAck      => Reply::class,
		PacketCode::DisconnectNak      => Reply::class,
		PacketCode::CoaAck             => Reply::class,
		PacketCode::CoaNak             => Reply::class,
	];

	/**
	 * @param array<int, class-string<Message>> $knownMessageTypes override to teach the codec a code
	 *                                                             of your own, or to swap out what a
	 *                                                             standard one decodes to
	 */
	public function __construct(
		protected array $knownMessageTypes = self::KNOWN_MESSAGE_TYPES,
	) {}

	/**
	 * decodes a raw datagram into a Message; $peer is whoever Server already resolved the sender
	 * to be, from the source IP.
	 *
	 * @throws RadiusRuntimeException
	 */
	public function decode(string $raw, Peer $peer): Message
	{
		[, $attributeBytes] = $this->split($raw);

		$code = ord($raw[0]);
		$identifier = ord($raw[1]);
		$authenticator = substr($raw, 4, 16);
		[$standard, $vendor] = $this->decodeAttributes($attributeBytes);

		$message = $this->knownMessageTypes[$code] ?? Message::class;

		return new $message($peer, $code, $identifier, $authenticator, $standard, $vendor);
	}

	/**
	 * recompute the authenticator the way the message itself says it is built and compare it to
	 * what actually arrived. For an Accounting/CoA/Disconnect request that is MD5(header + 16
	 * zero bytes + attributes + secret); an Access-Request signs nothing and so passes by
	 * construction - {@see AccessRequest}.
	 *
	 * @throws RadiusRuntimeException
	 */
	public function verifyRequestAuthenticator(string $raw, Message $message): bool
	{
		[$header, $attributeBytes] = $this->split($raw);

		return hash_equals(
			known_string: $message->signedAuthenticator($header, $attributeBytes),
			user_string: substr($raw, 4, 16),
		);
	}

	/**
	 * authenticator on a reply (Access-Accept/Reject/Challenge, Accounting-Response,
	 * CoA/Disconnect-ACK/NAK) is MD5(header + the request's own authenticator + attributes +
	 * secret).
	 *
	 * @throws RadiusRuntimeException
	 */
	public function verifyReplyAuthenticator(string $raw, string $requestAuthenticator, Peer $peer): bool
	{
		return $this->verify($raw, $requestAuthenticator, $peer);
	}

	/**
	 * frame a message for the wire; how its authenticator is seeded and signed is the message's
	 * own business, which is what lets a Reply key itself to the request it answers and an
	 * Access-Request carry a nonce, with nothing in here branching on the code. Who it is signed
	 * for is the message's own peer, so a message is a self-contained thing to hand a socket.
	 *
	 * @param int<0, 255> $identifier
	 *
	 * @throws RadiusRuntimeException
	 * @throws RandomException
	 */
	public function encode(Message $message, int $identifier): string
	{
		// asked for once and memoised on the message: an Access-Request's seed is random, and the
		// hidden attributes and the header have to be built around the same 16 bytes
		$seed = $message->authenticatorSeed();

		$attributeBytes = $this->encodeAttributes($message->pushedAttributes(), $message->getPeer(), $seed);
		$length = self::HEADER_LENGTH + strlen($attributeBytes);
		if ($length > self::MAX_LENGTH) {
			throw new RadiusRuntimeException(sprintf('encoded RADIUS packet of %d bytes exceeds the %d-byte maximum', $length, self::MAX_LENGTH));
		}

		$header = pack('CCn', $message->getPacketCode(), $identifier, $length);

		return $header.$message->signedAuthenticator($header, $attributeBytes).$attributeBytes;
	}

	/**
	 * @throws RadiusRuntimeException
	 */
	protected function verify(string $raw, string $authenticatorSeed, Peer $peer): bool
	{
		[$header, $attributeBytes] = $this->split($raw);

		return hash_equals(
			known_string: $peer->sign($header.$authenticatorSeed.$attributeBytes),
			user_string: substr($raw, 4, 16),
		);
	}

	/**
	 * the two spans an authenticator is computed over: the 4 header bytes ahead of it, and the
	 * attributes behind it.
	 *
	 * @return array{0: string, 1: string}
	 *
	 * @throws RadiusRuntimeException
	 */
	protected function split(string $raw): array
	{
		if (strlen($raw) < self::HEADER_LENGTH) {
			throw new RadiusRuntimeException('packet shorter than the 20-byte RADIUS header');
		}

		$length = (ord($raw[2]) << 8) | ord($raw[3]);
		if ($length < self::HEADER_LENGTH || $length > strlen($raw)) {
			throw new RadiusRuntimeException('invalid RADIUS packet length');
		}

		return [substr($raw, 0, 4), substr($raw, self::HEADER_LENGTH, $length - self::HEADER_LENGTH)];
	}

	/**
	 * @return array{0: array<int, string>, 1: array<int, array<int, string>>}
	 *
	 * @throws RadiusRuntimeException
	 */
	protected function decodeAttributes(string $raw): array
	{
		$offset = 0;
		$vendor = [];
		$standard = [];
		$length = strlen($raw);

		while ($offset < $length) {
			if ($offset + 2 > $length) {
				throw new RadiusRuntimeException('truncated attribute header');
			}

			$type = ord($raw[$offset]);
			$attrLength = ord($raw[$offset + 1]);
			if ($attrLength < 2 || (($offset + $attrLength) > $length)) {
				throw new RadiusRuntimeException('invalid attribute length');
			}

			$value = substr($raw, $offset + 2, $attrLength - 2);

			if (26 === $type) {
				$this->decodeVendorSpecific($value, $vendor);
			}
			else {
				$standard[$type] = $value;
			}

			$offset += $attrLength;
		}

		return [$standard, $vendor];
	}

	/**
	 * @param array<int, array<int, string>> $vendor
	 */
	protected function decodeVendorSpecific(string $value, array &$vendor): void
	{
		if (strlen($value) < 6) {
			return; // too short to hold a vendor id + at least one sub-attribute, ignore
		}

		$offset = 4;
		$length = strlen($value);
		$vendorId = (ord($value[0]) << 24) | (ord($value[1]) << 16) | (ord($value[2]) << 8) | ord($value[3]);

		while ($offset < $length) {
			if ($offset + 2 > $length) {
				return;
			}

			$vendorType = ord($value[$offset]);
			$vendorLength = ord($value[$offset + 1]);
			if ($vendorLength < 2 || $offset + $vendorLength > $length) {
				return;
			}

			$vendor[$vendorId][$vendorType] = substr($value, $offset + 2, $vendorLength - 2);

			$offset += $vendorLength;
		}
	}

	/**
	 * @param iterable<Attribute> $attributes
	 *
	 * @throws RadiusRuntimeException
	 */
	protected function encodeAttributes(iterable $attributes, Peer $peer, string $authenticatorSeed): string
	{
		$raw = '';

		foreach ($attributes as $attribute) {
			if ($attribute instanceof EncryptedAttribute) {
				$attribute->setPeer($peer);
				$attribute->setAuthenticator($authenticatorSeed);
			}

			$encoded = $attribute->dehydrate();
			if (null === $encoded) {
				continue; // Missing/unfilled, nothing to send
			}

			$isVendor = $attribute instanceof VendorAttribute;
			$limit = $isVendor ? self::MAX_VENDOR_ATTRIBUTE_VALUE_LENGTH : self::MAX_ATTRIBUTE_VALUE_LENGTH;

			// the length octet would wrap silently and put a packet on the wire that no NAS can
			// parse, so an oversized value is refused rather than truncated
			if (strlen($encoded) > $limit) {
				throw new RadiusRuntimeException(sprintf("attribute '%s' encodes to %d bytes, more than the %d a RADIUS attribute can carry", $attribute::identifier(), strlen($encoded), $limit));
			}

			if (!$isVendor) {
				$raw .= pack('CC', $attribute::type(), 2 + strlen($encoded)).$encoded;

				continue;
			}

			$vsaValue = pack('N', $attribute::vendorId()).pack('CC', $attribute::type(), 2 + strlen($encoded)).$encoded;
			$raw .= pack('CC', 26, 2 + strlen($vsaValue)).$vsaValue;
		}

		return $raw;
	}
}
