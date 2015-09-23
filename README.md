# Woohoo Labs. Yin Middlewares

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

**Woohoo Labs. Yin Middlewares is a collection of middlewares which helps you to integrate
[Woohoo Labs. Yin](https://github.com/woohoolabs/yin) into your PHP applications.**

## Introduction

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

The interface design of our middlewares is based on the style that is advocated by
Matthew Weier O'Phinney. That's why they are compatible with middlewares built
for [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony),
[Zend-Stratigility](https://github.com/zendframework/zend-stratigility) and
[Slim Framework 3](https://github.com/slimphp/Slim).

#### `JsonApiRequestValidatorMiddleware`

The middleware is mainly useful in a development environment, and it is able to validate a
PSR-7 request against the JSON and the JSON API schema. Just add it to your
application (the example is for [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony)):

```php
$harmony->addMiddleware("request_validator", new JsonApiRequestValidatorMiddleware());
```

If validation fails, the appropriate JSON API errors will be sent. If you want to customize
the messages or the responses or anything else, feel free to extend the class and override its methods.

Available options for the middleware (they can be set in the constructor):

- `includeOriginalMessageInResponse`: If true, the original request will be included in the "meta"
top-level member.
- `checkMediaType`: If true, the middleware performs content-negotiation as specified by the JSON API
spec. In this case, the "Content-Type" and the "Accept" header is checked.
- `CheckQueryParams`: If true, query parameters are validated against the JSON API specification.
- `lintBody`: If true, the request gets linted.

#### `JsonApiResponseValidatorMiddleware`

The middleware is mainly useful in a development environment, and it is able to validate a
PSR-7 response against the JSON and the JSON API schema. Just add it to your
application (the example is for [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony)):

```php
$harmony->addMiddleware("response_validator", new JsonApiResponseValidatorMiddleware());
```

If validation fails, the appropriate JSON API errors will be sent. If you want to customize
the messages or the responses or anything else, feel free to extend the class and override its methods.

Available options for the middleware (they can be set in the constructor):

- `includeOriginalMessageInResponse`: If true, the original response which would have been sent,
will be included in the "meta" top-level member.
- `lintBody`: If true, the response gets linted.
- `validateBody`: If true, the response is validated against the JSON API schema.

#### `JsonApiDispatcherMiddleware`

The middleware is able to dispatch JSON API-aware controllers. Just add it to your
application (the example is for [Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony)):

```php
$harmony->addMiddleware("json_api_dispatcher", new JsonApiDispatcherMiddleware());
```

The middleware works exactly as [the one in Woohoo Labs. Harmony](https://github.com/woohoolabs/harmony#using-your-favourite-di-container-with-harmony),
the only difference is that controllers' signature will be:

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

The difference is subtle, as the `JsonApi` object contains a PSR-7 compatible request,
and PSR-7 responses can also be created with it. Learn more from the documentation of
[Woohoo Labs. Yin](https://github.com/woohoolabs/yin#jsonapi-class)

#### `JsonApiCatchingDispatcherMiddleware`

It is almost the same as the previous middleware, it only adds some exception handling functionality
to the `JsonApiCatchingDispatcherMiddleware`. When a JSON API exception is thrown, it catches it
and converts it to a proper JSON API error response. If you want to customize the messages or
the responses or anything else, feel free to extend the class and override its methods.

## Versioning

This library follows [SemVer v2.0.0](http://semver.org/).

## Change log

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
