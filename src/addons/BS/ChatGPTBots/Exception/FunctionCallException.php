<?php

namespace BS\ChatGPTBots\Exception;

class FunctionCallException extends \Exception
{
    protected string $response = '';

    public function __construct(
        $message = "",
        ?string $response = ''
    ) {
        parent::__construct($message);
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    public function getResponseJson(): ?array
    {
        return @json_decode($this->response, true);
    }
}
