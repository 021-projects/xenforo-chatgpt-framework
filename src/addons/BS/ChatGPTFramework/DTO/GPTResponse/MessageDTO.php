<?php

namespace BS\ChatGPTFramework\DTO\GPTResponse;

use BS\ChatGPTFramework\Enums\MessageRole;

class MessageDTO
{
    public function __construct(
        public readonly MessageRole $role,
        public readonly ?string $content = null,
        public readonly ?ToolCallsDTO $toolCalls = null,
    ) {}
}
