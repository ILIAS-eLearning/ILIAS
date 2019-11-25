<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\OnScreenChat\DTO;

/**
 * Class ConversationDto
 * @package ILIAS\OnScreenChat\DTO
 */
class ConversationDto
{
    /** @var string */
    private $id = '';
    /** @var bool */
    private $isGroup = false;
    /** @var array */
    private $subscriberUsrIds = [];
    /** @var MessageDto */
    private $lastMessage;

    /**
     * ConversationDto constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id) : void
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isGroup() : bool
    {
        return $this->isGroup;
    }

    /**
     * @param bool $isGroup
     */
    public function setIsGroup(bool $isGroup) : void
    {
        $this->isGroup = $isGroup;
    }

    /**
     * @return array
     */
    public function getSubscriberUsrIds() : array
    {
        return $this->subscriberUsrIds;
    }

    /**
     * @param array $subscriberUsrIds
     */
    public function setSubscriberUsrIds(array $subscriberUsrIds) : void
    {
        $this->subscriberUsrIds = $subscriberUsrIds;
    }

    /**
     * @return MessageDto
     */
    public function getLastMessage() : MessageDto
    {
        return $this->lastMessage;
    }

    /**
     * @param MessageDto $lastMessage
     */
    public function setLastMessage(MessageDto $lastMessage) : void
    {
        $this->lastMessage = $lastMessage;
    }
}