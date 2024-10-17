<?php

namespace BS\ChatGPTBots\DTO\GPTResponse;

use BS\ChatGPTBots\Enums\MessageRole;

class MessageDTO
{
    public function __construct(
        public readonly MessageRole $role,
        public readonly ?string $content = null,
        public readonly ?ToolCallsDTO $toolCalls = null,
    ) {}
}
