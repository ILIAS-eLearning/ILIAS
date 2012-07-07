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
		
		if ($this->mode == IL_COMP_MODULE)
		{
			$this->addColumn($lng->txt("cmps_module"));
			$this->addColumn($lng->txt("cmps_rep_object"));
		}
		else
		{
			$this->addColumn($lng->txt("cmps_service"));
		}
		$this->addColumn($lng->txt("cmps_plugin_slot"));
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_row_component.html",
			"Services/Component");
		$this->getComponents();
		$this->setDefaultOrderField("subdir");
		$this->setLimit(10000);
		
		// save options command
		$this->addCommandButton("saveOptions", $lng->txt("cmps_save_options"));

		if ($this->mode == IL_COMP_MODULE)
		{
			$this->setTitle($lng->txt("cmps_modules"));
		}
		else
		{
			$this->setTitle($lng->txt("cmps_services"));
		}
	}
	
	/**
	* Get pages for list.
	*/
	function getComponents()
	{
		if ($this->mode == IL_COMP_MODULE)
		{
			include_once("./Services/Component/classes/class.ilModule.php");
			$modules = ilModule::getAvailableCoreModules();
			$this->setData($modules);
		}
		else
		{
			include_once("./Services/Component/classes/class.ilService.php");
			$services = ilService::getAvailableCoreServices();
			$this->setData($services);
		}
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilSetting, $objDefinition;
		
		// plugin slots
		$plugin_slots = ilComponent::lookupPluginSlots($this->mode, $a_set["subdir"]);

		foreach ($plugin_slots as $slot)
		{
			$this->tpl->setCurrentBlock("slot");
			$this->tpl->setVariable("SLOT_NAME", $slot["name"]);
			$this->tpl->setVariable("SLOT_ID", $slot["id"]);
			$this->tpl->setVariable("TXT_DIR", $lng->txt("cmps_dir"));
			$this->tpl->setVariable("SLOT_DIR", $slot["dir_pres"]);
			$this->tpl->setVariable("TXT_LANG_PREFIX", $lng->txt("cmps_lang_prefix"));
			$this->tpl->setVariable("LANG_PREFIX", $slot["lang_prefix"]);
			
			$ilCtrl->setParameter($this->parent_obj, "ctype", $this->mode);
			$ilCtrl->setParameter($this->parent_obj, "cname", $a_set["subdir"]);
			$ilCtrl->setParameter($this->parent_obj, "slot_id", $slot["id"]);
			$this->tpl->setVariable("HREF_SHOW_SLOT",
				$ilCtrl->getLinkTarget($this->parent_obj, "showPluginSlot"));
			$this->tpl->setVariable("TXT_SHOW_SLOT", $lng->txt("cmps_show_details"));
			$this->tpl->parseCurrentBlock();
		}
			
		
		// repository object types
		if ($this->mode == IL_COMP_MODULE)
		{
			$rep_types = 
				$objDefinition->getRepositoryObjectTypesForComponent(IL_COMP_MODULE, $a_set["subdir"]);

			foreach ($rep_types as $rt)
			{
				// group
				if ($rt["grp"] != "")
				{
					$this->tpl->setCurrentBlock("group");
					$this->tpl->setVariable("TXT_GROUP", $lng->txt("cmps_group"));
					$gi = $objDefinition->getGroup($rt["grp"]);
					$this->tpl->setVariable("VAL_GROUP", $gi["name"]);
					$this->tpl->setVariable("VAL_GROUP_ID", $rt["grp"]);
					$this->tpl->parseCurrentBlock();
				}
				
				$this->tpl->setCurrentBlock("rep_object");
				$this->tpl->setVariable("TXT_REP_OBJECT",
					$rt["class_name"]);
				$this->tpl->setVariable("TXT_REP_OBJECT_ID",
					$rt["id"]);
				$this->tpl->setVariable("IMG_REP_OBJECT",
					ilUtil::getImagePath("icon_".$rt["id"].".png"));
					
				// add new position
				$this->tpl->setVariable("TXT_ADD_NEW_POS",
					$lng->txt("cmps_add_new_rank"));
				$this->tpl->setVariable("VAR_POS",
					"obj_pos[".$rt["id"]."]");
				$pos = ($ilSetting->get("obj_add_new_pos_".$rt["id"]) > 0)
					? $ilSetting->get("obj_add_new_pos_".$rt["id"])
					: $rt["default_pos"];
				$this->tpl->setVariable("VAL_POS",
					ilUtil::prepareFormOutput($pos));
					
				// disable creation
				$this->tpl->setVariable("TXT_DISABLE_CREATION",
					$lng->txt("cmps_disable_creation"));
				$this->tpl->setVariable("VAR_DISABLE_CREATION",
					"obj_dis_creation[".$rt["id"]."]");
				if ($ilSetting->get("obj_dis_creation_".$rt["id"]))
				{
					$this->tpl->setVariable("CHECKED_DISABLE_CREATION",
						' checked="checked" ');
				}
				
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("rep_object_td");
			if (count($rep_types) == 0)
			{
				$this->tpl->setVariable("DUMMY", "&nbsp;");
			}
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TXT_MODULE_NAME", $a_set["subdir"]);
	}

}
?>
