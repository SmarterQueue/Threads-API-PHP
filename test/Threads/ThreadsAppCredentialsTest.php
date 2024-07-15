<?php

use PHPUnit\Framework\TestCase;
use SmarterQueue\Threads\ThreadsAppCredentials;

/**
 * @internal
 *
 * @coversNothing
 */
class ThreadsAppCredentialsTest extends TestCase
{
    public function testProperties()
    {
        $credentials = new ThreadsAppCredentials('Test id', 'Test secret');

        $this->assertSame('Test id', $credentials->clientId);
        $this->assertSame('Test secret', $credentials->clientSecret);
    }
}
