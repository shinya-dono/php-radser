<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Tests\Support;

use Shinya\PhpRadser\Peer;
use PHPUnit\Framework\TestCase;
use Shinya\PhpRadser\PacketCode;
use Shinya\PhpRadser\Contracts\Message;
use Shinya\PhpRadser\Support\PacketCodec;
use Shinya\PhpRadser\Contracts\AccessRequest;
use PHPUnit\Framework\Attributes\CoversNothing;
use Shinya\PhpRadser\Rfc2865\Attributes\UserName;
use Shinya\PhpRadser\Rfc2865\Attributes\ReplyMessage;
use Shinya\PhpRadser\Rfc2865\Attributes\UserPassword;
use Shinya\PhpRadser\Exceptions\RadiusRuntimeException;
use Shinya\PhpRadser\Vendors\Mikrotik\Attributes\MikrotikRateLimit;

/**
 * @internal
 */
#[CoversNothing]
final class PacketCodecTest extends TestCase
{
	public function testEncodeRequestRoundTripsThroughDecode(): void
	{
		$packetCodec = new PacketCodec();
		$peer = new Peer('10.0.0.1', 'secret');

		$message = new Message($peer, PacketCode::AccountingRequest);
		$message->push(UserName::make('alice'));
		$message->push(MikrotikRateLimit::make('1M/1M'));

		$raw = $packetCodec->encode($message, 7);

		$decoded = $packetCodec->decode($raw, $peer);

		$this->assertTrue($packetCodec->verifyRequestAuthenticator($raw, $decoded));
		$this->assertFalse($packetCodec->verifyRequestAuthenticator($raw, $packetCodec->decode($raw, new Peer('10.0.0.1', 'wrong'))));

		$this->assertSame(PacketCode::AccountingRequest, $decoded->getPacketCode());
		$this->assertSame(7, $decoded->getIdentifier());
		$this->assertSame('alice', $decoded->get(UserName::class)->dehydrate());
		$this->assertSame('1M/1M', $decoded->get(MikrotikRateLimit::class)->dehydrate());
	}

	/**
	 * an Access-Request's authenticator is a random nonce, not a digest of the packet, so it is
	 * unverifiable by design and differs on every encode.
	 */
	/**
	 * an attribute's length is a single octet, so a value past 253 bytes would wrap it and put a
	 * packet on the wire no NAS can parse - the encoder has to refuse instead.
	 */
	public function testEncodeRefusesAnOversizedAttributeValue(): void
	{
		$packetCodec = new PacketCodec();
		$peer = new Peer('10.0.0.1', 'secret');

		$message = new Message($peer, PacketCode::AccountingRequest);
		$message->push(UserName::make(str_repeat('a', PacketCodec::MAX_ATTRIBUTE_VALUE_LENGTH + 1)));

		$this->expectException(RadiusRuntimeException::class);

		$packetCodec->encode($message, 7);
	}

	public function testAccessRequestCarriesARandomAuthenticator(): void
	{
		$packetCodec = new PacketCodec();
		$peer = new Peer('10.0.0.1', 'secret');

		$first = $packetCodec->encode(new AccessRequest($peer, PacketCode::AccessRequest), 7);
		$second = $packetCodec->encode(new AccessRequest($peer, PacketCode::AccessRequest), 7);

		$this->assertNotSame(substr($first, 4, 16), substr($second, 4, 16));

		// unverifiable by design: there is nothing in the packet the nonce is derived from, so
		// the check has nothing to disagree with and passes whatever the secret
		$this->assertTrue($packetCodec->verifyRequestAuthenticator($first, $packetCodec->decode($first, new Peer('10.0.0.1', 'wrong'))));
	}

	/**
	 * the hidden attribute has to be keyed to the very authenticator the codec put in the header,
	 * otherwise the far side can't unwrap it.
	 */
	public function testEncodedPasswordIsKeyedToTheAuthenticatorInTheHeader(): void
	{
		$packetCodec = new PacketCodec();
		$peer = new Peer('10.0.0.1', 'secret');

		$accessRequest = new AccessRequest($peer, PacketCode::AccessRequest);
		$accessRequest->push(UserPassword::make('hunter2'));

		$message = $packetCodec->decode($packetCodec->encode($accessRequest, 7), $peer);

		$this->assertSame('hunter2', $message->get(UserPassword::class)->getPlainText());
	}

	public function testEncodeReplyBindsToTheRequestAuthenticator(): void
	{
		$packetCodec = new PacketCodec();
		$peer = new Peer('10.0.0.1', 'secret');

		$message = $packetCodec->decode(
			$packetCodec->encode(new AccessRequest($peer, PacketCode::AccessRequest), 7),
			$peer,
		);

		$reply = $message->reply(PacketCode::AccessAccept);
		$reply->push(ReplyMessage::make('welcome'));

		$raw = $packetCodec->encode($reply, $message->getIdentifier());

		$this->assertTrue($packetCodec->verifyReplyAuthenticator($raw, $message->getAuthenticator(), $peer));
		$this->assertFalse($packetCodec->verifyReplyAuthenticator($raw, str_repeat("\0", 16), $peer));
	}

	/**
	 * a code we never named is still a packet somebody meant to send - it has to reach a handler
	 * as a plain Message rather than blowing up the decode.
	 */
	public function testAnUnknownPacketCodeDecodesToAPlainMessage(): void
	{
		$packetCodec = new PacketCodec();
		$peer = new Peer('10.0.0.1', 'secret');

		$message = new Message($peer, 200);
		$message->push(UserName::make('alice'));

		$decoded = $packetCodec->decode($packetCodec->encode($message, 7), $peer);

		$this->assertSame(Message::class, $decoded::class);
		$this->assertSame(200, $decoded->getPacketCode());
		$this->assertSame('alice', $decoded->get(UserName::class)->dehydrate());
		$this->assertTrue($packetCodec->verifyRequestAuthenticator($packetCodec->encode($message, 7), $decoded));
	}

	/**
	 * and a vendor that wants one of those codes to behave differently registers a class for it
	 * instead of sending a PR.
	 */
	public function testAnUnknownCodeCanBeTaughtBehaviourThroughTheRegistry(): void
	{
		$peer = new Peer('10.0.0.1', 'secret');
		$packetCodec = new PacketCodec([200 => AccessRequest::class]);

		$message = $packetCodec->decode($packetCodec->encode(new AccessRequest($peer, 200), 7), $peer);

		$this->assertInstanceOf(AccessRequest::class, $message);
	}
}
