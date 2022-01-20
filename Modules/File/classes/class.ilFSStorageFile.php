<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilFSStorageFile
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFSStorageFile extends ilFileSystemAbstractionStorage
{

    /**
     * ilFSStorageFile constructor.
     *
     * @param int $a_container_id
     */
    public function __construct($a_container_id = 0)
    {
        parent::__construct(self::STORAGE_DATA, true, $a_container_id);
    }


    protected function getPathPostfix(): string
    {
        return 'file';
    }


    /**
     * @return string
     */
    protected function getPathPrefix(): string
    {
        return 'ilFile';
    }
}
