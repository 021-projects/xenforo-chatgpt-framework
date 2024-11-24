<?php

namespace BS\ChatGPTFramework\DTO\JsonSchema\Concerns;

trait Transformation
{
    protected $_transform = null;

    /**
     * Apply transformation to the field when calling toObject
     *
     * @param  callable  $transform
     * @return \BS\ChatGPTFramework\DTO\JsonSchema\Field|\BS\ChatGPTFramework\DTO\JsonSchema\Concerns\Transformation
     */
    public function transform(callable $transform): self
    {
        $this->_transform = $transform;
        return $this;
    }

    protected function doTransformation(\stdClass $obj): \stdClass
    {
        if ($this->_transform !== null) {
            $obj = call_user_func($this->_transform, $obj);
        }
        return $obj;
    }
}
