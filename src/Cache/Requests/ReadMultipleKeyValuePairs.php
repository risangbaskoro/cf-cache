<?php

namespace Baskoro\CloudflareCache\Cache\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\AcceptsJson;

class ReadMultipleKeyValuePairs extends Request implements HasBody
{
    use AcceptsJson;
    use HasJsonBody;

    public Method $method = Method::POST;

    public function __construct(
        public array $keys,
        public string $type = 'text',
        public bool $withMetadata = false,
    ) {
        //
    }

    public function resolveEndpoint(): string
    {
        return '/bulk/get';
    }

    public function defaultBody(): array
    {
        return [
            'keys' => $this->keys,
            'type' => $this->type,
            'withMetadata' => $this->withMetadata,
        ];
    }
}
