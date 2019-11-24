<?php

declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequest;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequestInterface;
use WoohooLabs\Yin\JsonApi\Serializer\DeserializerInterface;
use WoohooLabs\Yin\JsonApi\Serializer\JsonDeserializer;

class YinCompatibilityMiddleware implements MiddlewareInterface
{
    private ExceptionFactoryInterface $exceptionFactory;
    protected DeserializerInterface $deserializer;

    public function __construct(
        ?ExceptionFactoryInterface $exceptionFactory = null,
        ?DeserializerInterface $deserializer = null
    ) {
        $this->exceptionFactory = $exceptionFactory ?? new DefaultExceptionFactory();
        $this->deserializer = $deserializer ?? new JsonDeserializer();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request instanceof JsonApiRequestInterface === false) {
            $request = $this->createJsonApiRequest($request);
        }

        return $handler->handle($request);
    }

    protected function createJsonApiRequest(ServerRequestInterface $request): JsonApiRequest
    {
        return new JsonApiRequest($request, $this->exceptionFactory, $this->deserializer);
    }
}
