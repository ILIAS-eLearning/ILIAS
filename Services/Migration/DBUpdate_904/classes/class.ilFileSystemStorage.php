<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once('Services/Migration/DBUpdate_904/classes/class.ilUpdateUtils.php');
abstract class ilFileSystemStorage
{
    const STORAGE_WEB = 1;
    const STORAGE_DATA = 2;
    
    const FACTOR = 100;
    const MAX_EXPONENT = 3;
    
    
    private $container_id;
    private $storage_type;
    private $path_conversion = false;
    
    private $path;
    private $short_path;
    
    /**
     * Constructor
     *
     * @access public
     * @param int storage type
     * @param bool En/Disable automatic path conversion. If enabled files with id 123 will be stored in directory files/1/file_123
     * @param int object id of container (e.g file_id or mob_id)
     *
     */
    public function __construct($a_storage_type, $a_path_conversion, $a_container_id)
    {
        $this->storage_type = $a_storage_type;
        $this->path_conversion = $a_path_conversion;
        $this->container_id = $a_container_id;
        
        // Get path info
        $this->init();
    }
    
    /**
     * Create a path from an id: e.g 12345 will be converted to 12/34/<name>_5
     *
     * @access public
     * @static
     *
     * @param int container id
     * @param string name
     */
    public static function _createPathFromId($a_container_id, $a_name)
    {
        $path = array();
        $found = false;
        $num = $a_container_id;
        for ($i = self::MAX_EXPONENT; $i > 0;$i--) {
            $factor = pow(self::FACTOR, $i);
            if (($tmp = (int) ($num / $factor)) or $found) {
                $path[] = $tmp;
                $num = $num % $factor;
                $found = true;
            }
        }

        if (count($path)) {
            $path_string = (implode('/', $path));
        }
        return $path_string;
    }
    
    /**
     * Get path prefix. Prefix that will be prepended to the path
     * No trailing slash. E.g ilFiles for files
     *
     * @abstract
     * @access protected
     *
     * @return string path prefix e.g files
     */
    abstract protected function getPathPrefix();
    
    /**
     * Get directory name. E.g for files => file
     * Only relative path, no trailing slash
     * '_<obj_id>' will be appended automatically
     *
     * @abstract
     * @access protected
     *
     * @return string directory name
     */
    abstract protected function getPathPostfix();
    
    /**
     * Create directory
     *
     * @access public
     *
     */
    public function create()
    {
        if (!file_exists($this->short_path)) {
            ilUpdateUtils::makeDirParents(ilUpdateUtils::removeTrailingPathSeparators($this->short_path));
        }
        return true;
    }
    
    
    /**
     * Get absolute path of storage directory
     *
     * @access public
     *
     */
    public function getAbsolutePath()
    {
        return $this->path;
    }
    
    public function getShortPath()
    {
        return $this->short_path;
    }
    
    public function rename($from, $to)
    {
        return @rename($from, $to);
    }
    
    /**
     * Read path info
     *
     * @access private
     */
    private function init()
    {
        switch ($this->storage_type) {
            case self::STORAGE_DATA:
                $this->path = ilUpdateUtils::getDataDir();
                break;
                
            case self::STORAGE_WEB:
                $this->path = ilUpdateUtils::getWebspaceDir();
                break;
        }
        $this->path = ilUpdateUtils::removeTrailingPathSeparators($this->path);
        $this->path .= '/';
        
        // Append path prefix
        $this->path .= ($this->getPathPrefix() . '/');
        
        if ($this->path_conversion) {
            if ($path_id = self::_createPathFromId($this->container_id, $this->getPathPostfix())) {
                $this->path = $this->path . '/' . $path_id;
                $this->path .= '/';
                $this->short_path = $this->path;
            } else {
                $this->short_path = $this->path;
            }
            $this->path .= ($this->getPathPostfix() . '_' . $this->container_id);
        } else {
            $this->short_path = $this->path;
            $this->path .= ($this->getPathPostfix() . '_' . $this->container_id);
        }
        return true;
    }
}
