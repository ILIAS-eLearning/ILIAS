<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    protected function getPathPostfix() : string
    {
        return 'mail';
    }

    protected function getPathPrefix() : string
    {
        return 'mail';
    }

    public function getRelativePathExMailDirectory() : string
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
