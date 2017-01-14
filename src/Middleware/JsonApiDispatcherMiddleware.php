<?php
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

    /**
     * @param \WoohooLabs\Yin\jsonApi\Exception\ExceptionFactoryInterface $exceptionFactory
     * @param \Psr\Container\ContainerInterface $container
     * @param SerializerInterface|null $serializer
     * @param string $handlerAttributeName
     */
    public function __construct(
        ContainerInterface $container = null,
        ExceptionFactoryInterface $exceptionFactory = null,
        SerializerInterface $serializer = null,
        $handlerAttributeName = "__action"
    ) {
        $this->container = $container;
        $this->exceptionFactory = $exceptionFactory;
        $this->serializer = $serializer ? $serializer : new DefaultSerializer();
        $this->handlerAttributeName = $handlerAttributeName;
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

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface $response
     */
    protected function getDispatchErrorResponse(ResponseInterface $response)
    {
        return $this->getErrorDocument($this->getDispatchError())->getResponse($this->serializer, $response);
    }

    /**
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getDispatchError()
    {
        $error = new Error();
        $error->setStatus(404);
        $error->setTitle("Resource was not not found!");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Document\ErrorDocument
     */
    protected function getErrorDocument(Error $error)
    {
        $errorDocument = new ErrorDocument();
        $errorDocument->addError($error);

        return $errorDocument;
    }
}
