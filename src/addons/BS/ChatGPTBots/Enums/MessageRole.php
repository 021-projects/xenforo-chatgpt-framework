<?php

namespace BS\ChatGPTBots\Enums;

enum MessageRole: string
{
    case Assistant = 'assistant';
    case System = 'system';
    case Tool = 'tool';
    case User = 'user';
}
