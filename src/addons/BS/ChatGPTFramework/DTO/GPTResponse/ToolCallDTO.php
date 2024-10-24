<?php

namespace BS\ChatGPTFramework\DTO\GPTResponse;

use BS\ChatGPTFramework\DTO\GPTResponse\Enums\ToolCallType;

class ToolCallDTO
{
    public function __construct(
        public readonly string $id,
        public readonly ToolCallType $type,
        public readonly ?FunctionDTO $func,
    ) {}
}
