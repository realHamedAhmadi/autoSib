<?php

namespace App\Exceptions;

use App\Models\AutomationRunUserCare;

class IgnoreCareException extends \RuntimeException
{
    public function __construct(
        string $message = 'Ignore Care',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
