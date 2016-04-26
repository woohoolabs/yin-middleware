<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

use Exception;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiExceptionInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

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
     * @param bool $catching
     * @param bool $verbose
     */
    public function __construct($catching = true, $verbose = false)
    {
        $this->isCatching = $catching;
        $this->verbose = $verbose;
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
                return $exception->getErrorDocument()->getResponse($response, null, $additionalMeta);
            }
        }

        return $next($request, $response);
    }

    /**
     * @param \Exception $exception
     * @return array
     */
    protected function getExceptionMeta(Exception $exception)
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
