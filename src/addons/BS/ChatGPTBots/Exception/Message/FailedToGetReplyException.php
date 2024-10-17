<?php

namespace BS\ChatGPTBots\Exception\Message;

class FailedToGetReplyException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Failed to get a reply from ChatGPT');
    }
}
