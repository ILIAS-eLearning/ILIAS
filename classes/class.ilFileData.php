<?php
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
		$path = $this->ilias->ini->readVariable("server","data_dir");
		if(!is_writable($path))
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