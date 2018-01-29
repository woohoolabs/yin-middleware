<?php
declare(strict_types=1);

namespace WoohooLabs\YinMiddleware\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ResponseBodyInvalidJson;
use WoohooLabs\Yin\JsonApi\Exception\ResponseBodyInvalidJsonApi;
use WoohooLabs\Yin\JsonApi\Request\Request;
use WoohooLabs\YinMiddleware\Middleware\JsonApiResponseValidatorMiddleware;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class JsonApiResponseValidatorMiddlewareTest extends TestCase
{
    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function successOnEmptyResponseBody()
    {
        $middleware = new JsonApiResponseValidatorMiddleware();

        $response = new Response();

        $middleware->process($this->getRequest(), $this->createHandler($response));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function successOnValidResponseBody()
    {
        $middleware = new JsonApiResponseValidatorMiddleware();

        $response = new Response();
        $response->getBody()->write($this->getValidResponseBody());

        $middleware->process($this->getRequest(), $this->createHandler($response));
    }

    /**
     * @test
     */
    public function exceptionOnInvalidJsonResponseBody()
    {
        $middleware = new JsonApiResponseValidatorMiddleware();

        $response = new Response();
        $response->getBody()->write($this->getInvalidJsonResponseBody());

        $this->expectException(ResponseBodyInvalidJson::class);
        $middleware->process($this->getRequest(), $this->createHandler($response));
    }

    /**
     * @test
     */
    public function exceptionOnInvalidJsonApiResponseBody()
    {
        $middleware = new JsonApiResponseValidatorMiddleware();

        $response = new Response();
        $response->getBody()->write($this->getInvalidJsonApiResponseBody());

        $this->expectException(ResponseBodyInvalidJsonApi::class);
        $middleware->process($this->getRequest(), $this->createHandler($response));
    }

    private function getValidResponseBody(): string
    {
        return <<<EOF
{
  "links": {
    "self": "http://example.com/articles",
    "next": "http://example.com/articles?page[offset]=2",
    "last": "http://example.com/articles?page[offset]=10"
  },
  "data": [{
    "type": "articles",
    "id": "1",
    "attributes": {
      "title": "JSON API paints my bikeshed!"
    },
    "relationships": {
      "author": {
        "links": {
          "self": "http://example.com/articles/1/relationships/author",
          "related": "http://example.com/articles/1/author"
        },
        "data": { "type": "people", "id": "9" }
      },
      "comments": {
        "links": {
          "self": "http://example.com/articles/1/relationships/comments",
          "related": "http://example.com/articles/1/comments"
        },
        "data": [
          { "type": "comments", "id": "5" },
          { "type": "comments", "id": "12" }
        ]
      }
    },
    "links": {
      "self": "http://example.com/articles/1"
    }
  }],
  "included": [{
    "type": "people",
    "id": "9",
    "attributes": {
      "first-name": "Dan",
      "last-name": "Gebhardt",
      "twitter": "dgeb"
    },
    "links": {
      "self": "http://example.com/people/9"
    }
  }, {
    "type": "comments",
    "id": "5",
    "attributes": {
      "body": "First!"
    },
    "relationships": {
      "author": {
        "data": { "type": "people", "id": "2" }
      }
    },
    "links": {
      "self": "http://example.com/comments/5"
    }
  }, {
    "type": "comments",
    "id": "12",
    "attributes": {
      "body": "I like XML better"
    },
    "relationships": {
      "author": {
        "data": { "type": "people", "id": "9" }
      }
    },
    "links": {
      "self": "http://example.com/comments/12"
    }
  }]
}
EOF;
    }

    private function getInvalidJsonResponseBody(): string
    {
        return <<<EOF
{
  "links": {
    "self": "http://example.com/articles",
    "next": "http://example.com/articles?page[offset]=2",
    "last": "http://example.com/articles?page[offset]=10"
  },
}
EOF;
    }

    private function getInvalidJsonApiResponseBody(): string
    {
        return <<<EOF
{
    "links": {
        "self": "http://example.com/articles",
        "next": "http://example.com/articles?page[offset]=2",
        "last": "http://example.com/articles?page[offset]=10"
    },
    "data": [
        {
            "type": "articles",
            "id": "1",
            "key": "1",
            "attributes": {
                "title": "JSON API paints my bikeshed!"
            }
        }
    ]
}
EOF;
    }

    private function getRequest(): Request
    {
        return new Request(new ServerRequest(), new DefaultExceptionFactory());
    }

    private function createHandler(ResponseInterface $response): RequestHandlerInterface
    {
        return new class($response) implements RequestHandlerInterface {
            /**
             * @var ResponseInterface
             */
            private $response;

            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }
}
