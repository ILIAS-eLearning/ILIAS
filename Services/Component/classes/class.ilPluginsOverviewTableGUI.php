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
		$this->addColumn($lng->txt("cmps_plugin"));
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
				$slot = new ilPluginSlot(IL_COMP_MODULE, $m["subdir"], $ps["id"]);
				foreach ($slot->getPluginsInformation() as $p)
				{
					$plugins[] = $p + array("slot_name" => $slot->getSlotName());
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
					$plugins[] = $p + array("slot_name" => $slot->getSlotName());
				}
			}
		}
		$this->setData($plugins);		
		//include_once("./Services/Component/classes/class.ilService.php");
		//$services = ilService::getAvailableCoreServices();
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
//var_dump($a_set);
		$this->tpl->setVariable("TXT_SLOT_NAME",
			$a_set["slot_name"]);
		$this->tpl->setVariable("TXT_COMP_NAME",
			$a_set["component_type"]."/".$a_set["component_name"]);
		$this->tpl->setVariable("TXT_PLUGIN_NAME", $a_set["name"]);
	}

}
?>
