<?php

namespace Baskoro\CloudflareCache\Cache\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteKeyValuePair extends Request
{
    public Method $method = Method::DELETE;

    public function __construct(
        public string $key
    ) {
        //
    }

    public function resolveEndpoint(): string
    {
        $key = urlencode($this->key);

        return "/values/{$key}";
    }
}
