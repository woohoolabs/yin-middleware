<?php

declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequest;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequestInterface;
use WoohooLabs\YinMiddleware\Tests\Utils\DummyDeserializer;
use WoohooLabs\YinMiddleware\Tests\Utils\DummyExceptionFactory;
use WoohooLabs\YinMiddleware\Tests\Utils\SpyYinCompatibilityMiddleware;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class YinCompatibilityMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function processServerRequest(): void
    {
        $middleware = new SpyYinCompatibilityMiddleware(new DummyExceptionFactory(), new DummyDeserializer());

        $middleware->process($this->createServerRequest(), $this->createHandler());

        $this->assertInstanceOf(JsonApiRequest::class, $middleware->getUpgradedRequest());
    }

    /**
     * @test
     */
    public function processYinRequest(): void
    {
        $middleware = new SpyYinCompatibilityMiddleware();

        $middleware->process($this->createYinRequest(), $this->createHandler());

        $this->assertNull($middleware->getUpgradedRequest());
    }

    private function createServerRequest(): ServerRequest
    {
        return new ServerRequest();
    }

    private function createYinRequest(): JsonApiRequestInterface
    {
        return new JsonApiRequest(new ServerRequest(), new DefaultExceptionFactory());
    }

    private function createHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
    }
}
