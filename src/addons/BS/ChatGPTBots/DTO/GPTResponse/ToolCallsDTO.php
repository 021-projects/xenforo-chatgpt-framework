<?php

namespace BS\ChatGPTBots\DTO\GPTResponse;

class ToolCallsDTO
{
    protected array $decodedFunctions = [];

    public function __construct(protected array $toolCalls)
    {}

    public function func(string $name): ?FunctionDTO
    {
        if (isset($this->decodedFunctions[$name])) {
            return $this->decodedFunctions[$name];
        }

        $toolCall = $this->findToolCallForFunction($name);
        if ($toolCall === null) {
            return null;
        }

        return $this->decodedFunctions[$name] = new FunctionDTO(
            $toolCall['function']['name'],
            json_decode(
                $toolCall['function']['arguments'],
                true,
                512,
                JSON_THROW_ON_ERROR
            ),
        );
    }

    protected function findToolCallForFunction(string $name): ?array
    {
        foreach ($this->toolCalls as $toolCall) {
            if ($toolCall['function']['name'] === $name) {
                return $toolCall;
            }
        }

        return null;
    }
}