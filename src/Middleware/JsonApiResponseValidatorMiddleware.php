<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Negotiation\ResponseValidator;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\YinMiddlewares\Utils\JsonApiMessageValidator;

class JsonApiResponseValidatorMiddleware extends JsonApiMessageValidator
{
    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface $exceptionFactory
     * @param bool $includeOriginalMessageInResponse
     * @param bool $lintBody
     * @param bool $validateBody
     */
    public function __construct(
        ExceptionFactoryInterface $exceptionFactory,
        $includeOriginalMessageInResponse = true,
        $lintBody = true,
        $validateBody = true
    ) {
        parent::__construct($exceptionFactory, $includeOriginalMessageInResponse, $lintBody, $validateBody);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return void|\Psr\Http\Message\ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $validator= new ResponseValidator($this->exceptionFactory, $this->includeOriginalMessageInResponse);

        if ($this->lintBody) {
            $validator->lintBody($response);
        }

        if ($this->validateBody) {
            $validator->validateBody($response);
        }

        $next();
    }
}
