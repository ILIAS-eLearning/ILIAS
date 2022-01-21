<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\OnScreenChat\DTO;

/**
 * Class ConversationDto
 * @package ILIAS\OnScreenChat\DTO
 */
class ConversationDto
{
    private string $id;
    private bool $isGroup = false;
    /** @var int[] */
    private array $subscriberUsrIds = [];
    private MessageDto $lastMessage;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->lastMessage = new MessageDto('', $this);
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function setId(string $id) : void
    {
        $this->id = $id;
    }

    public function isGroup() : bool
    {
        return $this->isGroup;
    }

    public function setIsGroup(bool $isGroup) : void
    {
        $this->isGroup = $isGroup;
    }

    /**
     * @return int[]
     */
    public function getSubscriberUsrIds() : array
    {
        return $this->subscriberUsrIds;
    }

    /**
     * @param int[] $subscriberUsrIds
     */
    public function setSubscriberUsrIds(array $subscriberUsrIds) : void
    {
        $this->subscriberUsrIds = $subscriberUsrIds;
    }

    public function getLastMessage() : MessageDto
    {
        return $this->lastMessage;
    }

    public function setLastMessage(MessageDto $lastMessage) : void
    {
        $this->lastMessage = $lastMessage;
    }
}
