<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailsFolderObject
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsFolderObject
{
    protected int $folder_id = 0;
    protected string $folder_title = '';
    /** @var ilMailCronOrphanedMailsFolderMailObject[]*/
    protected array $orphaned_mail_objects = [];

    public function __construct(int $folder_id)
    {
        $this->setFolderId($folder_id);
    }

    public function getFolderId() : int
    {
        return $this->folder_id;
    }

    public function setFolderId(int $folder_id) : void
    {
        $this->folder_id = $folder_id;
    }

    public function getFolderTitle() : string
    {
        return $this->folder_title;
    }

    public function setFolderTitle(string $folder_title) : void
    {
        $this->folder_title = $folder_title;
    }

    public function addMailObject(ilMailCronOrphanedMailsFolderMailObject $mail_obj) : void
    {
        $this->orphaned_mail_objects[$mail_obj->getMailId()] = $mail_obj;
    }

    /**
     * @return ilMailCronOrphanedMailsFolderMailObject[]
     */
    public function getOrphanedMailObjects() : array
    {
        return $this->orphaned_mail_objects;
    }
}
