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

/**
 * ilMailCronOrphanedMailsNotificationCollectionObj
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsNotificationCollectionObj
{
    protected int $user_id = 0;
    /** @var array<int, ilMailCronOrphanedMailsFolderObject> */
    protected array $folder_objects = [];

    public function __construct(int $user_id)
    {
        $this->setUserId($user_id);
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id) : void
    {
        $this->user_id = $user_id;
    }

    public function addFolderObject(ilMailCronOrphanedMailsFolderObject $folder_obj) : void
    {
        $this->folder_objects[$folder_obj->getFolderId()] = $folder_obj;
    }

    public function getFolderObjectById(int $folder_id) : ?ilMailCronOrphanedMailsFolderObject
    {
        return $this->folder_objects[$folder_id] ?? null;
    }

    /**
     * @return array<int, ilMailCronOrphanedMailsFolderObject>
     */
    public function getFolderObjects() : array
    {
        return $this->folder_objects;
    }
}
