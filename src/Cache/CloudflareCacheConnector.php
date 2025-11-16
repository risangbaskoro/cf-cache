<?php

namespace Baskoro\CloudflareCache\Cache;

use Baskoro\CloudflareCache\CloudflareConnector;
use Baskoro\CloudflareCache\CloudflareKvException;
use Illuminate\Support\Str;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Response;
use Throwable;

class CloudflareCacheConnector extends CloudflareConnector
{
    public function __construct(
        protected string $token,
        protected string $accountId,
        protected string $namespaceId,
        public string $apiUrl = 'https://api.cloudflare.com/client/v4',
    ) {
        $this->authenticate(new TokenAuthenticator($token));
    }

    public function resolveBaseUrl(): string
    {
        return $this->apiUrl."/accounts/{$this->accountId}/storage/kv/namespaces/{$this->namespaceId}";
    }

    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable
    {
        $message = Str::between($response->json('errors.0.message'), "'", "'");

        return new CloudflareKvException($message, $senderException->getCode(), $senderException);
    }
}
