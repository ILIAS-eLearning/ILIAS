<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/WebServices/ECS/classes/class.ilRemoteObjectBase.php');

/** 
* Remote learning module app class
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ingroup ModulesRemoteLearningModule
*/

class ilObjRemoteLearningModule extends ilRemoteObjectBase
{
	const DB_TABLE_NAME = "rlm_settings";
	
	const ACTIVATION_OFFLINE = 0;
	const ACTIVATION_ONLINE = 1;
	
	protected $availability_type;

	public function initType()
	{
		$this->type = "rlm";
	}
	
	protected function getTableName()
	{
		return self::DB_TABLE_NAME;
	}
	
	protected function getECSObjectType()
	{
		return "/campusconnect/learningmodules";
	}
	
	/**
	 * Set Availability type
	 *
	 * @param int $a_type availability type
	 */
	public function setAvailabilityType($a_type)
	{
	 	$this->availability_type = $a_type;
	}
	
	/**
	 * get availability type
	 *
	 * @return int
	 */
	public function getAvailabilityType()
	{
	 	return $this->availability_type;
	}
	
	/**
	 * Lookup online
	 *
	 * @param int $a_obj_id obj_id
	 * @return bool
	 */
	public static function _lookupOnline($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM ".self::DB_TABLE_NAME.
			" WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		switch($row->availability_type)
		{
			case self::ACTIVATION_ONLINE:
				return true;
				
			case self::ACTIVATION_OFFLINE:
				return false;
		
			default:
				return false;
		}
		
		return false;
	}
	
	protected function doCreateCustomFields(array &$a_fields)
	{
		$a_fields["availability_type"] = array("integer", 0);	
	}

	protected function doUpdateCustomFields(array &$a_fields)
	{		
		$a_fields["availability_type"] = array("integer", $this->getAvailabilityType());			
	}

	protected function doReadCustomFields($a_row)
	{				
		$this->setAvailabilityType($a_row->availability_type);
	}
	
	protected function updateCustomFromECSContent(ilECSSetting $a_server, $a_ecs_content)
	{				
		$this->setAvailabilityType($a_ecs_content->availability == 'online' ? self::ACTIVATION_ONLINE : self::ACTIVATION_OFFLINE);				
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