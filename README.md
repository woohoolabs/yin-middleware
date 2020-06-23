# Woohoo Labs. Yin Middleware

[![Latest Version on Packagist][ico-version]][link-version]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-build]][link-build]
[![Coverage Status][ico-coverage]][link-coverage]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]
[![Gitter][ico-support]][link-support]

**Woohoo Labs. Yin Middleware is a collection of middleware which helps you to integrate
[Woohoo Labs. Yin](https://github.com/woohoolabs/yin) into your PHP applications.**

## Table of Contents

* [Introduction](#introduction)
* [Install](#install)
* [Basic Usage](#basic-usage)
* [Versioning](#versioning)
* [Change Log](#change-log)
* [Testing](#testing)
* [Contributing](#contributing)
* [Support](#support)
* [Credits](#credits)
* [License](#license)

## Introduction

### Features

- 100% [PSR-15](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-15-request-handlers.md) compatibility
- 100% [PSR-7](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md) compatibility
- Validation of requests against the JSON schema
- Validation of responses against the JSON and JSON:API schema
- Dispatching of JSON:API-aware controllers
- JSON:API exception handling

## Install

The only thing you need before getting started is [Composer](https://getcomposer.org).

### Install a PSR-7 implementation:

Because Yin Middleware requires a PSR-7 implementation (a package which provides the `psr/http-message-implementation` virtual
package), you must install one first. You may use [Zend Diactoros](https://github.com/zendframework/zend-diactoros) or
any other library of your preference:

```bash
$ composer require zendframework/zend-diactoros
```

### Install Yin Middleware:

To install the latest version of this library, run the command below:

```bash
$ composer require woohoolabs/yin-middleware
```

> Note: The tests and examples won't be downloaded by default. You have to use `composer require woohoolabs/yin-middleware --prefer-source`
or clone the repository if you need them.

Yin Middleware 4.1 requires PHP 7.4 at least, but you may use Yin Middleware 4.0.0 for PHP 7.1.

### Install the optional dependencies:

If you want to use `JsonApiRequestValidatorMiddleware` and `JsonApiResponseValidatorMiddleware` from the default middleware stack
then you have to require the following dependencies too:

```bash
$ composer require seld/jsonlint
$ composer require justinrainbow/json-schema
```

## Basic Usage

### Supported middleware interface design

The interface design of Yin-Middleware is based on the [PSR-15](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-15-request-handlers.md) de-facto standard.
That's why it is compatible with [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony),
[Zend-Stratigility](https://github.com/zendframework/zend-stratigility/), [Zend-Expressive](https://github.com/zendframework/zend-expressive/)
and many other frameworks.

The following sections will guide you through how to use and configure the provided middleware.

> Note: When passing a `ServerRequestInterface` instance to your middleware dispatcher, a
`WoohooLabs\Yin\JsonApi\Request\JsonApiRequestInterface` instance must be used in fact (the `WoohooLabs\Yin\JsonApi\Request\JsonApiRequest`
class possibly), otherwise the `JsonApiDispatcherMiddleware` and the `JsonApiExceptionHandlerMiddleware` will throw an
exception.

### YinCompatibilityMiddleware

This middleware facilitates the usage of Yin and Yin-Middleware in other frameworks. It does so by upgrading a basic PSR-7
request object to `JsonApiRequest`, which is suitable for working with Yin. Please keep in mind, that this middleware should
precede any other middleware that uses `JsonApiRequest` as `$request` parameter.

```php
$harmony->addMiddleware(new YinCompatibilityMiddleware());
```

Available configuration options for the middleware (they can be passed to the constructor):

- `exceptionFactory`: The [ExceptionFactoryInterface](https://github.com/woohoolabs/yin/#exceptions) instance to be used
- `deserializer`: The [DeserializerInterface](https://github.com/woohoolabs/yin/#custom-deserialization) instance to be used

### JsonApiRequestValidatorMiddleware

The middleware is mainly useful in a development environment, and it is able to validate a
PSR-7 request against the JSON and the JSON:API schema. Just add it to your
application (the example is for [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony)):

```php
$harmony->addMiddleware(new JsonApiRequestValidatorMiddleware());
```

If validation fails, an exception containing the appropriate JSON:API errors will be thrown. If you want to customize
the error messages or the response, provide an Exception Factory of your own. For other customizations, feel free to extend the class.

Available configuration options for the middleware (they can be passed to the constructor):

- `exceptionFactory`: The [ExceptionFactoryInterface](https://github.com/woohoolabs/yin/#exceptions) instance to be used
- `includeOriginalMessageInResponse`: If true, the original request body will be included in the "meta" top-level member
- `negotiate`: If true, the middleware performs content-negotiation as specified by the JSON:API spec. In this case,
the "Content-Type" and the "Accept" header is checked.
- `validateQueryParams`: If true, query parameters are validated against the JSON:API specification
- `validateJsonBody`: If true, the request body gets validated against the JSON schema

### JsonApiResponseValidatorMiddleware

The middleware is mainly useful in a development environment, and it is able to validate a PSR-7 response against the
JSON and the JSON:API schema. Just add it to your application (the example is for [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony)):

```php
$harmony->addMiddleware(new JsonApiResponseValidatorMiddleware());
```

If validation fails, an exception containing the appropriate JSON:API errors will be thrown. If you want to customize
the error messages or the response, provide an Exception Factory of your own. For other customizations, feel free to extend the class.

Available configuration options for the middleware (they can be passed to the constructor):

- `exceptionFactory`: The [ExceptionFactoryInterface](https://github.com/woohoolabs/yin/#exceptions) instance to be used
- `serializer`: The [SerializerInterface](https://github.com/woohoolabs/yin/#custom-serialization) instance to be used
- `includeOriginalMessageInResponse`: If true, the original response will be included in the "meta" top-level member
- `validateJsonBody`: If true, the response body gets validated against the JSON schema
- `validateJsonApiBody`: If true, the response is validated against the JSON:API schema

### JsonApiDispatcherMiddleware

This middleware is able to dispatch JSON:API-aware controllers. Just add it to your application (the example is for
[Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony)):

```php
$harmony->addMiddleware(new JsonApiDispatcherMiddleware());
```

This middleware works exactly as [the one in Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony#using-your-favourite-di-container-with-harmony),
the only difference is that it dispatches controller actions with the following signature:

```php
public function myAction(JsonApi $jsonApi): ResponseInterface;
```

instead of:

```php
public function myAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
```

The difference is subtle, as the `$jsonApi` object contains a PSR-7 compatible request, and PSR-7 responses can also be
created with it. Learn more from the documentation of [Woohoo Labs. Yin](https://github.com/woohoolabs/yin#jsonapi-class).

Available configuration options for the middleware (they can be passed to the constructor):

- `container`: A [PSR-11 compliant](https://www.php-fig.org/psr/psr-11/) container instance to be used to instantiate
the controller
- `exceptionFactory`: The [ExceptionFactoryInterface](https://github.com/woohoolabs/yin/#exceptions) instance to be
used (e.g.: when dispatching fails)
- `serializer`: The [SerializerInterface](https://github.com/woohoolabs/yin/#custom-serialization) instance to be used
- `handlerAttribute`: The name of the request attribute which stores a dispatchable controller (it is usually
provided by a router).

### JsonApiExceptionHandlerMiddleware

It catches exceptions and responds with an appropriate JSON:API error response.

Available configuration options for the middleware (they can be passed to the constructor):

- `errorResponsePrototype`: In case of an error, this response object will be manipulated and returned
- `catching`: If false, the middleware won't catch `JsonApiException`s
- `verbose`: If true, additional meta information will be provided about the exception thrown
- `exceptionFactory`: The [ExceptionFactoryInterface](https://github.com/woohoolabs/yin/#exceptions) instance to be used
- `serializer`: The [SerializerInterface](https://github.com/woohoolabs/yin/#custom-serialization) instance to be used

## Versioning

This library follows [SemVer v2.0.0](https://semver.org/).

## Change Log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

Woohoo Labs. Yin Middleware has a PHPUnit test suite. To run the tests, run the following command from the project folder
after you have copied phpunit.xml.dist to phpunit.xml:

``` bash
$ phpunit
```

Additionally, you may run `docker-compose up` or `make test` in order to execute the tests.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Support

Please see [SUPPORT](SUPPORT.md) for details.

## Credits

- [Máté Kocsis][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see the [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/woohoolabs/yin-middleware.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-build]: https://img.shields.io/github/workflow/status/woohoolabs/yin-middleware/Continuous%20Integration
[ico-coverage]: https://img.shields.io/codecov/c/github/woohoolabs/yin-middleware
[ico-code-quality]: https://img.shields.io/scrutinizer/g/woohoolabs/yin-middleware.svg
[ico-downloads]: https://img.shields.io/packagist/dt/woohoolabs/yin-middleware.svg
[ico-support]: https://badges.gitter.im/woohoolabs/yin-middleware.svg

[link-version]: https://packagist.org/packages/woohoolabs/yin-middleware
[link-build]: https://github.com/woohoolabs/yin-middleware/actions
[link-coverage]: https://codecov.io/gh/woohoolabs/yin-middleware
[link-code-quality]: https://scrutinizer-ci.com/g/woohoolabs/yin-middleware
[link-downloads]: https://packagist.org/packages/woohoolabs/yin-middleware
[link-author]: https://github.com/kocsismate
[link-contributors]: ../../contributors
[link-support]: https://gitter.im/woohoolabs/yin-middleware?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge
