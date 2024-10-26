<?php

namespace BS\ChatGPTFramework\DTO\JsonSchema\Concerns;

use BS\ChatGPTFramework\Enums\JsonSchema\Type;

trait StringTypeField
{
    protected ?int $_minLength = null;
    protected ?int $_maxLength = null;

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
        if ($this->_minLength !== null) {
            $obj->minLength = $this->_minLength;
        }
        if ($this->_maxLength !== null) {
            $obj->maxLength = $this->_maxLength;
        }
        return $obj;
    }

    public function allowed(array|string ...$value): self
    {
        $this->assertType(Type::STRING);

        if (count($value) === 1 && is_array($value[0])) {
            $value = $value[0];
        }

        $this->enum = array_merge($this->enum, $value);

        return $this;
    }

    public function notAllowed(array|string $value): self
    {
        $this->assertType(Type::STRING);

        if (count($value) === 1 && is_array($value[0])) {
            $value = $value[0];
        }

        $this->enum = array_filter(
            $this->enum,
            static fn($v) => ! in_array($v, $value, true)
        );

        return $this;
    }

    public function minLength(int $value): self
    {
        $this->assertType(Type::STRING);
        $this->_minLength = $value;
        return $this;
    }

    public function maxLength(int $value): self
    {
        $this->assertType(Type::STRING);
        $this->_maxLength = $value;
        return $this;
    }
}
