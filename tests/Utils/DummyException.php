<?php

namespace WoohooLabs\YinMiddleware\Tests\Utils;

use WoohooLabs\Yin\JsonApi\Exception\JsonApiException;
use WoohooLabs\Yin\JsonApi\Schema\Error;

class DummyException extends JsonApiException
{
    public function __construct()
    {
        parent::__construct("Dummy exception");
    }

    protected function getErrors(): array
    {
        return [
            Error::create()
                ->setStatus("555")
                ->setCode("Dummy"),
        ];
    }
}
