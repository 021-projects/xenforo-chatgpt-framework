<?php

namespace BS\ChatGPTFramework\DTO\GPTResponse;

class ToolCallsDTO
{
    protected array $decodedFunctions = [];

    public function __construct(protected array $toolCalls = [])
    {
    }

    public function isEmpty(): bool
    {
        return empty($this->toolCalls);
    }

    public function get(int $index): ?array
    {
        return $this->toolCalls[$index] ?? null;
    }

    public function has(int $index): bool
    {
        return isset($this->toolCalls[$index]);
    }

    public function set(int $index, array $value): void
    {
        $this->toolCalls[$index] = $value;
    }

    public function add(array $value): void
    {
        $this->toolCalls[] = $value;
    }

    public function raw(): array
    {
        return $this->toolCalls;
    }

    public function merge(ToolCallsDTO $calls): self
    {
        $this->toolCalls = array_merge($this->toolCalls, $calls->raw());
        return $this;
    }

    public function chunkMerge(ToolCallsDTO ...$calls): self
    {
        // recursive merge every column
        foreach ($calls as $call) {
            foreach ($call->raw() as $index => $value) {
                $existingCall = $this->get($index);
                if (! $existingCall) {
                    $this->add($value);
                    continue;
                }

                if (! isset($existingCall['function'])) {
                    $existingCall['function'] = $value['function'];
                    $this->set($index, $existingCall);
                    continue;
                }

                if (! isset($existingCall['function']['name'])) {
                    $existingCall['function']['name'] = '';
                }
                if (! isset($existingCall['function']['arguments'])) {
                    $existingCall['function']['arguments'] = '';
                }

                $existingCall['function']['name'] .= $value['function']['name'] ?? '';
                $existingCall['function']['arguments'] .= $value['function']['arguments'] ?? '';

                $this->set($index, $existingCall);
            }
        }

        return $this;
    }

    public function decodedFunctions(): array
    {
        if (! empty($this->decodedFunctions)
            && count($this->decodedFunctions) === count($this->toolCalls)
        ) {
            return $this->decodedFunctions;
        }

        $functions = [];
        foreach ($this->toolCalls as $toolCall) {
            $functions[] = $this->decodeToolCall($toolCall);
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

    public function hasFunc(string $name): bool
    {
        if (isset($this->decodedFunctions[$name])) {
            return true;
        }

        $toolCall = $this->findToolCallForFunction($name);
        return $toolCall !== null;
    }

    public function hasFuncStartsWith(string $name): bool
    {
        foreach ($this->toolCalls as $toolCall) {
            $fnName = $toolCall['function']['name'] ?? '';
            if (str_starts_with($fnName, $name)) {
                return true;
            }
        }

        return false;
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
            $fnName = $toolCall['function']['name'] ?? null;
            if ($fnName === $name) {
                return $toolCall;
            }
        }

        return null;
    }
}
