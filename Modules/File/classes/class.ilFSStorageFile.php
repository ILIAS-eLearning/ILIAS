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

/**
 * Class ilFSStorageFile
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFSStorageFile extends ilFileSystemAbstractionStorage
{
    /**
     * ilFSStorageFile constructor.
     */
    public function __construct(int $a_container_id = 0)
    {
        parent::__construct(self::STORAGE_DATA, true, $a_container_id);
    }


    protected function getPathPostfix(): string
    {
        return 'file';
    }


    protected function getPathPrefix(): string
    {
        return 'ilFile';
    }
}
