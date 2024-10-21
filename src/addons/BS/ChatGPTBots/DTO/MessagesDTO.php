<?php

namespace BS\ChatGPTBots\DTO;

use BS\ChatGPTBots\Enums\MessageRole;
use BS\ChatGPTBots\Service\MessageParser;
use Traversable;

class MessagesDTO implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var MessageDTO[]
     */
    protected array $items;

    protected MessageParser $msgParser;

    public function __construct(array|MessageDTO ...$items)
    {
        if (count($items) === 1 && is_array($items[0])) {
            $items = $items[0];
        }

        // validate items
        foreach ($items as $item) {
            if ($item instanceof MessageDTO) {
                continue;
            }
            throw new \InvalidArgumentException(
                'All items must be instances of MessageDTO'
            );
        }

        $this->items = $items;
        $this->msgParser = \XF::service(MessageParser::class);
    }

    public function prepend(MessageDTO ...$items): self
    {
        $this->items = array_merge($items, $this->items);
        return $this;
    }

    public function append(MessageDTO ...$items): self
    {
        $this->items = array_merge($this->items, $items);
        return $this;
    }

    public function splice(
        int $offset,
        int $length = null,
        ?MessageDTO $replacement = null
    ): self {
        $this->items = array_splice($this->items, $offset, $length, $replacement);
        return $this;
    }

    public function merge(MessagesDTO $messages): self
    {
        return $this->append(...$messages->getItems());
    }

    /**
     * Shortcut for append
     *
     * @param  \BS\ChatGPTBots\DTO\MessageDTO  ...$items
     * @return $this
     */
    public function add(MessageDTO ...$items): self
    {
        return $this->append(...$items);
    }

    public function addFromText(
        string $text,
        array $imageUrls = [],
        MessageRole $role = MessageRole::USER,
        string $name = '',
        bool $splitQuotes = false,
        ?int $assistantUserId = null,
        string $assistantName = ''
    ): self {
        if (! $splitQuotes) {
            $this->add(new MessageDTO($text, $imageUrls, $role, $name));
            return $this;
        }

        $quotes = $this->msgParser->getQuotes($text, $assistantUserId);
        if (empty($quotes)) {
            $this->add(new MessageDTO($text, $imageUrls, $role, $name));
            return $this;
        }

        $lastQuote = array_pop($quotes);

        foreach ($quotes as $quote) {
            $this->add(new MessageDTO(
                $quote['content'],
                role: MessageRole::ASSISTANT,
                name: $assistantName
            ));
            $this->add(new MessageDTO($quote['message'], name: $name));
        }

        $this->add(new MessageDTO(
            $lastQuote['content'],
            role: MessageRole::ASSISTANT,
            name: $assistantName
        ));
        $this->add(new MessageDTO($lastQuote['message'], $imageUrls, name: $name));

        return $this;
    }

    public function unique(): self
    {
        $items = [];

        // check for consecutive duplicates
        $lastMessage = null;
        foreach ($this->items as $message) {
            if ($lastMessage && $lastMessage->text === $message->text) {
                continue;
            }

            $items[] = $message;
            $lastMessage = $message;
        }

        return new self($items);
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        return array_map(
            static fn (MessageDTO $item) => $item->toObject(),
            $this->items
        );
    }

    public function __clone()
    {
        $items = [];
        foreach ($this->items as $item) {
            $items[] = clone $item;
        }
        $this->items = $items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isNotEmpty(): bool
    {
        return !empty($this->items);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): ?MessageDTO
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (! ($value instanceof MessageDTO)) {
            throw new \InvalidArgumentException(
                'Value must be an instance of MessageDTO'
            );
        }

        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function first(): ?MessageDTO
    {
        return reset($this->items) ?: null;
    }

    public function last(): ?MessageDTO
    {
        return end($this->items) ?: null;
    }

    public function count(): int
    {
        return count($this->items);
    }
}
