<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Administration settings form handler
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilAdministrationGUI.php 43022 2013-06-26 13:32:48Z tamstutz $
 */
class ilAdministrationSettingsFormHandler
{
	protected static $OBJ_MAP;
	
	const FORM_PRIVACY = 1;
	const FORM_SECURITY = 2;
	const FORM_FILES_QUOTA = 3;
	const FORM_LP = 4;
	const FORM_MAIL = 5;
	const FORM_COURSE = 6;
	const FORM_GROUP = 7;
	const FORM_REPOSITORY = 8;
	const FORM_LDAP = 9;
	const FORM_FORUM = 10;
	const FORM_ACCESSIBILITY = 11;
	const FORM_WSP = 12;
	const FORM_TAGGING = 13;

	const SETTINGS_USER             = "usrf";
	const SETTINGS_GENERAL          = "adm";
	const SETTINGS_FILE             = "facs";
	const SETTINGS_ROLE             = "rolf";
	const SETTINGS_FORUM            = "frma";
	const SETTINGS_LRES             = "lrss";
	const SETTINGS_REPOSITORY       = "reps";
	const SETTINGS_PD               = "pdts";
	const SETTINGS_COURSE           = "crss";
	const SETTINGS_GROUP            = "grps";
	const SETTINGS_PRIVACY_SECURITY = "ps";
	const SETTINGS_CALENDAR         = "cals";
	const SETTINGS_AUTH             = "auth";
	const SETTINGS_WIKI             = "wiks";
	const SETTINGS_PORTFOLIO        = "prfa";

	const VALUE_BOOL = "bool";
	
	protected static function initObjectMap()
	{		
		global $tree;
		
		$map = array("adm" => SYSTEM_FOLDER_ID);
		foreach($tree->getChilds(SYSTEM_FOLDER_ID) as $obj)
		{
			$map[$obj["type"]] = $obj["ref_id"];			
		}
		
		self::$OBJ_MAP = $map;
	}	
	
	protected static function getRefId($a_obj_type)
	{
		if(!is_array(self::$OBJ_MAP))
		{
			self::initObjectMap();
		}		
		return self::$OBJ_MAP[$a_obj_type];
	}
	
	public static function getSettingsGUIInstance($a_settings_obj_type)
	{				
		global $objDefinition, $ilCtrl;
		
		$ref_id = self::getRefId($a_settings_obj_type);		
		$obj_type = ilObject::_lookupType($ref_id, true);
		
		$class_name = $objDefinition->getClassName($obj_type);	
		$class_name = "ilObj".$class_name."GUI";
		
		$class_path = $ilCtrl->lookupClassPath($class_name);
		include_once($class_path);
		
		if(is_subclass_of($class_name, "ilObject2GUI"))
		{
			$gui_obj = new $class_name($ref_id, ilObject2GUI::REPOSITORY_NODE_ID);
		}
		else
		{
			$gui_obj = new $class_name("", $ref_id, true, false);
		}						

		$gui_obj->setCreationMode(true);
		
		return $gui_obj;
	}
	
	public static function addFieldsToForm($a_form_id, ilPropertyFormGUI $a_form, ilObjectGUI $a_parent_gui)
	{					
		switch($a_form_id)
		{
			case self::FORM_SECURITY:
				$types = array(self::SETTINGS_GENERAL, self::SETTINGS_USER, self::SETTINGS_FILE, self::SETTINGS_ROLE);
				break;	
			
			case self::FORM_PRIVACY:
				$types = array(self::SETTINGS_ROLE, self::SETTINGS_FORUM, self::SETTINGS_LRES);
				break;	
			
			case self::FORM_FILES_QUOTA:
				$types = array(self::SETTINGS_PD);
				break;	
			
			case self::FORM_LP:
				$types = array(self::SETTINGS_REPOSITORY);
				break;	
			
			case self::FORM_ACCESSIBILITY:
				$types = array(self::SETTINGS_FORUM, self::SETTINGS_AUTH, self::SETTINGS_WIKI);
				break;
				
			case self::FORM_MAIL:
				$types = array(self::SETTINGS_COURSE, self::SETTINGS_GROUP);
				break;
			
			case self::FORM_COURSE:
			case self::FORM_GROUP:
				$types = array(self::SETTINGS_PRIVACY_SECURITY, self::SETTINGS_CALENDAR, self::SETTINGS_GENERAL);
				break;
			
			case self::FORM_WSP:
				$types = array(self::SETTINGS_PORTFOLIO);
				break;
			
			case self::FORM_TAGGING:
				$types = array(self::SETTINGS_REPOSITORY);
				break;
			
			default:
				$types = null;
				break;
		}		
		
		if(is_array($types))
		{
			foreach($types as $type)
			{
				$gui = self::getSettingsGUIInstance($type);			
				if($gui && method_exists($gui, "addToExternalSettingsForm"))
				{
					$data = $gui->addToExternalSettingsForm($a_form_id);
					if(is_array($data))
					{
						self::parseFieldDefinition($type, $a_form, $gui, $data);					
					}					
				}
			}
		}
		
		// cron jobs - special handling
				
		include_once "Modules/SystemFolder/classes/class.ilObjSystemFolderGUI.php";
		$parent_gui = new ilObjSystemFolderGUI(null, SYSTEM_FOLDER_ID, true);	
		$parent_gui->setCreationMode(true);
		
		include_once "Services/Cron/classes/class.ilCronManagerGUI.php";
		$gui = new ilCronManagerGUI();
		$data = $gui->addToExternalSettingsForm($a_form_id);
		if(sizeof($data))
		{
			self::parseFieldDefinition("cron", $a_form, $parent_gui, $data);
		}
	}	
	
