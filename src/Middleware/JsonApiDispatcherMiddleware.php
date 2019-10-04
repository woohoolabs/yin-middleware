<?php

declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequestInterface;
use WoohooLabs\Yin\JsonApi\Serializer\JsonSerializer;
use WoohooLabs\Yin\JsonApi\Serializer\SerializerInterface;
use WoohooLabs\YinMiddleware\Exception\JsonApiRequestException;

use function is_array;
use function is_callable;
use function is_string;

class JsonApiDispatcherMiddleware implements MiddlewareInterface
{
    private ExceptionFactoryInterface $exceptionFactory;
    protected ?ContainerInterface $container;
    protected SerializerInterface $serializer;
    protected string $handlerAttributeName;

    public function __construct(
        ?ContainerInterface $container = null,
        ?ExceptionFactoryInterface $exceptionFactory = null,
        ?SerializerInterface $serializer = null,
        string $handlerAttributeName = "__action"
    ) {
        $this->container = $container;
        $this->exceptionFactory = $exceptionFactory ?? new DefaultExceptionFactory();
        $this->serializer = $serializer ?? new JsonSerializer();
        $this->handlerAttributeName = $handlerAttributeName;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $callable = $request->getAttribute($this->handlerAttributeName);
        $jsonApiRequest = $this->getJsonApiRequest($request);

        $response = $handler->handle($jsonApiRequest);

        if ($callable === null) {
            throw $this->exceptionFactory->createResourceNotFoundException($jsonApiRequest);
        }

        $jsonApi = new JsonApi($jsonApiRequest, $response, $this->exceptionFactory, $this->serializer);

        if (is_array($callable) && is_string($callable[0])) {
            $object = $this->container !== null ? $this->container->get($callable[0]) : new $callable[0]();
            $response = $object->{$callable[1]}($jsonApi);
        } else {
            if ($this->container !== null && !is_callable($callable)) {
                $callable = $this->container->get($callable);
            }
            $response = $callable($jsonApi);
        }

        return $response;
    }

    protected function getJsonApiRequest(ServerRequestInterface $request): JsonApiRequestInterface
    {
        if ($request instanceof JsonApiRequestInterface) {
            return $request;
        }

        throw new JsonApiRequestException();
    }
}
