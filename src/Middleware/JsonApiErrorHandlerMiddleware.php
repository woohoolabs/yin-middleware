<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiException;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

class JsonApiErrorHandlerMiddleware
{
    /**
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return void|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        try {
            $next();
        } catch (JsonApiException $e) {
            return $e->getErrorDocument()->getResponse($response);
        }
    }
}
