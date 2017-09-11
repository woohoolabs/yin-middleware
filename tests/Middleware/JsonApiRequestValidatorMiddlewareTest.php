<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
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
    public function successOnValidQueryParams()
    {
        $requestValidator = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            false,
            true,
            false
        );

        $request = $this->getRequest("https://example.com/test?foo_bar=baz&page[number]=1&page[size]=10");

        $requestValidator($request, $this->getResponse(), $this->getNext());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function successOnEmptyRequestBody()
    {
        $requestValidator = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            false,
            false,
            true
        );

        $request = $this->getRequest();

        $requestValidator($request, $this->getResponse(), $this->getNext());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function successOnValidRequestBody()
    {
        $requestValidator = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            false,
            false,
            true
        );

        $request = $this->getRequest("", $this->getValidRequestBody());

        $requestValidator($request, $this->getResponse(), $this->getNext());
    }

    /**
     * @test
     */
    public function exceptionOnInvalidJsonRequestBody()
    {
        $requestValidator = new JsonApiRequestValidatorMiddleware(
            null,
            true,
            false,
            false,
            true
        );

        $request = $this->getRequest("", $this->getInvalidJsonRequestBody());

        $this->expectException(RequestBodyInvalidJson::class);
        $requestValidator($request, $this->getResponse(), $this->getNext());
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

    private function getRequest(string $uri = "", string $body = "", string $method = "GET"): Request
    {
        $request = new ServerRequest([], [], $uri, $method, new Stream("php://memory", "rw"));
        $request->getBody()->write($body);

        return new Request($request, new DefaultExceptionFactory());
    }

    private function getResponse()
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
