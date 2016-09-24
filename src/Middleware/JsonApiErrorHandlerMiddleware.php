<?php
namespace WoohooLabs\YinMiddleware\Middleware;

use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiExceptionInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Serializer\DefaultSerializer;
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
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param bool $catching
     * @param bool $verbose
     * @param SerializerInterface $serializer
     */
    public function __construct($catching = true, $verbose = false, SerializerInterface $serializer = null)
    {
        $this->isCatching = $catching;
        $this->verbose = $verbose;
        $this->serializer = $serializer !== null ? $serializer : new DefaultSerializer();
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return void|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        if ($this->isCatching === true) {
            try {
                return $next($request, $response);
            } catch (JsonApiExceptionInterface $exception) {
                $additionalMeta = $this->verbose === true ? $this->getExceptionMeta($exception) : [];
                return $exception->getErrorDocument()->getResponse($this->serializer, $response, null, $additionalMeta);
            }
        }

        return $next($request, $response);
    }

    /**
     * @param \Exception $exception
     * @return array
     */
    protected function getExceptionMeta($exception)
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
