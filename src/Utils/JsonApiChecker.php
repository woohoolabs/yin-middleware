<?php
namespace WoohooLabs\YinMiddlewares\Utils;

use JsonSchema\RefResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

class JsonApiChecker
{
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
        $jsonApiSchemaPath = realpath("../../data/json-api-schema.json");

        $retriever = new UriRetriever();
        $schema = $retriever->retrieve('file://' . $jsonApiSchemaPath);

        $refResolver = new RefResolver($retriever);
        $refResolver->resolve($schema, 'file://' . dirname($jsonApiSchemaPath));

        $validator = new Validator();
        $validator->check($message, $schema);

        $errors = [];
        if ($validator->isValid() === false) {
            foreach ($validator->getErrors() as $error) {
                $errors[$error["property"]] = $error["message"];
            }
        }

        return $errors;
    }
}
