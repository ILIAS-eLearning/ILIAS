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
		$this->setRowTemplate("tpl.table_row_module.html",
			"Services/Repository");	
		$this->setLimit(10000);		
		
		$this->setExternalSorting(true);		
		$this->getComponents();
	}
	
	/**
	* Get pages for list.
	*/
	function getComponents()
	{
		global $objDefinition, $ilSetting;
				
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
		
		$data = ilUtil::sortArray($data, "pos", "asc", true);
		
		$this->setData($data);
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;
									
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
		
		$this->tpl->setVariable("TXT_MODULE_NAME", $a_set["subdir"]);
	}
}

?>