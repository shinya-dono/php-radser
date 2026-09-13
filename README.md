# php-radser

A RADIUS server toolkit for PHP 8.5, built on ReactPHP.

This is not a daemon you configure with a text file. It is a library you build a RADIUS server
out of: the wire framing, the authenticator arithmetic, the shared-secret hiding and the
request/reply correlation are handled for you, and the policy — who gets in, what they get, what
gets logged — stays in your code as an ordinary PHP class.

```php
$server = new Server(
    handlers: [new AuthHandler()],
    peerRegistry: $registry,
    serverConfig: new ServerConfig('0.0.0.0', 1812, 1813),
);

$server->start();
```

Everything described below is covered by the test suite, including an end-to-end suite that
drives a real server process with FreeRADIUS's `radclient` over real UDP.

---

## Contents

- [What you get](#what-you-get)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- [How a packet flows](#how-a-packet-flows)
- [The moving parts](#the-moving-parts)
- [Peers and shared secrets](#peers-and-shared-secrets)
- [Handlers](#handlers)
- [Reading attributes](#reading-attributes)
- [Replying](#replying)
- [Accounting](#accounting)
- [Sending CoA and Disconnect requests](#sending-coa-and-disconnect-requests)
- [Errors](#errors)
- [Gotchas](#gotchas)
- [Extending and injecting](#extending-and-injecting)
- [Development](#development)
- [Upgrade guide](#upgrade-guide)
- [License](#license)

---

## What you get

|                                |                                                                                    |
|--------------------------------|------------------------------------------------------------------------------------|
| **Authentication**             | Access-Request handling, Accept/Reject/Challenge replies                           |
| **Accounting**                 | Accounting-Request handling on a separate socket, Accounting-Response              |
| **Dynamic authorization**      | CoA-Request and Disconnect-Request as promises, RFC 5176                           |
| **Attribute dictionary**       | RFC 2865, 2866, 2869, 3576/5176, Microsoft and Mikrotik, as generated typed classes |
| **Hidden attributes**          | RFC 2865 5.2 password hiding, unwrapped on the way in                              |
| **CHAP and MS-CHAP**           | CHAP, MS-CHAPv1 and MS-CHAPv2 response checks, MS-CHAP2-Success and error replies  |
| **Message-Authenticator**      | Added to every non-accounting packet, verified on the way in, BlastRADIUS-safe     |
| **Authenticator checks**       | Requests and replies verified against the peer's secret before a handler sees them |
| **Vendor-specific attributes** | Vendor-Specific (26) nesting, encode and decode                                    |
| **Non-blocking**               | One ReactPHP event loop, no threads, no forking                                    |

## Requirements

- PHP 8.5 or newer, with `ext-bcmath` (for the 64-bit counters, whose top end a PHP int cannot hold)
- `ext-mbstring` and `ext-openssl`, for MS-CHAP (the UTF-16 password hash and DES)
- `react/datagram`, `react/event-loop`, `react/promise` (pulled in by Composer)
- `radclient` from FreeRADIUS, for the end-to-end tests only

## Installation

```bash
composer require shinya-dono/php-radser
```

Or point Composer at the repository directly:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/shinya-dono/php-radser" }
    ],
    "require": {
        "shinya-dono/php-radser": "^0.2"
    }
}
```

## Quick start

A complete authentication server. Save it, run it with `php server.php`, and point a NAS at
`udp/1812`.

```php
<?php

declare(strict_types = 1);

require __DIR__.'/vendor/autoload.php';

use Closure;
use Throwable;
use React\EventLoop\Loop;
use Shinya\PhpRadser\Peer;
use Shinya\PhpRadser\Server;
use Shinya\PhpRadser\PacketCode;
use Shinya\PhpRadser\PeerRegistry;
use Shinya\PhpRadser\Contracts\Channel;
use Shinya\PhpRadser\Contracts\Handler;
use Shinya\PhpRadser\Contracts\Message;
use Shinya\PhpRadser\Contracts\ErrorHandler;
use Shinya\PhpRadser\Support\ServerConfig;
use Shinya\PhpRadser\Rfc2865\Attributes\UserName;
use Shinya\PhpRadser\Rfc2865\Attributes\ReplyMessage;
use Shinya\PhpRadser\Rfc2865\Attributes\UserPassword;
use Shinya\PhpRadser\Rfc2865\Attributes\SessionTimeout;

final class AuthHandler implements Handler
{
    public function handle(Message $message, Channel $channel, Closure $stopBubbling): void
    {
        if (PacketCode::AccessRequest !== $message->getPacketCode()) {
            return;
        }

        $username = $message->get(UserName::class)->read();
        $password = $message->get(UserPassword::class)->getPlainText();

        if ('alice' !== $username || 'hunter2' !== $password) {
            $channel->send(
                $message->reply(PacketCode::AccessReject)
                    ->push(ReplyMessage::make('bad credentials')),
            );

            $stopBubbling();

            return;
        }

        $channel->send(
            $message->reply(PacketCode::AccessAccept)
                ->push(ReplyMessage::make('welcome back, '.$username))
                ->push(SessionTimeout::make(3600)),
        );

        $stopBubbling();
    }
}

final class StderrErrors implements ErrorHandler
{
    public function handle(Throwable $throwable, string $remoteAddress): void
    {
        fwrite(STDERR, sprintf("[%s] %s\n", $remoteAddress, $throwable->getMessage()));
    }
}

$registry = new PeerRegistry();
$registry->register(new Peer('127.0.0.1', 'testing123'));

$server = new Server(
    handlers: [new AuthHandler()],
    peerRegistry: $registry,
    serverConfig: new ServerConfig('127.0.0.1', 1812, 1813),
    errorHandler: new StderrErrors(),
);

$server->start();
```

Note what is *not* there: no `Loop::run()`. ReactPHP's global `Loop` runs itself when the script
would otherwise exit, so `start()` is the last line you need. Call `$server->stop()` (from a
timer or a signal handler) to close both sockets and let the process end.

Test it:

```bash
printf 'User-Name = alice\nUser-Password = hunter2\nNAS-IP-Address = 127.0.0.1\n' \
  | radclient -x 127.0.0.1:1812 auth testing123
```

```
Received Access-Accept Id 85 from 127.0.0.1:1812 to 127.0.0.1:51521 length 47
    Reply-Message = "welcome back, alice"
    Session-Timeout = 3600
```

## How a packet flows

```
  inbound datagram (auth socket 1812 / acct socket 1813)
        |
        v
  PeerRegistry::get(source ip)
        |
        +-- unknown source ------------------------> dropped, silently, no error raised
        |
        v
  PacketCodec::decode() resolves the code to a class
        |
        +-- a Reply (Access-Accept, CoA-Ack, Disconnect-Nak, ...)
        |     |
        |     +-- no request waiting on that identifier -> UnexpectedReplyException
        |     +-- authenticator does not match what we sent -> InvalidAuthenticatorException
        |     +-- matched -> resolves the Channel::sendAsync() promise
        |                    (a Reply NEVER reaches a Handler)
        |
        +-- anything else: a request
              |
              +-- authenticator or Message-Authenticator does not verify -> InvalidAuthenticatorException
              |   (an Access-Request's authenticator is a nonce; only its Message-Authenticator can fail)
              +-- Access-Request without one, peer requires it -> MissingMessageAuthenticatorException
              |
              v
        Handler::handle($message, $channel, $stopBubbling)   [each handler in turn]
              |
              v
        Channel::send($message->reply(...))  ->  back to the port the request came from
```

Anything thrown along the way costs that one datagram and is passed to your `ErrorHandler`.
The server stays up.

## The moving parts

| Class | Role |
|---|---|
| `Server` | Owns the two sockets, decodes, verifies, dispatches |
| `Peer` | A NAS: its IP, shared secret and CoA port. A value, cheap to copy |
| `PeerRegistry` | IP to `Peer` lookup. Unregistered sources are ignored |
| `Contracts\Handler` | Your policy. Receives every inbound request |
| `Contracts\Message` | One packet. `get()`/`has()` to read, `push()` to build |
| `Contracts\Reply` | A message keyed to another message's authenticator |
| `Contracts\AccessRequest` | A message carrying a random nonce instead of a signature |
| `Contracts\Channel` | Sends back to the peer: `send()` for replies, `sendAsync()` for requests |
| `Contracts\Attribute` | One attribute, hydrated lazily from raw wire bytes |
| `Support\PacketCodec` | Wire framing and the code-to-class map |
| `Support\ServerConfig` | Host, ports, and the ReactPHP datagram factory |
| `RequestQueue` | The requests we sent a peer and are still waiting on |
| `PacketCode` | Named constants for the wire codes. Plain ints, not an enum |

## Peers and shared secrets

A peer is registered by IP. That IP is the identity: the source address of a datagram is what
selects the secret it is verified with.

```php
$registry = new PeerRegistry();

$registry->register(new Peer('10.0.0.1', 'secret-for-this-nas'));
$registry->register(new Peer('10.0.0.2', 'a-different-secret'));

// third argument is the port the NAS listens on for CoA/Disconnect, RFC 5176 default 3799
$registry->register(new Peer('10.0.0.3', 'secret', 1700));
```

### Message-Authenticator

Every packet except accounting goes out with a Message-Authenticator (RFC 3579), and a packet that
arrives carrying one is dropped unless it verifies. On an Access-Accept, Reject or Challenge it is
the first attribute, which is what defeats BlastRADIUS (CVE-2024-3596).

An Access-Request *without* one is still accepted by default, because older NASes never send it.
Once yours do, require it per peer:

```php
$registry->register(new Peer('10.0.0.1', 'secret', requireMessageAuthenticator: true));
```

A request that arrives without one is then dropped and reported as a
`MissingMessageAuthenticatorException` — count those in your `ErrorHandler` while you roll it out.

IPv6 peers are normalised, so `::1`, `0:0:0:0:0:0:0:1` and `::0001` are the same registration.

`Peer` also carries the two halves of the shared-secret arithmetic, should you need them
directly: `sign()` (RFC 2865 3), `cipher()` and `decipher()` (RFC 2865 5.2).

## Handlers

A handler is one method. Register as many as you like; each sees every inbound request, in
order, until one stops the chain.

```php
final class LoggingHandler implements Handler
{
    public function handle(Message $message, Channel $channel, Closure $stopBubbling): void
    {
        error_log(sprintf('%s from %s', $message->describe(), $message->getPeer()->ip));

        // no $stopBubbling() call: the next handler still runs
    }
}

$server = new Server([new LoggingHandler(), new AuthHandler(), new AcctHandler()], $registry);
```

`$stopBubbling()` prevents *later* handlers from running. It does not return from yours — the
rest of your method still executes, so `return` after calling it.

Both the auth and the accounting socket feed the same handler list, so branch on the code:

```php
match ($message->getPacketCode()) {
    PacketCode::AccessRequest     => $this->authenticate($message, $channel),
    PacketCode::AccountingRequest => $this->account($message, $channel),
    default                       => null,
};
```

## Reading attributes

Attributes are typed classes. `get()` always returns an instance — never null — so reads chain
safely; `read()` gives you the value or null. Use `has()` when "absent" and "empty" differ.

```php
$username = $message->get(UserName::class)->read();          // string|null
$nasPort  = $message->get(NasPort::class)->read();           // int|null

if ($message->has(CallingStationId::class)) {
    $mac = $message->get(CallingStationId::class)->read();
}
```

Attributes hydrate lazily: nothing is parsed until you ask for it, and the result is memoised
per message.

### The five families

| Base class | `read()` returns | Built with |
|---|---|---|
| `RawAttribute` / `StringAttribute` | `string\|null` | `make(string $value)` |
| `IntegerLikeAttribute` | `int\|null` | `make(int $value)` |
| `Integer64Attribute` | `string\|null`, a decimal string | `make(int\|string $value)` |
| `DateAttribute` | `DateTimeImmutable\|null` | `make(DateTimeImmutable $value)` |
| `EnumAttribute` | a `BackedEnum` case, or null | `make(BackedEnum $case)` |
| `EncryptedStringAttribute` | ciphertext — use `getPlainText()` | `make(string $plaintext)` |

Address attributes (`IpAddrAttribute`, `Ipv6AddrAttribute` and the prefix types) are a special
case of the first family: they read and write the *human-readable* form, not packed bytes.

```php
$ip = $message->get(FramedIpAddress::class)->read();   // '10.10.0.7'

FramedIpAddress::make('10.10.0.7');                    // correct
FramedIpAddress::make(inet_pton('10.10.0.7'));         // wrong: ValueError when it is sent
```

`Integer64Attribute` is the odd one out: a uint64 runs to 18446744073709551615 and a PHP int
stops at half that, so its value is a decimal string and the wire conversion goes through bcmath.
Compare two of them with `bccomp()`, not `==`.

```php
$octets = $message->get(AcctInputOctets64::class)->read();   // '18446744073709551615'

if (1 === bccomp($octets, $quota)) {
    // over quota
}
```

### Enumerated values

Attributes with a closed set of values decode straight to a PHP enum:

```php
use Shinya\PhpRadser\Rfc2866\AcctStatusTypeValue;
use Shinya\PhpRadser\Rfc2866\Attributes\AcctStatusType;

$status = $message->get(AcctStatusType::class)->read();

if (AcctStatusTypeValue::Start === $status) {
    // open the session record
}
```

### Hidden attributes

`User-Password` travels hidden behind the shared secret. `read()` gives you the ciphertext as it
arrived; `getPlainText()` unwraps it.

```php
$password = $message->get(UserPassword::class)->getPlainText();  // string|null
```

`Message` injects the peer and the request authenticator immediately after hydration, which is
what makes this work. Hydrate a `UserPassword` outside a `Message` and `getPlainText()` returns
null — there is no key to undo it with.

### CHAP and MS-CHAP

Neither sends the password. The NAS sends proof that the user knows one, and you check that proof
against what you hold. `Support\Chap` and `Support\MsChap` do the arithmetic; the decision stays
in your handler.

```php
use Shinya\PhpRadser\Support\Chap;
use Shinya\PhpRadser\Support\MsChap;
use Shinya\PhpRadser\Rfc2865\Attributes\ChapPassword;
use Shinya\PhpRadser\Vendors\Microsoft\Attributes\MsChapResponse;
use Shinya\PhpRadser\Vendors\Microsoft\Attributes\MsChap2Response;

$ntHash = MsChap::ntHash($password);   // or the NT hash you stored instead of the password

$accept = $message->reply(PacketCode::AccessAccept);
$reject = $message->reply(PacketCode::AccessReject);

if ($message->has(MsChap2Response::class)) {
    $success = MsChap::verifyV2($message, $ntHash);

    $channel->send(null !== $success ? $accept->push($success) : $reject->push(MsChap::error($message)));
}
elseif ($message->has(MsChapResponse::class)) {
    $channel->send(MsChap::verifyV1($message, $ntHash) ? $accept : $reject->push(MsChap::error($message)));
}
elseif ($message->has(ChapPassword::class)) {
    $channel->send(Chap::verify($message, $password) ? $accept : $reject);
}
```

- CHAP needs the password in the clear. MS-CHAP only needs its 16-byte NT hash, so if MS-CHAP is
  all you serve you can store `MsChap::ntHash()` instead of the password.
- An MS-CHAPv2 Access-Accept **must** carry the `MS-CHAP2-Success` that `verifyV2()` returns.
  Windows, RouterOS and strongSwan hang up on an Accept without it.
- `MsChap::error()` builds the `MS-CHAP-Error` for a reject. Pass a Windows error code where 691
  (bad credentials) says too little: 648 for an expired password, 647 for a disabled account.
- Not covered: password change (`MS-CHAP-CPW-*`), LM-only responses and MPPE keys.

### Vendor attributes

Vendor-Specific (26) nesting is unwrapped for you. A vendor attribute is read exactly like a
standard one:

```php
use Shinya\PhpRadser\Vendors\Mikrotik\Attributes\MikrotikRateLimit;

$limit = $message->get(MikrotikRateLimit::class)->read();   // '2M/10M'
```

## Replying

Build a reply with `$message->reply($code)` and push the attributes you want on it. `push()`
returns the message, so it chains, and repeating an attribute is allowed where the RFC allows it.

```php
$channel->send(
    $message->reply(PacketCode::AccessAccept)
        ->push(ReplyMessage::make('line one'))
        ->push(ReplyMessage::make('line two'))
        ->push(SessionTimeout::make(3600))
        ->push(FramedIpAddress::make('10.10.0.7'))
        ->push(MikrotikRateLimit::make('2M/10M')),
);
```

An Access-Challenge works the same way; carry your `State` so the next Access-Request can be
tied back to this exchange:

```php
$channel->send(
    $message->reply(PacketCode::AccessChallenge)
        ->push(ReplyMessage::make('Enter the code from your token'))
        ->push(State::make($opaqueHandle)),
);
```

`Channel::send()` accepts a `Reply` and nothing else, and the only correct way to get one is
`Message::reply()` — see [Gotchas](#gotchas).

## Accounting

The accounting socket is separate but the handler list is shared, so it is one more branch:

```php
final class AcctHandler implements Handler
{
    public function handle(Message $message, Channel $channel, Closure $stopBubbling): void
    {
        if (PacketCode::AccountingRequest !== $message->getPacketCode()) {
            return;
        }

        $this->records->write(
            $message->get(AcctSessionId::class)->read(),
            $message->get(UserName::class)->read(),
            $message->get(AcctStatusType::class)->read(),
            $message->get(AcctSessionTime::class)->read(),
        );

        // an Accounting-Response carries no attributes
        $channel->send($message->reply(PacketCode::AccountingResponse));

        $stopBubbling();
    }
}
```

Unlike an Access-Request, an Accounting-Request *is* signed over its own contents, so one that
does not verify against the peer's secret never reaches your handler (RFC 2866 4.1). A spoofed
source address cannot write a peer's session records.

## Sending CoA and Disconnect requests

`Channel::sendAsync()` sends a request to the peer's CoA port and returns a promise that resolves
with the NAS's Ack or Nak, or rejects when it times out.

```php
use Shinya\PhpRadser\Exceptions\RequestTimedOutException;

$disconnect = new Message($message->getPeer(), PacketCode::DisconnectRequest);
$disconnect->push(UserName::make('alice'));

$channel->sendAsync($disconnect)->then(
    static function (array $answer): void {
        [$reply, $channel] = $answer;

        PacketCode::DisconnectAck === $reply->getPacketCode()
            ? error_log('session torn down')
            : error_log('NAS refused: '.$reply->describe());
    },
    static function (Throwable $throwable): void {
        error_log($throwable instanceof RequestTimedOutException
            ? 'NAS never answered'
            : $throwable->getMessage());
    },
);
```

The promise resolves with `[Message, Channel]`. Correlation is by the one-byte identifier, and
the answer is verified against the authenticator the request went out with, so a stale or
forged reply on a recycled identifier is rejected rather than delivered.

The default timeout is 5 seconds and one peer can have at most 256 requests in flight — that is
the whole identifier space RADIUS gives you. Both are adjustable; see below.

## Errors

Every exception extends `RadiusRuntimeException`, which extends `RuntimeException`.

| Exception | Raised when |
|---|---|
| `RadiusRuntimeException` | The datagram is not a RADIUS packet we can read, or an encode is impossible |
| `InvalidAuthenticatorException` | A packet is not signed with the peer's shared secret, or its Message-Authenticator does not verify |
| `MissingMessageAuthenticatorException` | An Access-Request lacks the Message-Authenticator its peer requires; extends `InvalidAuthenticatorException` |
| `UnexpectedReplyException` | A peer answered a request nobody is waiting on |
| `RequestTimedOutException` | A `sendAsync()` request went unanswered (rejects the promise) |
| `InvalidAttributeValueException` | `Attribute::validate()` rejected a value |

Everything except `RequestTimedOutException` reaches your `ErrorHandler`:

```php
final class SentryErrors implements ErrorHandler
{
    public function handle(Throwable $throwable, string $remoteAddress): void
    {
        // the packet is already dropped; this is a place to log or count, not to recover
        $this->logger->warning('radius: dropped a packet', [
            'peer'  => $remoteAddress,
            'error' => $throwable->getMessage(),
        ]);
    }
}
```

Pass no `ErrorHandler` and a packet that blows up disappears without a trace, which is rarely
what you want outside a test.

## Gotchas

**A reply must come from `Message::reply()`.**
Its authenticator is keyed to the request's. Hand-build one with `new Reply(...)` and it gets
signed against sixteen zero bytes instead, the NAS drops it as forged, and you see a timeout with
nothing in your logs. `Channel::send()` takes a `Reply` and not a `Message` specifically to make
the wrong thing hard to pass.

**`make()` builds, `hydrate()` parses.**
`hydrate()` is for raw bytes off the wire and is what the library calls internally. It matters
most for hidden attributes: `hydrate()` marks the value as *already encrypted*.

```php
UserPassword::make('hunter2')->dehydrate();     // ciphertext, correct
UserPassword::hydrate('hunter2')->dehydrate();  // the plaintext, on the wire, in the clear
```

For plain string attributes the two happen to agree, so `ReplyMessage::hydrate('hi')` works by
accident. Use `make()` anyway.

**`get()` never returns null.**
It returns an unfilled attribute whose `read()` is null. `if ($message->get(X::class))` is
always true. Use `has()`, or check `read()`.

**An Access-Request is only authenticated by its Message-Authenticator.**
RFC 2865 gives it a random nonce rather than a signature, so without a Message-Authenticator there
is nothing in one to verify. One that carries it is verified; one that does not is accepted unless
its peer sets `requireMessageAuthenticator` — see [Message-Authenticator](#message-authenticator).
Accounting, CoA and all replies are verified either way.

**Unknown sources vanish silently.**
A datagram from an IP with no `Peer` registered is dropped without an exception and without
reaching your `ErrorHandler`. If a NAS "isn't working", check the registry first.

**A peer is identified by IP alone, not IP and port.**
Replies go back to the ephemeral port the request came from; CoA and Disconnect go to the port
you registered the peer with (3799 by default). Those are different sockets on the NAS.

**Handle the `sendAsync()` rejection.**
The promise rejects on timeout. Leave it unhandled and ReactPHP reports an unhandled rejection
with a stack trace where you wanted a log line. Attach a rejection handler, or `->catch()` it
explicitly even if you intend to ignore it.

**`$stopBubbling()` does not return.**
It stops *later* handlers. The rest of your own method still runs.

**One loop, one thread.**
A blocking call in a handler — a synchronous database query, `sleep()`, a slow file read —
stalls every other request on both sockets. Keep handlers non-blocking, or move the work off
the loop.

**`validate()` is opt-in.**
Nothing calls `Attribute::validate()` for you. Call it yourself if you want a value checked
before it goes out. An attribute holding a value with no wire form — a uint64 past its range, an
`IpAddrAttribute` holding something that is not an address — sends nothing rather than sending
something wrong, so `validate()` is how you hear about it.

**Packets cap at 4096 bytes, single attributes at 253.**
An attribute's length is one octet, so its value cannot exceed 253 bytes — 247 inside a
Vendor-Specific block. `PacketCodec::encode()` throws a `RadiusRuntimeException` on either limit
rather than emitting a packet no NAS can parse. Long `Reply-Message` chains and large vendor
blobs are the usual cause; split them across repeated attributes.

**Ports 1812 and 1813 need root.**
Bind above 1024 in development, or grant `CAP_NET_BIND_SERVICE`.

## Extending and injecting

Every collaborator `Server` uses is a constructor parameter with a working default. Replace the
ones you care about and leave the rest alone.

```php
$server = new Server(
    handlers: [$auth, $accounting],
    peerRegistry: $registry,
    serverConfig: $config,
    packetCodec: $codec,
    errorHandler: $errors,
);
```

### Listening somewhere else

`ServerConfig` carries the bind address, both ports, and the ReactPHP datagram factory — swap
the factory to bind on your own event loop.

```php
use React\Datagram\Factory;
use React\EventLoop\Loop;

$config = new ServerConfig(
    host: '0.0.0.0',
    authPort: 1812,
    acctPort: 1813,
    factory: new Factory(Loop::get()),
);
```

### Teaching the codec a new packet code

`PacketCodec` maps a wire code to the class it decodes to. A code that is not in the map decodes
to a plain `Message` and reaches your handlers like any other request, so unknown codes already
work — you only extend the map when a code needs *different authenticator behaviour*.

```php
use Shinya\PhpRadser\Contracts\AccessRequest;

// Status-Server (code 12, RFC 5997) carries a nonce exactly like an Access-Request
$codec = new PacketCodec(PacketCodec::KNOWN_MESSAGE_TYPES + [12 => AccessRequest::class]);

$server = new Server(handlers: [$handler], peerRegistry: $registry, packetCodec: $codec);
```

Register a class against `Reply::class` and inbound packets with that code stop reaching handlers
and start resolving `sendAsync()` promises instead. What is and is not a reply is decided by that
type, not by a list of codes.

### A message type of your own

Subclass `Message` when a packet's authenticator is built differently. Two methods decide
everything:

```php
final class NoncedRequest extends Message
{
    // the 16 bytes the authenticator is computed over and hidden attributes are keyed to
    public function authenticatorSeed(): string
    {
        return $this->authenticatorSeed ??= '' !== $this->authenticator
            ? $this->authenticator
            : random_bytes(16);
    }

    // what goes on the wire, and what an inbound packet is checked against
    public function signedAuthenticator(string $header, string $attributeBytes): string
    {
        return $this->authenticatorSeed();
    }
}

$codec = new PacketCodec(PacketCodec::KNOWN_MESSAGE_TYPES + [211 => NoncedRequest::class]);
```

Because verification calls the same `signedAuthenticator()` that signing does, a subclass decides
both directions at once and they cannot drift apart.

### Looking peers up somewhere other than memory

`PeerRegistry::get()` is the single lookup point. Override it.

```php
final class DatabasePeerRegistry extends PeerRegistry
{
    /** @var array<string, Peer> */
    private array $cache = [];

    public function __construct(private readonly PDO $pdo) {}

    public function get(string $ip): Peer|null
    {
        if (isset($this->cache[$ip])) {
            return $this->cache[$ip];
        }

        $row = $this->pdo->prepare('select secret, coa_port from nas where ip = ?');
        $row->execute([$ip]);

        if (!$found = $row->fetch(PDO::FETCH_ASSOC)) {
            return null;
        }

        return $this->cache[$ip] = new Peer($ip, $found['secret'], (int) $found['coa_port']);
    }
}
```

Cache it. `get()` is called once per inbound datagram, and an uncached query there puts a
round trip on your hot path.

### Changing the request timeout or identifier policy

`RequestQueue` is created per peer by `Server::requestsTo()`. Override it to change the timeout,
or subclass `RequestQueue` to change how identifiers are handed out.

```php
final class PatientServer extends Server
{
    protected function requestsTo(Peer $peer): RequestQueue
    {
        return $this->requestQueues[$peer->ip] ??= new RequestQueue($peer, timeout: 15.0);
    }
}
```

The default `nextIdentifier()` hands out the lowest free identifier, so an answered one is
reusable immediately. If you meet a NAS that suppresses duplicates on the identifier alone,
subclass and cycle through the space instead.

### Writing an attribute by hand

Most attributes are generated, but nothing stops you writing one. Pick the base class that
matches the wire type and implement two static methods — three for a vendor attribute.

```php
use Shinya\PhpRadser\Contracts\VendorAttribute;
use Shinya\PhpRadser\Attributes\StringAttribute;

final class AcmeQuota extends StringAttribute implements VendorAttribute
{
    public static function identifier(): string
    {
        return 'Acme-Quota';
    }

    public static function type(): int
    {
        return 7;          // the vendor-scoped sub-type, not 26
    }

    public static function vendorId(): int
    {
        return 32473;      // your SMI enterprise number
    }
}
```

There is no registration step. `Message::get()` resolves a class to wire bytes through these
statics at the moment you ask for it, so the class existing is enough.

Available bases: `StringAttribute`, `OctetsAttribute`, `IntegerAttribute`, `Integer64Attribute`,
`ByteAttribute`, `ShortAttribute`, `SignedAttribute`, `DateAttribute`, `IpAddrAttribute`,
`Ipv6AddrAttribute`, `Ipv6PrefixAttribute`, `Ipv4PrefixAttribute`, `IfidAttribute`,
`EtherAttribute`, `AbinaryAttribute`, `EnumAttribute`, `EncryptedStringAttribute`.

For a closed set of values, extend `EnumAttribute` and point it at a backed enum:

```php
enum AcmeTierValue: int
{
    case Bronze = 1;
    case Silver = 2;
    case Gold   = 3;
}

final class AcmeTier extends EnumAttribute
{
    public static function identifier(): string { return 'Acme-Tier'; }
    public static function type(): int          { return 8; }

    protected static function enum(): string    { return AcmeTierValue::class; }
}
```

Add validation by overriding `validate()` and calling `throwInvalid()`; remember that nothing
invokes it for you.

## Development

```bash
composer test        # phpunit
composer analyze     # phpstan, strict rules
composer cs          # rector, then php-cs-fixer
```

The end-to-end suite drives a real server process with FreeRADIUS's `radclient` over real UDP.
It skips itself when `radclient` is not on `PATH`; install `freeradius-utils` (or your
distribution's equivalent) to run it.

The soak script hammers a server with every packet shape it can meet — good requests, handlers
that throw, replies nobody is waiting on, and garbage — then reports whether its memory settled
or kept climbing:

```bash
php tests/e2e/soak.php 50000 500
```

## Upgrade guide

A caret constraint on a 0.x version only follows patch releases — `^0.1` will never install 0.2.0 —
so every minor version is an upgrade you opt into:

```bash
composer require shinya-dono/php-radser:^0.2
```

### 0.1.x to 0.2.0

0.2.0 adds [Message-Authenticator](#message-authenticator) handling and
[CHAP and MS-CHAP](#chap-and-ms-chap). Most servers upgrade without a code change, but what goes on
the wire is different, and so is what gets dropped.

**Every packet except accounting now goes out with a Message-Authenticator.**
Access-Accept, Access-Reject and Access-Challenge carry it as their first attribute, which is what
closes BlastRADIUS (CVE-2024-3596). Access-Requests, CoA and Disconnect requests and their ACK/NAK
carry it too. 0.1.x never sent one. What to check:

- The attribute has been standard since RFC 2869 (2000) and NASes accept it. If a very old one
  starts discarding replies after the upgrade, this is why.
- Every reply is 18 bytes longer. One that was already close to the 4096-byte limit can now make
  `encode()` throw a `RadiusRuntimeException`.
- A `MessageAuthenticator` you push yourself is ignored; the codec always computes its own.
- A request you start with `sendAsync()` can go out without one: build it from a `Message` subclass
  whose `carriesMessageAuthenticator()` returns false. Replies can be opted out the same way, but
  only through a custom request class registered in `PacketCodec`, and doing so reopens BlastRADIUS.

**A packet whose Message-Authenticator does not verify is now dropped.**
0.1.x ignored the attribute on the way in. 0.2.0 checks it whenever it is present, on requests and
on the CoA/Disconnect answers you are waiting for, and hands an `InvalidAuthenticatorException` to
your `ErrorHandler` when it does not match. A NAS that sends a correct one sees no difference.

One NAS does notice: one that sends a Message-Authenticator but whose shared secret disagrees with
yours. On 0.1.x its Access-Requests
still reached your handler — the password just decrypted to garbage and you rejected it. On 0.2.0
they are dropped before any handler runs, so the NAS gets no reply and times out. If a NAS "stops
answering" after the upgrade and your `ErrorHandler` reports `is not signed with our shared secret`,
fix the secret.

**Requiring a Message-Authenticator is new, and off by default.**
An Access-Request without one is still accepted, exactly as in 0.1.x. The mitigation is only
complete once you require it, which you do per peer once that NAS sends one:

```php
// 0.1.x, and still the 0.2.0 default
$registry->register(new Peer('10.0.0.1', 'secret'));

// 0.2.0, once this NAS sends a Message-Authenticator
$registry->register(new Peer('10.0.0.1', 'secret', requireMessageAuthenticator: true));
```

To find out which NASes already send one before you flip the switch, log it from a handler:

```php
use Shinya\PhpRadser\Rfc2869\Attributes\MessageAuthenticator;

if (PacketCode::AccessRequest === $message->getPacketCode() && !$message->has(MessageAuthenticator::class)) {
    error_log('no Message-Authenticator from '.$message->getPeer()->ip);
}
```

With the flag on, a request without one is dropped and reported as a
`MissingMessageAuthenticatorException`. It extends `InvalidAuthenticatorException`, so an
`ErrorHandler` that already handles that one keeps working.

**Two more PHP extensions are required.**
`ext-mbstring` and `ext-openssl`, for MS-CHAP. Composer refuses to install 0.2.0 without them; most
PHP builds ship both.

**If you extend the library's classes:**

- `Peer::__construct()` has a new fourth parameter, `requireMessageAuthenticator`. A subclass that
  overrides the constructor should accept it and pass it on.
- `Message` has a new public method, `carriesMessageAuthenticator()`. A subclass that already
  defines a method with that name now overrides it.
- `PacketCodec::verifyRequestAuthenticator()` and `PacketCodec::verify()` now also check the
  Message-Authenticator. An override that does not call the parent skips that check.
- `InvalidAuthenticatorException::__construct()` takes an optional second argument, the reason
  shown in its message. Existing calls are unaffected.

## License

MIT. See [LICENSE](LICENSE).
