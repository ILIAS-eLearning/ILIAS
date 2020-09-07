<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailsFolderObject
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsFolderObject
{
    /**
     * @var int
     */
    protected $folder_id = 0;

    /**
     * @var string
     */
    protected $folder_title = '';

    /**
     * @var ilMailCronOrphanedMailsFolderMailObject[]
     */
    protected $orphaned_mail_objects = array();

    /**
     * @param $folder_id
     */
    public function __construct($folder_id)
    {
        $this->setFolderId($folder_id);
    }

    /**
     * @return int
     */
    public function getFolderId()
    {
        return $this->folder_id;
    }

    /**
     * @param int $folder_id
     */
    public function setFolderId($folder_id)
    {
        $this->folder_id = $folder_id;
    }

    /**
     * @return string
     */
    public function getFolderTitle()
    {
        return $this->folder_title;
    }

    /**
     * @param string $folder_title
     */
    public function setFolderTitle($folder_title)
    {
        $this->folder_title = $folder_title;
    }

    /**
     * @param ilMailCronOrphanedMailsFolderMailObject $mail_obj
     */
    public function addMailObject(ilMailCronOrphanedMailsFolderMailObject $mail_obj)
    {
        $this->orphaned_mail_objects[$mail_obj->getMailId()] = $mail_obj;
    }

    /**
     * @return ilMailCronOrphanedMailsFolderMailObject[]
     */
    public function getOrphanedMailObjects()
    {
        return $this->orphaned_mail_objects;
    }
}
