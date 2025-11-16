<?php

namespace Baskoro\CloudflareCache;

use Exception;
use Saloon\Http\Response;

class CloudflareKvException extends Exception
{
    public static function make(Response $response)
    {
        $code = $response->json('errors.0.code');
        $message = $response->json('errors.0.message');

        return new static($message, $code, $response->toException());
    }
}
