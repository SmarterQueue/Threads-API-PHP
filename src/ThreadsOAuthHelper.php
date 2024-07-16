<?php

namespace SmarterQueue\Threads;

class ThreadsOAuthHelper
{
    public function __construct(protected ThreadsApi $threadsApi) {}

    public function getLoginUrl(array $scopes, string $redirectUri, ?string $state = null): string
    {
        $credentials = $this->threadsApi->getAppCredentials();
        $options = [
            'client_id' => $credentials->clientId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(',', $scopes),
            'response_type' => 'code',
        ];
        if (null !== $state) {
            $options['state'] = $state;
        }

        return sprintf('https://threads.net/oauth/authorize?%s', http_build_query($options));
    }

    public function getShortLivedAccessToken(string $code, string $redirectUri): ThreadsResponse
    {
        $credentials = $this->threadsApi->getAppCredentials();
        $params = [
            'client_id' => $credentials->clientId,
            'client_secret' => $credentials->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ];

        return $this->threadsApi->post('oauth/access_token', $params, '');
    }

    public function getLongLivedAccessToken(string $accessToken): ThreadsResponse
    {
        $credentials = $this->threadsApi->getAppCredentials();
        $params = [
            'client_secret' => $credentials->clientSecret,
            'access_token' => $accessToken,
            'grant_type' => 'th_exchange_token',
        ];

        return $this->threadsApi->get('access_token', $params, '');
    }

    public function refreshLongLivedAccessToken(string $accessToken): ThreadsResponse
    {
        $credentials = $this->threadsApi->getAppCredentials();
        $params = [
            'client_secret' => $credentials->clientSecret,
            'access_token' => $accessToken,
            'grant_type' => 'th_refresh_token',
        ];

        return $this->threadsApi->get('refresh_access_token', $params, '');
    }
}
