<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("Services/Component/classes/class.ilComponent.php");


/**
 * TableGUI class for components listing
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesComponent
 */
class ilComponentsTableGUI extends ilTable2GUI
{
	private $mode;
	
	function ilComponentsTableGUI($a_parent_obj, $a_parent_cmd = "",
		$a_mode = IL_COMP_MODULE)
	{
		global $ilCtrl, $lng;
		
		$this->mode = $a_mode;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setId("cmpstbl".$a_mode);
		
		switch($this->mode) 
		{
			case IL_COMP_MODULE:
				$this->setTitle($lng->txt("cmps_modules"));
				
				$this->addColumn($lng->txt("cmps_module"), "subdir");	
				$this->addColumn($lng->txt("cmps_group"), "group");	
				$this->addColumn($lng->txt("cmps_rep_object"), "object");							
				$this->addColumn($lng->txt("cmps_add_new_rank"), "pos");
				$this->addColumn($lng->txt("cmps_enable_creation"), "creation");
				
				// save options command
				$this->addCommandButton("saveModules", $lng->txt("cmps_save_options"));		
				
				$this->setDefaultOrderField("pos");
				break;
			
			case IL_COMP_SLOTS:
				$this->setTitle($lng->txt("cmps_slots"));
				
				$this->addColumn($lng->txt("cmps_service")." / ".$lng->txt("cmps_module"), "subdir");	
				$this->addColumn($lng->txt("cmps_plugin_slot"), "name");
				$this->addColumn($lng->txt("cmps_dir"), "dir");	
				$this->addColumn($lng->txt("cmps_lang_prefix"), "lang");	
				$this->addColumn($lng->txt("action"), "lang");		
				
				$this->setDefaultOrderField("name");
				break;
		}
				
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_row_component.html",
			"Services/Component");
		$this->getComponents();
		$this->setDefaultOrderField("subdir");
		$this->setLimit(10000);		
	}
	
	/**
	* Get pages for list.
	*/
	function getComponents()
	{
		global $objDefinition, $ilSetting;
		
		if ($this->mode == IL_COMP_MODULE)
		{
			include_once("./Services/Component/classes/class.ilModule.php");
			
			$data = array();
			foreach(ilModule::getAvailableCoreModules() as $mod)
			{
				$has_repo = false;
				$rep_types = 
					$objDefinition->getRepositoryObjectTypesForComponent(IL_COMP_MODULE, $mod["subdir"]);
				if(sizeof($rep_types) > 0)
				{
					foreach($rep_types as $ridx => $rt)
					{
						// we only want to display repository modules
						if($rt["repository"])
						{
							$has_repo = true;							
						}
						else
						{
							unset($rep_types[$ridx]);
						}
					}										
				}				
				if($has_repo)
				{		
					foreach($rep_types as $rt)
					{
						$pos = ($ilSetting->get("obj_add_new_pos_".$rt["id"]) > 0)
							? $ilSetting->get("obj_add_new_pos_".$rt["id"])
							: $rt["default_pos"];
					
						$group = null;
						if ($rt["grp"] != "")
						{
							$group = $objDefinition->getGroup($rt["grp"]);
							$group = $group["name"];
						}
						
						$data[] = array(
							"id" => $rt["id"],
							"object" => $rt["class_name"],
							"subdir" => $mod["subdir"],
							"pos" => $pos,
							"creation" => !(bool)$ilSetting->get("obj_dis_creation_".$rt["id"], false),
							"group_id" => $rt["grp"],
							"group" => $group
						);
						
					}
				}				
			}
			
			$this->setData($data);
		}
		else
		{
			
			$data = array();
						
			include_once("./Services/Component/classes/class.ilService.php");
			foreach(ilService::getAvailableCoreServices() as $obj)
			{								
				foreach (ilComponent::lookupPluginSlots(IL_COMP_SERVICE, $obj["subdir"]) as $slot)
				{
					$data[] = array(
						"subdir" => $obj["subdir"],
						"id" => $slot["id"],
						"name" => $slot["name"],
						"dir" => $slot["dir_pres"],
						"lang" => $slot["lang_prefix"],	
						"ctype" => IL_COMP_SERVICE
					);		
				}								
			}
			
			include_once("./Services/Component/classes/class.ilModule.php");			
			foreach(ilModule::getAvailableCoreModules() as $obj)
			{								
				foreach (ilComponent::lookupPluginSlots(IL_COMP_MODULE, $obj["subdir"]) as $slot)
				{
					$data[] = array(
						"subdir" => $obj["subdir"],
						"id" => $slot["id"],
						"name" => $slot["name"],
						"dir" => $slot["dir_pres"],
						"lang" => $slot["lang_prefix"],	
						"ctype" => IL_COMP_MODULE
					);		
				}								
			}
			
			$this->setData($data);
		}
	}
	
	public function numericOrdering($a_field) 
	{
		if($a_field == "pos")
		{
			return true;
		}
		return false;
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;
		
		// repository object types
		if ($this->mode == IL_COMP_MODULE)
		{						
			// group
			if ($a_set["group_id"] != "")
			{
				$this->tpl->setCurrentBlock("group");		
				$this->tpl->setVariable("VAL_GROUP", $a_set["group"]);
				$this->tpl->setVariable("VAL_GROUP_ID", $a_set["group_id"]);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("rep_object");
			$this->tpl->setVariable("TXT_REP_OBJECT", $a_set["object"]);
			$this->tpl->setVariable("TXT_REP_OBJECT_ID", $a_set["id"]);
			$this->tpl->setVariable("IMG_REP_OBJECT",
				ilUtil::getImagePath("icon_".$a_set["id"].".png"));

			// add new position
			$this->tpl->setVariable("VAR_POS", "obj_pos[".$a_set["id"]."]");		
			$this->tpl->setVariable("VAL_POS", ilUtil::prepareFormOutput($a_set["pos"]));

			// enable creation
			$this->tpl->setVariable("VAR_DISABLE_CREATION",	"obj_enbl_creation[".$a_set["id"]."]");
			if ($a_set["creation"])
			{
				$this->tpl->setVariable("CHECKED_DISABLE_CREATION",
					' checked="checked" ');
			}							
		}		
		else
		{						
			$this->tpl->setVariable("SLOT_NAME", $a_set["name"]);
			$this->tpl->setVariable("SLOT_ID", $a_set["id"]);
			$this->tpl->setVariable("SLOT_DIR", $a_set["dir"]);
			$this->tpl->setVariable("LANG_PREFIX", $a_set["lang"]);

			$ilCtrl->setParameter($this->parent_obj, "ctype", $a_set["ctype"]);
			$ilCtrl->setParameter($this->parent_obj, "cname", $a_set["subdir"]);
			$ilCtrl->setParameter($this->parent_obj, "slot_id", $a_set["id"]);
			$this->tpl->setVariable("HREF_SHOW_SLOT",
				$ilCtrl->getLinkTarget($this->parent_obj, "showPluginSlotInfo"));
			$this->tpl->setVariable("TXT_SHOW_SLOT", $lng->txt("cmps_show_details"));					
		}

		$this->tpl->setVariable("TXT_MODULE_NAME", $a_set["subdir"]);
	}

}
?>
