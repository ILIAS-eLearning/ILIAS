<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
*
* @ingroup ServicesMigration
*/
class ilFSStorageMailMailMigration extends ilFileSystemStorageMailMigration
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
                $path = ilUpdateUtilsMailMigration::getDataDir();
                break;
                
            case self::STORAGE_WEB:
                $path = ilUpdateUtilsMailMigration::getWebspaceDir();
                break;
        }
        $path = ilUpdateUtilsMailMigration::removeTrailingPathSeparators($path);
        $path .= '/';
        
        // Append path prefix
        $path .= ($this->getPathPrefix() . '/');
        
        return str_replace($path, '', $this->getAbsolutePath());
    }
}