	protected static function parseFieldValue($a_field_type, &$a_field_value)
	{
		global $lng;
		
		switch($a_field_type)
		{
			case self::VALUE_BOOL:
				$a_field_value = (bool)$a_field_value ?
					$lng->txt("enabled") :
					$lng->txt("disabled");
				return $a_field_value;		
		}	

		if(!is_numeric($a_field_value) && 
			$a_field_value !== null && !trim($a_field_value))
		{
			$a_field_value = "-";
		}

		if(is_numeric($a_field_value) || $a_field_value !== "")
		{
			return true;
		}		
		return false;
	}
	
	protected static function parseFieldDefinition($a_type, ilPropertyFormGUI $a_form, ilObjectGUI $a_gui, $a_data)
	{
		global $lng, $rbacsystem, $ilCtrl;
		
		if(!is_array($a_data))
		{
			return;
		}

		foreach($a_data as $area_caption => $fields)
		{	
			if(is_numeric($area_caption) || !trim($area_caption))
			{
				$area_caption = "obj_".$a_type;
			}

			if(is_array($fields) && sizeof($fields) == 2)
			{
				$cmd = $fields[0];
				$fields = $fields[1];
				if(is_array($fields))
				{
					$ftpl = new ilTemplate("tpl.external_settings.html", true, true, "Services/Administration");

						
					$stack = array();
					foreach($fields as $field_caption_id => $field_value)
					{
						$field_type = $subitems = null;
						if(is_array($field_value))
						{
							$field_type = $field_value[1];
							$subitems = $field_value[2];
							$field_value = $field_value[0];							
						}
									
						if(self::parseFieldValue($field_type, $field_value))
						{
							$ftpl->setCurrentBlock("value_bl");
							$ftpl->setVariable("VALUE", $field_value);
							$ftpl->parseCurrentBlock();
						}
																															
						if(is_array($subitems))						
						{
							$ftpl->setCurrentBlock("subitem_bl");						
							foreach($subitems as $sub_caption_id => $sub_value)
							{		
								$sub_type = null;
								if(is_array($sub_value))
								{
									$sub_type = $sub_value[1];
									$sub_value = $sub_value[0];							
								}
								self::parseFieldValue($sub_type, $sub_value);
								
								$ftpl->setVariable("SUBKEY", $lng->txt($sub_caption_id));										
								$ftpl->setVariable("SUBVALUE", $sub_value);		
								$ftpl->parseCurrentBlock();
							}
						}								
						
						$ftpl->setCurrentBlock("row_bl");
						$ftpl->setVariable("KEY", $lng->txt($field_caption_id));
						$ftpl->parseCurrentBlock();
					}

					if ($rbacsystem->checkAccess("visible,read", $a_gui->object->getRefId()))
					{	
						if(!$cmd)
						{
							$cmd = "view";
						}
						$ilCtrl->setParameter($a_gui, "ref_id", $a_gui->object->getRefId());

						$ftpl->setCurrentBlock("edit_bl");
						$ftpl->setVariable("URL_EDIT", $ilCtrl->getLinkTargetByClass(array("ilAdministrationGUI", get_class($a_gui)), $cmd));
						$ftpl->setVariable("TXT_EDIT", $lng->txt("adm_external_setting_edit"));
						$ftpl->parseCurrentBlock();
					}			

					$ext = new ilCustomInputGUI($lng->txt($area_caption));
					$ext->setHtml($ftpl->get());
					$a_form->addItem($ext);
				}						
			}
		}
	}	
}

?>