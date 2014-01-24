<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/WebServices/ECS/classes/class.ilRemoteObjectBase.php');

/** 
* Remote category app class
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ingroup ModulesRemoteCategory
*/

class ilObjRemoteCategory extends ilRemoteObjectBase
{
	const DB_TABLE_NAME = "rcat_settings";

	public function initType()
	{
		$this->type = "rcat";
	}
	
	protected function getTableName()
	{
		return self::DB_TABLE_NAME;
	}
	
	protected function getECSObjectType()
	{
		return "/campusconnect/categories";
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