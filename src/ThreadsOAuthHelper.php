<?php

namespace SmarterQueue\Threads;

class ThreadsOAuthHelper
{
    public function __construct(protected ThreadsApi $threadsApi) {}

    public function getLoginUrl(array $scopes, string $redirectUri, ?string $state = null): string
    {
        $credentials = $this->threadsApi->getCredentials();
        $options = [
            'client_id' => $credentials['client_id'],
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
        $credentials = $this->threadsApi->getCredentials();
        $url = sprintf('%s/oauth/access_token', $this->getApiBaseUrl());
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'client_id' => $credentials['client_id'],
                'client_secret' => $credentials['client_secret'],
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ],
        ];

        return $this->threadsApi->sendRequest('POST', $url, $options);
    }

    public function getLongLivedAccessToken(string $accessToken): ThreadsResponse
    {
        $credentials = $this->threadsApi->getCredentials();
        $url = sprintf('%s/access_token', $this->getApiBaseUrl());
        $options = [
            'query' => [
                'client_secret' => $credentials['client_secret'],
                'access_token' => $accessToken,
                'grant_type' => 'th_exchange_token',
            ],
        ];

        return $this->threadsApi->sendRequest('GET', $url, $options);
    }

    public function refreshLongLivedAccessToken(string $accessToken): ThreadsResponse
    {
        $credentials = $this->threadsApi->getCredentials();
        $url = sprintf('%s/refresh_access_token', $this->getApiBaseUrl());
        $options = [
            'query' => [
                'client_secret' => $credentials['client_secret'],
                'access_token' => $accessToken,
                'grant_type' => 'th_refresh_token',
            ],
        ];

        return $this->threadsApi->sendRequest('GET', $url, $options);
    }

    protected function getApiBaseUrl(): string
    {
        return 'https://graph.threads.net';
    }
}
