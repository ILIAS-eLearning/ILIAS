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
				
		$this->addColumn($lng->txt("cmps_plugin"), "plugin_name");
		$this->addColumn($lng->txt("cmps_plugin_slot"), "slot_name");
		$this->addColumn($lng->txt("cmps_component"), "component_name");			
		$this->addColumn($lng->txt("active"), "plugin_active");
		$this->addColumn($lng->txt("action"));
		
		$this->setDefaultOrderField("plugin_name");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.plugin_overview_row.html",
			"Services/Component");
		$this->getComponents();
		$this->setLimit(10000);
		
		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");		
	}
	
	/**
	* Get pages for list.
	*/
	function getComponents()
	{
		global $ilCtrl;
		
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
					$conf = false;
					if(ilPlugin::hasConfigureClass($slot->getPluginsDirectory(), $p["name"]) &&
						$ilCtrl->checkTargetClass(ilPlugin::getConfigureClassName($p["name"])))
					{
						$conf = true;
					}
					
					$plugins[] =  array("slot_name" => $slot->getSlotName(),
						"component_type" => IL_COMP_SERVICE,
						"component_name" => $s["subdir"],
						"slot_id" => $ps["id"],
						"plugin_id" => $p["id"],			
						"plugin_name" => $p["name"],
						"plugin_active" => $p["is_active"],
						"activation_possible" => $p["activation_possible"],
						"needs_update" => $p["needs_update"],
						"has_conf" => $conf,
						"has_lang" => (bool)sizeof(ilPlugin::getAvailableLangFiles(
							$slot->getPluginsDirectory()."/".$p["name"]."/lang")));
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
		
		// actions 
		
		$ilCtrl->setParameter($this->parent_obj, "ctype", $a_set["component_type"]);
		$ilCtrl->setParameter($this->parent_obj, "cname", $a_set["component_name"]);		
			$ilCtrl->setParameter($this->parent_obj, "slot_id", $a_set["slot_id"]);
		
		$action = array();
					
		$ilCtrl->setParameter($this->parent_obj, "plugin_id", $a_set["plugin_id"]);
		$action[$lng->txt("info")] = $ilCtrl->getLinkTarget($this->parent_obj, "showPlugin");
		$ilCtrl->setParameter($this->parent_obj, "plugin_id", "");
		
		$ilCtrl->setParameter($this->parent_obj, "pname", $a_set["plugin_name"]);
		
		if ($a_set["plugin_active"])
		{			
			if ($a_set["has_lang"])
			{
				$action[$lng->txt("cmps_refresh")] = 
					$ilCtrl->getLinkTarget($this->parent_obj, "refreshLanguages");				
			}
			
			if ($a_set["has_conf"])
			{			
				$action[$lng->txt("cmps_configure")] = 
					$ilCtrl->getLinkTargetByClass(strtolower(ilPlugin::getConfigureClassName($a_set["plugin_name"])), "configure");			
			}
							
			$action[$lng->txt("cmps_deactivate")] = 
				$ilCtrl->getLinkTarget($this->parent_obj, "deactivatePlugin");	
		}
		else if ($a_set["activation_possible"])		
		{
			$action[$lng->txt("cmps_activate")] = 
				$ilCtrl->getLinkTarget($this->parent_obj, "activatePlugin");	
		}
		
		// update button
		if ($a_set["needs_update"])
		{
			$action[$lng->txt("cmps_update")] =	
				$ilCtrl->getLinkTarget($this->parent_obj, "updatePlugin");
		}
		
		$ilCtrl->setParameter($this->parent_obj, "pname", "");

		if(sizeof($action))
		{				
			$alist = new ilAdvancedSelectionListGUI();
			$alist->setId($a_set["plugin_id"]);
			$alist->setListTitle($lng->txt("actions"));
				
			foreach($action as $caption => $cmd)
			{
				$alist->addItem($caption, "", $cmd);
			}
			
			$this->tpl->setVariable("ACTION_SELECTOR", $alist->getHTML());
		}
		
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