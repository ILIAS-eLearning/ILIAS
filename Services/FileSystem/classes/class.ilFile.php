<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Base class for all file (directory) operations
* This class is abstract and needs to be extended
*
* @author	Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/
class ilFile
{
    /**
    * Path of directory
    * @var string path
    * @access private
    */
    public $path;

    /**
    * ilias object
    * @var object Ilias
    * @access public
    */
    public $ilias;


    /**
    * Constructor
    * get ilias object
    * @access	public
    */
    public function __construct()
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        $this->ilias = &$ilias;
    }

    /**
    * delete trailing slash of path variables
    * @param	string	path
    * @access	public
    * @return	string	path
    */
    public function deleteTrailingSlash($a_path)
    {
        // DELETE TRAILING '/'
        if (substr($a_path, -1) == '/' or substr($a_path, -1) == "\\") {
            $a_path = substr($a_path, 0, -1);
        }

        return $a_path;
    }
}
