<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/WebServices/ECS/classes/class.ilRemoteObjectBase.php');

/** 
* Remote file app class
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ingroup ModulesRemoteFile
*/

class ilObjRemoteFile extends ilRemoteObjectBase
{
	const DB_TABLE_NAME = "rfil_settings";
	
	protected $version;
	protected $version_tstamp;

	public function initType()
	{
		$this->type = "rfil";
	}
	
	protected function getTableName()
	{
		return self::DB_TABLE_NAME;
	}
	
	protected function getECSObjectType()
	{
		return "/campusconnect/files";
	}
	
	/**
	 * Set version 
	 *
	 * @param int $a_version
	 */
	public function setVersion($a_version)
	{
	 	$this->version = (int)$a_version;
	}
	
	/**
	 * get version
	 *
	 * @return int
	 */
	public function getVersion()
	{
	 	return $this->version;
	}
	
	/**
	 * Set version timestamp
	 *
	 * @param int $a_version
	 */
	public function setVersionDateTime($a_tstamp)
	{
	 	$this->version_tstamp = (int)$a_tstamp;
	}
	
	/**
	 * get version timestamp
	 *
	 * @return int
	 */
	public function getVersionDateTime()
	{
	 	return $this->version_tstamp;
	}
	
	protected function doCreateCustomFields(array &$a_fields)
	{
		$a_fields["version"] = array("integer", 1);	
		$a_fields["version_tstamp"] = array("integer", time());	
	}

	protected function doUpdateCustomFields(array &$a_fields)
	{		
		$a_fields["version"] = array("integer", $this->getVersion());			
		$a_fields["version_tstamp"] = array("integer", $this->getVersionDateTime());			
	}

	protected function doReadCustomFields($a_row)
	{				
		$this->setVersion($a_row->version);
		$this->setVersionDateTime($a_row->version_tstamp);
	}
	
	protected function updateCustomFromECSContent(ilECSSetting $a_server, $a_ecs_content)
	{				
		$this->setVersion($a_ecs_content->version);				
		$this->setVersionDateTime($a_ecs_content->version_date);				
	}
	
	/**
	 * Get version info
	 * 
	 * used in ilRemoteFileListGUI
	 * 
	 * @param int $a_obj_id
	 * @return string
	 */
	public static function _lookupVersionInfo($a_obj_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT version, version_tstamp".
			" FROM ".self::DB_TABLE_NAME.
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		$res = (int)$row["version"];
		
		if($row["version_tstamp"])
		{
			$res .= " (".ilDatePresentation::formatDate(new ilDateTime($row["version_tstamp"], IL_CAL_UNIX)).")";
		}
		
		return $res;
	}
	
	// 
	// no late static binding yet
	//
	
	public static function _lookupMID($a_obj_id)
	{
		return ilRemoteObjectBase::_lookupMID($a_obj_id, self::DB_TABLE_NAME);
	}
	
	public static function _lookupOrganization($a_obj_id)
	{
		return ilRemoteObjectBase::_lookupOrganization($a_obj_id, self::DB_TABLE_NAME);
	}
}

?>