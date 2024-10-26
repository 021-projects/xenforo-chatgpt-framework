<?php

namespace BS\ChatGPTFramework\Exceptions\Message;

class ResponseException extends \Exception
{
    protected string|bool $response = '';

    public function __construct(
        $message = '',
        string|bool $response = ''
    ) {
        parent::__construct($message);
        $this->response = $response;
    }

    /**
     * @return string|bool
     */
    public function getResponse(): string|bool
    {
        return $this->response;
    }

    public function getJsonResponse(): ?array
    {
        if (is_string($this->response)) {
            return json_decode(
                $this->response,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        return null;
    }
}
