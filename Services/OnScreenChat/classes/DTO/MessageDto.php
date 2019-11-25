<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\OnScreenChat\DTO;

/**
 * Class MessageDto
 * @package ILIAS\OnScreenChat\DTO
 */
class MessageDto
{
    /** @var string */
    private $id = '';
    /** @var ConversationDto */
    private $conversation;
    /** @var int */
    private $authorUsrId;
    /** @var int */
    private $createdTimestamp;
    /** @var string */
    private $message;

    /**
     * MessageDtop constructor.
     * @param string $id
     * @param ConversationDto $conversation
     */
    public function __construct(string $id, ConversationDto $conversation)
    {
        $this->id = $id;
        $this->conversation = $conversation;
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
     * @return ConversationDto
     */
    public function getConversation() : ConversationDto
    {
        return $this->conversation;
    }

    /**
     * @param ConversationDto $conversation
     */
    public function setConversation(ConversationDto $conversation) : void
    {
        $this->conversation = $conversation;
    }

    /**
     * @return int
     */
    public function getAuthorUsrId() : int
    {
        return $this->authorUsrId;
    }

    /**
     * @param int $authorUsrId
     */
    public function setAuthorUsrId(int $authorUsrId) : void
    {
        $this->authorUsrId = $authorUsrId;
    }

    /**
     * @return int
     */
    public function getCreatedTimestamp() : int
    {
        return $this->createdTimestamp;
    }

    /**
     * @param int $createdTimestamp
     */
    public function setCreatedTimestamp(int $createdTimestamp) : void
    {
        $this->createdTimestamp = $createdTimestamp;
    }

    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message) : void
    {
        $this->message = $message;
    }
}