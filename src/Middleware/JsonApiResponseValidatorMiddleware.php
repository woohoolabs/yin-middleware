<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Middleware;

use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Negotiation\ResponseValidator;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Serializer\JsonSerializer;
use WoohooLabs\Yin\JsonApi\Serializer\SerializerInterface;
use WoohooLabs\YinMiddleware\Utils\JsonApiMessageValidator;

class JsonApiResponseValidatorMiddleware extends JsonApiMessageValidator
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        ?ExceptionFactoryInterface $exceptionFactory = null,
        SerializerInterface $serializer = null,
        bool $includeOriginalMessageInResponse = true,
        bool $lintBody = true,
        bool $validateBody = true
    ) {
        parent::__construct($includeOriginalMessageInResponse, $lintBody, $validateBody, $exceptionFactory);
        $this->serializer = $serializer ?? new JsonSerializer();
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
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
