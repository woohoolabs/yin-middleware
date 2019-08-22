<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Tests\Utils;

use WoohooLabs\Yin\JsonApi\Exception\AbstractJsonApiException;
use WoohooLabs\Yin\JsonApi\Schema\Error\Error;

class DummyException extends AbstractJsonApiException
{
    public function __construct()
    {
        parent::__construct("Dummy exception");
    }

    /**
     * @return array<int, Error>
     */
    protected function getErrors(): array
    {
        return [
            Error::create()
                ->setStatus("555")
                ->setCode("Dummy"),
        ];
    }
}
