<?php
namespace WoohooLabs\YinMiddleware\Middleware;

use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Negotiation\ResponseValidator;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Serializer\DefaultSerializer;
use WoohooLabs\Yin\JsonApi\Serializer\SerializerInterface;
use WoohooLabs\YinMiddleware\Utils\JsonApiMessageValidator;

class JsonApiResponseValidatorMiddleware extends JsonApiMessageValidator
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface|null $exceptionFactory
     * @param SerializerInterface|null $serializer
     * @param bool $includeOriginalMessageInResponse
     * @param bool $lintBody
     * @param bool $validateBody
     */
    public function __construct(
        ExceptionFactoryInterface $exceptionFactory = null,
        SerializerInterface $serializer = null,
        $includeOriginalMessageInResponse = true,
        $lintBody = true,
        $validateBody = true
    ) {
        parent::__construct($includeOriginalMessageInResponse, $lintBody, $validateBody, $exceptionFactory);
        $this->serializer = $serializer ? $serializer : new DefaultSerializer();
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return void|\Psr\Http\Message\ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $validator = new ResponseValidator(
            $this->serializer,
            $this->exceptionFactory,
            $this->includeOriginalMessageInResponse
        );

        if ($this->lintBody) {
            $validator->lintBody($response);
        }

        if ($this->validateBody) {
            $validator->validateBody($response);
        }

        return $next($request, $response);
    }
}
