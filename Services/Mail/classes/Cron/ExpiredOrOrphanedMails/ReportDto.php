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

use ILIAS\Mail\Cron\ExpiredOrOrphanedMails\FolderDto;

class ReportDto
{
    private int $user_id;
    /** @var array<int, FolderDto> */
    private array $folder_objects = [];

    public function __construct(int $user_id)
    {
        $this->user_id = $user_id;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function addFolderObject(FolderDto $folder_obj) : void
    {
        $this->folder_objects[$folder_obj->getFolderId()] = $folder_obj;
    }

    public function getFolderObjectById(int $folder_id) : ?FolderDto
    {
        return $this->folder_objects[$folder_id] ?? null;
    }

    /**
     * @return array<int, FolderDto>
     */
    public function getFolderObjects() : array
    {
        return $this->folder_objects;
    }
}
