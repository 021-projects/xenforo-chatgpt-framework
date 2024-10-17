<?php

namespace BS\ChatGPTBots\DTO\GPTResponse;

class ErrorDTO
{
    public function __construct(
        public readonly string $type,
        public readonly string $code,
        public readonly string $message,
        public readonly ?string $param,
    ) {}
}
