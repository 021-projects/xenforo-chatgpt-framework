<?php

namespace BS\ChatGPTBots\Exception\Message;

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
}
