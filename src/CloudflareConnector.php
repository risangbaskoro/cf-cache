<?php

namespace Baskoro\CloudflareCache;

use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;

abstract class CloudflareConnector extends Connector
{
    public function __construct(
        protected string $token,
        protected string $accountId,
        public string $apiUrl = 'https://api.cloudflare.com/client/v4',
    ) {
        $this->authenticate(new TokenAuthenticator($token));
    }

    public function resolveBaseUrl(): string
    {
        return $this->apiUrl;
    }
}
