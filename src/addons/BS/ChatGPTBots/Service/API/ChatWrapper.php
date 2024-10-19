<?php

namespace BS\ChatGPTBots\Service\API;

use BS\ChatGPTBots\DTO\GPTResponse\ErrorDTO;
use BS\ChatGPTBots\DTO\GPTResponse\MessageDTO;
use BS\ChatGPTBots\DTO\GPTResponse\StreamChunkDTO;
use BS\ChatGPTBots\DTO\GPTResponse\ToolCallsDTO;
use BS\ChatGPTBots\Enums\MessageRole;
use BS\ChatGPTBots\Exception\Message\EmptyMessage;
use BS\ChatGPTBots\Exception\Message\EmptyResponseException;
use BS\ChatGPTBots\Exception\Message\NoContentException;
use BS\ChatGPTBots\Exception\Message\ResponseException;
use BS\ChatGPTBots\Exception\Message\ResponseError;
use BS\ChatGPTBots\Exception\Message\WrongResponseTypeException;
use Orhanerday\OpenAi\OpenAi;
use XF\App;
use XF\Service\AbstractService;

class ChatWrapper extends AbstractService
{
    protected OpenAi $api;

    private const JSON_END = '}]}';
    /**
     * Using to split JSON responses from the stream API.
     *
     * @var string
     */
    private const STREAM_RESPONSE_DELIMITER = self::JSON_END."\n\n";

    public function __construct(App $app, ?OpenAi $api = null)
    {
        parent::__construct($app);

        $this->api = $api ?? $app->container('openAi:api');
    }

    /**
     * @param  array  $params
     * @param  callable  $output function(StreamChunkDTO $chunk): void
     * @param  bool  $ignoreExceptions
     * @return void
     * @throws \JsonException
     */
    public function streamMessage(
        array $params,
        callable $output,
        bool $ignoreExceptions = false,
    ): void {
        $params['stream'] = true;

        try {
            $this->api->chat($params, function ($curlInfo, $response) use ($output) {
                $chunk = $this->parseStreamChunk($response);
                if (! $chunk->isEmpty()) {
                    $output($chunk);
                }

                return strlen($response);
            });
        } catch (\Exception $e) {
            \XF::logException($e, false, 'ChatGPT exception: ');
            !$ignoreExceptions && throw $e;
        }
    }

    protected function parseStreamChunk(mixed $response): StreamChunkDTO
    {
        $jsonResponses = [];

        if (is_string($response)) {
            $response = str_replace('data: ', '', $response);
            $jsonResponses = explode(self::STREAM_RESPONSE_DELIMITER, $response);
            $jsonResponses = array_map(
                static function ($json) {
                    if (substr($json, -3) !== self::JSON_END) {
                        $json .= self::JSON_END;
                    }
                    return @json_decode(
                        $json,
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    );
                },
                $jsonResponses
            );
            $jsonResponses = array_filter($jsonResponses);

            foreach ($jsonResponses as $json) {
                $this->assertNoResponseError($json);
            }
        }

        $getFirstDelta = static function (array $json): ?array {
            return $json['choices'][0]['delta'] ?? null;
        };

        $firstDelta = $getFirstDelta($jsonResponses[0]);
        $role = MessageRole::tryFrom($firstDelta['role']);

        $content = implode(
            '',
            array_map(
                static fn($json) => $json['choices'][0]['delta']['content'] ?? '',
                $jsonResponses
            )
        );

        return new StreamChunkDTO(
            role: $role,
            content: $content
        );
    }

    /**
     * @param  array  $params
     * @param  bool  $mustHasContent If true, throws an exception if the message content is empty.
     *
     * @return \BS\ChatGPTBots\DTO\GPTResponse\MessageDTO
     * @throws \BS\ChatGPTBots\Exception\Message\EmptyMessage
     * @throws \BS\ChatGPTBots\Exception\Message\EmptyResponseException
     * @throws \BS\ChatGPTBots\Exception\Message\NoContentException
     * @throws \BS\ChatGPTBots\Exception\Message\ResponseException
     * @throws \BS\ChatGPTBots\Exception\Message\WrongResponseTypeException
     * @throws \JsonException
     */
    public function getMessage(
        array $params,
        bool $mustHasContent = true,
    ): MessageDTO {
        try {
            $response = $this->api->chat($params);
            if (! $response) {
                throw new EmptyResponseException($response);
            }

            if (is_bool($response)) {
                throw new WrongResponseTypeException($response);
            }

            $msg = $this->parseMessage($response);

            if ($mustHasContent && ! $msg->content) {
                throw new NoContentException($response);
            }

            return $msg;
        } catch (\Exception $e) {
            if ($e instanceof ResponseException) {
                $this->logResponseException($e);
            } else {
                \XF::logException($e, false, 'ChatGPT exception: ');
            }

            throw $e;
        }
    }

    protected function parseMessage(string $response): MessageDTO
    {
        $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNoResponseError($response);

        $firstMsg = $response['choices'][0]['message'] ?? null;

        if (! $firstMsg) {
            throw new EmptyMessage(json_encode($response));
        }

        $role = MessageRole::tryFrom($firstMsg['role']) ?? MessageRole::ASSISTANT;
        $toolCallsDto = null;

        $toolCalls = $firstMsg['tool_calls'] ?? null;
        if ($toolCalls) {
            $toolCallsDto = new ToolCallsDTO($toolCalls);
        }

        return new MessageDTO(
            role: $role,
            content: $firstMsg['content'] ?? null,
            toolCalls: $toolCallsDto
        );
    }

    protected function assertNoResponseError(array $response): void
    {
        if (! empty($error = $response['error'] ?? null)) {
            throw new ResponseError(new ErrorDTO(
                type: $error['type'] ?? 'unknown',
                code: $error['code'] ?? 'unknown',
                message: $error['message'],
                param: $error['param'] ?? null
            ), json_encode($response));
        }
    }

    protected function logResponseException(ResponseException $e): void
    {
        $_POST['chat_gpt_response'] = $e->getResponse();
        \XF::logException($e, false, 'ChatGPT response error: ');
        unset($_POST['chat_gpt_response']);
    }
}
