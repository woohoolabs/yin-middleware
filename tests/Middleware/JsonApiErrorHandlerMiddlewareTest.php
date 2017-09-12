<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Request\Request;
use WoohooLabs\YinMiddleware\Middleware\JsonApiErrorHandlerMiddleware;
use WoohooLabs\YinMiddleware\Tests\Utils\DummyException;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class JsonApiErrorHandlerMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function exceptionWhenNotCatching()
    {
        $middleware = new JsonApiErrorHandlerMiddleware(false, false);

        $this->expectException(DummyException::class);
        $middleware($this->getRequest(), $this->getResponse(), $this->getNext());
    }

    /**
     * @test
     */
    public function responseWhenException()
    {
        $middleware = new JsonApiErrorHandlerMiddleware(true, false);

        $response = $middleware($this->getRequest(), $this->getResponse(), $this->getNext());

        $this->assertEquals("555", $response->getStatusCode());
    }

    /**
     * @test
     */
    public function notVerboseResponseWhenException()
    {
        $middleware = new JsonApiErrorHandlerMiddleware(true, false);

        $response = $middleware($this->getRequest(), $this->getResponse(), $this->getNext());

        $this->assertArrayNotHasKey("meta", json_decode($response->getBody()->__toString(), true));
    }

    /**
     * @test
     */
    public function verboseResponseWhenException()
    {
        $middleware = new JsonApiErrorHandlerMiddleware(true, true);

        $response = $middleware($this->getRequest(), $this->getResponse(), $this->getNext());
        $body = json_decode($response->getBody()->__toString(), true);

        $this->assertEquals("0", $body["meta"]["code"]);
        $this->assertEquals("Dummy exception", $body["meta"]["message"]);
    }

    private function getRequest(): Request
    {
        return new Request(new ServerRequest(), new DefaultExceptionFactory());
    }

    private function getResponse(): Response
    {
        return new Response();
    }

    private function getNext(): callable
    {
        return function () {
            throw new DummyException();
        };
    }
}
