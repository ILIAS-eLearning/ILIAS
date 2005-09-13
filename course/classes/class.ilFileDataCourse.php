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
* This class handles all operations of archive files for the course object
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package	ilias-course
*/
require_once("classes/class.ilFileData.php");
				
class ilFileDataCourse extends ilFileData
{
	/**
	* path of exercise directory
	* @var string path
	* @access private
	*/
	var $course_path;

	var $course_obj = null;

	/**
	* Constructor
	* call base constructors
	* checks if directory is writable and sets the optional obj_id
	* @param integereger obj_id
	* @access	public
	*/
	function ilFileDataCourse(&$course_obj)
	{
		define('COURSE_PATH','course');
		parent::ilFileData();
		$this->course_path = parent::getPath()."/".COURSE_PATH;
		$this->course_obj =& $course_obj;

		// IF DIRECTORY ISN'T CREATED CREATE IT
		if(!$this->__checkPath())
		{
			$this->__initDirectory();
		}
		// Check import dir
		$this->__checkImportPath();
	}
	
	function getArchiveFile($a_rel_name)
	{
		if(@file_exists($this->course_path.'/'.$a_rel_name.'.zip'))
		{
			return $this->course_path.'/'.$a_rel_name.'.zip';
		}
		if(@file_exists($this->course_path.'/'.$a_rel_name.'.pdf'))
		{
			return $this->course_path.'/'.$a_rel_name.'.pdf';
		}
		return false;
	}



	function deleteArchive($a_rel_name)
	{
		$this->deleteZipFile($this->course_path.'/'.$a_rel_name.'.zip');
		$this->deleteDirectory($this->course_path.'/'.$a_rel_name);
		$this->deleteDirectory(CLIENT_WEB_DIR.'/courses/'.$a_rel_name);
		$this->deletePdf($this->course_path.'/'.$a_rel_name.'.pdf');

		return true;
	}
	function deleteZipFile($a_abs_name)
	{
		if(@file_exists($a_abs_name))
		{
			@unlink($a_abs_name);

			return true;
		}
		return false;
	}
	function deleteDirectory($a_abs_name)
	{
		if(file_exists($a_abs_name))
		{
			ilUtil::delDir($a_abs_name);
			
			return true;
		}
		return false;
	}
	function deletePdf($a_abs_name)
	{
		if(@file_exists($a_abs_name))
		{
			@unlink($a_abs_name);

			return true;
		}
		return false;
	}

	function copy($a_from,$a_to)
	{
		if(@file_exists($a_from))
		{
			@copy($a_from,$this->getCoursePath().'/'.$a_to);

			return true;
		}
		return false;
	}

	function rCopy($a_from,$a_to)
	{
		ilUtil::rCopy($a_from,$this->getCoursePath().'/'.$a_to);

		return true;
	}


	function addDirectory($a_rel_name)
	{
		ilUtil::makeDir($this->getCoursePath().'/'.$a_rel_name);

		return true;
	}

	function writeToFile($a_data,$a_rel_name)
	{
		if(!$fp = @fopen($this->getCoursePath().'/'.$a_rel_name,'w+'))
		{
			die("Cannot open file: ".$this->getCoursePath().'/'.$a_rel_name);
		}
		@fwrite($fp,$a_data);

		return true;
	}

	function zipFile($a_rel_name,$a_zip_name)
	{
		ilUtil::zip($this->getCoursePath().'/'.$a_rel_name,$this->getCoursePath().'/'.$a_zip_name);

		// RETURN filesize
		return filesize($this->getCoursePath().'/'.$a_zip_name);
	}


	/**
	* get exercise path 
	* @access	public
	* @return string path
	*/
	function getCoursePath()
	{
		return $this->course_path;
	}

	function createOnlineVersion($a_rel_name)
	{
		ilUtil::makeDir(CLIENT_WEB_DIR.'/courses/'.$a_rel_name);
		ilUtil::rCopy($this->getCoursePath().'/'.$a_rel_name,CLIENT_WEB_DIR.'/courses/'.$a_rel_name);

		return true;
	}

	function getOnlineLink($a_rel_name)
	{
		return ilUtil::getWebspaceDir('filesystem').'/courses/'.$a_rel_name.'/index.html';
	}


	// METHODS FOR XML IMPORT OF COURSE
	function createImportFile($a_tmp_name,$a_name)
	{
		ilUtil::makeDir($this->getCoursePath().'/import/crs_'.$this->course_obj->getId());

		ilUtil::moveUploadedFile($a_tmp_name,
								 $a_name, 
								 $this->getCoursePath().'/import/crs_'.$this->course_obj->getId().'/'.$a_name);
		$this->import_file_info = pathinfo($this->getCoursePath().'/import/crs_'.$this->course_obj->getId().'/'.$a_name);

	}

	function unpackImportFile()
	{
		return ilUtil::unzip($this->getCoursePath().'/import/crs_'.$this->course_obj->getId().'/'.$this->import_file_info['basename']);
	}

	function validateImportFile()
	{
		if(!is_dir($this->getCoursePath().'/import/crs_'.$this->course_obj->getId()).'/'.
		   basename($this->import_file_info['basename'],'.zip'))
		{
			return false;
		}
		if(!file_exists($this->getCoursePath().'/import/crs_'.$this->course_obj->getId()
						.'/'.basename($this->import_file_info['basename'],'.zip')
						.'/'.basename($this->import_file_info['basename'],'.zip').'.xml'))
		{
			return false;
		}
	}

	function getImportFile()
	{
		return $this->getCoursePath().'/import/crs_'.$this->course_obj->getId()
			.'/'.basename($this->import_file_info['basename'],'.zip')
			.'/'.basename($this->import_file_info['basename'],'.zip').'.xml';
	}
	



	// PRIVATE METHODS
	function __checkPath()
	{
		if(!@file_exists($this->getCoursePath()))
		{
			return false;
		}
		if(!@file_exists(CLIENT_WEB_DIR.'/courses'))
		{
			ilUtil::makeDir(CLIENT_WEB_DIR.'/courses');
		}

			
		$this->__checkReadWrite();

		return true;
	}
	
	function __checkImportPath()
	{
		if(!@file_exists($this->getCoursePath().'/import'))
		{
			ilUtil::makeDir($this->getCoursePath().'/import');
		}

		if(!is_writable($this->getCoursePath().'/import') or !is_readable($this->getCoursePath().'/import'))
		{
			$this->ilias->raiseError("Course import path is not readable/writable by webserver",$this->ilias->error_obj->FATAL);
		}
	}

	/**
	* check if directory is writable
	* overwritten method from base class
	* @access	private
	* @return bool
	*/
	function __checkReadWrite()
	{
		if(is_writable($this->course_path) && is_readable($this->course_path))
		{
			return true;
		}
		else
		{
			$this->ilias->raiseError("Exercise directory is not readable/writable by webserver",$this->ilias->error_obj->FATAL);
		}
	}
	/**
	* init directory
	* overwritten method
	* @access	public
	* @return string path
	*/
	function __initDirectory()
	{
		if(is_writable($this->getPath()))
		{
			ilUtil::makeDir($this->getPath().'/'.COURSE_PATH);
			$this->course_path = $this->getPath().'/'.COURSE_PATH;
			
			return true;
		}
		return false;
	}
}
