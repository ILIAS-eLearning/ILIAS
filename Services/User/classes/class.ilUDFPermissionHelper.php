<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Component/classes/class.ilClaimingPermissionHelper.php";

/** 
 * UDF permission helper  
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesUser
 */
class ilUDFPermissionHelper extends ilClaimingPermissionHelper
{
	const CONTEXT_UDF = 1;
	const CONTEXT_FIELD = 2;
	
	
	const ACTION_UDF_CREATE_FIELD = 1;
	
	const ACTION_FIELD_EDIT = 1;
	const ACTION_FIELD_DELETE = 2;
	const ACTION_FIELD_EDIT_PROPERTY = 3;
	const ACTION_FIELD_EDIT_ACCESS = 4;
	
	
	const SUBACTION_FIELD_TITLE = 1;	
	const SUBACTION_FIELD_PROPERTIES = 2;	
	
	const SUBACTION_FIELD_ACCESS_VISIBLE_PERSONAL = 1;
	const SUBACTION_FIELD_ACCESS_VISIBLE_REGISTRATION = 2;
	const SUBACTION_FIELD_ACCESS_VISIBLE_LOCAL = 3;
	const SUBACTION_FIELD_ACCESS_VISIBLE_COURSES = 4;
	const SUBACTION_FIELD_ACCESS_VISIBLE_GROUPS = 5;
	const SUBACTION_FIELD_ACCESS_CHANGEABLE_PERSONAL = 6;
	const SUBACTION_FIELD_ACCESS_CHANGEABLE_LOCAL = 7;
	const SUBACTION_FIELD_ACCESS_REQUIRED = 8;
	const SUBACTION_FIELD_ACCESS_EXPORT = 9;
	const SUBACTION_FIELD_ACCESS_SEARCHABLE = 10;
	const SUBACTION_FIELD_ACCESS_CERTIFICATE = 11;
		
	
	// caching
		
	protected function readContextIds($a_context_type)
	{
		global $ilDB;
		
		switch($a_context_type)
		{
			case self::CONTEXT_UDF:
				return array($_REQUEST["ref_id"]);
					
			case self::CONTEXT_FIELD:			
				$set = $ilDB->query("SELECT field_id id".
					" FROM udf_definition");						
				break;
								
			default:
				return array();
		}
		
		$res = array();		
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row["id"];
		}		
		return $res;
	}
	
	
	// permissions
	
	protected function buildPermissionMap()
	{
		return array(
			self::CONTEXT_UDF => array(
				"actions" => array(				
					self::ACTION_UDF_CREATE_FIELD
				)
			),			
			self::CONTEXT_FIELD => array(
				"actions" => array(
					self::ACTION_FIELD_EDIT,
					self::ACTION_FIELD_DELETE
				),
				"subactions" => array(
					self::ACTION_FIELD_EDIT_PROPERTY => 
						array(
							self::SUBACTION_FIELD_TITLE
							,self::SUBACTION_FIELD_PROPERTIES
						)
					,self::ACTION_FIELD_EDIT_ACCESS => 
						array(
							self::SUBACTION_FIELD_ACCESS_VISIBLE_PERSONAL
							,self::SUBACTION_FIELD_ACCESS_VISIBLE_REGISTRATION
							,self::SUBACTION_FIELD_ACCESS_VISIBLE_LOCAL
							,self::SUBACTION_FIELD_ACCESS_VISIBLE_COURSES
							,self::SUBACTION_FIELD_ACCESS_VISIBLE_GROUPS
							,self::SUBACTION_FIELD_ACCESS_CHANGEABLE_PERSONAL
							,self::SUBACTION_FIELD_ACCESS_CHANGEABLE_LOCAL
							,self::SUBACTION_FIELD_ACCESS_REQUIRED
							,self::SUBACTION_FIELD_ACCESS_EXPORT
							,self::SUBACTION_FIELD_ACCESS_SEARCHABLE
							,self::SUBACTION_FIELD_ACCESS_CERTIFICATE
						)
				)
			)
		);		
	}
	
	
	// plugins
	
	protected function getActivePlugins()
	{
		global $ilPluginAdmin;
		
		$res = array();
		
		foreach($ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "User", "udfc") as $plugin_name)
		{
			 $res[] = $ilPluginAdmin->getPluginObject(IL_COMP_SERVICE, 
					"User", "udfc", $plugin_name);			
		}
		
		return $res;
	}	
}

?>