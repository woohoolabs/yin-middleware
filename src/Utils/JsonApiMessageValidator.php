<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Utils;

use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;

abstract class JsonApiMessageValidator
{
    /**
     * @var ExceptionFactoryInterface
     */
    protected $exceptionFactory;

    /**
     * @var bool
     */
    protected $includeOriginalMessageInResponse;

    /**
     * @var bool
     */
    protected $validateJsonBody;

    /**
     * @var bool
     */
    protected $validateJsonApiBody;

    public function __construct(
        bool $includeOriginalMessageInResponse,
        bool $validateJsonBody,
        bool $validateJsonApiBody,
        ?ExceptionFactoryInterface $exceptionFactory = null
    ) {
        $this->exceptionFactory = $exceptionFactory ?? new DefaultExceptionFactory();
        $this->includeOriginalMessageInResponse = $includeOriginalMessageInResponse;
        $this->validateJsonBody = $validateJsonBody;
        $this->validateJsonApiBody = $validateJsonApiBody;
    }
}
