<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\OnScreenChat\DTO;

/**
 * Class MessageDto
 * @package ILIAS\OnScreenChat\DTO
 */
class MessageDto
{
    private string $id;
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


    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getConversation(): ConversationDto
    {
        return $this->conversation;
    }

    public function setConversation(ConversationDto $conversation): void
    {
        $this->conversation = $conversation;
    }

    public function getAuthorUsrId(): int
    {
        return $this->authorUsrId;
    }

    public function setAuthorUsrId(int $authorUsrId): void
    {
        $this->authorUsrId = $authorUsrId;
    }

    public function getCreatedTimestamp(): int
    {
        return $this->createdTimestamp;
    }

    public function setCreatedTimestamp(int $createdTimestamp): void
    {
        $this->createdTimestamp = $createdTimestamp;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
