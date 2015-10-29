<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

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
     * @param bool $isCatching
     */
    public function __construct($isCatching = true)
    {
        $this->isCatching = $isCatching;
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
            } catch (JsonApiExceptionInterface $e) {
                return $e->getErrorDocument()->getResponse($response);
            }
        } else {
            $next();
        }
    }
}
