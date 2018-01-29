<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ResourceNotFound;
use WoohooLabs\Yin\JsonApi\Request\Request;
use WoohooLabs\YinMiddleware\Middleware\JsonApiDispatcherMiddleware;
use WoohooLabs\YinMiddleware\Tests\Utils\FakeController;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class JsonApiDispatcherMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function error404WhenActionIsNull()
    {
        $middleware = new JsonApiDispatcherMiddleware();

        $this->expectException(ResourceNotFound::class);
        $middleware->process($this->createRequest(null), $this->createHandler());
    }

    /**
     * @test
     */
    public function invokeAsCallableObject()
    {
        $middleware = new JsonApiDispatcherMiddleware();

        $response = $middleware->process($this->createRequest([FakeController::class, "__invoke"]), $this->createHandler());

        $this->assertEquals("201", $response->getStatusCode());
    }

    /**
     * @test
     */
    public function invokeAsFunction()
    {
        $middleware = new JsonApiDispatcherMiddleware();

        $response = $middleware->process($this->createRequest(new FakeController()), $this->createHandler());

        $this->assertEquals("201", $response->getStatusCode());
    }

    private function createRequest($action): Request
    {
        $request = new ServerRequest();
        $request = $request->withAttribute("__action", $action);

        return new Request($request, new DefaultExceptionFactory());
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
