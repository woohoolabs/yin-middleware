<?php

namespace WoohooLabs\YinMiddleware\Tests\Utils;

use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;

class FakeController
{
    public function __invoke(JsonApi $jsonApi): ResponseInterface
    {
        return $jsonApi->response->withStatus("201");
    }
}
