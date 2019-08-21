<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Utils;

use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;

abstract class JsonApiMessageValidator
{
    protected ExceptionFactoryInterface $exceptionFactory;
    protected bool $includeOriginalMessageInResponse;
    protected bool $validateJsonBody;
    protected bool $validateJsonApiBody;

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
