<?php

namespace BS\ChatGPTBots\DTO\GPTResponse;

use BS\ChatGPTBots\DTO\GPTResponse\Enums\ToolCallType;

class ToolCallDTO
{
    public function __construct(
        public readonly string $id,
        public readonly ToolCallType $type,
        public readonly ?FunctionDTO $func,
    ) {}
}
