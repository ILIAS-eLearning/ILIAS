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
	
	const SETTINGS_USER = "usrf";
	const SETTINGS_GENERAL = "adm";
	const SETTINGS_FILE = "facs";
	
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
	
	protected static function getSettingsGUIInstance($a_settings_obj_type)
	{				
		global $objDefinition, $ilCtrl;
		
		$ref_id = self::getRefId($a_settings_obj_type);		
		$obj_type = ilObject::_lookupType($ref_id, true);
		
		$class_name = $objDefinition->getClassName($obj_type);	
		$class_name = "ilObj".$class_name."GUI";
		
		$class_path = $ilCtrl->lookupClassPath($class_name);
		include_once($class_path);
		$gui_obj = new $class_name("", $ref_id, true, false);
		$gui_obj->setCreationMode(true);
		
		return $gui_obj;
	}
	
	public static function addFieldsToForm($a_form_id, ilPropertyFormGUI $a_form, ilObjectGUI $a_parent_gui)
	{			
		global $lng, $rbacsystem, $ilCtrl;
		
		switch($a_form_id)
		{
			case self::FORM_SECURITY:
				$types = array(self::SETTINGS_GENERAL, self::SETTINGS_USER, self::SETTINGS_FILE);
				break;				
			
			default:
				return;
		}		
		
		foreach($types as $type)
		{
			$gui = self::getSettingsGUIInstance($type);			
			if($gui && method_exists($gui, "addToExternalSettingsForm"))
			{
				$sec = new ilFormSectionHeaderGUI();
				$sec->setTitle($lng->txt("obj_".$type));
				$a_form->addItem($sec);
				
				$cmd = $gui->addToExternalSettingsForm($a_form_id, $a_form, $a_parent_gui);
				
				if ($rbacsystem->checkAccess("visible,read", $gui->object->getRefId()))
				{	
					if(!$cmd)
					{
						$cmd = "view";
					}
					$ilCtrl->setParameter($gui, "ref_id", $gui->object->getRefId());
					$link = $ilCtrl->getLinkTarget($gui, $cmd);
					
					$url = new ilCustomInputGUI($lng->txt("settings"));
					$url->setHtml('<a href="'.$link.'" class="submit">'.$lng->txt("edit").'</a>');
					$a_form->addItem($url);
				}				
			}
		}
	}	
}

?>