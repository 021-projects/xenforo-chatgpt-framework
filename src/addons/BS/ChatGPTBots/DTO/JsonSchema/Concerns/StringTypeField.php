<?php

namespace BS\ChatGPTBots\DTO\JsonSchema\Concerns;

use BS\ChatGPTBots\Enums\JsonSchema\Type;

trait StringTypeField
{
    /**
     * Enum values for string type
     *
     * @var array
     */
    protected array $enum = [];

    public function stringObject(): \stdClass
    {
        $obj = $this->defaultObject();
        if (! empty($this->enum)) {
            $obj->enum = $this->enum;
        }
        return $obj;
    }

    public function allowed(string $value): self
    {
        $this->assertType(Type::STRING);

        $this->enum[] = $value;

        return $this;
    }

    public function notAllowed(string $value): self
    {
        $this->assertType(Type::STRING);

        $this->enum = array_filter($this->enum, static fn($v) => $v !== $value);

        return $this;
    }
}
