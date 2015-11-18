## 0.6.0 - unreleased

ADDED:

CHANGED:

REMOVED:

FIXED:

## 0.5.0 - 2015-11-18

ADDED:

- Configuration option for `JsonApiDispatcherMiddleware` to define the request attribute name storing the route handler
- Configuration option for `JsonApiErrorHandler` whether to catch or not `JsonApiException`-s

CHANGED:

- Woohoo Labs. Yin 0.8.0 is the minimum requirement
- `JsonApiDispatcherMiddleware` dispatches the route handler from the container when it is not a `callable` 

FIXED:

- PHP version constraint in composer.json

## 0.4.0 - 2015-10-05

CHANGED
- `JsonApiCatchingDispatcherMiddleware` is now `JsonApiErrorHandlerMiddleware`
- Woohoo Labs. Yin 0.7.0 is the minimum requirement

## 0.3.0 - 2015-09-23

CHANGED:

- Woohoo Labs. Yin 0.6.0 is the minimum requirement
- `JsonApiCatchingDispatcherMiddleware` handles more exceptions
- Updated JSON API schema

FIXED:

- `JsonApiDispatcherMiddleware` returns an error when the current route can't be found
- Request body now doesn't get validated

## 0.2.0 - 2015-08-27

ADDED:

- `JsonApiCatchingDispatcherMiddleware` makes error handling easier

CHANGED:

- Woohoo Labs. Yin 0.4.2 is the minimum requirement

FIXED:

- `JsonApiDispatcherMiddleware` returns an error when the current route can't be found

## 0.1.1 - 2015-08-19

FIXED:

- Fixed option for displaying the original error
- Original response can be sent along with the validation error response
- Body gets validated properly

## 0.1.0 - 2015-08-17

- Initial release
