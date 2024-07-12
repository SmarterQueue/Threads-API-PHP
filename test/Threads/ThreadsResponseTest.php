<?php

use PHPUnit\Framework\TestCase;
use SmarterQueue\Threads\ThreadsResponse;

/**
 * @internal
 *
 * @coversNothing
 */
class ThreadsResponseTest extends TestCase
{
    public function testResponse()
    {
        $httpCode = 200;
        $headers = ['Content-Type' => 'application/json'];
        $decodedData = ['key' => 'value'];

        $response = new ThreadsResponse($httpCode, $headers, $decodedData);

        $this->assertSame($httpCode, $response->httpCode);
        $this->assertSame($headers, $response->headers);
        $this->assertSame($decodedData, $response->decodedData);
    }
}
