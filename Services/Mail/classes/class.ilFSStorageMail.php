<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');
/**
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
*
* @ingroup ServicesMail
*/
class ilFSStorageMail extends ilFileSystemStorage
{
    private $usr_id = 0;
    
    /**
     * Constructor
     *
     * @access public
     * @param int object id of container (e.g file_id or mob_id)
     *
     */
    public function __construct($a_container_id, $a_usr_id)
    {
        $this->usr_id = $a_usr_id;
        
        parent::__construct(self::STORAGE_DATA, true, $a_container_id);
    
        $this->appendToPath('_' . $this->usr_id);
    }
    
    /**
     * Implementation of abstract method
     *
     * @access protected
     *
     */
    protected function getPathPostfix()
    {
        return 'mail';
    }
    
    /**
     * Implementation of abstract method
     *
     * @access protected
     *
     */
    protected function getPathPrefix()
    {
        return 'mail';
    }
    
    public function getRelativePathExMailDirectory()
    {
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
