<?php

namespace App\Exceptions;

use App\Models\AutomationRunUserCare;

class IgnoreCareException extends \RuntimeException
{
    public function __construct(
        string $message = 'نادیده گرفته شد. برای اولین بار باید توسط کاربر مراقبت انجام شود.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
