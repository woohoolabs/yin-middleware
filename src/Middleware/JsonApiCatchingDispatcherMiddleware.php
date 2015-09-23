<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\ClientGeneratedIdAlreadyExists;
use WoohooLabs\Yin\JsonApi\Exception\ClientGeneratedIdNotSupported;
use WoohooLabs\Yin\JsonApi\Exception\FullReplacementProhibited;
use WoohooLabs\Yin\JsonApi\Exception\InclusionNotSupported;
use WoohooLabs\Yin\JsonApi\Exception\InclusionUnrecognized;
use WoohooLabs\Yin\JsonApi\Exception\MediaTypeUnacceptable;
use WoohooLabs\Yin\JsonApi\Exception\MediaTypeUnsupported;
use WoohooLabs\Yin\JsonApi\Exception\QueryParamUnrecognized;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipTypeNotAppropriate;
use WoohooLabs\Yin\JsonApi\Exception\ResourceIdInvalid;
use WoohooLabs\Yin\JsonApi\Exception\ResourceIdMissing;
use WoohooLabs\Yin\JsonApi\Exception\ResourceTypeMissing;
use WoohooLabs\Yin\JsonApi\Exception\ResourceTypeUnacceptable;
use WoohooLabs\Yin\JsonApi\Exception\RemovalProhibited;
use WoohooLabs\Yin\JsonApi\Exception\SortingNotSupported;
use WoohooLabs\Yin\JsonApi\Exception\SortParamUnrecognized;
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
            $next($request, $response);
        } catch (ClientGeneratedIdNotSupported $e) {
            return $this
                ->getClientGeneratedIdNotSupportedErrorDocument($this->getClientGeneratedIdNotSupportedError($e))
                ->getResponse($response);
        } catch (ClientGeneratedIdAlreadyExists $e) {
            return $this
                ->getClientGeneratedIdAlreadyExistsErrorDocument($this->getClientGeneratedIdAlreadyExistsError($e))
                ->getResponse($response);
        } catch (FullReplacementProhibited $e) {
            return $this
                ->getFullReplacementProhibitedErrorDocument($this->getFullReplacementProhibitedError($e))
                ->getResponse($response);
        } catch (InclusionNotSupported $e) {
            return $this
                ->getInclusionNotSupportedErrorDocument($this->getInclusionNotSupportedError($e))
                ->getResponse($response);
        } catch (InclusionUnrecognized $e) {
            return $this
                ->getInclusionUnrecognizedErrorDocument($this->getInclusionUnrecognizedError($e))
                ->getResponse($response);
        } catch (MediaTypeUnacceptable $e) {
            return $this
                ->getMediaTypeUnacceptableErrorDocument($this->getMediaTypeUnacceptableError($e))
                ->getResponse($response);
        } catch (MediaTypeUnsupported $e) {
            return $this
                ->getMediaTypeUnsupportedErrorDocument($this->getMediaTypeUnsupportedError($e))
                ->getResponse($response);
        } catch (QueryParamUnrecognized $e) {
            return $this
                ->getQueryParamUnrecognizedErrorDocument($this->getQueryParamUnrecognizedError($e))
                ->getResponse($response);
        } catch (RelationshipTypeNotAppropriate $e) {
            return $this
                ->getRelationshipTypeNotAppropriateErrorDocument($this->getRelationshipTypeNotAppropriateError($e))
                ->getResponse($response);
        } catch (RemovalProhibited $e) {
            return $this
                ->getRemovalProhibitedErrorDocument($this->getRemovalProhibitedError($e))
                ->getResponse($response);
        } catch (ResourceIdInvalid $e) {
            return $this
                ->getResourceIdInvalidErrorDocument($this->getResourceIdInvalidError($e))
                ->getResponse($response);
        } catch (ResourceIdMissing $e) {
            return $this
                ->getResourceIdMissingErrorDocument($this->getResourceIdMissingError($e))
                ->getResponse($response);
        } catch (ResourceTypeMissing $e) {
            return $this
                ->getResourceTypeMissingErrorDocument($this->getResourceTypeMissingError($e))
                ->getResponse($response);
        } catch (ResourceTypeUnacceptable $e) {
            return $this
                ->getResourceTypeUnacceptableErrorDocument($this->getResourceTypeUnacceptableError($e))
                ->getResponse($response);
        } catch (SortingNotSupported $e) {
            return $this
                ->getSortingNotSupportedErrorDocument($this->getSortingNotSupportedError($e))
                ->getResponse($response);
        } catch (SortParamUnrecognized $e) {
            return $this
                ->getSortParamUnrecognizedErrorDocument($this->getSortParamUnrecognizedError($e))
                ->getResponse($response);
        }
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
     * @param \WoohooLabs\Yin\JsonApi\Exception\ClientGeneratedIdAlreadyExists $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getClientGeneratedIdAlreadyExistsError(ClientGeneratedIdAlreadyExists $e)
    {
        $error = new Error();
        $error->setStatus(409);
        $error->setTitle($e->getMessage());

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
     * @param \WoohooLabs\Yin\JsonApi\Exception\ClientGeneratedIdNotSupported $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getClientGeneratedIdNotSupportedError(ClientGeneratedIdNotSupported $e)
    {
        $error = new Error();
        $error->setStatus(403);
        $error->setTitle($e->getMessage());

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
     * @param \WoohooLabs\Yin\JsonApi\Exception\FullReplacementProhibited $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getFullReplacementProhibitedError(FullReplacementProhibited $e)
    {
        $error = new Error();
        $error->setStatus(403);
        $error->setTitle($e->getMessage());

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
     * @param \WoohooLabs\Yin\JsonApi\Exception\InclusionNotSupported $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getInclusionNotSupportedError(InclusionNotSupported $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle($e->getMessage());

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
     * @param \WoohooLabs\Yin\JsonApi\Exception\InclusionUnrecognized $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getInclusionUnrecognizedError(InclusionUnrecognized $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle($e->getMessage());

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getMediaTypeUnacceptableErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\MediaTypeUnacceptable $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getMediaTypeUnacceptableError(MediaTypeUnacceptable $e)
    {
        $error = new Error();
        $error->setStatus(406);
        $error->setTitle($e->getMessage());

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getMediaTypeUnsupportedErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\MediaTypeUnsupported $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getMediaTypeUnsupportedError(MediaTypeUnsupported $e)
    {
        $error = new Error();
        $error->setStatus(415);
        $error->setTitle($e->getMessage());

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getRelationshipTypeNotAppropriateErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\RelationshipTypeNotAppropriate $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getRelationshipTypeNotAppropriateError(RelationshipTypeNotAppropriate $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle($e->getMessage());

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
     * @param \WoohooLabs\Yin\JsonApi\Exception\ResourceIdMissing $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getResourceIdMissingError(ResourceIdMissing $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle($e->getMessage());

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getResourceIdInvalidErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\ResourceIdInvalid $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getResourceIdInvalidError(ResourceIdInvalid $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle($e->getMessage());

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
     * @param \WoohooLabs\Yin\JsonApi\Exception\ResourceTypeMissing $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getResourceTypeMissingError(ResourceTypeMissing $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle($e->getMessage());

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
     * @param \WoohooLabs\Yin\JsonApi\Exception\ResourceTypeUnacceptable $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getResourceTypeUnacceptableError(ResourceTypeUnacceptable $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle($e->getMessage());

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
     * @param \WoohooLabs\Yin\JsonApi\Exception\QueryParamUnrecognized $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getQueryParamUnrecognizedError(QueryParamUnrecognized $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle($e->getMessage());

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

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\RemovalProhibited $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getRemovalProhibitedError(RemovalProhibited $e)
    {
        $error = new Error();
        $error->setStatus(403);
        $error->setTitle($e->getMessage());

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getSortingNotSupportedErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\SortingNotSupported $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getSortingNotSupportedError(SortingNotSupported $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle($e->getMessage());

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error $error
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getSortParamUnrecognizedErrorDocument(Error $error)
    {
        return $this->getErrorDocument($error);
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\SortParamUnrecognized $e
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getSortParamUnrecognizedError(SortParamUnrecognized $e)
    {
        $error = new Error();
        $error->setStatus(400);
        $error->setTitle($e->getMessage());

        return $error;
    }
}
