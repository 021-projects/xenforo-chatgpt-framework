<?php

namespace BS\ChatGPTBots\Repository;

use XF\Entity\ConversationMaster;
use XF\Entity\ConversationMessage;
use XF\Entity\ProfilePost;
use XF\Entity\ProfilePostComment;
use XF\Entity\Thread;
use XF\Entity\User;
use XF\Mvc\Entity\Repository;

class Message extends Repository
{
    use Concerns\MessageContent;

    public function fetchMessagesFromThread(
        Thread $thread,
        int $stopPosition = null,
        ?User $assistant = null,
        bool $transformAssistantQuotesToMessages = true,
        int $startPosition = null,
        bool $removeQuotesFromAssistantMessages = true
    ) {
        /** @var \XF\Finder\Post $finder */
        $finder = $this->finder('XF:Post');
        $posts = $finder
            ->inThread($thread, ['visibility' => 'visible']);

        if ($startPosition !== null) {
            $posts->where('position', '>=', $startPosition);
        }

        if ($stopPosition !== null) {
            $posts->where('position', '<=', $stopPosition);
        }

        $posts = $posts->orderByDate()
            ->fetchColumns(['user_id', 'message']);

        $messages = array_map(function ($post) use ($assistant, $removeQuotesFromAssistantMessages) {
            if ($assistant) {
                $role = $assistant->user_id === $post['user_id']
                    ? 'assistant'
                    : 'user';
            } else {
                $role = 'user';
            }
            if ($role === 'assistant' && $removeQuotesFromAssistantMessages) {
                $post['message'] = $this->removeQuotes($post['message']);
            }
            return $this->wrapMessage($post['message'], $role);
        }, $posts);

        if ($assistant && $transformAssistantQuotesToMessages) {
            $messages = $this->transformBotQuotesToMessages($messages, $assistant->user_id);
        }

        return $messages;
    }

    /**
     * @param  \XF\Entity\ConversationMaster  $conversation
     * @param  \XF\Entity\ConversationMessage|null  $beforeMessage
     * @param  \XF\Entity\User|null  $assistant
     * @param  int|null  $limit  if negative, fetches messages starting from beforeMessage, if positive, fetches messages before beforeMessage
     * @param  bool  $reverseLoad
     * @param  bool  $transformAssistantQuotesToMessages
     * @param  bool  $removeQuotesFromAssistantMessages
     * @return array|array[]
     */
    public function fetchMessagesFromConversation(
        ConversationMaster $conversation,
        ?ConversationMessage $beforeMessage = null,
        ?User $assistant = null,
        int $limit = 0,
        bool $reverseLoad = false,
        bool $transformAssistantQuotesToMessages = true,
        bool $removeQuotesFromAssistantMessages = true
    ) {
        /** @var \XF\Finder\ConversationMessage $finder */
        $finder = $this->finder('XF:ConversationMessage');

        $messagesFinder = $finder->inConversation($conversation);

        if ($beforeMessage) {
            $messagesFinder->where('message_date', '<=', $beforeMessage->message_date);
        }

        $messages = $messagesFinder
            ->order('message_date', $reverseLoad ? 'desc' : 'asc')
            ->limit($limit)
            ->fetch();

        if ($reverseLoad) {
            $messages = $messages->reverse();
        }

        $messages = array_map(function ($convMessage) use ($assistant, $removeQuotesFromAssistantMessages) {
            if ($assistant) {
                $role = $assistant->user_id === $convMessage['user_id']
                    ? 'assistant'
                    : 'user';
            } else {
                $role = 'user';
            }
            if ($role === 'assistant' && $removeQuotesFromAssistantMessages) {
                $convMessage['message'] = $this->removeQuotes($convMessage['message']);
            }
            return $this->wrapMessage($convMessage['message'], $role);
        }, $messages->toArray());

        if ($assistant && $transformAssistantQuotesToMessages) {
            $messages = $this->transformBotQuotesToMessages($messages, $assistant->user_id);
        }

        return $messages;
    }

    public function fetchCommentsFromProfilePost(
        ProfilePost $profilePost,
        ?ProfilePostComment $beforeComment = null,
        ?User $assistant = null,
        int $limit = 0,
        bool $reverseLoad = false
    ) {
        /** @var \XF\Finder\ProfilePostComment $commentsFinder */
        $commentsFinder = $this->finder('XF:ProfilePostComment');

        if ($beforeComment) {
            $commentsFinder->where('comment_date', '<=', $beforeComment->comment_date);
        }

        $comments = $commentsFinder->forProfilePost($profilePost)
            ->order('comment_date', $reverseLoad ? 'desc' : 'asc')
            ->limit($limit)
            ->fetch();

        if ($reverseLoad) {
            $comments = $comments->reverse();
        }

        return array_map(function ($comment) use ($assistant) {
            if ($assistant) {
                $role = $assistant->user_id === $comment['user_id']
                    ? 'assistant'
                    : 'user';
            } else {
                $role = 'user';
            }
            return $this->wrapMessage($comment['message'], $role);
        }, $comments->toArray());
    }

    public function transformBotQuotesToMessages(array $messages, int $assistantUserId = null)
    {
        $output = [];

        foreach ($messages as $message) {
            $quotes = $this->getQuotes($message['content'], $assistantUserId);
            if (empty($quotes)) {
                $output[] = $message;
            }

            foreach ($quotes as $quote) {
                $output[] = $this->wrapMessage($quote['content'], 'assistant');
                $output[] = $this->wrapMessage($quote['message']);
            }
        }

        return $output;
    }

    public function removeMessageDuplicates(array $messages): array
    {
        // to be sure that we have sequential array
        $messages = array_values($messages);

        $result = [];

        foreach ($messages as $key => $message) {
            if ($key === 0
                || $message['content'] !== $messages[$key - 1]['content']
            ) {
                $result[] = $message;
            }
        }

        return $result;
    }

    public function wrapMessage(string $content, string $role = 'user'): array
    {
        $content = $this->prepareContent($content);
        return compact('role', 'content');
    }

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