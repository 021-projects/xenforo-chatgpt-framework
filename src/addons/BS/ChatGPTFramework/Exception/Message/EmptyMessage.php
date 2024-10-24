<?php

namespace BS\ChatGPTFramework\Exception\Message;

class EmptyMessage extends ResponseException
{
    public function __construct(bool|string $response = '')
    {
        parent::__construct(
            'API response does not contain message key.',
            $response
        );
    }
}
