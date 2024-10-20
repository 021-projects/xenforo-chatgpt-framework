<?php

namespace BS\ChatGPTBots\DTO;

use BS\ChatGPTBots\Enums\MessageRole;

class MessageDTO
{
    public function __construct(
        public string $text = '',
        public array $imageUrls = [],
        public MessageRole $role = MessageRole::USER,
        public string $name = '',
    ) {}

    public function toObject(): \stdClass
    {
        $msg = new \stdClass();
        $msg->role = $this->role->value;
        $msg->content = [];

        if ($this->name) {
            $msg->name = $this->name;
        }

        if ($this->text) {
            $msg->content[] = [
                'type' => 'text',
                'text' => $this->cleanedText(),
            ];
        }

        // Only USER role allowed to send image_urls
        if ($this->role === MessageRole::USER) {
            foreach ($this->imageUrls as $imageUrl) {
                $info = is_array($imageUrl) ? $imageUrl : [
                    'url' => $imageUrl,
                ];
                $msg->content[] = [
                    'type' => 'image_url',
                    'image_url' => $info['url'],
                ];
            }
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
