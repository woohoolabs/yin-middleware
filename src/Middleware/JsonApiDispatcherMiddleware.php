<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Request\Request;
use WoohooLabs\Yin\JsonApi\Schema\Error;
use WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument;

class JsonApiDispatcherMiddleware
{
    /**
     * @var \Interop\Container\ContainerInterface
     */
    private $container;

    /**
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $callable = $request->getAttribute("__callable");

        if ($callable === null) {
            $this->getDispatchErrorDocument([$this->getDispatchError()])->getResponse($response);
        }

        $jsonApi = new JsonApi(new Request($request), $response);

        if (is_array($callable) && is_string($callable[0])) {
            $object = $this->container->get($callable[0]);
            $response = $object->{$callable[1]}($jsonApi);
        } else {
            $response = call_user_func($callable, $jsonApi);
        }

        $next($request, $response);
    }

    /**
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getDispatchError()
    {
        $error = new Error();
        $error->setStatus(404);
        $error->setTitle("Route not found");
        $error->setDetail("No dispatchable callable is added to the request as an attribute!");

        return $error;
    }

    /**
     * @param array $errors
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getDispatchErrorDocument(array $errors)
    {
        $errorDocument = new ErrorDocument();

        foreach ($errors as $error) {
            $errorDocument->addError($error);
        }

        return $errorDocument;
    }
}
