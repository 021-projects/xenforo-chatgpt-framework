<?php

namespace BS\ChatGPTFramework\Exceptions\Message;

use BS\ChatGPTFramework\DTO\GPTResponse\ErrorDTO;

class ResponseError extends ResponseException
{
    protected ErrorDTO $error;

    public function __construct(
        ErrorDTO $error,
        string|bool $response
    ) {
        $this->error = $error;

        parent::__construct(
            'Response has error: ' . $error->message,
            $response
        );
    }
}
