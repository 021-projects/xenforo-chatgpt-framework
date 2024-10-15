<?php

namespace BS\ChatGPTBots\Repository\Concerns;

trait MessageContent
{
    public function prepareContent(string $content): string
    {
        return $this->removeMentions($this->removeAttachBbCodes($content));
    }

    protected function removeAttachBbCodes(string $text): string
    {
        return trim((string)preg_replace('/\[attach.*?].*?\[\/attach]/Usi', '', $text));
    }

    protected function removeMentions(string $text): string
    {
        return preg_replace('/\[user=\d+]|\[\/user]/i', '', $text);
    }
}