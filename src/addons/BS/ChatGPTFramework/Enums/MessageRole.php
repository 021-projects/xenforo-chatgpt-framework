<?php

namespace BS\ChatGPTFramework\Enums;

enum MessageRole: string
{
    case ASSISTANT = 'assistant';
    case SYSTEM = 'system';
    case TOOL = 'tool';
    case USER = 'user';
}
