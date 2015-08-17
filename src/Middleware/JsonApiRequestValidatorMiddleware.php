<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\YinMiddlewares\Utils\JsonApiMessageValidator;

class JsonApiRequestValidatorMiddleware extends JsonApiMessageValidator
{
    /**
     * @param bool $includeOriginalMessage
     * @param bool $lint
     * @param bool $validate
     */
    public function __construct($includeOriginalMessage = true, $lint = true, $validate = true)
    {
        parent::__construct($includeOriginalMessage, $lint, $validate);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return void|\Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response = $this->check($request, $response);

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
        $error->setStatus(400);
        $error->setTitle("The request body is not a valid JSON document");

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
        $error->setStatus(400);
        $error->setTitle("The request body is not a valid JSON API document");

        return $error;
    }
}
