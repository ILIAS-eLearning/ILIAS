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
		
		$this->mode = $a_mode;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn($lng->txt("cmps_plugin_slot"));
		$this->addColumn($lng->txt("cmps_plugins"));
		$this->addColumn($lng->txt("actions"));
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
		include_once("./Services/Component/classes/class.ilModule.php");
		$modules = ilModule::getAvailableCoreModules();
		$slots = array();
		foreach ($modules as $m)
		{
			$plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_MODULE,
				$m["subdir"]);
			foreach ($plugin_slots as $ps)
			{
				$plugins = array();
				include_once("./Services/Component/classes/class.ilPluginSlot.php");
				$slot = new ilPluginSlot(IL_COMP_MODULE, $m["subdir"], $ps["id"]);
				foreach ($slot->getPluginsInformation() as $p)
				{
					$plugins[] = $p;
				}
				if (count($plugins) > 0)
				{
					$slots[] = array("slot_name" => $slot->getSlotName(),
						"component_type" => IL_COMP_MODULE,
						"component_name" => $m["subdir"],
						"slot_id" => $ps["id"],
						"plugins" => $plugins);
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
				$plugins = array();
				$slot = new ilPluginSlot(IL_COMP_SERVICE, $s["subdir"], $ps["id"]);
				foreach ($slot->getPluginsInformation() as $p)
				{
					$plugins[] = $p;
				}
				if (count($plugins) > 0)
				{
					$slots[] = array("slot_name" => $slot->getSlotName(),
						"component_type" => IL_COMP_SERVICE,
						"component_name" => $s["subdir"],
						"slot_id" => $ps["id"],
						"plugins" => $plugins);
				}
			}
		}
		$this->setData($slots);
		//include_once("./Services/Component/classes/class.ilService.php");
		//$services = ilService::getAvailableCoreServices();
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
		$this->tpl->setCurrentBlock("cmd");
		$this->tpl->setVariable("TXT_CMD", $lng->txt("administrate"));
		$this->tpl->setVariable("HREF_CMD",
			$ilCtrl->getLinkTarget($this->parent_obj, "showPluginSlot"));
		$this->tpl->parseCurrentBlock();

		foreach ($a_set["plugins"] as $p)
		{
			$act_str = ($p["is_active"])
				? " <b>(".$lng->txt("active").")</b>"
				: "";
			$this->tpl->setCurrentBlock("plugin");
			$this->tpl->setVariable("TXT_PLUGIN_NAME", $p["name"].$act_str);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("TXT_SLOT_NAME",
			$a_set["slot_name"]);
		$this->tpl->setVariable("TXT_COMP_NAME",
			$a_set["component_type"]."/".$a_set["component_name"]);
	}

}
?>
