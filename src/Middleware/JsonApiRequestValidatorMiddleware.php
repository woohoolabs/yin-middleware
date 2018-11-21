<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Negotiation\RequestValidator;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\YinMiddleware\Exception\RequestException;
use WoohooLabs\YinMiddleware\Utils\JsonApiMessageValidator;

class JsonApiRequestValidatorMiddleware extends JsonApiMessageValidator implements MiddlewareInterface
{
    /**
     * @var bool
     */
    protected $negotiate;

    /**
     * @var bool
     */
    protected $validateQueryParams;

    /**
     * @var RequestValidator
     */
    protected $validator;

    public function __construct(
        ?ExceptionFactoryInterface $exceptionFactory = null,
        bool $includeOriginalMessageInResponse = true,
        bool $negotiate = true,
        bool $validateQueryParams = true,
        bool $validateJsonBody = true
    ) {
        parent::__construct($includeOriginalMessageInResponse, $validateJsonBody, false, $exceptionFactory);
        $this->negotiate = $negotiate;
        $this->validateQueryParams = $validateQueryParams;
        $this->validator = new RequestValidator($this->exceptionFactory, $this->includeOriginalMessageInResponse);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jsonApiRequest = $this->getJsonApiRequest($request);

        if ($this->negotiate) {
            $this->validator->negotiate($jsonApiRequest);
        }

        if ($this->validateQueryParams) {
            $this->validator->validateQueryParams($jsonApiRequest);
        }

        if ($this->validateJsonBody) {
            $this->validator->validateJsonBody($jsonApiRequest);
        }

        return $handler->handle($request);
    }

    protected function getJsonApiRequest(ServerRequestInterface $request): RequestInterface
    {
        if ($request instanceof RequestInterface) {
            return $request;
        }

        throw new RequestException();
    }
}
