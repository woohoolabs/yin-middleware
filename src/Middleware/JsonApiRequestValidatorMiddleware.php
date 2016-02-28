<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Negotiation\RequestValidator;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\YinMiddlewares\Utils\JsonApiMessageValidator;

class JsonApiRequestValidatorMiddleware extends JsonApiMessageValidator
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
     * @param \WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface $exceptionFactory
     * @param bool $includeOriginalMessageInResponse
     * @param bool $negotiate
     * @param bool $checkQueryParams
     * @param bool $lintBody
     */
    public function __construct(
        ExceptionFactoryInterface $exceptionFactory,
        $includeOriginalMessageInResponse = true,
        $negotiate = true,
        $checkQueryParams = true,
        $lintBody = true
    ) {
        parent::__construct($exceptionFactory, $includeOriginalMessageInResponse, $lintBody, false);
        $this->negotiate = $negotiate;
        $this->checkQueryParams = $checkQueryParams;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return void|\Psr\Http\Message\ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
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

        return $next();
    }
}
