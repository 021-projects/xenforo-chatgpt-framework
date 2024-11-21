<?php

namespace BS\ChatGPTFramework\DTO;

use BS\ChatGPTFramework\Enums\MessageRole;

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

        if (! empty($sanitizedName = $this->sanitizedName())) {
            $msg->name = $sanitizedName;
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
                $urlObj = new \stdClass();
                $urlObj->url = is_string($imageUrl) ? $imageUrl : $imageUrl['url'];
                $urlObj->detail = is_string($imageUrl) ? 'auto' : $imageUrl['detail'];
                $msg->content[] = [
                    'type' => 'image_url',
                    'image_url' => $urlObj,
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

    /**
     * Name must match ^[a-zA-Z0-9_-]+$ regex
     *
     * @return string
     */
    public function sanitizedName(): string
    {
        $name = preg_replace('/\s+/', '_', $this->name);
        $name = utf8_romanize(utf8_deaccent($name));
        if ($newName = @iconv('UTF-8', 'ASCII//TRANSLIT', $name)) {
            $name = $newName;
        }
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
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
