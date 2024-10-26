<?php

namespace BS\ChatGPTFramework\Exceptions\Message;

class WrongResponseTypeException extends ResponseException
{
    public function __construct(string|bool $response)
    {
        parent::__construct(
            'Curl was returned wrong response type, only string can be processed',
            $response
        );
    }
}
