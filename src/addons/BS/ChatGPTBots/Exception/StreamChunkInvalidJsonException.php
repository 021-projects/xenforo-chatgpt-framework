<?php

namespace BS\ChatGPTBots\Exception;

class StreamChunkInvalidJsonException extends StreamChunkException
{
    public function __construct(string $chunk)
    {
        parent::__construct('Stream chunk has invalid JSON', $chunk);
    }
}
