<?php

namespace BS\ChatGPTFramework\Exception;

class StreamChunkInvalidJsonException extends StreamChunkException
{
    public function __construct(string $chunk)
    {
        parent::__construct('Stream chunk has invalid JSON', $chunk);
    }
}
