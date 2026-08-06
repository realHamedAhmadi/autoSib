<?php

namespace App\Exceptions;

use RuntimeException;

class DoesNotHaveCareException extends RuntimeException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(
        string $message = 'The care is not assigned to this person.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
