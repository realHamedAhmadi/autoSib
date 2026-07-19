<?php

namespace App\Exceptions;

use RuntimeException;

class SibApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $sibCode = null,
        public readonly ?string $traceId = null,
        public readonly array $responseData = [],
        public readonly ?int $httpStatus = null,
    ) {
        parent::__construct($message);
    }
}
