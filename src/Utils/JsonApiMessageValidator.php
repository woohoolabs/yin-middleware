<?php
namespace WoohooLabs\YinMiddleware\Utils;

use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;

abstract class JsonApiMessageValidator
{
    /**
     * @var \WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface
     */
    protected $exceptionFactory;

    /**
     * @var bool
     */
    protected $includeOriginalMessageInResponse;

    /**
     * @var bool
     */
    protected $lintBody;

    /**
     * @var bool
     */
    protected $validateBody;

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface $exceptionFactory
     * @param bool $includeOriginalMessageInResponse
     * @param bool $lintBody
     * @param bool $validateBody
     */
    public function __construct(
        $includeOriginalMessageInResponse,
        $lintBody,
        $validateBody,
        ExceptionFactoryInterface $exceptionFactory = null
    ) {
        $this->exceptionFactory = $exceptionFactory ? $exceptionFactory : new DefaultExceptionFactory();
        $this->includeOriginalMessageInResponse = $includeOriginalMessageInResponse;
        $this->lintBody = $lintBody;
        $this->validateBody = $validateBody;
    }
}
