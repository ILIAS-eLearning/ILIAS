<?php

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

declare(strict_types=1);

namespace ILIAS\Mail\Folder;

class MailFolderData
{
    public function __construct(
        private readonly int $folder_id,
        private readonly int $user_id,
        private readonly MailFolderType $type,
        private readonly string $title
    ) {
    }

    public function getFolderId(): int
    {
        return $this->folder_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): MailFolderType
    {
        return $this->type;
    }

    public function isInbox(): bool
    {
        return $this->type === MailFolderType::INBOX;
    }

    public function isDrafts(): bool
    {
        return $this->type === MailFolderType::DRAFTS;
    }

    public function isSent(): bool
    {
        return $this->type === MailFolderType::SENT;
    }
    public function isTrash(): bool
    {
        return $this->type === MailFolderType::TRASH;
    }

    public function isUserLocalFolder(): bool
    {
        return $this->type === MailFolderType::LOCAL;
    }

    public function isUserFolder(): bool
    {
        return $this->type === MailFolderType::USER;
    }

    public function hasIncomingMails(): bool
    {
        return !$this->isDrafts() && !$this->isSent();
    }

    public function hasOutgoingMails(): bool
    {
        return $this->isDrafts() || $this->isSent();
    }


}
