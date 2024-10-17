<?php

namespace BS\ChatGPTBots\Enums\JsonSchema;

enum Type: string
{
    case OBJECT = 'object';
    case ARRAY = 'array';
    case STRING = 'string';
    case NUMBER = 'number';
    case INTEGER = 'integer';
    case BOOLEAN = 'boolean';
    case NULL = 'null';
}
