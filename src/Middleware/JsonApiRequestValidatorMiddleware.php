<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Negotiation\RequestValidator;
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
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $validator = new RequestValidator($this->exceptionFactory, $this->includeOriginalMessageInResponse);

        if ($this->negotiate) {
            $validator->negotiate($request);
        }

        if ($this->checkQueryParams) {
            $validator->validateQueryParams($request);
        }

        if ($this->lintBody) {
            $validator->lintBody($request);
        }

        return $handler->handle($request);
    }
}
