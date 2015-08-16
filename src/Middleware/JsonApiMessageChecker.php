<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Schema\Error;
use WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument;
use WoohooLabs\YinMiddlewares\Utils\JsonApiChecker;

abstract class JsonApiMessageChecker
{
    /**
     * @var bool
     */
    private $lint;

    /**
     * @var bool
     */
    private $validate;

    /**
     * @var \WoohooLabs\YinMiddlewares\Utils\JsonApiChecker
     */
    private $checker;

    /**
     * @param bool $lint
     * @param bool $validate
     */
    public function __construct($lint = true, $validate = true)
    {
        $this->lint = $lint;
        $this->validate = $validate;
        $this->checker = new JsonApiChecker();
    }

    /**
     * @param \Psr\Http\Message\MessageInterface $message
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    protected function check(MessageInterface $message, ResponseInterface $response)
    {
        if ($this->lint === true) {
            $errorMessage = $this->checker->lint($message->getBody()->getContents());

            if ($errorMessage) {
                $error = $this->getLintError($errorMessage);
                $errorDocument = $this->getLintErrorDocument([$error])->getResponse($response);
                return $errorDocument;
            }
        }

        if ($this->validate === true) {
            $errorMessages = $this->checker->validate(json_decode($message->getBody()->getContents()));

            if (empty($errorMessages) === false) {
                $errors = [];
                foreach ($errorMessages as $property => $errorMessage) {
                    $errors[] = $this->getValidationError($property, $errorMessage);
                }
                $errorDocument = $this->getValidationErrorDocument($errors)->getResponse($response);
                return $errorDocument;
            }
        }

        return null;
    }

    /**
     * @param string $message
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getLintError($message)
    {
        $error = new Error();
        $error->setTitle($message);

        return $error;
    }

    /**
     * @param string $property
     * @param string $message
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getValidationError($property, $message)
    {
        $error = new Error();
        $error->setTitle("$property: $message");

        return $error;
    }

    /**
     * @param array $errors
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getLintErrorDocument(array $errors)
    {
        $errorDocument = $this->getErrorDocument($errors);

        return $errorDocument;
    }

    /**
     * @param array $errors
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getValidationErrorDocument(array $errors)
    {
        $errorDocument = $this->getErrorDocument($errors);

        return $errorDocument;
    }

    /**
     * @param array $errors
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getErrorDocument(array $errors)
    {
        $errorDocument = new ErrorDocument();

        foreach ($errors as $error) {
            $errorDocument->addError($error);
        }

        return $errorDocument;
    }
}
