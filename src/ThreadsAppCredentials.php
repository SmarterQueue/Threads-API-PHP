<?php

namespace SmarterQueue\Threads;

class ThreadsAppCredentials
{
    public function __construct(public readonly string $clientId, public readonly string $clientSecret) {}
}
