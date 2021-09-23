<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
*/
class ilFSStorageMail extends ilFileSystemStorage
{
    private int $usr_id = 0;

    public function __construct(int $a_container_id, int $a_usr_id)
    {
        $this->usr_id = $a_usr_id;
        
        parent::__construct(self::STORAGE_DATA, true, $a_container_id);
    
        $this->appendToPath('_' . $this->usr_id);
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
                $path = ilUtil::getDataDir();
                break;
                
            case self::STORAGE_WEB:
                $path = ilUtil::getWebspaceDir();
                break;
        }
        $path = ilUtil::removeTrailingPathSeparators($path);
        $path .= '/';
        
        // Append path prefix
        $path .= ($this->getPathPrefix() . '/');
        
        return str_replace($path, '', $this->getAbsolutePath());
    }
}
