<?php

namespace BS\ChatGPTBots;

use BS\ChatGPTBots\Exception\FunctionCallException;
use BS\ChatGPTBots\Exception\ReplyException;
use Orhanerday\OpenAi\OpenAi;

class Response
{
    private const STREAM_RESPONSE_DELIMITER = "}]}\n\n";
    public static function streamReplyWithLogErrors(
        OpenAi $api,
        array $params,
        callable $output,
        bool $throwExceptions = false
    ): bool {
        $params['stream'] = true;

        try {
            return (bool)$api->chat($params, function ($curlInfo, $response) use ($output) {
                $reply = self::getStreamReply($response);
                if ('' !== $reply) {
                    $output($reply);
                }

                return strlen($response);
            });
        } catch (\Exception $e) {
            \XF::logException($e, false, 'ChatGPT exception: ');

            if ($throwExceptions) {
                throw $e;
            }
        }

        return false;
    }

    public static function getReplyWithLogErrors(
        OpenAi $api,
        array $params,
        bool $throwExceptions = false
    ) {
        try {
            $response = $api->chat($params);
            if (! $response) {
                throw new ReplyException('Empty response', $response);
            }

            $reply = self::getReply($response);
            if (! $reply) {
                throw new ReplyException('Empty reply', $response);
            }

            return $reply;
        } catch (ReplyException $e) {
            $_POST['_chatGptResponse'] = $e->getResponse();
            \XF::logException($e, false, 'ChatGPT response error: ');
            unset($_POST['_chatGptResponse']);

            if ($throwExceptions) {
                throw $e;
            }
        } catch (\Exception $e) {
            \XF::logException($e, false, 'ChatGPT exception: ');

            if ($throwExceptions) {
                throw $e;
            }
        }

        return "Sorry I couldn't contact the ChatGPT think tank :(";
    }

    public static function getFunctionCallWithLogErrors(
        OpenAi $api,
        array $params,
        bool $throwExceptions = false
    ) {
        try {
            $response = $api->chat($params);
            if (! $response) {
                throw new FunctionCallException('Empty response', $response);
            }

            $functionCall = self::getFunctionCall($response);
            if (empty($functionCall)) {
                throw new FunctionCallException('Empty function call', $response);
            }

            return $functionCall;
        } catch (FunctionCallException $e) {
            $_POST['_chatGptResponse'] = $e->getResponse();
            \XF::logException($e, false, 'ChatGPT function call error: ');
            unset($_POST['_chatGptResponse']);

            if ($throwExceptions) {
                throw $e;
            }
        } catch (\Exception $e) {
            \XF::logException($e, false, 'ChatGPT function call exception: ');

            if ($throwExceptions) {
                throw $e;
            }
        }

        return "Sorry I couldn't contact the ChatGPT think tank :(";
    }

    public static function getFunctionCall($response)
    {
        if (is_string($response)) {
            $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        }

        return $response['choices'][0]['message']['function_call'] ?? [];
    }

    public static function buildFunctionForCall(
        string $name,
        array $parameters = [],
        array $required = [],
        string $description = ''
    ) {
        return [
            'name' => $name,
            'description' => $description,
            'parameters' => [
                'type' => 'object',
                'properties' => $parameters,
                'required' => $required
            ]
        ];
    }

    public static function getReply($response)
    {
        if (is_string($response)) {
            $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        }

        return $response['choices'][0]['message']['content'] ?? '';
    }

    public static function getStreamReply($response)
    {
        $jsonResponses = [];

        if (is_string($response)) {
            $response = str_replace('data: ', '', $response);
            $jsonResponses = explode(self::STREAM_RESPONSE_DELIMITER, $response);
            $jsonResponses = array_map(
                static function ($json) {
                    if (substr($json, -3) !== '}]}') {
                        $json .= '}]}';
                    }
                    return @json_decode($json, true);
                },
                $jsonResponses
            );
            $jsonResponses = array_filter($jsonResponses);
        }

        return implode(
            '',
            array_map(
                static fn($json) => $json['choices'][0]['delta']['content'] ?? '',
                $jsonResponses
            )
        );
    }
}
