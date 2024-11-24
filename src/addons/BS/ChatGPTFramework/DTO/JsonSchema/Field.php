<?php

namespace BS\ChatGPTFramework\DTO\JsonSchema;

use BS\ChatGPTFramework\DTO\JsonSchema\Concerns\ArrayTypeField;
use BS\ChatGPTFramework\DTO\JsonSchema\Concerns\NumericTypeField;
use BS\ChatGPTFramework\DTO\JsonSchema\Concerns\ObjectTypeField;
use BS\ChatGPTFramework\DTO\JsonSchema\Concerns\StringTypeField;
use BS\ChatGPTFramework\DTO\JsonSchema\Concerns\Transformation;
use BS\ChatGPTFramework\DTO\JsonSchema\Concerns\Validation;
use BS\ChatGPTFramework\Enums\JsonSchema\Type;

/**
 * Class JsonSchemaField
 *
 * Represents a field in JSON schema
 *
 * @package BS\ChatGPTBots\DTO
 * @see https://json-schema.org/understanding-json-schema/reference
 */
class Field
{
    use ArrayTypeField;
    use StringTypeField;
    use ObjectTypeField;
    use NumericTypeField;
    use Validation;
    use Transformation;

    public function __construct(
        protected Type $type,
        public string $description = '',
        ?array $properties = null,
        ?array $required = null,
        ?array $enum = null,
        ?int $minimum = null,
        ?int $maximum = null,
        int|bool|null $exclusiveMinimum = null,
        int|bool|null $exclusiveMaximum = null,
        ?int $multipleOf = null,
        ?int $minLength = null,
        ?int $maxLength = null,
        array|bool|null $prefixItems = null,
        Field|bool|null $items = null,
        ?Field $contains = null,
        ?int $minContains = null,
        ?int $maxContains = null,
        ?int $minItems = null,
        ?int $maxItems = null,
        ?bool $uniqueItems = null,
        ?callable $transform = null,
    ) {
        if ($properties !== null) {
            $this->addProps($properties);
        }

        if ($required !== null) {
            $this->required($required);
        }

        if ($enum !== null) {
            $this->allowed($enum);
        }

        if ($minimum !== null) {
            $this->min($minimum);
        }

        if ($maximum !== null) {
            $this->max($maximum);
        }

        if ($exclusiveMinimum !== null) {
            $this->exclusiveMin($exclusiveMinimum);
        }

        if ($exclusiveMaximum !== null) {
            $this->exclusiveMax($exclusiveMaximum);
        }

        if ($multipleOf !== null) {
            $this->multipleOf($multipleOf);
        }

        if ($minLength !== null) {
            $this->minLength($minLength);
        }

        if ($maxLength !== null) {
            $this->maxLength($maxLength);
        }

        if ($prefixItems !== null) {
            $this->prefixItems($prefixItems);
        }

        if ($items !== null) {
            $this->items($items);
        }

        if ($contains !== null) {
            $this->contains($contains);
        }

        if ($minContains !== null) {
            $this->minContains($minContains);
        }

        if ($maxContains !== null) {
            $this->maxContains($maxContains);
        }

        if ($minItems !== null) {
            $this->minItems($minItems);
        }

        if ($maxItems !== null) {
            $this->maxItems($maxItems);
        }

        if ($uniqueItems !== null) {
            $this->uniqueItems($uniqueItems);
        }

        if ($transform !== null) {
            $this->transform($transform);
        }
    }

    public function toObject(): \stdClass
    {
        $obj = match ($this->type) {
            Type::ARRAY => $this->arrayObject(),
            Type::STRING => $this->stringObject(),
            Type::OBJECT => $this->objectObject(),
            Type::NUMBER, Type::INTEGER => $this->numericObject(),
            default => $this->defaultObject(),
        };
        return $this->doTransformation($obj);
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

    protected function mapToObject(array $array): array
    {
        return array_map(static fn($value) => $value->toObject(), $array);
    }
}
