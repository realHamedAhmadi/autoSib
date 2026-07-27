<?php

namespace App\Exceptions;

use App\Models\AutomationRunUserCare;

class CareAlreadyTakenException extends \RuntimeException
{
    public function __construct(
        string $message = 'مراقبت قبلا انجام شده است.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
