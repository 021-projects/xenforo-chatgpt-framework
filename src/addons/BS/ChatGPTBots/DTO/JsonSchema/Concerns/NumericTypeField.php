<?php

namespace BS\ChatGPTBots\DTO\JsonSchema\Concerns;

use BS\ChatGPTBots\Enums\JsonSchema\Type;

trait NumericTypeField
{
    protected int|float|null $minimum;

    protected int|float|null $maximum;

    protected int|float|bool|null $exclusiveMinimum;

    protected int|float|bool|null $exclusiveMaximum;

    protected int|float|null $_multipleOf;

    protected function numericObject(): \stdClass
    {
        $obj = $this->defaultObject();
        $props = $this->filterArray([
            'minimum' => $this->minimum,
            'maximum' => $this->maximum,
            'exclusiveMinimum' => $this->exclusiveMinimum,
            'exclusiveMaximum' => $this->exclusiveMaximum,
            'multipleOf' => $this->_multipleOf,
        ]);
        foreach ($props as $key => $value) {
            $obj->{$key} = $value;
        }
        return $obj;
    }

    public function min(int|float $value): self
    {
        $this->assertType(Type::NUMBER, Type::INTEGER);

        $this->minimum = $value;

        return $this;
    }

    public function max(int|float $value): self
    {
        $this->assertType(Type::NUMBER, Type::INTEGER);

        $this->maximum = $value;

        return $this;
    }

    public function exclusiveMin(int|float $value): self
    {
        $this->assertType(Type::NUMBER, Type::INTEGER);

        $this->exclusiveMinimum = $value;

        return $this;
    }

    public function exclusiveMax(int|float $value): self
    {
        $this->assertType(Type::NUMBER, Type::INTEGER);

        $this->exclusiveMaximum = $value;

        return $this;
    }

    public function multipleOf(int|float $value): self
    {
        $this->assertType(Type::NUMBER, Type::INTEGER);

        $this->_multipleOf = $value;

        return $this;
    }

}
