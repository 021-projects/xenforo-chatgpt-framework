<?php

namespace BS\ChatGPTBots\DTO\JsonSchema\Concerns;

use BS\ChatGPTBots\DTO\Field;
use BS\ChatGPTBots\Enums\JsonSchema\Type;

trait ObjectTypeField
{
    protected array $_required = [];

    /**
     * Properties for object type
     *
     * @var array
     */
    protected array $properties = [];

    protected function objectObject(): \stdClass
    {
        $obj = $this->defaultObject();

        $properties = array_map(
            static fn(Field $field, string $key) => $field->toObject(),
            $this->properties,
        );
        $obj->properties = $properties;

        if (! empty($this->_required)) {
            $obj->required = $this->_required;
        }

        return $obj;
    }

    public function addProp(
        string $key,
        Field $field,
    ): self {
        $this->assertType(Type::OBJECT);

        $this->properties[$key] = $field;

        return $this;
    }

    public function removeProp(string $key): self
    {
        $this->assertType(Type::OBJECT);

        unset($this->properties[$key]);

        return $this;
    }

    public function required(array ...$keys): self
    {
        $this->assertType(Type::OBJECT);

        if (count($keys) === 1 && is_array($keys[0])) {
            $keys = $keys[0];
        }

        $this->_required[] = array_merge($this->_required, $keys);

        return $this;
    }

    public function notRequired(array ...$keys): self
    {
        $this->assertType(Type::OBJECT);

        if (count($keys) === 1 && is_array($keys[0])) {
            $keys = $keys[0];
        }

        $this->required = array_diff($this->required, $keys);

        return $this;
    }
}