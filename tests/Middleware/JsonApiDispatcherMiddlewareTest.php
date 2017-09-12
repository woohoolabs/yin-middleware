<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
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

        $response = $middleware($this->getRequest(null), $this->getResponse(), $this->getNext());

        $this->assertEquals("404", $response->getStatusCode());
    }

    /**
     * @test
     */
    public function invokeAsCallableObject()
    {
        $middleware = new JsonApiDispatcherMiddleware();

        $response = $middleware($this->getRequest([FakeController::class, "__invoke"]), $this->getResponse(), $this->getNext());

        $this->assertEquals("201", $response->getStatusCode());
    }

    /**
     * @test
     */
    public function invokeAsFunction()
    {
        $middleware = new JsonApiDispatcherMiddleware();

        $response = $middleware($this->getRequest(new FakeController()), $this->getResponse(), $this->getNext());

        $this->assertEquals("201", $response->getStatusCode());
    }

    private function getRequest($action): Request
    {
        $request = new ServerRequest();
        $request = $request->withAttribute("__action", $action);

        return new Request($request, new DefaultExceptionFactory());
    }

    private function getResponse(): Response
    {
        return new Response();
    }

    private function getNext(): callable
    {
        return function ($request, $response) {
            return $response;
        };
    }
}
