<?php

namespace BS\ChatGPTFramework\Exception\Message;

class EmptyResponseException extends ResponseException
{
    public function __construct(
        string|bool $response,
    ) {
        parent::__construct('API returned empty response', $response);
    }
}
