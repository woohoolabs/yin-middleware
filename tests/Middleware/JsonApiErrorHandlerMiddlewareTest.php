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
use WoohooLabs\YinMiddleware\Middleware\JsonApiExceptionHandlerMiddleware;
use WoohooLabs\YinMiddleware\Tests\Utils\DummyException;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use function json_decode;

class JsonApiErrorHandlerMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function exceptionWhenNotCatching(): void
    {
        $middleware = new JsonApiExceptionHandlerMiddleware($this->createResponse(), false, false);

        $this->expectException(DummyException::class);

        $middleware->process($this->createRequest(), $this->createHandler());
    }

    /**
     * @test
     */
    public function responseWhenException(): void
    {
        $middleware = new JsonApiExceptionHandlerMiddleware($this->createResponse(), true, false);

        $response = $middleware->process($this->createRequest(), $this->createHandler());

        $this->assertEquals("555", $response->getStatusCode());
    }

    /**
     * @test
     */
    public function notVerboseResponseWhenException(): void
    {
        $middleware = new JsonApiExceptionHandlerMiddleware($this->createResponse(), true, false);

        $response = $middleware->process($this->createRequest(), $this->createHandler());

        $this->assertArrayNotHasKey("meta", $this->getBody($response));
    }

    /**
     * @test
     */
    public function verboseResponseWhenException(): void
    {
        $middleware = new JsonApiExceptionHandlerMiddleware($this->createResponse(), true, true);
        $response = $middleware->process($this->createRequest(), $this->createHandler());

        $body = $this->getBody($response);

        $this->assertEquals("0", $body["meta"]["code"]);
        $this->assertEquals("Dummy exception", $body["meta"]["message"]);
    }

    private function createRequest(): JsonApiRequestInterface
    {
        return new JsonApiRequest(new ServerRequest(), new DefaultExceptionFactory());
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

    private function getBody(ResponseInterface $response): array
    {
        return json_decode($response->getBody()->__toString(), true);
    }
}
