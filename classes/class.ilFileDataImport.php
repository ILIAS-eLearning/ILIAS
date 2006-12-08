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
* This class handles all operations on files for the exercise object
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$Id$
* 
*/
require_once("classes/class.ilFileData.php");
				
class ilFileDataImport extends ilFileData
{
	/**
	* path of exercise directory
	* @var string path
	* @access private
	*/
	var $import_path;

	/**
	* Constructor
	* call base constructors
	* checks if directory is writable and sets the optional obj_id
	* @param integereger obj_id
	* @access	public
	*/
	function ilFileDataImport()
	{
		define('IMPORT_PATH','import');
		parent::ilFileData();
		$this->import_path = parent::getPath()."/".IMPORT_PATH;
		
		// IF DIRECTORY ISN'T CREATED CREATE IT
		// STATIC CALL TO AVOID OVERWRITE PROBLEMS
		ilFileDataImport::_initDirectory();
	}

	/**
	* get exercise path 
	* @access	public
	* @return string path
	*/
	function getPath()
	{
		return $this->import_path;
	}

	// PRIVATE METHODS
	function __checkPath()
	{
		if(!@file_exists($this->getPath()))
		{
			return false;
		}
		$this->__checkReadWrite();

		return true;
	}
	/**
	* check if directory is writable
	* overwritten method from base class
	* @access	private
	* @return bool
	*/
	function __checkReadWrite()
	{
		if(is_writable($this->import_path) && is_readable($this->import_path))
		{
			return true;
		}
		else
		{
			$this->ilias->raiseError("Import directory is not readable/writable by webserver",$this->ilias->error_obj->FATAL);
		}
	}
	/**
	* init directory
	* overwritten method
	* @access	public
	* @static
	* @return string path
	*/
	function _initDirectory()
	{
		if(!@file_exists($this->import_path))
		{
			ilUtil::makeDir($this->import_path);
		}
		return true;
	}
}
