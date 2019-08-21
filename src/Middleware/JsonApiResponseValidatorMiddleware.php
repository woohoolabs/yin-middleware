<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Negotiation\ResponseValidator;
use WoohooLabs\Yin\JsonApi\Serializer\JsonSerializer;
use WoohooLabs\Yin\JsonApi\Serializer\SerializerInterface;
use WoohooLabs\YinMiddleware\Utils\JsonApiMessageValidator;

class JsonApiResponseValidatorMiddleware extends JsonApiMessageValidator implements MiddlewareInterface
{
    private ResponseValidator $validator;

    public function __construct(
        ?ExceptionFactoryInterface $exceptionFactory = null,
        ?SerializerInterface $serializer = null,
        bool $includeOriginalMessageInResponse = true,
        bool $lintBody = true,
        bool $validateBody = true
    ) {
        parent::__construct($includeOriginalMessageInResponse, $lintBody, $validateBody, $exceptionFactory);
        $serializer = $serializer ?? new JsonSerializer();
        $this->validator = new ResponseValidator(
            $serializer,
            $this->exceptionFactory,
            $this->includeOriginalMessageInResponse
        );
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($this->validateJsonBody) {
            $this->validator->validateJsonBody($response);
        }

        if ($this->validateJsonApiBody) {
            $this->validator->validateJsonApiBody($response);
        }

        return $response;
    }
}
