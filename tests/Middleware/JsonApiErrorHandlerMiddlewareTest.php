<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
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
        $middleware = new JsonApiErrorHandlerMiddleware($this->createResponse(), false, false);

        $this->expectException(DummyException::class);
        $middleware->process($this->createRequest(), $this->createHandler());
    }

    /**
     * @test
     */
    public function responseWhenException()
    {
        $middleware = new JsonApiErrorHandlerMiddleware($this->createResponse(), true, false);

        $response = $middleware->process($this->createRequest(), $this->createHandler());

        $this->assertEquals("555", $response->getStatusCode());
    }

    /**
     * @test
     */
    public function notVerboseResponseWhenException()
    {
        $middleware = new JsonApiErrorHandlerMiddleware($this->createResponse(), true, false);

        $response = $middleware->process($this->createRequest(), $this->createHandler());

        $this->assertArrayNotHasKey("meta", json_decode($response->getBody()->__toString(), true));
    }

    /**
     * @test
     */
    public function verboseResponseWhenException()
    {
        $middleware = new JsonApiErrorHandlerMiddleware($this->createResponse(), true, true);

        $response = $middleware->process($this->createRequest(), $this->createHandler());
        $body = json_decode($response->getBody()->__toString(), true);

        $this->assertEquals("0", $body["meta"]["code"]);
        $this->assertEquals("Dummy exception", $body["meta"]["message"]);
    }

    private function createRequest(): Request
    {
        return new Request(new ServerRequest(), new DefaultExceptionFactory());
    }

    private function createResponse(): Response
    {
        return new Response();
    }

    private function createHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new DummyException();
            }
        };
    }
}
