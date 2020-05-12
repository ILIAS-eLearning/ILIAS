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


/**
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesExercise
 */
class ilFSStorageExc5242
{
    const FACTOR = 100;
    const MAX_EXPONENT = 3;

    private $container_id;
    private $path_conversion = false;

    protected $path;

    /**
     * Constructor
     *
     * @param int	exercise id
     */
    public function __construct($a_container_id = 0, $a_ass_id = 0)
    {
        $this->ass_id = $a_ass_id;

        $this->path_conversion = true;
        $this->container_id = $a_container_id;

        // Get path info
        $this->init();
    }

    /**
     * Create a path from an id: e.g 12345 will be converted to 12/34/<name>_5
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
            $path_string = (implode('/', $path) . '/');
        }
        return $path_string . $a_name . '_' . $a_container_id;
    }

    /**
     * Read path info
     *
     * @access private
     */
    protected function init()
    {
        $this->path = CLIENT_DATA_DIR;

        $this->path = ilUtil::removeTrailingPathSeparators($this->path);
        $this->path .= '/';

        // Append path prefix
        $this->path .= ($this->getPathPrefix() . '/');

        $this->path .= self::_createPathFromId($this->container_id, $this->getPathPostfix());

        $this->path .= "/ass_" . $this->ass_id;

        return true;
    }

    /**
     * Implementation of abstract method
     *
     * @access protected
     *
     */
    protected function getPathPostfix()
    {
        return 'exc';
    }

    /**
     * Implementation of abstract method
     *
     * @access protected
     *
     */
    protected function getPathPrefix()
    {
        return 'ilExercise';
    }
    /**
     * Get assignment files
     */
    public function getFiles()
    {
        $files = array();
        if (!is_dir($this->path)) {
            return $files;
        }

        $dp = opendir($this->path);
        while ($file = readdir($dp)) {
            if (!is_dir($this->path . '/' . $file)) {
                $files[] = array(
                    'name' => $file,
                    'size' => filesize($this->path . '/' . $file),
                    'ctime' => filectime($this->path . '/' . $file),
                    'fullpath' => $this->path . '/' . $file);
            }
        }
        closedir($dp);
        $files = ilUtil::sortArray($files, "name", "asc");
        return $files;
    }
}
