<?php

use PHPUnit\Framework\TestCase;
use SmarterQueue\Threads\ThreadsException;

/**
 * @internal
 *
 * @coversNothing
 */
class ThreadsExceptionTest extends TestCase
{
    public function testException()
    {
        $previous = new Exception('Previous exception');
        $exception = new ThreadsException('Test message', 123, $previous, 'Test type', 404, 456, 'fbTraceId');

        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame('Test type', $exception->errorType);
        $this->assertSame(404, $exception->httpCode);
        $this->assertSame(456, $exception->subcode);
        $this->assertSame('fbTraceId', $exception->fbTraceId);
    }
}
