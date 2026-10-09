<?php

namespace App\Exceptions;

use Exception;

/**
 * Raised when the ticketing system's identity API rejects or cannot service
 * a login/logout call. $status mirrors the HTTP status it returned so the
 * controller can show the right message (401 bad credentials, 403 inactive
 * account, 429 rate-limited, 5xx/0 unreachable).
 */
class TicketingIdentityException extends Exception
{
    public function __construct(string $message, public readonly int $status = 0)
    {
        parent::__construct($message);
    }
}
