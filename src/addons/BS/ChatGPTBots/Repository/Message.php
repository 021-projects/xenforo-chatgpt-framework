<?php

namespace BS\ChatGPTBots\Repository;

use BS\ChatGPTBots\DTO\MessagesDTO;
use BS\ChatGPTBots\Enums\MessageRole;
use BS\ChatGPTBots\Service\MessageParser;
use XF\Entity\ConversationMaster;
use XF\Entity\ConversationMessage;
use XF\Entity\ProfilePost;
use XF\Entity\ProfilePostComment;
use XF\Entity\Thread;
use XF\Entity\User;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Manager;
use XF\Mvc\Entity\Repository;

class Message extends Repository
{
    protected MessageParser $messageParser;

    public function __construct(Manager $em, $identifier)
    {
        parent::__construct($em, $identifier);
        $this->messageParser = \XF::service(MessageParser::class);
    }

    public function fetchMessagesFromThread(
        Thread $thread,
        int $stopPosition = null,
        ?User $assistant = null,
        bool $transformAssistantQuotesToMessages = true,
        int $startPosition = null,
        bool $removeQuotesFromAssistantMessages = true,
        bool $addImages = false,
    ): MessagesDTO {
        /** @var \XF\Finder\PostFinder|\XF\Finder\Post $finder */
        $finder = $this->finder('XF:Post');
        $posts = $finder
            ->inThread($thread, ['visibility' => 'visible']);

        if ($startPosition !== null) {
            $posts->where('position', '>=', $startPosition);
        }

        if ($stopPosition !== null) {
            $posts->where('position', '<=', $stopPosition);
        }

        $posts = $posts->orderByDate()->fetch();

        $messages = new MessagesDTO();

        /** @var \XF\Entity\Post $post */
        foreach ($posts as $post) {
            $role = $assistant && $assistant->user_id === $post['user_id']
                ? MessageRole::ASSISTANT
                : MessageRole::USER;
            $text = $post->message;
            $imageUrls = $addImages && $role === MessageRole::USER
                ? $this->getImageUrlsFromAttachments($post->Attachments)
                : [];

            if ($role === MessageRole::ASSISTANT
                && $removeQuotesFromAssistantMessages
            ) {
                $text = $this->messageParser->removeQuotes($post['message']);
            }

            $messages->addFromText(
                $text,
                $imageUrls,
                role: $role,
                name: $post->username,
                splitQuotes: $transformAssistantQuotesToMessages,
                assistantUserId: $assistant?->user_id,
                assistantName: $assistant?->username ?? ''
            );
        }

        return $messages;
    }

    /**
     * @param  \XF\Entity\ConversationMaster  $conversation
     * @param  \XF\Entity\ConversationMessage|null  $beforeMessage
     * @param  \XF\Entity\User|null  $assistant
     * @param  int  $limit  if negative, fetches messages starting from beforeMessage, if positive, fetches messages before beforeMessage
     * @param  bool  $reverseLoad
     * @param  bool  $transformAssistantQuotesToMessages
     * @param  bool  $removeQuotesFromAssistantMessages
     * @param  bool  $addImages
     * @return \BS\ChatGPTBots\DTO\MessagesDTO
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function fetchMessagesFromConversation(
        ConversationMaster $conversation,
        ?ConversationMessage $beforeMessage = null,
        ?User $assistant = null,
        int $limit = 0,
        bool $reverseLoad = false,
        bool $transformAssistantQuotesToMessages = true,
        bool $removeQuotesFromAssistantMessages = true,
        bool $addImages = false,
    ): MessagesDTO {
        /** @var \XF\Finder\ConversationMessageFinder|\XF\Finder\ConversationMessage $finder */
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

        $messagesDto = new MessagesDTO();

        foreach ($messages as $message) {
            $role = $assistant && $assistant->user_id === $message->user_id
                ? MessageRole::ASSISTANT
                : MessageRole::USER;
            $imageUrls = $addImages && $role === MessageRole::USER
                ? $this->getImageUrlsFromAttachments($message->Attachments)
                : [];

            if ($role === MessageRole::ASSISTANT
                && $removeQuotesFromAssistantMessages
            ) {
                $message->message = $this->messageParser->removeQuotes($message->message);
            }

            $messagesDto->addFromText(
                $message->message,
                $imageUrls,
                role: $role,
                name: $message->username,
                splitQuotes: $transformAssistantQuotesToMessages,
                assistantUserId: $assistant?->user_id,
                assistantName: $assistant?->username ?? ''
            );
        }

        return $messagesDto;
    }

    public function fetchCommentsFromProfilePost(
        ProfilePost $profilePost,
        ?ProfilePostComment $beforeComment = null,
        ?User $assistant = null,
        int $limit = 0,
        bool $reverseLoad = false,
        bool $addImages = false,
    ): MessagesDTO {
        /** @var \XF\Finder\ProfilePostCommentFinder|\XF\Finder\ProfilePostComment $commentsFinder */
        $commentsFinder = $this->finder('XF:ProfilePostComment');

        if ($beforeComment) {
            $commentsFinder->where(
                'comment_date',
                '<=',
                $beforeComment->comment_date
            );
        }

        $comments = $commentsFinder->forProfilePost($profilePost)
            ->order('comment_date', $reverseLoad ? 'desc' : 'asc')
            ->limit($limit)
            ->fetch();

        if ($reverseLoad) {
            $comments = $comments->reverse();
        }

        $messages = new MessagesDTO();

        foreach ($comments as $comment) {
            $role = $assistant && $assistant->user_id === $comment->user_id
                ? MessageRole::ASSISTANT
                : MessageRole::USER;
            $imageUrls = $addImages && $role === MessageRole::USER
                ? $this->getImageUrlsFromAttachments($comment->Attachments)
                : [];

            $messages->addFromText(
                $comment->message,
                $imageUrls,
                role           : $role,
                name           : $comment->username,
                assistantUserId: $assistant?->user_id,
                assistantName  : $assistant?->username ?? ''
            );
        }

        return $messages;
    }

    /**
     * @param  \XF\Entity\Attachment[]  $attachments
     * @return array
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function getImageUrlsFromAttachments(
        AbstractCollection|array $attachments
    ): array {
        $fs = $this->app()->fs();
        $base64 = static function ($abstractPath, $ext) use ($fs) {
            $data = $fs->read($abstractPath);
            return 'data:image/'.$ext.';base64,'.base64_encode($data);
        };

        $imageUrls = [];
        foreach ($attachments as $attachment) {
            if ($attachment->type_grouping !== 'image') {
                continue;
            }

            $imageUrls[] = $base64(
                $attachment->Data->getExistingAbstractedDataPath(),
                $attachment->extension
            );
        }

        return $imageUrls;
    }
}
