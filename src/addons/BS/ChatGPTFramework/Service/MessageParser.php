<?php

namespace BS\ChatGPTFramework\Service;

use XF\Service\AbstractService;

class MessageParser extends AbstractService
{
    public function getQuotes(
        string $text,
        int $userId = null,
        int $postId = null,
        string $postType = 'post'
    ): array {
        $pattern = $this->getQuotesPattern($userId, $postId, $postType);
        preg_match_all($pattern, $text, $matches);

        $quotes = [];
        foreach ($matches[0] as $match) {
            $quotes[] = array_merge(
                $this->parseQuote($match),
                compact('match')
            );
        }
        return $quotes;
    }

    public function removeQuotes(
        string $text,
        int $userId = null,
        int $postId = null,
        string $postType = 'post',
        bool $withMessage = false
    ): string {
        $pattern = $this->getQuotesPattern($userId, $postId, $postType, $withMessage);
        return trim((string)preg_replace($pattern, '', $text));
    }

    public function parseQuote(string $text, string $postType = '[a-zA-Z_]*'): array
    {
        $pattern = '/\[quote="[^"]+,\s*'.$postType.':\s*(?<post_id>\d+),\s*member:\s*(?<user_id>\d+)"]\n?(?<content>.+?)\n?\[\/quote\]\n(?<message>(?:(?!\[quote).)*)/is';
        preg_match($pattern, $text, $matches);
        return [
            'post_id' => isset($matches['post_id']) ? (int)$matches['post_id'] : null,
            'user_id' => isset($matches['user_id']) ? (int)$matches['user_id'] : null,
            'content' => isset($matches['content']) ? trim($matches['content']) : '',
            'message' => isset($matches['message']) ? trim($matches['message']) : '',
        ];
    }

    public function getQuotesPattern(
        int $userId = null,
        int $postId = null,
        string $postType = 'post',
        bool $withMessage = true
    ): string {
        // Quote example [QUOTE="Assistant, post: 666, member: 101"]
        // Build 'post: 666' part of pattern
        $patternPostPart = $postId ? "[^\"]+,\s*{$postType}:\s*{$postId}[,\"]" : '';
        // Build 'member: 101' part of pattern
        $patternMemberPart = $userId ? "[^\"]+,\s*member:\s*{$userId}[,\"]" : '';
        // Build '="Assistant, post: 666, member: 101"' part of pattern
        $patternInfoPart = $patternPostPart || $patternMemberPart
            ? "=\"{$patternPostPart}{$patternMemberPart}"
            : '.*?';
        // Build message part of pattern
        $messagePattern = $withMessage ? '\n[^\[]*(?:(?!\[quote).)*' : '';
        // Build full pattern
        return "/\[quote{$patternInfoPart}]\\n?(.+?)\\n?\[\/quote\]{$messagePattern}/is";
    }
}
