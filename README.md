# Woohoo Labs. Yin Middlewares

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

**Woohoo Labs. Yin Middlewares is a collection of middlewares which helps you to integrate
[Woohoo Labs. Yin](https://github.com/woohoolabs/yin) into your PHP applications.**

## Table of Contents

* [Introduction](#introduction)
* [Install](#install)
* [Basic Usage](#basic-usage)
* [Versioning](#versioning)
* [Change Log](#change-log)
* [Contributing](#contributing)
* [Credits](#credits)
* [License](#license)

## Introduction

Yin. middlewares are compatible with frameworks like [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony),
[Zend-Stratigility](https://github.com/zendframework/zend-stratigility/), [Zend-Expressive](https://github.com/zendframework/zend-expressive/) or
[Slim Framework 3](http://www.slimframework.com/docs/concepts/middleware.html). Read more in the
[Supported middleware interface design section](https://github.com/woohoolabs/yin-middlewares#supported-middleware-interface-design).

#### Features

- 100% [PSR-7](http://www.php-fig.org/psr/psr-7/) compatibility
- Validation of requests against the JSON schema
- Validation of responses against the JSON and JSON API schema
- Dispatching of JSON API-aware controllers
- JSON API exception handling

## Install

You need [Composer](https://getcomposer.org) to install this library. Run the command below and you will get the latest
version:

```bash
$ composer require woohoolabs/yin-middlewares
```

## Basic Usage

#### Supported middleware interface design

The interface design of our middlewares is based on the "request, response, next" style advocated
by such prominent developers as [Matthew Weier O'Phinney](https://mwop.net/) (you can read more on the
topic [in his blog post](https://mwop.net/blog/2015-01-08-on-http-middleware-and-psr-7.html)). That's why
our middlewares are compatible with [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony),
[Zend-Stratigility](https://github.com/zendframework/zend-stratigility/), [Zend-Expressive](https://github.com/zendframework/zend-expressive/) or
[Slim Framework 3](http://www.slimframework.com/docs/concepts/middleware.html).

The following sections will guide you through how to use and configure the provided middlewares.

#### JsonApiRequestValidatorMiddleware

The middleware is mainly useful in a development environment, and it is able to validate a
PSR-7 request against the JSON and the JSON API schema. Just add it to your
application (the example is for [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony)):

```php
$harmony->addMiddleware("request_validator", new JsonApiRequestValidatorMiddleware());
```

If validation fails, the appropriate JSON API errors will be sent. If you want to customize
the error messages or the responses, provide an Exception Factory of your own.
For other customizations, feel free to extend the class.

Available configuration options for the middleware (they can be set in the constructor):

- `exceptionFactory`: The [Exception Factory](https://github.com/woohoolabs/yin/#exceptions) instance to be used
- `includeOriginalMessageInResponse`: If true, the original request will be included in the "meta"
top-level member
- `negotiate`: If true, the middleware performs content-negotiation as specified by the JSON API
spec. In this case, the "Content-Type" and the "Accept" header is checked.
- `checkQueryParams`: If true, query parameters are validated against the JSON API specification
- `lintBody`: If true, the request body gets linted

#### JsonApiResponseValidatorMiddleware

The middleware is mainly useful in a development environment, and it is able to validate a
PSR-7 response against the JSON and the JSON API schema. Just add it to your
application (the example is for [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony)):

```php
$harmony->addMiddleware("response_validator", new JsonApiResponseValidatorMiddleware());
```

If validation fails, the appropriate JSON API errors will be sent. If you want to customize
the messages or the responses, provide an Exception Factory of your own. For other customizations,
feel free to extend the class.

Available configuration options for the middleware (they can be set in the constructor):

- `exceptionFactory`: The [Exception Factory](https://github.com/woohoolabs/yin/#exceptions) instance to be used
- `includeOriginalMessageInResponse`: If true, the original response will be included in the "meta" top-level member
- `lintBody`: If true, the response body gets linted
- `validateBody`: If true, the response is validated against the JSON API schema

#### JsonApiDispatcherMiddleware

The middleware is able to dispatch JSON API-aware controllers. Just add it to your
application (the example is for [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony)):

```php
$harmony->addMiddleware("json_api_dispatcher", new JsonApiDispatcherMiddleware());
```

The middleware works exactly as [the one in Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony#using-your-favourite-di-container-with-harmony),
the only difference is that it dispatches controllers with the following signature:

```php
/**
 * @param JsonApi $jsonApi
 * @param ResponseInterface $response
 */
public function myController(JsonApi $jsonApi);
```

instead of:

```php
/**
 * @param ServerRequestInterface $request
 * @param ResponseInterface $response
 */
public function myController(ServerRequestInterface $request, ResponseInterface $response);
```

The difference is subtle, as the `WoohooLabs\Yin\JsonApi\JsonApi` object contains a PSR-7 compatible request,
and PSR-7 responses can also be created with it. Learn more from the documentation of
[Woohoo Labs. Yin](https://github.com/woohoolabs/yin#jsonapi-class).

Available configuration options for the middleware (they can be set in the constructor):

- `exceptionFactory`: The [Exception Factory](https://github.com/woohoolabs/yin/#exceptions) instance to be
used (e.g.: when dispatching fails)
- `container`: A [Container Interop-compliant](https://github.com/container-interop/container-interop) container
instance to be used to instantiate the controller
- `handlerAttribute`: The name of the request attribute which stores a dispatchable controller (it is usually
provided by a router).

#### JsonApiErrorHandlerMiddleware

It catches `JsonApiException`-s and responds with the JSON API error response associated with the exception.
Available configuration options for the middleware (they can be set in the constructor):

- `catching`: If false, the middleware won't catch `JsonApiException`-s
- `verbose`: If true, additional meta information will be provided about the exception thrown

If you want to catch `\Exception`-s too, you have to extend the class and wrap it like that:

```php
class MyErrorHandlerMiddleware extends JsonApiErrorHandlerMiddleware
{
    /**
     * @var \WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface
     */
    protected $exceptionFactory;

    /**
     * @param \WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface $exceptionFactory
     */
    public function __construct(ExceptionFactoryInterface $exceptionFactory)
    {
        $this->exceptionFactory = $exceptionFactory;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return void|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        try {
            $result = parent::__invoke($request, $response, $next);
            if ($result) {
                return $result;
            }
        } catch (\Exception $e) {
            return $this->exceptionFactory->createApplicationErrorException($request)->getErrorDocument()->getResponse($response);
        }
    }
}
```

## Versioning

This library follows [SemVer v2.0.0](http://semver.org/).

## Change Log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Máté Kocsis][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see the [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/woohoolabs/yin-middlewares.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-travis]: https://img.shields.io/travis/woohoolabs/yin-middlewares/master.svg
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/woohoolabs/yin-middlewares.svg
[ico-code-quality]: https://img.shields.io/scrutinizer/g/woohoolabs/yin-middlewares.svg
[ico-downloads]: https://img.shields.io/packagist/dt/woohoolabs/yin-middlewares.svg

[link-packagist]: https://packagist.org/packages/woohoolabs/yin-middlewares
[link-travis]: https://travis-ci.org/woohoolabs/yin-middlewares
[link-scrutinizer]: https://scrutinizer-ci.com/g/woohoolabs/yin-middlewares/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/woohoolabs/yin-middlewares
[link-downloads]: https://packagist.org/packages/woohoolabs/yin-middlewares
[link-author]: https://github.com/kocsismate
[link-contributors]: ../../contributors
