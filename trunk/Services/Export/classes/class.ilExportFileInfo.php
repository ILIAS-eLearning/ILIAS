<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @classDescription Stores information of creation date and versions of export files
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesExport
*/
class ilExportFileInfo
{
	const CURRENT_VERSION = "4.1.0";
	
	
	private $obj_id = 0;
	private $version = self::CURRENT_VERSION;
	private $export_type = '';
	private $file_name = '';
	private $create_date = null;
	
	/**
	 * Constructor
	 */
	public function __construct($a_obj_id, $a_export_type = '',$a_filename = '')
	{
		$this->obj_id = $a_obj_id;
		$this->export_type = $a_export_type;
		$this->file_name = $a_filename;
		if($this->getObjId() and $this->getExportType() and $this->getFilename())
		{
			$this->read();
		}
	}

	/**
	 * Lookup last export 
	 * @param object $a_obj_id
	 * @param string type xml | html | scorm2004...
	 * @param string version 
	 * @return object ilExportFileInfo
	 */
	public static function lookupLastExport($a_obj_id,$a_type,$a_version = '')
	{
		global $ilDB;
		
		$query = "SELECT * FROM export_file_info ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer').' '.
			"AND export_type = ".$ilDB->quote($a_type,'text').' '.
			"ORDER BY create_date DESC";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!$a_version or $row->version == $a_version)
			{
				return new ilExportFileInfo($row->obj_id,$row->export_type,$row->filename);
			}
		}
		return null;
	}

	
	/**
	 * Delete all export entries by obj_id
	 * @param object $a_obj_id
	 * @return 
	 */
	public static function deleteByObjId($a_obj_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM export_file_info WHERE obj_id = ".$ilDB->quote($a_obj_id));
		return true;
	}
	
	
	
	/**
	 * set export type
	 * @param string $a_type xml | html ...
	 * @return 
	 */
	public function setExportType($a_type)
	{
		$this->export_type = $a_type;
	}
	
	/**
	 * get export type
	 * @return string export type
	 */
	public function getExportType()
	{
		return $this->export_type;
	}
	
	/**
	 * set filename
	 * @param string $a_name 
	 * @return 
	 */
	public function setFilename($a_name)
	{
		$this->file_name = $a_name;
	}
	
	/**
	 * get filename
	 * @return 
	 */
	public function getFilename()
	{
		return $this->file_name;
	}
	
	public function getBasename($a_ext = '.zip')
	{
		return basename($this->getFilename(),$a_ext);
	}
	
	/**
	 * Set obj id
	 * @param object $a_id
	 * @return 
	 */
	public function setObjId($a_id)
	{
		$this->obj_id = $a_id;
	}
	
	/**
	 * Get obj id
	 * @return 
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}

	/** 
	 * set version
	 * @return 
	 */
	public function setVersion($a_version)
	{
		$this->version = $a_version;
	}
	
	/**
	 * get version
	 * @return 
	 */
	public function getVersion()
	{
		return $this->version;
	}
	
	/**
	 * get creation date
	 * @return ilDateTime $date
	 */
	public function getCreationDate()
	{
		return $this->create_date instanceof ilDateTime ? $this->create_date : new ilDateTime(time(),IL_CAL_UNIX);
	}
	
	/**
	 * set creation date
	 * @param ilDateTime $dt [optional]
	 * @return 
	 */
	public function setCreationDate(ilDateTime $dt = null)
	{
		$this->create_date = $dt;
	}
	
	/**
	 * Create new export entry
	 * @return 
	 */
	public function create()
	{
		global $ilDB;
		
		$query = "INSERT INTO export_file_info (obj_id, export_type, filename, version, create_date) ".
			"VALUES ( ".
			$ilDB->quote($this->getObjId(),'integer').', '.
			$ilDB->quote($this->getExportType(),'text').', '.
			$ilDB->quote($this->getFilename(),'text').', '.
			$ilDB->quote($this->getVersion(),'text').', '.
			$ilDB->quote($this->getCreationDate()->get(IL_CAL_DATETIME,'',ilTimeZone::UTC),'timestamp').' '.
			")";
		$ilDB->manipulate($query);
	}
	
	/**
	 * Delete one export entry
	 * @return 
	 */
	public function delete()
	{
		global $ilDB;
		
		$ilDB->manipulate('DELETE FROM export_file_info '.
			'WHERE obj_id = '.$ilDB->quote($this->getObjId(),'integer').' '.
			'AND filename = '.$ilDB->quote($this->getFilename(),'text')
		);
		return true;
	}
	
	/**
	 * Read
	 * @return 
	 */
	protected function read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM export_file_info ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId(),'integer').' '.
			"AND export_type = ".$ilDB->quote($this->getExportType(),'text').' '.
			"AND filename = ".$ilDB->quote($this->getFilename(),'text');
			
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setVersion($row->version);
			$this->setCreationDate(new ilDateTime($row->create_date,IL_CAL_DATETIME,ilTimeZone::UTC));
		}
		return true;
	}
}
?>