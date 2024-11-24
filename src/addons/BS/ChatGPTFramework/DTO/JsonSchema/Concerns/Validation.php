<?php

namespace BS\ChatGPTFramework\DTO\JsonSchema\Concerns;

trait Validation
{
    protected function assertArrayItemsInstanceOf(array $items, array|string $class): void
    {
        foreach ($items as $item) {
            $valid = is_array($class)
                ? array_reduce(
                    $class,
                    static fn ($carry, $class) => $carry || is_a($item, $class), false
                )
                : is_a($item, $class);
            if (! $valid) {
                throw new \InvalidArgumentException(
                    'Items must be an instance of '.$class
                );
            }
        }
    }
}
