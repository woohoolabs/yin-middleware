<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Utils;

use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
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

    public function __construct(
        bool $includeOriginalMessageInResponse,
        bool $lintBody,
        bool $validateBody,
        ExceptionFactoryInterface $exceptionFactory = null
    ) {
        $this->exceptionFactory = $exceptionFactory ? $exceptionFactory : new DefaultExceptionFactory();
        $this->includeOriginalMessageInResponse = $includeOriginalMessageInResponse;
        $this->lintBody = $lintBody;
        $this->validateBody = $validateBody;
    }
}
