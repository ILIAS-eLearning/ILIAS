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

namespace ILIAS\Mail\Cron\ExpiredOrOrphanedMails;

class FolderDto
{
    private int $folder_id;
    private ?string $folder_title;
    /** @var MailDto[] */
    private array $orphaned_mail_objects = [];

    public function __construct(int $folder_id, ?string $folder_title)
    {
        $this->folder_id = $folder_id;
        $this->folder_title = $folder_title;
    }

    public function getFolderId(): int
    {
        return $this->folder_id;
    }

    public function getFolderTitle(): ?string
    {
        return $this->folder_title;
    }

    public function addMailObject(MailDto $mail_obj): void
    {
        $this->orphaned_mail_objects[$mail_obj->getMailId()] = $mail_obj;
    }

    /**
     * @return MailDto[]
     */
    public function getOrphanedMailObjects(): array
    {
        return $this->orphaned_mail_objects;
    }
}
