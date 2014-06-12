<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Component/classes/class.ilClaimingPermissionHelper.php";

/** 
 * Advanced metadata permission helper  
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDPermissionHelper extends ilClaimingPermissionHelper
{
	const CONTEXT_MD = 1;
	const CONTEXT_RECORD = 2;
	const CONTEXT_FIELD = 3;
	const CONTEXT_SUBSTITUTION = 4;
	const CONTEXT_SUBSTITUTION_COURSE = 5;
	const CONTEXT_SUBSTITUTION_CATEGORY = 6;
			
	
	const ACTION_MD_CREATE_RECORD = 1;
	const ACTION_MD_IMPORT_RECORDS = 2;
	
	const ACTION_RECORD_EDIT = 5;
	const ACTION_RECORD_DELETE = 6;
	const ACTION_RECORD_EXPORT = 7;
	const ACTION_RECORD_TOGGLE_ACTIVATION = 8;
	const ACTION_RECORD_EDIT_PROPERTY = 9;
	const ACTION_RECORD_EDIT_FIELDS = 10;
	const ACTION_RECORD_CREATE_FIELD = 11;
	const ACTION_RECORD_FIELD_POSITIONS = 12;
	
	const ACTION_FIELD_EDIT = 13;
	const ACTION_FIELD_DELETE = 14;
	const ACTION_FIELD_EDIT_PROPERTY = 15;
	
	const ACTION_SUBSTITUTION_SHOW_DESCRIPTION = 16;
	const ACTION_SUBSTITUTION_SHOW_FIELDNAMES = 17;
	const ACTION_SUBSTITUTION_FIELD_POSITIONS = 18;
	
	const ACTION_SUBSTITUTION_COURSE_SHOW_FIELD = 19;
	const ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY = 20;
	
	const ACTION_SUBSTITUTION_CATEGORY_SHOW_FIELD = 21;
	const ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY = 22;
	
	
	const SUBACTION_RECORD_TITLE = 1;
	const SUBACTION_RECORD_DESCRIPTION = 2;
	const SUBACTION_RECORD_OBJECT_TYPES = 3;	
	
	const SUBACTION_FIELD_TITLE = 4;
	const SUBACTION_FIELD_DESCRIPTION = 5;
	const SUBACTION_FIELD_SEARCHABLE = 6;		
	const SUBACTION_FIELD_PROPERTIES = 7;	
		
	const SUBACTION_SUBSTITUTION_BOLD = 8;
	const SUBACTION_SUBSTITUTION_NEWLINE = 9;
	
		
	
	// caching
		
	protected function readContextIds($a_context_type)
	{
		global $ilDB;
		
		switch($a_context_type)
		{
			case self::CONTEXT_MD:
				return array($_REQUEST["ref_id"]);
			
			case self::CONTEXT_RECORD:
				$set = $ilDB->query("SELECT record_id id".
					" FROM adv_md_record");				
				break;
						
			case self::CONTEXT_FIELD:
			case self::CONTEXT_SUBSTITUTION_COURSE:
			case self::CONTEXT_SUBSTITUTION_CATEGORY:
				$set = $ilDB->query("SELECT field_id id".
					" FROM adv_mdf_definition");						
				break;
			
			case self::CONTEXT_SUBSTITUTION:
				return array("crs", "cat");				
										
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
			self::CONTEXT_MD =>	array(
				"actions" => array(				
					self::ACTION_MD_CREATE_RECORD
					,self::ACTION_MD_IMPORT_RECORDS
				)
			),
			self::CONTEXT_RECORD => array(
				"actions" => array(
					self::ACTION_RECORD_EDIT
					,self::ACTION_RECORD_DELETE
					,self::ACTION_RECORD_EXPORT
					,self::ACTION_RECORD_TOGGLE_ACTIVATION
					,self::ACTION_RECORD_EDIT_FIELDS
					,self::ACTION_RECORD_FIELD_POSITIONS
					,self::ACTION_RECORD_CREATE_FIELD
				),
				"subactions" => array(
					self::ACTION_RECORD_EDIT_PROPERTY =>
						array(
							self::SUBACTION_RECORD_TITLE
							,self::SUBACTION_RECORD_DESCRIPTION
							,self::SUBACTION_RECORD_OBJECT_TYPES
						)
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
							,self::SUBACTION_FIELD_DESCRIPTION
							,self::SUBACTION_FIELD_SEARCHABLE
							,self::SUBACTION_FIELD_PROPERTIES
						)
				)
			),
			self::CONTEXT_SUBSTITUTION => array(
				"actions" => array(
					self::ACTION_SUBSTITUTION_SHOW_DESCRIPTION
					,self::ACTION_SUBSTITUTION_SHOW_FIELDNAMES
					,self::ACTION_SUBSTITUTION_FIELD_POSITIONS
				)
			),
			self::CONTEXT_SUBSTITUTION_COURSE => array(
				"actions" => array(
					self::ACTION_SUBSTITUTION_COURSE_SHOW_FIELD					
				),
				"subactions" => array(
					self::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY => 
						array(
							self::SUBACTION_SUBSTITUTION_BOLD
							,self::SUBACTION_SUBSTITUTION_NEWLINE
						)
				)
			),
			self::CONTEXT_SUBSTITUTION_CATEGORY => array(
				"actions" => array(
					self::ACTION_SUBSTITUTION_CATEGORY_SHOW_FIELD					
				),
				"subactions" => array(
					self::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY => 
						array(
							self::SUBACTION_SUBSTITUTION_BOLD
							,self::SUBACTION_SUBSTITUTION_NEWLINE
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
		
		foreach($ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "AdvancedMetaData", "amdc") as $plugin_name)
		{
			 $res[] = $ilPluginAdmin->getPluginObject(IL_COMP_SERVICE, 
					"AdvancedMetaData", "amdc", $plugin_name);			
		}
		
		return $res;
	}	
}

?>