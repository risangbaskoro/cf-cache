<?php

namespace Baskoro\CloudflareCache\Cache\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\AcceptsJson;

class WriteMultipleKeyValuePairs extends Request implements HasBody
{
    use AcceptsJson;
    use HasJsonBody;

    public Method $method = Method::PUT;

    public function __construct(
        public array $values,
    ) {
        //
    }

    public function resolveEndpoint(): string
    {
        return '/bulk';
    }

    public function defaultBody(): array
    {
        return $this->values;
    }
}
