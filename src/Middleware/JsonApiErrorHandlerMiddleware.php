<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Middleware;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiExceptionInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Response\Responder;
use WoohooLabs\Yin\JsonApi\Serializer\JsonSerializer;
use WoohooLabs\Yin\JsonApi\Serializer\SerializerInterface;

class JsonApiErrorHandlerMiddleware
{
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
        bool $catching = true,
        bool $verbose = false,
        ?ExceptionFactoryInterface $exceptionFactory = null,
        ?SerializerInterface $serializer = null
    ) {
        $this->isCatching = $catching;
        $this->verbose = $verbose;
        $this->exceptionFactory = $exceptionFactory ?? new DefaultExceptionFactory();
        $this->serializer = $serializer ?? new JsonSerializer();
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if ($this->isCatching === true) {
            try {
                return $next($request, $response);
            } catch (JsonApiExceptionInterface $exception) {
                $responder = new Responder($request, $response, $this->exceptionFactory, $this->serializer);
                $additionalMeta = $this->verbose === true ? $this->getExceptionMeta($exception) : [];

                return $responder->genericError($exception->getErrorDocument(), [], null, $additionalMeta);
            }
        }

        return $next($request, $response);
    }

    protected function getExceptionMeta(Throwable $exception): array
    {
        return [
            "code" => $exception->getCode(),
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine(),
            "trace" => $exception->getTrace()
        ];
    }
}
