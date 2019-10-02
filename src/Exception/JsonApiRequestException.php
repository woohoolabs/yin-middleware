<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Exception;

use Exception;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequestInterface;
use function sprintf;

class JsonApiRequestException extends Exception
{
    public function __construct()
    {
        parent::__construct(sprintf("The request must be a %s instance!", JsonApiRequestInterface::class));
    }
}
