<?php

namespace BS\ChatGPTFramework\DTO;

class ToolsDTO
{
    protected array $tools;

    public function __construct(array|ToolDTO ...$tools)
    {
        if (count($tools) === 1 && is_array($tools[0])) {
            $tools = $tools[0];
        }

        $this->tools = $tools;
    }

    public function toArray(): array
    {
        $tools = [];
        foreach ($this->tools as $tool) {
            $tools[] = $tool->toObject();
        }

        return $tools;
    }

    public function add(ToolDTO $tool): void
    {
        $this->tools[] = $tool;
    }

    public function removeByFuncName(string $name): void
    {
        foreach ($this->tools as $key => $tool) {
            if ($tool->function?->name === $name) {
                unset($this->tools[$key]);
            }
        }
    }
}
