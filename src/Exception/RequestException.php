<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Exception;

use Exception;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

class RequestException extends Exception
{
    public function __construct()
    {
        parent::__construct("The request must be a " . RequestInterface::class . " instance!");
    }
}
