<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* This class handles all operations of export files for the group object
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
*/
require_once("classes/class.ilFileData.php");
				
define('GROUP_PATH','group');

class ilFileDataGroup extends ilFileData
{
	/**
	* path of exercise directory
	* @var string path
	* @access private
	*/
	var $group_path;

	var $group_obj = null;

	/**
	* Constructor
	* call base constructors
	* checks if directory is writable and sets the optional obj_id
	* @param integereger obj_id
	* @access	public
	*/
	function ilFileDataGroup($group_obj)
	{
		global $ilias;

		parent::ilFileData();

		$this->ilias =& $ilias;
		$this->group_path = parent::getPath()."/".GROUP_PATH;
		$this->group_obj =& $group_obj;

		// IF DIRECTORY ISN'T CREATED CREATE IT
		if(!$this->__checkPath())
		{
			$this->__initDirectory();
		}
		// Check import dir
		#$this->__checkImportPath();
	}

	// METHODS FOR XML IMPORT OF Groups
	function createImportFile($a_tmp_name,$a_name)
	{
		ilUtil::moveUploadedFile($a_tmp_name,
								 $a_name, 
								 $this->getGroupPath().'/import/'.$a_name);
		$this->import_file_info = pathinfo($this->getGroupPath().'/import/'.$a_name);

	}

	function unpackImportFile()
	{
		return ilUtil::unzip($this->getGroupPath().'/import/'.$this->import_file_info['basename']);
	}

	function validateImportFile()
	{
		if(!is_dir($this->getGroupPath().'/import/'.basename($this->import_file_info['basename'],'.zip')))
		{
			return false;
		}
		if(!file_exists($this->getGroupPath().'/import'.
						'/'.basename($this->import_file_info['basename'],'.zip').
						'/'.basename($this->import_file_info['basename'],'.zip').'.xml'))
		{
			return false;
		}
		return true;
	}

	function getImportFile()
	{
		return $this->getGroupPath().'/import'
			.'/'.basename($this->import_file_info['basename'],'.zip')
			.'/'.basename($this->import_file_info['basename'],'.zip').'.xml';
	}



	// Export functions
	function getExportFile($a_rel_name)
	{
		if(@file_exists($abs = $this->group_path.'/grp_'.$this->group_obj->getId().'/'.$a_rel_name))
		{
			return $abs;
		}
		return false;
	}

	function getExportFiles()
	{
		$path = $this->getGroupPath().'/grp_'.$this->group_obj->getId();

		
		if(!@file_exists($path) or !is_readable($path))
		{
			return array();
		}
		$dp = dir($path);

		// Get file
		while($entry = $dp->read())
		{
			if ($entry != "." and
				$entry != ".." and
				substr($entry, -4) == ".zip" and
				ereg("^[0-9]{10}_{2}[0-9]+_{2}(grp_)*[0-9]+\.zip\$", $entry))
				{
					$files[$entry] = array("file" => $entry,
										   "size" => filesize($path."/".$entry),
										   'type' => "XML");
				}
		}
		return $files ? $files : array();
	}

	function deleteFile($a_rel_name)
	{
		if(@file_exists($abs = $this->group_path.'/grp_'.$this->group_obj->getId().'/'.$a_rel_name))
		{
			@unlink($abs);

			return true;
		}
		return false;
	}

	function deleteDirectory($a_rel_dir)
	{
		if(!@file_exists($abs = $this->getGroupPath().'/grp_'.$this->group_obj->getId().'/'.$a_rel_dir))
		{
			return false;
		}
		ilUtil::delDir($abs);

		return true;
	}

	// Static
	function _deleteAll($a_obj_id)
	{
		ilUtil::delDir(CLIENT_DATA_DIR.'/'.GROUP_PATH.'/grp_'.$a_obj_id);

		return true;
	}

	function addGroupDirectory()
	{
		if(@file_exists($this->getGroupPath().'/grp_'.$this->group_obj->getId()))
		{
			return false;
		}
		ilUtil::makeDir($this->getGroupPath().'/grp_'.$this->group_obj->getId());

		return true;
	}

	function addImportDirectory()
	{
		if(@file_exists($this->getGroupPath().'/import'))
		{
			return false;
		}
		ilUtil::makeDir($this->getGroupPath().'/import');

		return true;
	}		
		

	function addDirectory($a_rel_name)
	{
		if(@file_exists($this->getGroupPath().'/grp_'.$this->group_obj->getId().'/'.$a_rel_name))
		{
			return false;
		}
		ilUtil::makeDir($this->getGroupPath().'/grp_'.$this->group_obj->getId().'/'.$a_rel_name);

		return true;
	}

	function writeToFile($a_data,$a_rel_name)
	{
		if(!$fp = @fopen($this->getGroupPath().'/grp_'.$this->group_obj->getId().'/'.$a_rel_name,'w+'))
		{
			die("Cannot open file: ".$this->getGroupPath().'/'.$a_rel_name);
		}
		@fwrite($fp,$a_data);

		return true;
	}

	function zipFile($a_rel_name,$a_zip_name)
	{
		ilUtil::zip($this->getGroupPath().'/grp_'.$this->group_obj->getId().'/'.$a_rel_name,
					$this->getGroupPath().'/grp_'.$this->group_obj->getId().'/'.$a_zip_name);

		return true;
	}


	/**
	* get exercise path 
	* @access	public
	* @return string path
	*/
	function getGroupPath()
	{
		return $this->group_path;
	}


	// PRIVATE METHODS
	function __checkPath()
	{
		if(!@file_exists($this->getGroupPath()))
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
		if(is_writable($this->group_path) && is_readable($this->group_path))
		{
			return true;
		}
		else
		{
			$this->ilias->raiseError("Group directory is not readable/writable by webserver",$this->ilias->error_obj->FATAL);
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
			ilUtil::makeDir($this->getPath().'/'.GROUP_PATH);
			$this->group_path = $this->getPath().'/'.GROUP_PATH;
			
			return true;
		}
		return false;
	}
}
