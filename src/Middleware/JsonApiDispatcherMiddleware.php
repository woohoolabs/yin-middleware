<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Document\ErrorDocument;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Schema\Error;
use WoohooLabs\Yin\JsonApi\Serializer\DefaultSerializer;
use WoohooLabs\Yin\JsonApi\Serializer\SerializerInterface;

class JsonApiDispatcherMiddleware
{
    /**
     * @var \WoohooLabs\Yin\jsonApi\Exception\ExceptionFactoryInterface
     */
    private $exceptionFactory;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $handlerAttributeName;

    public function __construct(
        ContainerInterface $container = null,
        ExceptionFactoryInterface $exceptionFactory = null,
        SerializerInterface $serializer = null,
        string $handlerAttributeName = "__action"
    ) {
        $this->container = $container;
        $this->exceptionFactory = $exceptionFactory;
        $this->serializer = $serializer ? $serializer : new DefaultSerializer();
        $this->handlerAttributeName = $handlerAttributeName;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $callable = $request->getAttribute($this->handlerAttributeName);

        if ($callable === null) {
            return $this->getDispatchErrorResponse($response);
        }

        $jsonApi = new JsonApi($request, $response, $this->exceptionFactory, $this->serializer);

        if (is_array($callable) && is_string($callable[0])) {
            $object = $this->container !== null ? $this->container->get($callable[0]) : new $callable[0]();
            $response = $object->{$callable[1]}($jsonApi);
        } else {
            if (!is_callable($callable)) {
                $callable = $this->container->get($callable);
            }
            $response = call_user_func($callable, $jsonApi);
        }

        return $next($request, $response);
    }

    protected function getDispatchErrorResponse(ResponseInterface $response): ResponseInterface
    {
        return $this->getErrorDocument($this->getDispatchError())->getResponse($this->serializer, $response);
    }

    protected function getDispatchError(): Error
    {
        $error = new Error();
        $error->setStatus(404);
        $error->setTitle("Resource was not not found!");

        return $error;
    }

    protected function getErrorDocument(Error $error): ErrorDocument
    {
        $errorDocument = new ErrorDocument();
        $errorDocument->addError($error);

        return $errorDocument;
    }
}
