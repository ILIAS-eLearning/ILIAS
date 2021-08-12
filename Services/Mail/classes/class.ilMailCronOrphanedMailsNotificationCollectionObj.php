<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailsNotificationCollectionObj
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsNotificationCollectionObj
{
    protected int $user_id = 0;

    /**
     * @var ilMailCronOrphanedMailsFolderObject[]
     */
    protected array $folder_objects = [];

    /**
     * @param $user_id
     */
    public function __construct(int $user_id)
    {
        $this->setUserId($user_id);
    }
    
    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId(int $user_id) : void
    {
        $this->user_id = $user_id;
    }

    /**
     * @param ilMailCronOrphanedMailsFolderObject $folder_obj
     */
    public function addFolderObject(ilMailCronOrphanedMailsFolderObject $folder_obj) : void
    {
        $this->folder_objects[$folder_obj->getFolderId()] = $folder_obj;
    }

    /**
     * @param $folder_id
     * @return ilMailCronOrphanedMailsFolderObject
     */
    public function getFolderObjectById(int $folder_id) : \ilMailCronOrphanedMailsFolderObject
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
