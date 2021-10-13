<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailsNotificationCollectionObj
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsNotificationCollectionObj
{
    protected int $user_id = 0;
    /** @var ilMailCronOrphanedMailsFolderObject[] */
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

    public function getFolderObjectById(int $folder_id) : ilMailCronOrphanedMailsFolderObject
    {
        return $this->folder_objects[$folder_id];
    }

    /**
     * @return ilMailCronOrphanedMailsFolderObject[]
     */
    public function getFolderObjects() : array
    {
        return $this->folder_objects;
    }
}
