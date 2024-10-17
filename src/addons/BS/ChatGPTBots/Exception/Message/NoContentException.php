<?php

namespace BS\ChatGPTBots\Exception\Message;

class NoContentException extends ResponseException
{
    public function __construct(
        string|bool $response,
    ) {
        parent::__construct('Message content is empty', $response);
    }
}
