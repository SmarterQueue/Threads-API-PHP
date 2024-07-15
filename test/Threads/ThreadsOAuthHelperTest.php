<?php

use PHPUnit\Framework\TestCase;
use SmarterQueue\Threads\ThreadsApi;
use SmarterQueue\Threads\ThreadsAppCredentials;
use SmarterQueue\Threads\ThreadsOAuthHelper;
use SmarterQueue\Threads\ThreadsResponse;

/**
 * @internal
 *
 * @coversNothing
 */
class ThreadsOAuthHelperTest extends TestCase
{
    protected $mockThreadsApi;
    protected ThreadsOAuthHelper $oAuthHelper;

    protected function setUp(): void
    {
        $appCredentials = new ThreadsAppCredentials('test-client-id', 'test-client-secret');
        $this->mockThreadsApi = $this->createMock(ThreadsApi::class);
        $this->mockThreadsApi->method('getAppCredentials')->willReturn($appCredentials);
        $this->oAuthHelper = new ThreadsOAuthHelper($this->mockThreadsApi);
    }

    public function testGetLoginUrl()
    {
        $scopes = ['scope1', 'scope2'];
        $redirectUri = 'https://example.com/redirect';
        $state = 'test-state';

        $loginUrl = $this->oAuthHelper->getLoginUrl($scopes, $redirectUri, $state);
        $expectedUrl = 'https://threads.net/oauth/authorize?client_id=test-client-id&redirect_uri=https%3A%2F%2Fexample.com%2Fredirect&scope=scope1%2Cscope2&response_type=code&state=test-state';

        $this->assertSame($expectedUrl, $loginUrl);
    }

    public function testGetShortLivedAccessToken()
    {
        $code = 'test-code';
        $redirectUri = 'https://example.com/redirect';
        $responseBody = ['access_token' => 'short-lived-token'];

        $this->mockThreadsApi->method('sendRequest')->willReturn(new ThreadsResponse(200, [], $responseBody));

        $result = $this->oAuthHelper->getShortLivedAccessToken($code, $redirectUri);
        $this->assertInstanceOf(ThreadsResponse::class, $result);
        $this->assertSame($responseBody, $result->decodedData);
    }

    public function testGetLongLivedAccessToken()
    {
        $accessToken = 'short-lived-token';
        $responseBody = ['access_token' => 'long-lived-token'];

        $this->mockThreadsApi->method('sendRequest')->willReturn(new ThreadsResponse(200, [], $responseBody));

        $result = $this->oAuthHelper->getLongLivedAccessToken($accessToken);
        $this->assertInstanceOf(ThreadsResponse::class, $result);
        $this->assertSame($responseBody, $result->decodedData);
    }

    public function testRefreshLongLivedAccessToken()
    {
        $accessToken = 'long-lived-token';
        $responseBody = ['access_token' => 'refreshed-long-lived-token'];

        $this->mockThreadsApi->method('sendRequest')->willReturn(new ThreadsResponse(200, [], $responseBody));

        $result = $this->oAuthHelper->refreshLongLivedAccessToken($accessToken);
        $this->assertInstanceOf(ThreadsResponse::class, $result);
        $this->assertSame($responseBody, $result->decodedData);
    }
}
