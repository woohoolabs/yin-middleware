<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\MediaTypeUacceptable;
use WoohooLabs\Yin\JsonApi\Exception\MediaTypeUnsupported;
use WoohooLabs\Yin\JsonApi\Exception\QueryParamUnrecognized;
use WoohooLabs\Yin\JsonApi\Schema\Error;
use WoohooLabs\YinMiddlewares\Utils\JsonApiMessageValidator;

class JsonApiRequestValidatorMiddleware extends JsonApiMessageValidator
{
    /**
     * @var bool
     */
    private $checkMediaType;

    /**
     * @var bool
     */
    private $checkQueryParams;

    /**
     * @param bool $includeOriginalMessageInResponse
     * @param bool $checkMediaType
     * @param bool $checkQueryParams
     * @param bool $lint
     * @param bool $validate
     */
    public function __construct(
        $includeOriginalMessageInResponse = true,
        $checkMediaType = true,
        $checkQueryParams = true,
        $lint = true,
        $validate = true
    ) {
        parent::__construct($includeOriginalMessageInResponse, $lint, $validate);
        $this->checkMediaType = $checkMediaType;
        $this->checkQueryParams = $checkQueryParams;
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

        if ($this->checkMediaType) {
            try {
                $request->validateContentHeader();
                $request->validateAcceptHeader();
            } catch (MediaTypeUnsupported $e) {
                return $this->getContentTypeHeaderErrorDocument($this->getContentTypeHeaderError($e));
            } catch (MediaTypeUnacceptable $e) {
                return $this->getAcceptHeaderErrorDocument($this->getAcceptHeaderError($e));
            }
        }

        if ($this->checkQueryParams) {
            try {
                $request->validateQueryParams();
            } catch (QueryParamUnrecognized $e) {
                return $this->getQueryParamsErrorDocument($this->getQueryParamsError($e));
            }
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

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\MediaTypeUnsupported $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getContentTypeHeaderError(MediaTypeUnsupported $e)
    {
        $error = new Error();
        $error->setStatus(415);
        $error->setTitle("Unsupported media type: '" . $e->getMediaType() . "'");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getContentTypeHeaderErrorDocument(Error $error)
    {
        return $this->getErrorDocument([$error]);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\MediaTypeUnacceptable $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getAcceptHeaderError(MediaTypeUnacceptable $e)
    {
        $error = new Error();
        $error->setStatus(406);
        $error->setTitle("Unacceptable media type: '" .  $e->getMediaType() . "'");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getAcceptHeaderErrorDocument(Error $error)
    {
        return $this->getErrorDocument([$error]);
    }

    /**
     * @param string $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getQueryParamsError(QueryParamUnrecognized $e)
    {
        $error = new Error();
        $error->setStatus(406);
        $error->setTitle("Unrecognized query parameter: '" .  $e->getQueryParam() . "'");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getQueryParamsErrorDocument(Error $error)
    {
        return $this->getErrorDocument([$error]);
    }
}
