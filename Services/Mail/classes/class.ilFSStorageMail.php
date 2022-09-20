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

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilFSStorageMail extends ilFileSystemAbstractionStorage
{
    public function __construct(int $a_container_id, int $a_usr_id)
    {
        parent::__construct(self::STORAGE_DATA, true, $a_container_id);

        $this->appendToPath('_' . $a_usr_id);
    }

    protected function getPathPostfix(): string
    {
        return 'mail';
    }

    protected function getPathPrefix(): string
    {
        return 'mail';
    }

    public function getRelativePathExMailDirectory(): string
    {
        $path = '';
        switch ($this->getStorageType()) {
            case self::STORAGE_DATA:
                $path = ilFileUtils::getDataDir();
                break;

            case self::STORAGE_WEB:
                $path = ilFileUtils::getWebspaceDir();
                break;
        }
        $path = ilFileUtils::removeTrailingPathSeparators($path);
        $path .= '/';

        $path .= ($this->getPathPrefix() . '/');

        return str_replace($path, '', $this->getAbsolutePath());
    }
}
