<?php
namespace WoohooLabs\YinMiddlewares\Utils;

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
        ExceptionFactoryInterface $exceptionFactory,
        $includeOriginalMessageInResponse,
        $lintBody,
        $validateBody
    ) {
        $this->exceptionFactory = $exceptionFactory === null ? new ExceptionFactory() : $exceptionFactory;
        $this->includeOriginalMessageInResponse = $includeOriginalMessageInResponse;
        $this->lintBody = $lintBody;
        $this->validateBody = $validateBody;
    }
}
