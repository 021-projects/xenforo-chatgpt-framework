# Getting started

## Obtain Your OpenAI API Key
To use the ChatGPT API Framework, you need an API key from OpenAI. Register at [OpenAI](https://platform.openai.com/signup/) to obtain your key.

## Initialize the OpenAI API
The ChatGPT API Framework simplifies the initialization of the OpenAI API. Use the following code snippet to get started:
```php
/** \Orhanerday\OpenAi\OpenAi $api */
$api = \XF::app()->container('chatGPT');
```
This code initializes the OpenAI API and assigns it to the `$api` variable.

## Get a reply from ChatGPT
To retrieve a reply from ChatGPT, utilize the `Response::getReply()` function. Here’s an example:
```php
use BS\ChatGPTBots\Response;

$messages = [
    ['role' => 'user', 'content' => 'Hello!']
];

$reply = Response::getReply(
    $api->chat([
        'model'             => 'gpt-3.5-turbo',
        'messages'          => $messages,
        'temperature'       => 1.0,
        'max_tokens'        => 420,
        'frequency_penalty' => 0,
        'presence_penalty'  => 0,
    ])
);
```
This code initializes an array of messages and calls the chat() function to get a response, which is stored in the `$reply` variable.

## Get a Reply with Error Logging
To retrieve a reply while logging any potential errors, use the `Response::getReplyWithLogErrors()` method:
```php
use BS\ChatGPTBots\Response;

$messages = [
    ['role' => 'user', 'content' => 'Hello!']
];

$reply = Response::getReplyWithLogErrors(
    $api->chat([
        'model'             => 'gpt-3.5-turbo',
        'messages'          => $messages,
        'temperature'       => 1.0,
        'max_tokens'        => 420,
        'frequency_penalty' => 0,
        'presence_penalty'  => 0,
    ])
);
```
This method attempts to get a reply from OpenAI's Chat API, logs any errors, and returns the reply.

# Message Repository **`\BS\ChatGPTBots\Repository\Message`**
The ChatGPT API Framework includes a message repository that manages messages for your bots. Below are some key functions:

## fetchMessagesFromThread()
Loads the context for the bot from a specified thread, converting bot quotes into messages for accurate context.
```php
public function fetchMessagesFromThread(
    Thread $thread,
    int $stopPosition = null,
    ?User $assistant = null,
    bool $transformAssistantQuotesToMessages = true,
    int $startPosition = null,
    bool $removeQuotesFromAssistantMessages = true
)
```


## fetchMessagesFromConversation()
This function loads the context for a bot from a conversation. Bot quotes are transformed into his messages for the correct context.
```php
public function fetchMessagesFromConversation(
    ConversationMaster $conversation,
    ?ConversationMessage $beforeMessage = null,
    ?User $assistant = null,
    int $limit = 0,
    bool $reverseLoad = false,
    bool $transformAssistantQuotesToMessages = true,
    bool $removeQuotesFromAssistantMessages = true
)
```

## fetchMessagesFromConversation()
Loads the context for the bot from a conversation, similarly converting bot quotes into messages.
```php
public function fetchMessagesFromConversation(
    ConversationMaster $conversation,
    ?ConversationMessage $beforeMessage = null,
    ?User $assistant = null,
    int $limit = 0,
    bool $reverseLoad = false,
    bool $transformAssistantQuotesToMessages = true,
    bool $removeQuotesFromAssistantMessages = true
)
```

## fetchCommentsFromProfilePost()
Loads the context for the bot from a profile post.
```php
public function fetchCommentsFromProfilePost(
    ProfilePost $profilePost,
    ?ProfilePostComment $beforeComment = null,
    ?User $assistant = null,
    int $limit = 0,
    bool $reverseLoad = false
)
```


## wrapMessage()
Generates a message array, preparing the content for the bot by removing unnecessary BB codes.
```php
public function wrapMessage(string $content, string $role = 'user'): array
```


## prepareContent()
Prepares message content for the bot, removing unnecessary BB codes.
```php
public function prepareContent(string $content, bool $stripQuotes = true): string
```


## getQuotes()
Parses quotes from the text, formatting them for convenience.
```php
public function getQuotes(string $text, int $userId = null, int $postId = null, string $postType = 'post'): array
```

## removeQuotes()
Removes quotes from the text, optionally targeting specific posts or users.
```php
public function removeQuotes(string $text, int $userId = null, int $postId = null, string $postType = 'post'): string
```
