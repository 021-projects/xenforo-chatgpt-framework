<?php

namespace BS\ChatGPTBots\DTO\GPTResponse;

use BS\ChatGPTBots\Enums\MessageRole;

/**
 * Class StreamChunkDTO
 *
 * Actually not supporting tool calls
 *
 * @package BS\ChatGPTBots\DTO\GPTResponse
 */
class StreamChunkDTO
{
    public function __construct(
        public readonly ?MessageRole $role = null,
        public readonly ?string $content = null,
        public readonly ?ToolCallsDTO $toolCalls = null,
    ) {}

    public function isEmpty(): bool
    {
        return null === $this->role
            && null === $this->content
            && ($this->toolCalls?->isEmpty() ?? true);
    }

    public function hasContent(): bool
    {
        return null !== $this->content && '' !== $this->content;
    }
}
