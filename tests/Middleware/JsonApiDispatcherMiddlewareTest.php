<?php

declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ResourceNotFound;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequest;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequestInterface;
use WoohooLabs\YinMiddleware\Exception\JsonApiRequestException;
use WoohooLabs\YinMiddleware\Middleware\JsonApiDispatcherMiddleware;
use WoohooLabs\YinMiddleware\Tests\Utils\FakeController;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class JsonApiDispatcherMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function error404WhenActionIsNull(): void
    {
        $middleware = new JsonApiDispatcherMiddleware();

        $this->expectException(ResourceNotFound::class);

        $middleware->process($this->createRequest(null), $this->createHandler());
    }

    /**
     * @test
     */
    public function exceptionOnInvalidServerRequest(): void
    {
        $middleware = new JsonApiDispatcherMiddleware();

        $this->expectException(JsonApiRequestException::class);

        $middleware->process(new ServerRequest(), $this->createHandler());
    }

    /**
     * @test
     */
    public function invokeAsCallableObject(): void
    {
        $middleware = new JsonApiDispatcherMiddleware();

        $response = $middleware->process($this->createRequest([new FakeController(), "__invoke"]), $this->createHandler());

        $this->assertEquals("201", $response->getStatusCode());
    }

    /**
     * @test
     */
    public function invokeAsFunction(): void
    {
        $middleware = new JsonApiDispatcherMiddleware();

        $response = $middleware->process($this->createRequest(new FakeController()), $this->createHandler());

        $this->assertEquals("201", $response->getStatusCode());
    }

    private function createRequest(?callable $action): JsonApiRequestInterface
    {
        $request = new ServerRequest();
        $request = $request->withAttribute("__action", $action);

        return new JsonApiRequest($request, new DefaultExceptionFactory());
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
