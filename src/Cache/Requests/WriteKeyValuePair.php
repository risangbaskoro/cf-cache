<?php

namespace Baskoro\CloudflareCache\Cache\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasMultipartBody;

class WriteKeyValuePair extends Request implements HasBody
{
    use HasMultipartBody;

    public Method $method = Method::PUT;

    public function __construct(
        protected string $key,
        protected string $value,
        protected ?int $ttl = null,
    ) {
        //
    }

    public function resolveEndpoint(): string
    {
        $key = urlencode($this->key);

        return "/values/{$key}";
    }

    protected function defaultQuery(): array
    {
        if (is_null($this->ttl)) {
            return parent::defaultQuery();
        }

        return [
            'expiration_ttl' => $this->ttl,
        ];
    }

    public function defaultBody(): array
    {
        return [
            new MultipartValue('value', $this->value),
        ];
    }
}
