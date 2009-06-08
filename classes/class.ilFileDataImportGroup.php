<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* This class handles all operations on files for the exercise object
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
*/
require_once("./classes/class.ilFileDataImport.php");
				
class ilFileDataImportGroup extends ilFileDataImport
{
	/**
	* path of exercise directory
	* @var string path
	* @access private
	*/
	var $group_path;

	var $files;
	var $xml_file;
	

	/**
	* Constructor
	* call base constructors
	* checks if directory is writable and sets the optional obj_id
	* @param integereger obj_id
	* @access	public
	*/
	function ilFileDataImportGroup()
	{
		define('GROUP_IMPORT_PATH','group');
		parent::ilFileDataImport();
		$this->group_path = parent::getPath()."/".GROUP_IMPORT_PATH;

		// IF DIRECTORY ISN'T CREATED CREATE IT
		// CALL STATIC TO AVOID OVERWRITE PROBLEMS
		ilFileDataImportGroup::_initDirectory();
		$this->__readFiles();
	}

	function getFiles()
	{
		return $this->files ? $this->files : array();
	}

	function getXMLFile()
	{
		return $this->xml_file;
	}

	function getObjectFile()
	{
		return $this->object_file;
	}

	/**
	* store uploaded file in filesystem
	* @param array HTTP_POST_FILES
	* @access	public
	* @return bool
	*/
	function storeUploadedFile($a_http_post_file)
	{
		// TODO: 
		// CHECK UPLOAD LIMIT
		// 

		if(isset($a_http_post_file) && $a_http_post_file['size'])
		{
			// DELETE OLD FILES
			$this->unlinkLast();
			
			ilUtil::moveUploadedFile($a_http_post_file['tmp_name'],
				$a_http_post_file['name'], $this->getPath().'/'.$a_http_post_file['name']);

			// CHECK IF FILE WITH SAME NAME EXISTS
			//move_uploaded_file($a_http_post_file['tmp_name'],$this->getPath().'/'.$a_http_post_file['name']);

			// UPDATE FILES LIST
			$this->__readFiles();
			return true;
		}
		else
		{
			return false;
		}
	}
	function findXMLFile($a_dir = '')
	{
		$a_dir = $a_dir ? $a_dir : $this->getPath();

		$this->__readFiles($a_dir);

		foreach($this->getFiles() as $file_data)
		{
			if(is_dir($file_data["abs_path"]))
			{
				$this->findXMLFile($file_data["abs_path"]);
			}
			if(($tmp = explode(".",$file_data["name"])) !== false)
			{
				if($tmp[count($tmp) - 1] == "xml")
				{
					return $this->xml_file = $file_data["abs_path"];
				}
			}
		}
		return $this->xml_file;
	}

	function findObjectFile($a_file,$a_dir = '')
	{
		$a_dir = $a_dir ? $a_dir : $this->getPath();

		$this->__readFiles($a_dir);

		foreach($this->getFiles() as $file_data)
		{
			if(is_dir($file_data["abs_path"]))
			{
				$this->findObjectFile($a_file,$file_data["abs_path"]);
			}
			if($file_data["name"] == $a_file)
			{
				return $this->object_file = $file_data["abs_path"];
			}
		}
		return $this->object_file;
	}
			


	function unzip()
	{
		foreach($this->getFiles() as $file_data)
		{
			ilUtil::unzip($file_data["abs_path"]);
			
			return true;
		}
		return false;
	}

	/**
	* get exercise path 
	* @access	public
	* @return string path
	*/
	function getPath()
	{
		return $this->group_path;
	}

	function unlinkLast()
	{
		foreach($this->getFiles() as $file_data)
		{
			if(is_dir($file_data["abs_path"]))
			{
				ilUtil::delDir($file_data["abs_path"]);
			}
			else
			{
				unlink($file_data["abs_path"]);
			}
		}
		return true;
	}
	// PRIVATE METHODS
	function __readFiles($a_dir = '')
	{
		$a_dir = $a_dir ? $a_dir : $this->getPath();

		$this->files = array();
		$dp = opendir($a_dir);

		while($file = readdir($dp))
		{
			if($file == "." or $file == "..")
			{
				continue;
			}
			$this->files[] = array(
				'name'			=> $file,
				'abs_path'		=> $a_dir."/".$file,
				'size'			=> filesize($a_dir."/".$file),
				'ctime'			=> ilFormat::formatDate(date('Y-m-d H:i:s',filectime($a_dir.'/'.$file))));
		}
		closedir($dp);

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
		if(is_writable($this->group_path) && is_readable($this->group_path))
		{
			return true;
		}
		else
		{
			$this->ilias->raiseError("Group import directory is not readable/writable by webserver",$this->ilias->error_obj->FATAL);
		}
	}
	/**
	* init directory
	* overwritten method
	* @access	public
	* @static
	* @return boolean
	*/
	function _initDirectory()
	{
		if(!@file_exists($this->group_path))
		{
			ilUtil::makeDir($this->group_path);
		}
		return true;
	}
}
