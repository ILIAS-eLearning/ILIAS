<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\OnScreenChat\DTO;

/**
 * Class MessageDto
 * @package ILIAS\OnScreenChat\DTO
 */
class MessageDto
{
    private $id;
    private ConversationDto $conversation;
    private int $createdTimestamp;
    private int $authorUsrId = 0;
    private string $message = '';

    public function __construct(string $id, ConversationDto $conversation)
    {
        $this->id = $id;
        $this->conversation = $conversation;
        $this->createdTimestamp = time();
    }


    public function getId() : string
    {
        return $this->id;
    }

    public function setId(string $id) : void
    {
        $this->id = $id;
    }

    public function getConversation() : ConversationDto
    {
        return $this->conversation;
    }

    public function setConversation(ConversationDto $conversation) : void
    {
        $this->conversation = $conversation;
    }

    public function getAuthorUsrId() : int
    {
        return $this->authorUsrId;
    }

    public function setAuthorUsrId(int $authorUsrId) : void
    {
        $this->authorUsrId = $authorUsrId;
    }

    public function getCreatedTimestamp() : int
    {
        return $this->createdTimestamp;
    }

    public function setCreatedTimestamp(int $createdTimestamp) : void
    {
        $this->createdTimestamp = $createdTimestamp;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function setMessage(string $message) : void
    {
        $this->message = $message;
    }
}
