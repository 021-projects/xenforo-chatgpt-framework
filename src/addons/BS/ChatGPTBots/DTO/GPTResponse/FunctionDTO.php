<?php

namespace BS\ChatGPTBots\DTO\GPTResponse;

class FunctionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly array $arguments,
    ) {}

    public function arg(string $name): mixed
    {
        return $this->arguments[$name] ?? null;
    }
}
