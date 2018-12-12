<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiExceptionInterface;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequestInterface;
use WoohooLabs\Yin\JsonApi\Response\Responder;
use WoohooLabs\Yin\JsonApi\Serializer\JsonSerializer;
use WoohooLabs\Yin\JsonApi\Serializer\SerializerInterface;
use WoohooLabs\YinMiddleware\Exception\JsonApiRequestException;

class JsonApiExceptionHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @var ResponseInterface
     */
    protected $errorResponsePrototype;

    /**
     * @var bool
     */
    protected $isCatching;

    /**
     * @var bool
     */
    protected $verbose;

    /**
     * @var ExceptionFactoryInterface
     */
    protected $exceptionFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(
        ResponseInterface $errorResponsePrototype,
        bool $catching = true,
        bool $verbose = false,
        ?ExceptionFactoryInterface $exceptionFactory = null,
        ?SerializerInterface $serializer = null
    ) {
        $this->errorResponsePrototype = $errorResponsePrototype;
        $this->isCatching = $catching;
        $this->verbose = $verbose;
        $this->exceptionFactory = $exceptionFactory ?? new DefaultExceptionFactory();
        $this->serializer = $serializer ?? new JsonSerializer();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isCatching === false) {
            return $handler->handle($request);
        }

        try {
            return $handler->handle($request);
        } catch (JsonApiExceptionInterface $exception) {
            return $this->handleJsonApiException($exception, $request);
        }
    }

    protected function handleJsonApiException(JsonApiExceptionInterface $exception, ServerRequestInterface $request): ResponseInterface
    {
        $jsonApiRequest = $this->getJsonApiRequest($request);
        $responder = $this->createResponder($jsonApiRequest);
        $additionalMeta = $this->getExceptionMeta($exception);

        return $responder->genericError($exception->getErrorDocument(), null, $additionalMeta);
    }

    protected function getExceptionMeta(Throwable $exception): array
    {
        if ($this->verbose === false) {
            return [];
        }

        return [
            "code" => $exception->getCode(),
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine(),
            "trace" => $exception->getTrace(),
        ];
    }

    protected function createResponder(JsonApiRequestInterface $request): Responder
    {
        return new Responder($request, $this->errorResponsePrototype, $this->exceptionFactory, $this->serializer);
    }

    protected function getJsonApiRequest(ServerRequestInterface $request): JsonApiRequestInterface
    {
        if ($request instanceof JsonApiRequestInterface) {
            return $request;
        }

        throw new JsonApiRequestException();
    }
}
