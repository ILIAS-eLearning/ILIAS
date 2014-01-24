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

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesAdvancedMetaData 
*/
class ilAdvancedMDRecordImportFiles
{
	const IMPORT_NAME = 'ilias_adv_md_record';

	private $import_dir = '';
	
	/**
	 * Constructor
	 *
	 * @access public
	 * 
	 */
	public function __construct()
	{
	 	$this->import_dir = ilUtil::getDataDir().'/ilAdvancedMetaData/import';
	 	$this->init();
	}
	
	/**
	 * get import directory
	 *
	 * @access public
	 * 
	 */
	public function getImportDirectory()
	{
	 	return $this->import_dir;
	}
	
	/**
	 * Get import file by creation date
	 *
	 * @access public
	 * @param int creation date (unix time)
	 * @return string absolute path
	 */
	public function getImportFileByCreationDate($a_unix_time)
	{
	 	$unix_time = (int) $a_unix_time;
	 	return $this->getImportDirectory().'/'.self::IMPORT_NAME.'_'.$unix_time.'.xml';
	}
	
	/**
	 * Delete a file 
	 *
	 * @access public
	 * @param int creation date (unix time)
	 * 
	 */
	public function deleteFileByCreationDate($a_unix_time)
	{
	 	$unix_time = (int) $a_unix_time;
	 	return @unlink($this->getImportDirectory().'/'.self::IMPORT_NAME.'_'.$unix_time.'.xml');
	}
	
	
	/**
	 * move uploaded files
	 *
	 * @access public
	 * @param string tmp name
	 * @return int creation time of newly created file. 0 on error
	 */
	public function moveUploadedFile($a_temp_name)
	{
		$creation_time = time();
		$file_name = $this->getImportDirectory().'/'.self::IMPORT_NAME.'_'.$creation_time.'.xml';
		
		if(!ilUtil::moveUploadedFile($a_temp_name,'',$file_name,false))
		{
			return false;
		}
		return $creation_time;
	}
	
	
	
	/**
	 * init function: create import directory, delete old files
	 *
	 * @access private
	 * 
	 */
	private function init()
	{
		if(!@is_dir($this->import_dir))
		{
			ilUtil::makeDirParents($this->import_dir);
		}
	 	
	}
}


?>