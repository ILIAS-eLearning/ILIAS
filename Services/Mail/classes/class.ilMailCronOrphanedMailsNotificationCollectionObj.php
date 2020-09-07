<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailsNotificationCollectionObj
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsNotificationCollectionObj
{
    /**
     * @var int
     */
    protected $user_id = 0;

    /**
     * @var ilMailCronOrphanedMailsFolderObject[]
     */
    protected $folder_objects = array();

    /**
     * @param $user_id
     */
    public function __construct($user_id)
    {
        $this->setUserId($user_id);
    }
    
    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @param ilMailCronOrphanedMailsFolderObject $folder_obj
     */
    public function addFolderObject(ilMailCronOrphanedMailsFolderObject $folder_obj)
    {
        $this->folder_objects[$folder_obj->getFolderId()] = $folder_obj;
    }

    /**
     * @param $folder_id
     * @return ilMailCronOrphanedMailsFolderObject
     */
    public function getFolderObjectById($folder_id)
    {
        return $this->folder_objects[$folder_id];
    }

    /**
     * @return ilMailCronOrphanedMailsFolderObject[]
     */
    public function getFolderObjects()
    {
        return $this->folder_objects;
    }
}
