# Threads API Wrapper for PHP
This provides a PHP wrapper around [Meta's Threads API](https://developers.facebook.com/docs/threads/).

# Usage
There are 2 main API service classes - the `ThreadsApi` and `ThreadsOAuthHelper`
* `ThreadsApi` - The base class for sending requests and parsing the response & handling errors
* `ThreadsOAuthHelper` - A helper class with methods to:
  * Build a login URL
  * Exchange the authorization code for a short-lived token
  * Exchange a short-lived token for a long-lived token
  * Refresh a long-lived token

On successful responses, the API services will return a `ThreadsResponse` object that will contain
the http code, headers, and decoded data.

On unsuccessful requests or PHP exceptions, the API services will throw a `ThreadsException` object that will contain
the message, http code, status code (if any), sub code (if any), error type (if any), fb trace id (if any),
and the previous exception (if any).

## Create a link to the login page
```php
use SmarterQueue\ThreadsApi;
use SmarterQueue\ThreadsOAuthHelper;

// Set up the API instances.
$threadsApi = new ThreadsApi($clientId, $clientSecret);
$threadsOAuthHelper = new ThreadsOAuthHelper($threadsApi);

// Setup CSRF protection.
$state = bin2hex(random_bytes(32));
$_SESSION['threads_state'] = $state;

// Setup scopes and callback.
$scopes = ['threads_basic', 'threads_content_publish'];
$redirectUri = 'https://my-site.com/oauth-callback';

// Generate login URL & redirect to it.
$loginUrl = $threadsOAuthHelper->getLoginUrl($scopes, $redirectUri, $state);
header('Location: ' . $loginUrl);
```

## Exchange code for access tokens
```php
use SmarterQueue\ThreadsApi;
use SmarterQueue\ThreadsOAuthHelper;

// Set up the API instances.
$threadsApi = new ThreadsApi($clientId, $clientSecret);
$threadsOAuthHelper = new ThreadsOAuthHelper($threadsApi);

// GET params.
$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;

// CSRF Check.
if (!isset($_SESSION['threads_state']) || $state === null || $state !== $_SESSION['threads_state'])
{
	throw new \Exception('CSRF Error - State mismatch');
}

// Exchange authorization code for short-lived token.
$redirectUri = 'https://my-site.com/oauth-callback';
$response = $threadsOAuthHelper->getShortLivedAccessToken($code, $redirectUri);
$shortLivedToken = $response->decodedData['access_token'];

// Exchange short-lived token for long-lived token.
$response = $threadsOAuthHelper->getLongLivedAccessToken($shortLivedToken);
$longLivedToken = $response->decodedData['access_token'];
```

## Refresh the long-lived access token
```php
use SmarterQueue\ThreadsApi;
use SmarterQueue\ThreadsOAuthHelper;

// Set up the API instances.
$threadsApi = new ThreadsApi($clientId, $clientSecret);
$threadsOAuthHelper = new ThreadsOAuthHelper($threadsApi);
$longLivedToken = 'Replace with your token here';
$threadsApi->setAccessToken($longLivedToken);

// Refresh the token.
$response = $threadsOAuthHelper->refreshLongLivedAccessToken($longLivedToken);
$refreshedToken = $response->decodedData['access_token'];
```

## General requests
```php
use SmarterQueue\ThreadsApi;
use SmarterQueue\ThreadsOAuthHelper;

// Set up the API instance.
$threadsApi = new ThreadsApi($clientId, $clientSecret);
$longLivedToken = 'Replace with your token here';
$threadsApi->setAccessToken($longLivedToken);

// Get my details
$response = $threadsApi->get('me', ['fields' => 'id,username,threads_profile_picture_url']);

// Publish a post
$containerResponse = $threadsApi->post('me/threads', ['media_type' => 'TEXT', 'text' => "Test post"]);
$threadsApi->post('me/threads_publish', ['creation_id' => $containerResponse->decodedData['id']]);
```

## Handling errors
```php
try {
  $containerResponse = $threadsApi->post('me/threads', ['media_type' => 'TEXT', 'text' => "Test post"]);
} catch (ThreadsApiException $e) {
  $logger->log('Error getting my details', [
    'message' => $e->getMessage(),
    'code' => $e->getCode(),
    'subCode' => $e->subcode,
    'httpCode' => $e->httpCode,
  ]);
}
```
-----
By [SmarterQueue](https://smarterqueue.com)

