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
class ilPluginsOverviewTableGUI extends ilTable2GUI
{
	private $mode;
	
	function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setId("cmpspl");
				
		$this->addColumn($lng->txt("cmps_plugin_slot"), "slot_name");
		$this->addColumn($lng->txt("cmps_component"), "component_name");	
		$this->addColumn($lng->txt("cmps_plugin"), "plugin_name");
		$this->addColumn($lng->txt("active"), "plugin_active");
		$this->addColumn($lng->txt("actions"));
		
		$this->setDefaultOrderField("plugin_name");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.plugin_overview_row.html",
			"Services/Component");
		$this->getComponents();
		$this->setLimit(10000);
		
	}
	
	/**
	* Get pages for list.
	*/
	function getComponents()
	{
		$plugins = array();
		
		include_once("./Services/Component/classes/class.ilModule.php");
		$modules = ilModule::getAvailableCoreModules();
		foreach ($modules as $m)
		{
			$plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_MODULE,
				$m["subdir"]);
			foreach ($plugin_slots as $ps)
			{				
				include_once("./Services/Component/classes/class.ilPluginSlot.php");
				$slot = new ilPluginSlot(IL_COMP_MODULE, $m["subdir"], $ps["id"]);
				foreach ($slot->getPluginsInformation() as $p)
				{
					$plugins[] = array("slot_name" => $slot->getSlotName(),
						"component_type" => IL_COMP_MODULE,
						"component_name" => $m["subdir"],
						"slot_id" => $ps["id"],						
						"plugin_id" => $p["id"],						
						"plugin_name" => $p["name"],
						"active" => $p["is_active"]);
				}				
			}
		}
		include_once("./Services/Component/classes/class.ilService.php");
		$services = ilService::getAvailableCoreServices();
		foreach ($services as $s)
		{
			$plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_SERVICE,
				$s["subdir"]);
			foreach ($plugin_slots as $ps)
			{				
				$slot = new ilPluginSlot(IL_COMP_SERVICE, $s["subdir"], $ps["id"]);
				foreach ($slot->getPluginsInformation() as $p)
				{
					$plugins[] =  array("slot_name" => $slot->getSlotName(),
						"component_type" => IL_COMP_SERVICE,
						"component_name" => $s["subdir"],
						"slot_id" => $ps["id"],
						"plugin_id" => $p["id"],			
						"plugin_name" => $p["name"],
						"plugin_active" => $p["is_active"]);
				}				
			}
		}
		$this->setData($plugins);
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $ilCtrl, $lng;

		$ilCtrl->setParameter($this->parent_obj, "ctype", $a_set["component_type"]);
		$ilCtrl->setParameter($this->parent_obj, "cname", $a_set["component_name"]);
		$ilCtrl->setParameter($this->parent_obj, "slot_id", $a_set["slot_id"]);
		$ilCtrl->setParameter($this->parent_obj, "plugin_id", $a_set["plugin_id"]);
		$this->tpl->setCurrentBlock("cmd");
		$this->tpl->setVariable("TXT_CMD", $lng->txt("administrate"));
		$this->tpl->setVariable("HREF_CMD",
			$ilCtrl->getLinkTarget($this->parent_obj, "showPluginSlot"));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setVariable("TXT_SLOT_NAME", $a_set["slot_name"]);
		$this->tpl->setVariable("TXT_COMP_NAME", 
			$a_set["component_type"]."/".$a_set["component_name"]);	
		
		$act_str = ($a_set["plugin_active"])
			? "<b>".$lng->txt("yes")."</b>"
			: $lng->txt("no");
		$this->tpl->setVariable("TXT_PLUGIN_NAME", $a_set["plugin_name"]);		
		$this->tpl->setVariable("TXT_ACTIVE", $act_str);		
	}

}
?>
