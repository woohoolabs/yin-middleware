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
    protected $checkQueryParams;

    /**
     * @var RequestValidator
     */
    protected $validator;

    public function __construct(
        ?ExceptionFactoryInterface $exceptionFactory = null,
        bool $includeOriginalMessageInResponse = true,
        bool $negotiate = true,
        bool $checkQueryParams = true,
        bool $lintBody = true
    ) {
        parent::__construct($includeOriginalMessageInResponse, $lintBody, false, $exceptionFactory);
        $this->negotiate = $negotiate;
        $this->checkQueryParams = $checkQueryParams;
        $this->validator = new RequestValidator($this->exceptionFactory, $this->includeOriginalMessageInResponse);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jsonApiRequest = $this->getJsonApiRequest($request);

        if ($this->negotiate) {
            $this->validator->negotiate($jsonApiRequest);
        }

        if ($this->checkQueryParams) {
            $this->validator->validateQueryParams($jsonApiRequest);
        }

        if ($this->lintBody) {
            $this->validator->lintBody($jsonApiRequest);
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
