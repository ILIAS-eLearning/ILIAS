<?php
/**
* This class handles all operations on files in directory /ilias_data/
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package	mail
*/
require_once("classes/class.ilFile.php");

class ilFileData extends ilFile
{

	/**
	* Constructor
	* setup an mail object
	* @param int user_id
	* @access	public
	*/
	function ilFileData()
	{
		parent::ilFile();
		$this->readPath();
	}

	/**
	* check if path exists and is writable
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
	* read path from ... TODO
	* @param string path
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