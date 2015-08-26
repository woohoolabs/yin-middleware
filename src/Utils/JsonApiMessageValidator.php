<?php
namespace WoohooLabs\YinMiddlewares\Utils;

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
    private $includeOriginalMessage;

    /**
     * @var bool
     */
    private $lint;

    /**
     * @var bool
     */
    private $validate;

    /**
     * @param bool $includeOriginalMessageInResponse
     * @param bool $lintBody
     * @param bool $validateBody
     */
    public function __construct($includeOriginalMessageInResponse = true, $lintBody = true, $validateBody = true)
    {
        $this->includeOriginalMessage = $includeOriginalMessageInResponse;
        $this->lint = $lintBody;
        $this->validate = $validateBody;
    }

    /**
     * @param \Psr\Http\Message\MessageInterface $message
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    protected function check(MessageInterface $message, ResponseInterface $response)
    {
        if ($message->getBody()->getSize() < 1) {
            return null;
        }

        if ($this->lint === true) {
            $errorMessage = $this->lint($message->getBody());

            if ($errorMessage) {
                $error = $this->getLintError($errorMessage);
                return $this->getLintErrorDocument([$error])->getResponse($response);
            }
        }

        if ($this->validate === true) {
            $errorMessages = $this->validate(json_decode($message->getBody()));

            if (empty($errorMessages) === false) {
                $errors = [];
                foreach ($errorMessages as $errorMessage) {
                    $errors[] = $this->getValidationError($errorMessage["property"], $errorMessage["message"]);
                }
                return $this->getValidationErrorDocument($message, $errors)->getResponse($response);
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
        $error->setDetail(ucfirst($message));
        if ($property) {
            $error->setSource(ErrorSource::fromParameter($property));
        }

        return $error;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error[] $errors
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getLintErrorDocument(array $errors)
    {
        $errorDocument = $this->getErrorDocument($errors);

        return $errorDocument;
    }

    /**
     * @param \Psr\Http\Message\MessageInterface $message
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error[] $errors
     * @return \WoohooLabs\Yin\JsonApi\Transformer\ErrorDocument
     */
    protected function getValidationErrorDocument(MessageInterface $message, array $errors)
    {
        $errorDocument = $this->getErrorDocument($errors);
        if ($this->includeOriginalMessage) {
            $errorDocument->setMeta(["original" => json_decode($message->getBody(), true)]);
        }

        return $errorDocument;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Schema\Error[] $errors
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
