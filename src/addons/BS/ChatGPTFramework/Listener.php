<?php

namespace BS\ChatGPTFramework;

use Orhanerday\OpenAi\OpenAi;
use XF\App;
use XF\Container;

class Listener
{
    public static function appSetup(App $app): void
    {
        $container = $app->container();
        $container->set(Consts::API_CONTAINER_KEY, function (Container $container) use ($app) {
            $apiKey = $app->options()->bsChatGptApiKey;
            if (! $apiKey) {
                return null;
            }
            return new OpenAi($apiKey);
        });
    }
}
