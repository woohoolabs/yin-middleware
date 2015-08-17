<?php
namespace WoohooLabs\YinMiddlewares\Middleware;

use JsonSchema\RefResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use WoohooLabs\Yin\JsonApi\Schema\Error;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;
use WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument;

abstract class JsonApiMessageValidator
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
     * @var bool
     */
    private $includeOriginalMessage;

    /**
     * @param bool $lint
     * @param bool $validate
     * @param bool $includeOriginalMessage
     */
    public function __construct($lint = true, $validate = true, $includeOriginalMessage = true)
    {
        $this->lint = $lint;
        $this->validate = $validate;
        $this->includeOriginalMessage = true;
    }

    /**
     * @param \Psr\Http\Message\MessageInterface $message
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    protected function check(MessageInterface $message, ResponseInterface $response)
    {
        if ($this->lint === true) {
            $errorMessage = $this->lint($message->getBody()->getContents());

            if ($errorMessage) {
                $error = $this->getLintError($errorMessage);
                $errorDocument = $this->getLintErrorDocument($message, [$error])->getResponse($response);
                return $errorDocument;
            }
        }

        if ($this->validate === true) {
            $errorMessages = $this->validate(json_decode($message->getBody()->getContents()));

            if (empty($errorMessages) === false) {
                $errors = [];
                foreach ($errorMessages as $errorMessage) {
                    $errors[] = $this->getValidationError($errorMessage["property"], $errorMessage["message"]);
                }
                $errorDocument = $this->getValidationErrorDocument($message, $errors)->getResponse($response);
                return $errorDocument;
            }
        }

        return null;
    }

    /**
     * @param string $message
     * @return string
     */
    public function lint($message)
    {
        try {
            $linter = new JsonParser();
            $linter->lint($message);
        } catch (ParsingException $e) {
            return $e->getMessage();
        }

        return "";
    }

    /**
     * @param object $message
     * @return array
     */
    public function validate($message)
    {
        $jsonApiSchemaPath = realpath(__DIR__ . "/../../data/json-api-schema.json");

        $retriever = new UriRetriever();
        $schema = $retriever->retrieve('file://' . $jsonApiSchemaPath);

        RefResolver::$maxDepth = 100;
        $refResolver = new RefResolver($retriever);
        $refResolver->resolve($schema, 'file://' . dirname($jsonApiSchemaPath) . "/json-api-schema.json");

        $validator = new Validator();
        $validator->check($message, $schema);

        return $validator->getErrors();
    }

    /**
     * @param string $message
     * @return \WoohooLabs\Yin\JsonApi\Schema\Error
     */
    protected function getLintError($message)
    {
        $error = new Error();
        $error->setDetail($message);

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
        $error->setDetail($message);
        if ($property) {
            $error->setSource(ErrorSource::fromParameter($property));
        }

        return $error;
    }

    /**
     * @param \Psr\Http\Message\MessageInterface $message
     * @param array $errors
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getLintErrorDocument(MessageInterface $message, array $errors)
    {
        $errorDocument = $this->getErrorDocument($message, $errors);

        return $errorDocument;
    }

    /**
     * @param \Psr\Http\Message\MessageInterface $message
     * @param array $errors
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getValidationErrorDocument(MessageInterface $message, array $errors)
    {
        $errorDocument = $this->getErrorDocument($message, $errors);

        return $errorDocument;
    }

    /**
     * @param \Psr\Http\Message\MessageInterface $message
     * @param array $errors
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getErrorDocument(MessageInterface $message, array $errors)
    {
        $errorDocument = new ErrorDocument();
        if ($this->includeOriginalMessage) {
            $errorDocument->setMeta(["original", $message->getBody()->getContents()]);
        }

        foreach ($errors as $error) {
            $errorDocument->addError($error);
        }

        return $errorDocument;
    }
}
