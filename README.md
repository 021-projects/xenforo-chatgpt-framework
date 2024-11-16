# Getting started

## Obtain Your OpenAI API Key
To use the ChatGPT API Framework, you need an API key from OpenAI. Register at [OpenAI](https://platform.openai.com/signup/) to obtain your key.

## Initialize the OpenAI API
The ChatGPT API Framework simplifies the initialization of the OpenAI API. Use the following code snippet to get started:
```php
use \BS\ChatGPTFramework\Consts;

/** \Orhanerday\OpenAi\OpenAi $api */
$api = \XF::app()->container(Consts::API_CONTAINER_KEY);
```

## Get message from ChatGPT
To retrieve a reply from ChatGPT, utilize the `\BS\ChatGPTFramework\Service\API\ChatWrapper` service. Here's an example:
```php
use BS\ChatGPTFramework\Service\API\ChatWrapper;
use BS\ChatGPTFramework\DTO\MessagesDTO;
use BS\ChatGPTFramework\DTO\MessageDTO;

/** @var ChatWrapper $wrapper */
$wrapper = \XF::service(ChatWrapper::class);

$messages = new MessagesDTO(
    new MessageDTO('Hello!')
);
$query = [
    'model'             => 'gpt-4o-mini',
    'messages'          => $messages->toArray(),
    'temperature'       => 1.0,
    'max_tokens'        => 420,
    'frequency_penalty' => 0,
    'presence_penalty'  => 0,
];

/** @var \BS\ChatGPTFramework\DTO\GPTResponse\MessageDTO $message */
$message = $wrapper->getMessage($query, mustHasContent: true);
/** @var \BS\ChatGPTFramework\DTO\GPTResponse\ToolCallsDTO $toolCalls */
$toolCalls = $message->toolCalls;
```

## Stream message from ChatGPT
```php
use BS\ChatGPTFramework\Service\API\ChatWrapper;
use BS\ChatGPTFramework\DTO\MessagesDTO;
use BS\ChatGPTFramework\DTO\MessageDTO;
use BS\ChatGPTFramework\DTO\GPTResponse\StreamChunkDTO;

/** @var ChatWrapper $wrapper */
$wrapper = \XF::service(ChatWrapper::class);

$messages = new MessagesDTO(
    new MessageDTO('Hello!')
);
$query = [
    'model'             => 'gpt-4o-mini',
    'messages'          => $messages->toArray(),
    'temperature'       => 1.0,
    'max_tokens'        => 420,
    'frequency_penalty' => 0,
    'presence_penalty'  => 0,
];

$outputMessage = '';

$wrapper->streamMessage($query, function (StreamChunkDTO $chunkDTO) use (&$outputMessage) {
    if (! $chunkDTO->hasContent()) {
        return;
    }
    $outputMessage .= $chunkDTO->content;
});
```

## Other Features
The add-on also allows generating MessagesDTO from different XenForo contexts.
Please check the `BS\ChatGPTFramework\Repository\Message` class for more details.
