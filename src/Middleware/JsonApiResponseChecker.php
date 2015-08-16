<?php
namespace WoohooLabs\YinMiddlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class JsonApiResponseChecker extends JsonApiMessageChecker
{
    /**
     * @param bool $lint
     * @param bool $validate
     */
    public function __construct($lint = true, $validate = true)
    {
        parent::__construct($lint, $validate);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return void|\Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response = $this->check($response, $response);

        if ($response !== null) {
            return $response;
        }

        $next();
    }
}
