<?php

namespace SmarterQueue\Threads;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

class ThreadsApi
{
    protected string $versionCode = 'v1.0';

    private ClientInterface $client;

    private ?string $accessToken = null;

    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        array|ClientInterface $clientOrConfig = [],
    ) {
        if ($clientOrConfig instanceof ClientInterface) {
            $this->client = $clientOrConfig;
        } else {
            $this->client = $this->buildClient($clientOrConfig);
        }
    }

    public function setVersionCode(string $versionCode): void
    {
        $this->versionCode = $versionCode;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getCredentials(): array
    {
        return ['client_id' => $this->clientId, 'client_secret' => $this->clientSecret];
    }

    public function get(string $endpoint, array $params = [], ?string $versionCode = null): ThreadsResponse
    {
        $versionCode = $versionCode ?? $this->versionCode;
        $options = [
            'query' => [
                'access_token' => $this->accessToken,
            ],
        ];
        $options['query'] = array_merge($options['query'], $params);

        return $this->sendRequest('GET', sprintf('%s/%s/%s', $this->getApiBaseUrl(), $versionCode, $endpoint), $options);
    }

    public function post(string $endpoint, array $params, ?string $versionCode = null): ThreadsResponse
    {
        $versionCode = $versionCode ?? $this->versionCode;
        $options = [
            'json' => [
                'access_token' => $this->accessToken,
            ],
        ];
        $options['json'] = array_merge($options['json'], $params);

        return $this->sendRequest('POST', sprintf('%s/%s/%s', $this->getApiBaseUrl(), $versionCode, $endpoint), $options);
    }

    public function sendRequest(string $method, string $uri, array $options): ThreadsResponse
    {
        if ('GET' !== $method && !isset($options['headers']['Content-Type'])) {
            $options['headers']['Content-Type'] = 'application/json';
        }

        try {
            $response = $this->client->request($method, $uri, $options);
            $content = $response->getBody()->getContents();
            $decodedData = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            return new ThreadsResponse($response->getStatusCode(), $response->getHeaders(), $decodedData);
        } catch (\Throwable $e) {
            throw $this->mapException($e);
        }
    }

    protected function mapException(\Throwable $e): ThreadsException
    {
        $response = $e instanceof RequestException ? $e->getResponse() : null;
        $errorMessage = $e->getMessage();
        $errorCode = $errorSubcode = $errorType = $errorFbTraceId = null;
        if ($response) {
            $content = $response->getBody()->getContents();
            if (str_contains($response->getHeaderLine('Content-Type'), 'application/json')) {
                $content = json_decode($content, true);
                if (JSON_ERROR_NONE === json_last_error()) {
                    $errorMessage = $content['error']['message'] ?? $e->getMessage();
                    $errorType = $content['error']['type'] ?? null;
                    $errorCode = $content['error']['code'] ?? null;
                    $errorSubcode = $content['error']['error_subcode'] ?? null;
                    $errorFbTraceId = $content['error']['fbtrace_id'] ?? null;
                }
            }
        }

        return new ThreadsException($errorMessage, (int) $e->getCode(), $e, $errorType, $errorCode, $errorSubcode, $errorFbTraceId);
    }

    protected function getApiBaseUrl(): string
    {
        return 'https://graph.threads.net';
    }

    protected function buildClient(array $clientConfig): ClientInterface
    {
        $config = array_merge([
            RequestOptions::TIMEOUT => 60,
            RequestOptions::CONNECT_TIMEOUT => 10,
        ], $clientConfig);

        return new Client($config);
    }
}
