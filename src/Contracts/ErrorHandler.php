<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Contracts;

use Throwable;

/**
 * handed to Server to be told about anything a Handler threw; the packet that caused it is
 * already dropped by the time this is called, so this is a place to log or count, not to recover.
 */
interface ErrorHandler
{
	public function handle(Throwable $throwable, string $remoteAddress): void;
}
