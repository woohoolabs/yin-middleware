<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\ClientGeneratedIdAlreadyExists;
use WoohooLabs\Yin\JsonApi\Exception\ClientGeneratedIdNotSupported;
use WoohooLabs\Yin\JsonApi\Exception\FullReplacementProhibited;
use WoohooLabs\Yin\JsonApi\Exception\InclusionNotSupported;
use WoohooLabs\Yin\JsonApi\Exception\InclusionUnrecognized;
use WoohooLabs\Yin\JsonApi\Exception\QueryParamUnrecognized;
use WoohooLabs\Yin\JsonApi\Exception\ResourceIdMissing;
use WoohooLabs\Yin\JsonApi\Exception\ResourceTypeMissing;
use WoohooLabs\Yin\JsonApi\Exception\ResourceTypeUnacceptable;
use WoohooLabs\Yin\JsonApi\Exception\RemovalProhibited;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Schema\Error;

class JsonApiCatchingDispatcherMiddleware extends JsonApiDispatcherMiddleware
{
    /**
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return void|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        try {
            $result = parent::__invoke($request, $response, $next);
            if ($result) {
                return $result;
            }
        } catch (InclusionNotSupported $e) {
            return $this
                ->getInclusionNotSupportedErrorDocument($this->getInclusionNotSupportedError())
                ->getResponse($response);
        } catch (InclusionUnrecognized $e) {
            return $this
                ->getInclusionUnrecognizedErrorDocument($this->getInclusionUnrecognizedError($e))
                ->getResponse($response);
        } catch (QueryParamUnrecognized $e) {
            return $this
                ->getQueryParamUnrecognizedErrorDocument($this->getQueryParamUnrecognizedError($e))
                ->getResponse($response);
        } catch (ClientGeneratedIdNotSupported $e) {
            return $this
                ->getClientGeneratedIdNotSupportedErrorDocument($this->getClientGeneratedIdNotSupportedError($e))
                ->getResponse($response);
        } catch (ClientGeneratedIdAlreadyExists $e) {
            return $this
                ->getClientGeneratedIdAlreadyExistsErrorDocument($this->getClientGeneratedIdAlreadyExistsError($e))
                ->getResponse($response);
        } catch (ResourceTypeMissing $e) {
            return $this
                ->getResourceTypeMissingErrorDocument($this->getResourceTypeMissingError($e))
                ->getResponse($response);
        } catch (ResourceTypeUnacceptable $e) {
            return $this
                ->getResourceTypeUnacceptableErrorDocument($this->getResourceTypeUnacceptableError($e))
                ->getResponse($response);
        } catch (ResourceIdMissing $e) {
            return $this
                ->getResourceIdMissingErrorDocument($this->getResourceIdMissingError($e))
                ->getResponse($response);
        } catch (FullReplacementProhibited $e) {
            return $this
                ->getFullReplacementProhibitedErrorDocument($this->getFullReplacementProhibitedError($e))
                ->getResponse($response);
        } catch (RemovalProhibited $e) {
            return $this
                ->getRemovalProhibitedErrorDocument($this->getRemovalProhibitedError($e))
                ->getResponse($response);
        }

        $next($request, $response);
    }

    /**
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getInclusionNotSupportedError()
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle("Inclusion is not supported");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getInclusionNotSupportedErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\InclusionUnrecognized $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getInclusionUnrecognizedError(InclusionUnrecognized $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle("Included path '" . $e->getIncludes() . "' is unrecognized");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getInclusionUnrecognizedErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\QueryParamUnrecognized $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getQueryParamUnrecognizedError(QueryParamUnrecognized $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle("Query parameter '" . $e->getQueryParam() . "' is unrecognized");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getQueryParamUnrecognizedErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\ClientGeneratedIdNotSupported $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getClientGeneratedIdNotSupportedError(ClientGeneratedIdNotSupported $e)
    {
        $error = new Error();
        $error->setStatus(403);
        $error->setTitle("Client generated ID '" . $e->getClientGeneratedId() . "' is not supported");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getClientGeneratedIdNotSupportedErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\ClientGeneratedIdAlreadyExists $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getClientGeneratedIdAlreadyExistsError(ClientGeneratedIdAlreadyExists $e)
    {
        $error = new Error();
        $error->setStatus(409);
        $error->setTitle("Client generated ID '" . $e->getClientGeneratedId() . "' already exists");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getClientGeneratedIdAlreadyExistsErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\ResourceTypeMissing $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getResourceTypeMissingError(ResourceTypeMissing $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle("Resource type is missing");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getResourceTypeMissingErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\ResourceTypeUnacceptable $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getResourceTypeUnacceptableError(ResourceTypeUnacceptable $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle("Resource type '" . $e->getType() . "' is unacceptable for this endpoint");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getResourceTypeUnacceptableErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\ResourceIdMissing $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getResourceIdMissingError(ResourceIdMissing $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle("Resource ID is missing");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getResourceIdMissingErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\FullReplacementProhibited $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getFullReplacementProhibitedError(FullReplacementProhibited $e)
    {
        $error = new Error();
        $error->setStatus(403);
        $error->setTitle("Full replacement of relationship '" . $e->getRelationshipName() . "' is prohibited");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getFullReplacementProhibitedErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\RemovalProhibited $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getRemovalProhibitedError(RemovalProhibited $e)
    {
        $error = new Error();
        $error->setStatus(403);
        $error->setTitle("Removal of relationship '" . $e->getRelationshipName() . "' is prohibited");

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getRemovalProhibitedErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }
}
