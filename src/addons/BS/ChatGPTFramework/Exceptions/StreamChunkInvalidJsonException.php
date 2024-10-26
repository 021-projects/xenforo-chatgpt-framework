<?php

namespace BS\ChatGPTFramework\Exceptions;

class StreamChunkInvalidJsonException extends StreamChunkException
{
    public function __construct(string $chunk)
    {
        parent::__construct('Stream chunk has invalid JSON', $chunk);
    }
}
