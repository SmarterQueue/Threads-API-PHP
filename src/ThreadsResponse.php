<?php

namespace SmarterQueue\Threads;

class ThreadsResponse
{
    public function __construct(
        public readonly int $httpCode,
        public readonly array $headers,
        public readonly array $decodedData,
    ) {}
}
