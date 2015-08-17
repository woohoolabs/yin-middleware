<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Schema\Error;

class JsonApiResponseValidatorMiddleware extends JsonApiMessageValidator
{
    /**
     * @param bool $lint
     * @param bool $validate
     * @param bool $includeOriginalMessage
     */
    public function __construct($lint = true, $validate = true, $includeOriginalMessage = true)
    {
        parent::__construct($lint, $validate, $includeOriginalMessage);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return void|\Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response = $this->check($response, $response);

        if ($response !== null) {
            return $response;
        }

        $next();
    }

    /**
     * @param string $message
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getLintError($message)
    {
        $error = parent::getLintError($message);
        $error->setStatus(500);
        $error->setTitle("The response body is not a valid JSON!");

        return $error;
    }

    /**
     * @param string $property
     * @param string $message
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getValidationError($property, $message)
    {
        $error = parent::getValidationError($property, $message);
        $error->setStatus(500);
        $error->setTitle("The response body is not a valid JSON API document!");

        return $error;
    }
}
