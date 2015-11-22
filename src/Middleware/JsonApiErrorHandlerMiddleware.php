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
                $next();
            } catch (JsonApiExceptionInterface $exception) {
                $errorDocument = $exception->getErrorDocument();
                if ($this->verbose === true) {
                    $additionalMeta = [
                        "code" => $exception->getCode(),
                        "message" => $exception->getMessage(),
                        "file" => $exception->getFile(),
                        "line" => $exception->getLine(),
                        "trace" => $exception->getTrace()
                    ];
                } else {
                    $additionalMeta = [];
                }

                return $exception->getErrorDocument()->getResponse($response, null, $additionalMeta);
            }
        } else {
            $next();
        }
    }
}
