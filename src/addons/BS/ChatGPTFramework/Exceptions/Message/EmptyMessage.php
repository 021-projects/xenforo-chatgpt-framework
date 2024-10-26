<?php

namespace BS\ChatGPTFramework\Exceptions\Message;

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
