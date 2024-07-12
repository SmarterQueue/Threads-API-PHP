<?php

namespace SmarterQueue\Threads;

class ThreadsException extends \Exception
{
    public function __construct(
        string $message,
        ?int $code = null,
        ?\Throwable $previous = null,
        public readonly ?string $errorType = null,
        public readonly ?int $httpCode = null,
        public readonly ?int $subcode = null,
        public readonly ?string $fbTraceId = null,
    ) {
        parent::__construct($message, $code ?? 0, $previous);
    }
}
