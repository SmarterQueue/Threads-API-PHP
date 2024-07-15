<?php

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use SmarterQueue\Threads\ThreadsApi;
use SmarterQueue\Threads\ThreadsException;
use SmarterQueue\Threads\ThreadsResponse;

/**
 * @internal
 *
 * @coversNothing
 */
class ThreadsApiTest extends TestCase
{
    protected string $clientId = 'test-client-id';
    protected string $clientSecret = 'test-client-secret';
    protected ThreadsApi $threadsApi;
    protected $mockClient;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(ClientInterface::class);
        $this->threadsApi = new ThreadsApi($this->clientId, $this->clientSecret, $this->mockClient);
    }

    public function testSetVersionCode()
    {
        $this->threadsApi->setVersionCode('v2.0');
        $this->assertSame('v2.0', $this->getPrivateProperty($this->threadsApi, 'versionCode'));
    }

    public function testSetAccessToken()
    {
        $this->threadsApi->setAccessToken('test-token');
        $this->assertSame('test-token', $this->getPrivateProperty($this->threadsApi, 'accessToken'));
    }

    public function testGetCredentials()
    {
        $credentials = $this->threadsApi->getAppCredentials();
        $this->assertSame($this->clientId, $credentials->clientId);
        $this->assertSame($this->clientSecret, $credentials->clientSecret);
    }

    public function testGetRequest()
    {
        $endpoint = 'test-endpoint';
        $params = ['param1' => 'value1'];
        $responseBody = ['key' => 'value'];

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn(json_encode($responseBody));
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockClient->method('request')->willReturn($mockResponse);

        $result = $this->threadsApi->get($endpoint, $params);
        $this->assertInstanceOf(ThreadsResponse::class, $result);
        $this->assertSame($responseBody, $result->decodedData);
    }

    public function testPostRequest()
    {
        $endpoint = 'test-endpoint';
        $params = ['param1' => 'value1'];
        $responseBody = ['key' => 'value'];

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn(json_encode($responseBody));
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockClient->method('request')->willReturn($mockResponse);

        $result = $this->threadsApi->post($endpoint, $params);
        $this->assertInstanceOf(ThreadsResponse::class, $result);
        $this->assertSame($responseBody, $result->decodedData);
    }

    public function testSendRequestWithException()
    {
        $this->expectException(ThreadsException::class);

        $endpoint = 'test-endpoint';

        $this->mockClient->method('request')->willThrowException(new RequestException('Error', $this->createMock(RequestInterface::class)));

        $this->threadsApi->setAccessToken('test-token');
        $this->threadsApi->get($endpoint);
    }

    protected function getPrivateProperty($object, $property)
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
