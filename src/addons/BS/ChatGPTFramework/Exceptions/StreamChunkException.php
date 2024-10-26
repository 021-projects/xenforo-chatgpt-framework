<?php

namespace BS\ChatGPTFramework\Exceptions;

class StreamChunkException extends \Exception
{
    public function __construct(
        string $message = 'Stream chunk exception',
        protected string $_chunk = ''
    ) {
        parent::__construct($message);
    }

    public function chunk(): string
    {
        return $this->_chunk;
    }
}
