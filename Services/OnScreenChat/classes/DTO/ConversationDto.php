<?php declare(strict_types=1);

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
