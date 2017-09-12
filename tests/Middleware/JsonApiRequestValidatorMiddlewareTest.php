<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\MediaTypeUnacceptable;
use WoohooLabs\Yin\JsonApi\Exception\MediaTypeUnsupported;
use WoohooLabs\Yin\JsonApi\Exception\QueryParamUnrecognized;
use WoohooLabs\Yin\JsonApi\Exception\RequestBodyInvalidJson;
use WoohooLabs\Yin\JsonApi\Request\Request;
use WoohooLabs\YinMiddleware\Middleware\JsonApiRequestValidatorMiddleware;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

class JsonApiRequestValidatorMiddlewareTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     * @test
     */
    public function successOnValidHeaders()
    {
        $middleware = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            true,
            false,
            false
        );

        $request = $this->getRequest(
            [],
            "",
            ["Content-Type" => "application/vnd.api+json", "Accept" => "application/vnd.api+json"]
        );

        $middleware($request, $this->getResponse(), $this->getNext());
    }

    /**
     * @doesNotPerformAssertions
     * @test
     */
    public function successOnMissingHeaders()
    {
        $middleware = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            true,
            false,
            false
        );

        $request = $this->getRequest();

        $middleware($request, $this->getResponse(), $this->getNext());
    }

    /**
     * @test
     */
    public function exceptionOnInvalidContentTypeHeader()
    {
        $middleware = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            true,
            false,
            false
        );

        $request = $this->getRequest([], "", ["Content-Type" => "application/vnd.api+json; version=1", "Accept" => "application/vnd.api+json"]);

        $this->expectException(MediaTypeUnsupported::class);
        $middleware($request, $this->getResponse(), $this->getNext());
    }

    /**
     * @test
     */
    public function exceptionOnInvalidAcceptHeader()
    {
        $middleware = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            true,
            false,
            false
        );

        $request = $this->getRequest([], "", ["Content-Type" => "application/vnd.api+json", "Accept" => "application/vnd.api+json; version=1"]);

        $this->expectException(MediaTypeUnacceptable::class);
        $middleware($request, $this->getResponse(), $this->getNext());
    }

    /**
     * @doesNotPerformAssertions
     * @test
     */
    public function successOnValidQueryParams()
    {
        $middleware = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            false,
            true,
            false
        );

        $request = $this->getRequest(["page" => ["number" => "1", "size" => "10"]]);

        $middleware($request, $this->getResponse(), $this->getNext());
    }

    /**
     * @test
     */
    public function exceptionOnInvalidQueryParams()
    {
        $middleware = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            false,
            true,
            false
        );

        $request = $this->getRequest(["foo" => "bar"]);

        $this->expectException(QueryParamUnrecognized::class);
        $middleware($request, $this->getResponse(), $this->getNext());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function successOnEmptyRequestBody()
    {
        $middleware = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            false,
            false,
            true
        );

        $request = $this->getRequest();

        $middleware($request, $this->getResponse(), $this->getNext());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function successOnValidRequestBody()
    {
        $middleware = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            false,
            false,
            true
        );

        $request = $this->getRequest([], $this->getValidRequestBody());

        $middleware($request, $this->getResponse(), $this->getNext());
    }

    /**
     * @test
     */
    public function exceptionOnInvalidJsonRequestBody()
    {
        $middleware = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            false,
            false,
            true
        );

        $request = $this->getRequest([], $this->getInvalidJsonRequestBody());

        $this->expectException(RequestBodyInvalidJson::class);
        $middleware($request, $this->getResponse(), $this->getNext());
    }

    private function getValidRequestBody(): string
    {
        return <<<EOF
{
  "data": {
    "type": "photos",
    "attributes": {
      "title": "Ember Hamster",
      "src": "http://example.com/images/productivity.png"
    },
    "relationships": {
      "photographer": {
        "data": { "type": "people", "id": "9" }
      }
    }
  }
}
EOF;
    }

    private function getInvalidJsonRequestBody(): string
    {
        return <<<EOF
{
  "data": {
    "type": "photos"
    "attributes": {
      "title": "Ember Hamster",
      "src": "http://example.com/images/productivity.png"
    },
  }
}
EOF;
    }

    private function getRequest(array $queryParams = [], string $body = "", array $headers = []): Request
    {
        $request = new ServerRequest([], [], "", "POST", new Stream("php://memory", "rw"));
        $request = $request->withQueryParams($queryParams);
        $request->getBody()->write($body);
        foreach ($headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

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
