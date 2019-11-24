<?php

declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Tests\Utils;

use Psr\Http\Message\ServerRequestInterface;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequest;
use WoohooLabs\YinMiddleware\Middleware\YinCompatibilityMiddleware;

class SpyYinCompatibilityMiddleware extends YinCompatibilityMiddleware
{
    private ?JsonApiRequest $upgradedRequest = null;

    protected function createJsonApiRequest(ServerRequestInterface $request): JsonApiRequest
    {
        $this->upgradedRequest = parent::createJsonApiRequest($request);

        return $this->upgradedRequest;
    }

    public function getUpgradedRequest(): ?JsonApiRequest
    {
        return $this->upgradedRequest;
    }
}
