<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* This class handles all operations on files in directory /ilias_data/
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package	ilias-mail
*/
require_once("classes/class.ilFile.php");

class ilFileData extends ilFile
{

	/**
	* Constructor
	* class bas constructor and read path of directory from ilias.ini
	* setup an mail object
	* @access	public
	*/
	function ilFileData()
	{
		require_once("classes/class.ilFile.php");
		parent::ilFile();
		$this->readPath();
	}

	/**
	* check if path exists and is writable
	* @param string path to check
	* @access	public
	* @return bool
	*/
	function checkPath($a_path)
	{
		if(is_writable($a_path))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	/**
	* read path from ilias_ini
	* the path is set during the setup routine
	* @access	public
	* @return string path
	*/
	function readPath()
	{
		//$path = $this->ilias->ini->readVariable("server","data_dir");
		if(!is_writable(ILIAS_DATA_DIR))
		{
			$this->ilias->raiseError("DATA DIRECTORY IS NOT WRITABLE",$this->ilias->error_obj->FATAL);
		}
		return $this->path = $this->deleteTrailingSlash($path);
	}
	/**
	* get Path 
	* @access	public
	* @return string path
	*/
	function getPath()
	{
		return $this->path;
	}
}
?>