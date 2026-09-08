<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Tests;

use Shinya\PhpRadser\Peer;
use PHPUnit\Framework\TestCase;
use Shinya\PhpRadser\PeerRegistry;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
final class PeerRegistryTest extends TestCase
{
	public function testAPeerIsFoundByAnyValidSpellingOfItsAddress(): void
	{
		$peerRegistry = new PeerRegistry();
		$peerRegistry->register($peer = new Peer('2001:0db8::0001', 'secret'));

		$this->assertSame($peer, $peerRegistry->get('2001:db8::1'));
		$this->assertSame($peer, $peerRegistry->get('2001:0DB8:0000:0000:0000:0000:0000:0001'));
		$this->assertNotInstanceOf(Peer::class, $peerRegistry->get('2001:db8::2'));
	}

	public function testIpv4PeersStillResolve(): void
	{
		$peerRegistry = new PeerRegistry();
		$peerRegistry->register($peer = new Peer('10.0.0.1', 'secret'));

		$this->assertSame($peer, $peerRegistry->get('10.0.0.1'));
		$this->assertNotInstanceOf(Peer::class, $peerRegistry->get('10.0.0.2'));
	}

	/**
	 * 'ip:port' is ambiguous for v6, so an endpoint has to bracket the address - rebuilding one
	 * from its parts without this loses every v6 reply.
	 */
	public function testV6EndpointsAreBracketed(): void
	{
		$peer = new Peer('0:0:0:0:0:0:0:1', 'secret');

		$this->assertSame('[0:0:0:0:0:0:0:1]:3799', $peer->coaAddress);
		$this->assertSame('[0:0:0:0:0:0:0:1]:42790', $peer->reachedFrom(42790)->address);
		$this->assertSame('10.0.0.1:42790', new Peer('10.0.0.1', 'secret')->reachedFrom(42790)->address);
	}

	/**
	 * a peer copied for one request keeps the CoA port it was configured with, so a reply and a
	 * CoA out of the same channel go to different places.
	 */
	public function testACopyKeepsTheCoaPort(): void
	{
		$peer = new Peer('10.0.0.1', 'secret', 3799)->reachedFrom(42790);

		$this->assertSame('10.0.0.1:42790', $peer->address);
		$this->assertSame('10.0.0.1:3799', $peer->coaAddress);
	}

	/**
	 * a hostname isn't an IP, so it is filed verbatim rather than silently dropped.
	 */
	public function testANonAddressIsKeptAsGiven(): void
	{
		$peerRegistry = new PeerRegistry();
		$peerRegistry->register($peer = new Peer('nas.example.test', 'secret'));

		$this->assertSame($peer, $peerRegistry->get('nas.example.test'));
	}
}
