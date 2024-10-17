<?php

namespace BS\ChatGPTBots\DTO;

use BS\ChatGPTBots\Enums\MessageRole;

class MessageDTO
{
    public function __construct(
        public string $text = '',
        public array $imageUrls = [],
        public MessageRole $role = MessageRole::USER,
    ) {}

    public function toObject(): \stdClass
    {
        $msg = new \stdClass();
        $msg->role = $this->role->value;
        $msg->content = [];

        if ($this->text) {
            $msg->content[] = [
                'type' => 'text',
                'text' => $this->cleanedText(),
            ];
        }

        foreach ($this->imageUrls as $imageUrl) {
            $msg->content[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $imageUrl,
                ],
            ];
        }

        return $msg;
    }

    public function isEmpty(): bool
    {
        return empty($this->text) && empty($this->imageUrls);
    }

    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    protected function cleanedText(): string
    {
        $text = $this->text;
        $text = $this->removeMentions($text);
        return $this->removeAttachBbCodes($text);
    }

    protected function removeAttachBbCodes(string $text): string
    {
        return trim((string)preg_replace(
            '/\[attach.*?].*?\[\/attach]/Usi',
            '',
            $text
        ));
    }

    protected function removeMentions(string $text): string
    {
        return preg_replace('/\[user=\d+]|\[\/user]/i', '', $text);
    }
}
