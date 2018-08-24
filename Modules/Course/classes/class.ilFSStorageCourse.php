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

include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ModulesCourse 
*/
class ilFSStorageCourse extends ilFileSystemStorage
{
	const MEMBER_EXPORT_DIR = 'memberExport';
	const INFO_DIR = 'info';
	const ARCHIVE_DIR = 'archives';
	
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
	 * Clone course data directory
	 *
	 * @access public
	 * @static
	 *
	 * @param string obj_id source
	 * @param string obj_id target
	 */
	public static function _clone($a_source_id,$a_target_id)
	{
		$source = new ilFSStorageCourse($a_source_id);
		$target = new ilFSStorageCourse($a_target_id);
		
		$target->create();
		ilFileSystemStorage::_copyDirectory($source->getAbsolutePath(),$target->getAbsolutePath());
		
		// Delete member export files
		$target->deleteDirectory($target->getMemberExportDirectory());
		
		unset($source);
		unset($target);
		return true;
	}
	
	// Info files
	/**
	 * init info directory
	 *
	 * @access public
	 * 
	 */
	public function initInfoDirectory()
	{
	 	ilUtil::makeDirParents($this->getInfoDirectory());
	}
	
	/**
	 * Get course info directory
	 *
	 * @access public
	 * 
	 */
	public function getInfoDirectory()
	{
	 	return $this->getAbsolutePath().'/'.self::INFO_DIR;
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
	
	// ARCHIVE Methods
	/**
	 * init Archive Directory
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function initArchiveDirectory()
	{
	 	ilUtil::makeDirParents($this->getArchiveDirectory());
	}
	
	/**
	 * Get archive directory
	 *
	 * @access public
	 * 
	 */
	public function getArchiveDirectory()
	{
	 	return $this->getAbsolutePath().'/'.self::ARCHIVE_DIR;
	}
	
	/**
	 * Add subdirectory for archives
	 *
	 * @access public
	 * @param string archive subdirectory name
	 *
	 */
	public function addArchiveSubDirectory($a_name)
	{
	 	ilUtil::makeDirParents($this->getArchiveDirectory().'/'.$a_name);
	}
	
	/**
	 * Write archive string to file
	 *
	 * @access public
	 * @param string relative filename
	 * 
	 */
	public function writeArchiveFile($a_data,$a_rel_name)
	{
	 	if(!$this->writeToFile($a_data,$this->getArchiveDirectory().'/'.$a_rel_name))
	 	{
			$this->log->write('Cannot write to file: '.$this->getArchiveDirectory().'/'.$a_rel_name);
			return false;
	 	}
		return true;
	}
	
	/**
	 * Zip archive directory
	 *
	 * @access public
	 * @param string relative name of directory to zip
	 * @param string zip archive name
	 * @return int filesize of zip archive
	 * 
	 */
	public function zipArchive($a_rel_name,$a_zip_name)
	{
		if(ilUtil::zip($this->getArchiveDirectory().'/'.$a_rel_name,$this->getArchiveDirectory().'/'.$a_zip_name))
		{
			return filesize($this->getArchiveDirectory().'/'.$a_zip_name);
		}
		return 0;
	}
	
	/**
	 * Delete one archive
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function deleteArchive($a_rel_name)
	{
		$this->deleteFile($this->getArchiveDirectory().'/'.$a_rel_name.'.zip');
		$this->deleteDirectory($this->getArchiveDirectory().'/'.$a_rel_name);
	}
	
	public function createArchiveOnlineVersion($a_rel_name)
	{
		ilUtil::makeDirParents(CLIENT_WEB_DIR.'/courses/'.$a_rel_name);
		ilUtil::rCopy($this->getArchiveDirectory().'/'.$a_rel_name,CLIENT_WEB_DIR.'/courses/'.$a_rel_name);

		return true;
	}
	
	public function getOnlineLink($a_rel_name)
	{
		return ilUtil::getWebspaceDir('filesystem').'/courses/'.$a_rel_name.'/index.html';
	}
	
	
	/**
	 * Implementation of abstract method
	 *
	 * @access protected
	 * 
	 */
	protected function getPathPostfix()
	{
	 	return 'crs';
	}
	
	/**
	 * Implementation of abstract method
	 *
	 * @access protected
	 * 
	 */
	protected function getPathPrefix()
	{
	 	return 'ilCourse';
	}
	
}




?>