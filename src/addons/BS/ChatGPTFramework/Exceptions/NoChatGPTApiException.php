<?php

namespace BS\ChatGPTFramework\Exceptions;

class NoChatGPTApiException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            'No ChatGPT API found in the container. Please make sure the ChatGPT Framework is installed, enabled and API key is set in options.'
        );
    }
}
