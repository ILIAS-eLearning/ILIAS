<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ModulesGroup 
*/
class ilFSStorageGroup extends ilFileSystemStorage
{
	const MEMBER_EXPORT_DIR = 'memberExport';
	
	private $log;
	/**
	 * Constructor
	 *
	 * @access public
	 * 
	 */
	public function __construct($a_container_id = 0)
	{
		global $log;
		
		$this->log = $log;
	 	parent::__construct(ilFileSystemStorage::STORAGE_DATA,true,$a_container_id);
	}
	
	/**
	 * Init export directory and create it if it does not exist
	 *
	 * @access public
	 * 
	 */
	public function initMemberExportDirectory()
	{
	 	ilUtil::makeDirParents($this->getMemberExportDirectory());
	}
	
	/**
	 * Get path of export directory
	 *
	 * @access public
	 * 
	 */
	public function getMemberExportDirectory()
	{
	 	return $this->getAbsolutePath().'/'.self::MEMBER_EXPORT_DIR;
	}
	
	/**
	 * Add new export file
	 *
	 * @access public
	 * @param string data
	 * @param string filename
	 * 
	 */
	public function addMemberExportFile($a_data,$a_rel_name)
	{
	 	$this->initMemberExportDirectory();
	 	if(!$this->writeToFile($a_data,$this->getMemberExportDirectory().'/'.$a_rel_name))
	 	{
			$this->log->write('Cannot write to file: '.$this->getMemberExportDirectory().'/'.$a_rel_name);
			return false;
	 	}

		return true;
	 	
	}
	
	/**
	 * Get all member export files
	 *
	 * @access public
	 * 
	 */
	public function getMemberExportFiles()
	{
		if(!@is_dir($this->getMemberExportDirectory()))
		{
			return array();
		}
		
		$files = array();
		$dp = @opendir($this->getMemberExportDirectory());

		while($file = readdir($dp))
		{
			if(is_dir($file))
			{
				continue;
			}
			
			if(preg_match("/^([0-9]{10})_[a-zA-Z]*_export_([a-z]+)_([0-9]+)\.[a-z]+$/",$file,$matches) and $matches[3] == $this->getContainerId())
			{
				$timest = $matches[1];
				$file_info['name'] = $matches[0];
				$file_info['timest'] = $matches[1];
				$file_info['type'] = $matches[2];
				$file_info['id'] = $matches[3];
				$file_info['size'] = filesize($this->getMemberExportDirectory().'/'.$file);
				
				$files[$timest] = $file_info;
			}
		}
		closedir($dp);
		return $files ? $files : array();
	}
	
	public function getMemberExportFile($a_name)
	{
		$file_name = $this->getMemberExportDirectory().'/'.$a_name;
		
		if(@file_exists($file_name))
		{
			return file_get_contents($file_name);
		}
	}
	
	/**
	 * Delete Member Export File
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function deleteMemberExportFile($a_export_name)
	{
	 	return $this->deleteFile($this->getMemberExportDirectory().'/'.$a_export_name);
	}
	
	
	
	
	
	/**
	 * Implementation of abstract method
	 *
	 * @access protected
	 * 
	 */
	protected function getPathPostfix()
	{
	 	return 'grp';
	}
	
	/**
	 * Implementation of abstract method
	 *
	 * @access protected
	 * 
	 */
	protected function getPathPrefix()
	{
	 	return 'ilGroup';
	}
	
}
?>