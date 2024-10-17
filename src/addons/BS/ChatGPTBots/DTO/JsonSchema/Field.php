<?php

namespace BS\ChatGPTBots\DTO\JsonSchema;

use BS\ChatGPTBots\DTO\JsonSchema\Concerns\NumericTypeField;
use BS\ChatGPTBots\DTO\JsonSchema\Concerns\ObjectTypeField;
use BS\ChatGPTBots\DTO\JsonSchema\Concerns\StringTypeField;
use BS\ChatGPTBots\Enums\JsonSchema\Type;

/**
 * Class JsonSchemaField
 *
 * Represents a field in JSON schema
 *
 * TODO: Add support for all JSON schema properties (Missed: Array)
 *
 * @package BS\ChatGPTBots\DTO
 * @see https://json-schema.org/understanding-json-schema/reference
 */
abstract class Field
{
    use StringTypeField;
    use ObjectTypeField;
    use NumericTypeField;

    public function __construct(
        protected Type $type,
        public string $description = '',
    ) {}

    public function toObject(): \stdClass
    {
        return match ($this->type) {
            Type::STRING => $this->stringObject(),
            Type::OBJECT => $this->objectObject(),
            Type::NUMBER, Type::INTEGER => $this->numericObject(),
            default => $this->defaultObject(),
        };
    }

    protected function defaultObject(): \stdClass
    {
        $obj = new \stdClass();
        $obj->type = $this->type->value;
        if (! empty($this->description)) {
            $obj->description = $this->description;
        }
        return $obj;
    }

    protected function assertType(Type ...$type): void
    {
        if (! in_array($this->type, $type, true)) {
            $types = array_map(static fn($t) => $t->value, $type);
            throw new \InvalidArgumentException(
                'This method can be called only for ' . implode(', ', $types) . ' type'
            );
        }
    }

    protected function filterArray(array $array): array
    {
        return array_filter($array, static fn($value) => ! empty($value));
    }
}
