# Woohoo Labs. Yin Middleware

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]
[![Gitter][ico-gitter]][link-gitter]

**Woohoo Labs. Yin Middleware is a collection of middleware which helps you to integrate
[Woohoo Labs. Yin](https://github.com/woohoolabs/yin) into your PHP applications.**

## Table of Contents

* [Introduction](#introduction)
* [Install](#install)
* [Basic Usage](#basic-usage)
* [Versioning](#versioning)
* [Change Log](#change-log)
* [Contributing](#contributing)
* [Support](#support)
* [Credits](#credits)
* [License](#license)

## Introduction

Yin Middleware are compatible with frameworks like [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony),
[Zend-Stratigility](https://github.com/zendframework/zend-stratigility/), [Zend-Expressive](https://github.com/zendframework/zend-expressive/) or
[Slim Framework 3](http://www.slimframework.com/docs/concepts/middleware.html). Read more in the
[Supported middleware interface design section](#supported-middleware-interface-design).

### Features

- 100% [PSR-7](http://www.php-fig.org/psr/psr-7/) compatibility
- Validation of requests against the JSON schema
- Validation of responses against the JSON and JSON:API schema
- Dispatching of JSON:API-aware controllers
- JSON:API exception handling

## Install

The only thing you need before getting started is [Composer](http://getcomposer.org).

### Install a PSR-7 implementation:

Because Yin Middleware requires a PSR-7 implementation (a package which provides the `psr/http-message-implementation` virtual
package), you must install one first. You may use [Zend Diactoros](https://github.com/zendframework/zend-diactoros) or
any other library of your preference:

```bash
$ composer require zendframework/zend-diactoros:^1.3.0
```

### Install Yin Middleware:

To install the latest version of this library, run the command below:

```bash
$ composer require woohoolabs/yin-middleware
```

Yin Middleware requires PHP 7.1 at least. You may use Yin Middleware 2.0 for PHP 7.0 and 1.0 for PHP 5.6.

## Basic Usage

### Supported middleware interface design

The interface design of our middleware is based on the "request, response, next" style advocated
by such prominent developers as [Matthew Weier O'Phinney](https://mwop.net/) (you can read more on the
topic [in his blog post](https://mwop.net/blog/2015-01-08-on-http-middleware-and-psr-7.html)). That's why
our middleware are compatible with [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony),
[Zend-Stratigility](https://github.com/zendframework/zend-stratigility/), [Zend-Expressive](https://github.com/zendframework/zend-expressive/) or
[Slim Framework 3](http://www.slimframework.com/docs/concepts/middleware.html).

The following sections will guide you through how to use and configure the provided middleware.

### JsonApiRequestValidatorMiddleware

The middleware is mainly useful in a development environment, and it is able to validate a
PSR-7 request against the JSON and the JSON:API schema. Just add it to your
application (the example is for [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony)):

```php
$harmony->addMiddleware(new JsonApiRequestValidatorMiddleware());
```

If validation fails, the appropriate JSON:API errors will be sent. If you want to customize
the error messages or the responses, provide an Exception Factory of your own.
For other customizations, feel free to extend the class.

Available configuration options for the middleware (they can be set in the constructor):

- `exceptionFactory`: The [Exception Factory](https://github.com/woohoolabs/yin/#exceptions) instance to be used
- `includeOriginalMessageInResponse`: If true, the original request will be included in the "meta"
top-level member
- `negotiate`: If true, the middleware performs content-negotiation as specified by the JSON:API
spec. In this case, the "Content-Type" and the "Accept" header is checked.
- `checkQueryParams`: If true, query parameters are validated against the JSON:API specification
- `lintBody`: If true, the request body gets linted

### JsonApiResponseValidatorMiddleware

The middleware is mainly useful in a development environment, and it is able to validate a
PSR-7 response against the JSON and the JSON:API schema. Just add it to your
application (the example is for [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony)):

```php
$harmony->addMiddleware(new JsonApiResponseValidatorMiddleware());
```

If validation fails, the appropriate JSON API errors will be sent. If you want to customize
the messages or the responses, provide an Exception Factory of your own. For other customizations,
feel free to extend the class.

Available configuration options for the middleware (they can be set in the constructor):

- `exceptionFactory`: The [Exception Factory](https://github.com/woohoolabs/yin/#exceptions) instance to be used
- `serializer`: The [Serializer](https://github.com/woohoolabs/yin/#custom-serialization) instance to be used
- `includeOriginalMessageInResponse`: If true, the original response will be included in the "meta" top-level member
- `lintBody`: If true, the response body gets linted
- `validateBody`: If true, the response is validated against the JSON:API schema

### JsonApiDispatcherMiddleware

The middleware is able to dispatch JSON:API-aware controllers. Just add it to your
application (the example is for [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony)):

```php
$harmony->addMiddleware(new JsonApiDispatcherMiddleware());
```

The middleware works exactly as [the one in Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony#using-your-favourite-di-container-with-harmony),
the only difference is that it dispatches controllers with the following signature:

```php
public function myController(JsonApi $jsonApi): ResponseInterface;
```

instead of:

```php
public function myController(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
```

The difference is subtle, as the `$jsonApi` object contains a PSR-7 compatible request,
and PSR-7 responses can also be created with it. Learn more from the documentation of
[Woohoo Labs. Yin](https://github.com/woohoolabs/yin#jsonapi-class).

Available configuration options for the middleware (they can be set in the constructor):

- `container`: A [PSR-11 compliant](http://www.php-fig.org/psr/psr-11/) container instance to be used to instantiate
the controller
- `exceptionFactory`: The [Exception Factory](https://github.com/woohoolabs/yin/#exceptions) instance to be
used (e.g.: when dispatching fails)
- `serializer`: The [Serializer](https://github.com/woohoolabs/yin/#custom-serialization) instance to be used
- `handlerAttribute`: The name of the request attribute which stores a dispatchable controller (it is usually
provided by a router).

### JsonApiErrorHandlerMiddleware

It catches `JsonApiException`s and responds with the JSON:API error response associated with the exception.
Available configuration options for the middleware (they can be set in the constructor):

- `catching`: If false, the middleware won't catch `JsonApiException`s
- `verbose`: If true, additional meta information will be provided about the exception thrown
- `exceptionFactory`: The [Exception Factory](https://github.com/woohoolabs/yin/#exceptions) instance to be
used
- `serializer`: The [Serializer](https://github.com/woohoolabs/yin/#custom-serialization) instance to be used

If you want to catch `\Throwable`s too, you have to extend the class and wrap it like that:

```php
class MyErrorHandlerMiddleware extends JsonApiErrorHandlerMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        try {
            return parent::__invoke($request, $response, $next);
        } catch (Throwable $e) {
            $responder = new Responder($request, $response, $this->exceptionFactory, $this->serializer);
            
            return $responder->genericError($this->exceptionFactory->createApplicationErrorException($request), [], null, $additionalMeta);
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

## Support

Please see [SUPPORT](SUPPORT.md) for details.

## Credits

- [Máté Kocsis][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see the [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/woohoolabs/yin-middleware.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-travis]: https://img.shields.io/travis/woohoolabs/yin-middleware/master.svg
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/woohoolabs/yin-middleware.svg
[ico-code-quality]: https://img.shields.io/scrutinizer/g/woohoolabs/yin-middleware.svg
[ico-downloads]: https://img.shields.io/packagist/dt/woohoolabs/yin-middleware.svg
[ico-gitter]: https://badges.gitter.im/woohoolabs/yin-middleware.svg

[link-packagist]: https://packagist.org/packages/woohoolabs/yin-middleware
[link-travis]: https://travis-ci.org/woohoolabs/yin-middleware
[link-scrutinizer]: https://scrutinizer-ci.com/g/woohoolabs/yin-middleware/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/woohoolabs/yin-middleware
[link-downloads]: https://packagist.org/packages/woohoolabs/yin-middleware
[link-author]: https://github.com/kocsismate
[link-contributors]: ../../contributors
[link-gitter]: https://gitter.im/woohoolabs/yin-middleware?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge
