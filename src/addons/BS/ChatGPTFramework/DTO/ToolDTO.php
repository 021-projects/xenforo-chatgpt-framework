<?php

namespace BS\ChatGPTFramework\DTO;

use BS\ChatGPTFramework\Enums\ToolType;

class ToolDTO
{
    public function __construct(
        public ?ToolType $type = ToolType::FUNCTION,
        public ?FunctionDTO $function = null,
    ) {}

    public function toObject(): \stdClass
    {
        $this->validateObject();

        $obj = new \stdClass();
        $obj->type = $this->type->value;

        if ($this->function) {
            $obj->function = $this->function->toObject();
        }

        return $obj;
    }

    protected function validateObject(): void
    {
        if ($this->type === ToolType::FUNCTION && !$this->function) {
            throw new \InvalidArgumentException('Function tool requires a function');
        }
    }
}
