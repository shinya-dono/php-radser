<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser;

/**
 * names for the RADIUS packet type codes, nothing more - a code is a plain int on the wire and
 * stays one in here. Deliberately not an enum: a code we never got round to naming is still a
 * valid packet, and rejecting it would mean a PR to this file every time somebody meets a vendor
 * with its own ideas.
 *
 * @see Support\PacketCodec::KNOWN_MESSAGE_TYPES for where a code picks up behaviour
 */
final class PacketCode
{
	public const int AccessRequest = 1;

	public const int AccessAccept = 2;

	public const int AccessReject = 3;

	public const int AccountingRequest = 4;

	public const int AccountingResponse = 5;

	public const int AccessChallenge = 11;

	public const int DisconnectRequest = 40;

	public const int DisconnectAck = 41;

	public const int DisconnectNak = 42;

	public const int CoaRequest = 43;

	public const int CoaAck = 44;

	public const int CoaNak = 45;
}
