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
	
	const SETTINGS_USER = "usrf";
	const SETTINGS_GENERAL = "adm";
	const SETTINGS_FILE = "facs";
	const SETTINGS_ROLE = "rolf";
	const SETTINGS_FORUM = "frma";
	const SETTINGS_LRES = "lrss";
	const SETTINGS_REPOSITORY = "reps";
	const SETTINGS_PD = "pdts";
	
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
				$types = array(self::SETTINGS_GENERAL, self::SETTINGS_USER, self::SETTINGS_FILE, self::SETTINGS_ROLE);
				break;	
			
			case self::FORM_PRIVACY:
				$types = array(self::SETTINGS_ROLE, self::SETTINGS_FORUM, self::SETTINGS_LRES);
				break;	
			
			case self::FORM_FILES_QUOTA:
				$types = array(self::SETTINGS_REPOSITORY, self::SETTINGS_PD);
				break;	
			
			case self::FORM_LP:
				$types = array(self::SETTINGS_REPOSITORY);
				break;	
			
			default:
				return;
		}		
		
		foreach($types as $type)
		{
			$gui = self::getSettingsGUIInstance($type);			
			if($gui && method_exists($gui, "addToExternalSettingsForm"))
			{	
				$data = $gui->addToExternalSettingsForm($a_form_id);
				if(is_array($data))
				{
					foreach($data as $area_caption => $fields)
					{	
						if(is_numeric($area_caption) || !trim($area_caption))
						{
							$area_caption = "obj_".$type;
						}
						
						if(is_array($fields) && sizeof($fields) == 2)
						{
							$cmd = $fields[0];
							$fields = $fields[1];
							if(is_array($fields))
							{
								$ftpl = new ilTemplate("tpl.external_settings.html", true, true, "Services/Administration");
													
								$ftpl->setCurrentBlock("row_bl");	
								foreach($fields as $field_caption_id => $field_value)
								{
									$field_type = null;
									if(is_array($field_value))
									{
										$field_type = $field_value[1];
										$field_value = $field_value[0];
									}
									switch($field_type)
									{
										case self::VALUE_BOOL:
											$field_value = (bool)$field_value ?
												$lng->txt("yes") :
												$lng->txt("no");
											break;
									}				

									if(substr($field_caption_id, 0, 1) == "~")
									{
										$depth = explode("~", $field_caption_id);
										$field_caption_id = array_pop($depth);
										$ftpl->setVariable("SPACER", ' style="padding-left:'.(sizeof($depth)*20).'px"');										
									}								
									
									if(!is_numeric($field_value) && !trim($field_value))
									{
										$field_value = "-";
									}
										
									$ftpl->setVariable("KEY", $lng->txt($field_caption_id));
									$ftpl->setVariable("VALUE", $field_value);
									$ftpl->parseCurrentBlock();
								}
								
								if ($rbacsystem->checkAccess("visible,read", $gui->object->getRefId()))
								{	
									if(!$cmd)
									{
										$cmd = "view";
									}
									$ilCtrl->setParameter($gui, "ref_id", $gui->object->getRefId());
									
									$ftpl->setCurrentBlock("edit_bl");
									$ftpl->setVariable("URL_EDIT", $ilCtrl->getLinkTarget($gui, $cmd));
									$ftpl->setVariable("TXT_EDIT", $lng->txt("adm_external_setting_edit"));
									$ftpl->parseCurrentBlock();
								}			
								
								$ext = new ilCustomInputGUI($lng->txt($area_caption).
									"<div class=\"small\"><em>".$lng->txt("adm_external_setting_prefix")."</em></div>");
								$ext->setHtml($ftpl->get());
								$a_form->addItem($ext);
							}						
						}
					}
				}
			}
		}
	}	
}

?>