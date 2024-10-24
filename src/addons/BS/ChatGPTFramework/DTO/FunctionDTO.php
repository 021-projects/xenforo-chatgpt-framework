<?php

namespace BS\ChatGPTFramework\DTO;

class FunctionDTO
{
    protected string $_name;

    public function __construct(
        string $name,
        public ?string $description = null,
        public ?ParametersDTO $parameters = null,
        public ?bool $strict = null,
    ) {
        $this->name($name);
    }

    public function toObject(): \stdClass
    {
        $obj = new \stdClass();
        $obj->name = $this->_name;

        if ($this->description) {
            $obj->description = $this->description;
        }

        if ($this->parameters) {
            $obj->parameters = $this->parameters->toObject();
        }

        if ($this->strict !== null) {
            $obj->strict = $this->strict;
        }

        return $obj;
    }

    public function name(string $name): self
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Function name cannot be empty');
        }

        $this->_name = $name;
        return $this;
    }
}
