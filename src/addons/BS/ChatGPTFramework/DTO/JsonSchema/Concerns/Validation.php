<?php

namespace BS\ChatGPTFramework\DTO\JsonSchema\Concerns;

trait Validation
{
    protected function assertArrayItemsInstanceOf(array $items, string $class): void
    {
        foreach ($items as $item) {
            if (! is_a($item, $class)) {
                throw new \InvalidArgumentException(
                    'Items must be an instance of '.$class
                );
            }
        }
    }
}
