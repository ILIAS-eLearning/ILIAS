<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("Services/Component/classes/class.ilComponent.php");

/**
 * TableGUI class for module listing
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesRepository
 */
class ilModulesTableGUI extends ilTable2GUI
{	
	protected $pos_group_options; // [array]
	
	function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilCtrl, $lng;
				
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setId("repmodtbl");
		
		$this->setTitle($lng->txt("cmps_modules"));

		$this->addColumn($lng->txt("cmps_add_new_rank"), "");
		$this->addColumn($lng->txt("cmps_rep_object"), "");	
		$this->addColumn($lng->txt("cmps_module"), "");	
		$this->addColumn($lng->txt("cmps_group"), "");											
		$this->addColumn($lng->txt("cmps_enable_creation"), "");

		// save options command
		$this->addCommandButton("saveModules", $lng->txt("cmps_save_options"));		
	
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_row_module.html", "Services/Repository");	
		$this->setLimit(10000);		
		$this->setExternalSorting(true);		
				
		$this->getComponents();		
	}
	
	/**
	* Get pages for list.
	*/
	function getComponents()
	{
		global $objDefinition, $ilSetting, $lng;
		
		// unassigned objects should be last
		$this->pos_group_options = array(0 => $lng->txt("rep_new_item_group_unassigned"));
		$pos_group_map[0] = 9999;
		
		include_once("Services/Repository/classes/class.ilObjRepositorySettings.php");
		foreach(ilObjRepositorySettings::getNewItemGroups() as $item)
		{
			$this->pos_group_options[$item["id"]] = $item["title"];
			$pos_group_map[$item["id"]] = $item["pos"];
		}				
				
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
					
					$pos_grp_id = $ilSetting->get("obj_add_new_pos_grp_".$rt["id"], 0);
					
					$pos_grp_pos = isset($pos_group_map[$pos_grp_id])
						? $pos_group_map[$pos_grp_id]
						: 9999;

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
						"pos_group" => $pos_grp_id,
						"creation" => !(bool)$ilSetting->get("obj_dis_creation_".$rt["id"], false),
						"group_id" => $rt["grp"],
						"group" => $group,
						"sort_key" => str_pad($pos_grp_pos, 4, "0", STR_PAD_LEFT).
						   str_pad($pos, 4, "0", STR_PAD_LEFT)												
					);					
				}
			}				
		}
				
		$data = ilUtil::sortArray($data, "sort_key", "asc", true);
		
		$this->setData($data);
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
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

		// grouping
		$sel = ilUtil::formSelect($a_set["pos_group"], 
			"obj_grp[".$a_set["id"]."]", $this->pos_group_options, false, true);
		$this->tpl->setVariable("GROUP_SEL", $sel);
		
		// position
		$this->tpl->setVariable("VAR_POS", "obj_pos[".$a_set["id"]."]");		
		$this->tpl->setVariable("VAL_POS", ilUtil::prepareFormOutput($a_set["pos"]));

		// enable creation
		$this->tpl->setVariable("VAR_DISABLE_CREATION",	"obj_enbl_creation[".$a_set["id"]."]");
		if ($a_set["creation"])
		{
			$this->tpl->setVariable("CHECKED_DISABLE_CREATION",
				' checked="checked" ');
		}							
		
		$this->tpl->setVariable("TXT_MODULE_NAME", $a_set["subdir"]);
	}
}

?>