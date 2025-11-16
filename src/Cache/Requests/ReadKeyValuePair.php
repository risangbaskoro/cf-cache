<?php

namespace Baskoro\CloudflareCache\Cache\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ReadKeyValuePair extends Request
{
    public Method $method = Method::GET;

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
