<?php

namespace BS\ChatGPTBots\DTO\GPTResponse;

class ToolCallsDTO
{
    protected array $decodedFunctions = [];

    public function __construct(protected array $toolCalls)
    {
    }

    /**
     * @return array|FunctionDTO[]
     * @throws \JsonException
     */
    public function keyedFunctions(): array
    {
        if (! empty($this->decodedFunctions)
            && count($this->decodedFunctions) === count($this->toolCalls)
        ) {
            return $this->decodedFunctions;
        }

        $functions = [];
        foreach ($this->toolCalls as $toolCall) {
            $functions[$toolCall['function']['name']] = $this->decodeToolCall($toolCall);
        }
        return $this->decodedFunctions = $functions;
    }

    public function func(string $name): ?FunctionDTO
    {
        if (isset($this->decodedFunctions[$name])) {
            return $this->decodedFunctions[$name];
        }

        $toolCall = $this->findToolCallForFunction($name);
        if ($toolCall === null) {
            return null;
        }

        return $this->decodedFunctions[$name] = $this->decodeToolCall($toolCall);
    }

    protected function decodeToolCall(array $toolCool): FunctionDTO
    {
        $name = $toolCool['function']['name'];
        $arguments = json_decode(
            $toolCool['function']['arguments'],
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        return new FunctionDTO($name, $arguments);
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
