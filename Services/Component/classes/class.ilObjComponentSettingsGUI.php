<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");


/**
* Components (Modules, Services, Plugins) Settings.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjComponentSettingsGUI: ilPermissionGUI
*
* @ingroup ServicesComponent
*/
class ilObjComponentSettingsGUI extends ilObjectGUI
{
    private static $ERROR_MESSAGE;
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = 'cmps';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule('cmps');
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $rbacsystem, $ilErr, $ilAccess, $ilCtrl;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		if(!$ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'),$ilErr->WARNING);
		}

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:

				// configure classes
				$config = false;
				if (substr(strtolower($next_class), strlen($next_class) - 9) == "configgui")
				{
					$path = $ilCtrl->lookupClassPath(strtolower($next_class));
					if ($path != "")
					{
						include_once($path);
						$nc = new $next_class();

						$pl = ilPluginAdmin::getPluginObject($_GET["ctype"],
							$_GET["cname"], $_GET["slot_id"], $_GET["pname"]);

						$nc->setPluginObject($pl);

						$ret = $this->ctrl->forwardCommand($nc);
						$config = true;
					}
				}

				if (!$config)
				{
					if(!$cmd || $cmd == 'view')
					{
						$cmd = "listPlugins";
					}

					$this->$cmd();
				}
				break;
		}
		return true;
	}

	/**
	 * Get tabs
	 *
	 * @access public
	 *
	 */
	public function getAdminTabs()
	{
		global $rbacsystem, $ilAccess, $lng;
		
		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("plugins",
				$lng->txt("cmps_plugins"),
				$this->ctrl->getLinkTarget($this, "listPlugins"));
				
			$this->tabs_gui->addTab("modules",
				$lng->txt("cmps_modules"),
				$this->ctrl->getLinkTarget($this, "listModules"));
				
			$this->tabs_gui->addTab("services",
				$lng->txt("cmps_services"),
				$this->ctrl->getLinkTarget($this, "listServices"));
		}
		
		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("perm_settings",
				$lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"));
		}
		
		if ($_GET["ctype"] == "Services")
		{
			$this->tabs_gui->activateTab("services");
		}
	}
	
	/**
	* List Modules
	*/
	public function listModules()
	{
		global $ilCtrl, $lng, $ilSetting;
		
		$this->tabs_gui->activateTab('modules');

		$tpl = new ilTemplate("tpl.component_list.html", true, true, "Services/Component");
		
		/*
		$tpl->setVariable("HREF_REFRESH_PLUGINS_INFORMATION",
			$ilCtrl->getLinkTarget($this, "refreshPluginsInformation"));
		$tpl->setVariable("TXT_REFRESH_PLUGINS_INFORMATION",
			$lng->txt("cmps_refresh_plugins_inf"));
		*/
		
		include_once("./Services/Component/classes/class.ilComponentsTableGUI.php");
		$comp_table = new ilComponentsTableGUI($this, "listModules");
		
		$tpl->setVariable("TABLE", $comp_table->getHTML());
		$this->tpl->setContent($tpl->get());
	}

	/**
	* List Services
	*/
	public function listServices()
	{
		global $ilCtrl, $lng, $ilSetting;
		
		$this->tabs_gui->activateTab('services');

		$tpl = new ilTemplate("tpl.component_list.html", true, true, "Services/Component");
		
		$ilCtrl->setParameter($this, "mode", IL_COMP_SERVICE);
		$tpl->setVariable("HREF_REFRESH_PLUGINS_INFORMATION",
			$ilCtrl->getLinkTarget($this, "refreshPluginsInformation"));
		$tpl->setVariable("TXT_REFRESH_PLUGINS_INFORMATION",
			$lng->txt("cmps_refresh_plugins_inf"));

		include_once("./Services/Component/classes/class.ilComponentsTableGUI.php");
		$comp_table = new ilComponentsTableGUI($this, "listServices", IL_COMP_SERVICE);
		
		$tpl->setVariable("TABLE", $comp_table->getHTML());
		$this->tpl->setContent($tpl->get());
	}
	
	/**
	 * List plugins
	 *
	 * @param
	 * @return
	 */
	function listPlugins()
	{
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("plugins");
		include_once("./Services/Component/classes/class.ilPluginsOverviewTableGUI.php");
		$table = new ilPluginsOverviewTableGUI($this, "listPlugins");
		$tpl->setContent($table->getHTML());
	}

	/**
	* Save Options.
	*/
	function saveOptions()
	{
		global $ilSetting, $ilCtrl, $lng;

		// disable creation
		if (is_array($_POST["obj_pos"]))
		{
			foreach($_POST["obj_pos"] as $k => $v)
			{
				$ilSetting->set("obj_dis_creation_".$k, (int) $_POST["obj_dis_creation"][$k]);
			}
		}
		
		// add new position
		$double = $ex_pos = array();
		if (is_array($_POST["obj_pos"]))
		{
			reset($_POST["obj_pos"]);
			foreach($_POST["obj_pos"] as $k => $v)
			{
				if (in_array($v, $ex_pos))
				{
					$double[$v] = $v;
				}
				$ex_pos[] = $v;
				$ilSetting->set("obj_add_new_pos_".$k, $v);
			}
		}
		
		if (count($double) == 0)
		{
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		}
		else
		{
			ilUtil::sendInfo($lng->txt("cmps_duplicate_positions")." ".implode($double, ", "), true);
		}
		
		$ilCtrl->redirect($this, "listModules");
	}

	/**
	 * Show information about a plugin slot.
	 */
	function showPluginSlotInfo()
	{
		global $tpl,$lng, $ilTabs, $ilCtrl;

		$ilTabs->clearTargets();
		if ($_GET["ctype"] == "Services")
		{
			$ilTabs->setBackTarget($lng->txt("cmps_services"),
				$ilCtrl->getLinkTarget($this, "listServices"));
		}
		else
		{
			$ilTabs->setBackTarget($lng->txt("cmps_modules"),
				$ilCtrl->getLinkTarget($this, "listModules"));
		}

		include_once("./Services/Component/classes/class.ilComponent.php");
		$comp = ilComponent::getComponentObject($_GET["ctype"], $_GET["cname"]);

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		// component
		$ne = new ilNonEditableValueGUI($lng->txt("cmps_component"), "");
		$ne->setValue($comp->getComponentType()."/".$comp->getName()." [".$comp->getId()."]");
		$form->addItem($ne);

		// plugin slot
		$ne = new ilNonEditableValueGUI($lng->txt("cmps_plugin_slot"), "");
		$ne->setValue($comp->getPluginSlotName($_GET["slot_id"])." [".$_GET["slot_id"]."]");
		$form->addItem($ne);

		// main dir
		$ne = new ilNonEditableValueGUI($lng->txt("cmps_main_dir"), "");
		$ne->setValue($comp->getPluginSlotDirectory($_GET["slot_id"])."/&lt;Plugin_Name&gt;");
		$form->addItem($ne);

		// plugin file
		$ne = new ilNonEditableValueGUI($lng->txt("cmps_plugin_file"), "");
		$ne->setValue("&lt;".$lng->txt("cmps_main_dir")."&gt;".
			"/classes/class.il&lt;Plugin_Name&gt;Plugin.php");
		$form->addItem($ne);

		// language files
		$ne = new ilNonEditableValueGUI($lng->txt("cmps_lang_files"), "");
		$ne->setValue("&lt;".$lng->txt("cmps_main_dir")."&gt;".
			"/lang/ilias_&lt;Language ID&gt;.lang");
		$form->addItem($ne);

		// db update
		$ne = new ilNonEditableValueGUI($lng->txt("cmps_db_update"), "");
		$ne->setValue("&lt;".$lng->txt("cmps_main_dir")."&gt;".
			"/sql/dbupdate.php");
		$form->addItem($ne);

		// lang prefix
		$ne = new ilNonEditableValueGUI($lng->txt("cmps_plugin_lang_prefixes"), "");
		$ne->setValue($comp->getPluginSlotLanguagePrefix($_GET["slot_id"])."&lt;Plugin_ID&gt;_");
		$form->addItem($ne);

		// db prefix
		$ne = new ilNonEditableValueGUI($lng->txt("cmps_plugin_db_prefixes"), "");
		$ne->setValue($comp->getPluginSlotLanguagePrefix($_GET["slot_id"])."&lt;Plugin_ID&gt;_");
		$form->addItem($ne);

		$form->setTitle($lng->txt("cmps_plugin_slot"));

		// set content and title
		$tpl->setContent($form->getHTML());
		$tpl->setTitle($comp->getComponentType()."/".$comp->getName().": ".
			$lng->txt("cmps_plugin_slot")." \"".$comp->getPluginSlotName($_GET["slot_id"])."\"");
		$tpl->setDescription("");
	}

	/**
	 * Show information about a plugin slot.
	 */
	function showPluginSlot()
	{
		global $tpl, $lng, $ilTabs, $ilCtrl;
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("cmps_plugins"),
			$ilCtrl->getLinkTarget($this, "listPlugins"));

		include_once("./Services/Component/classes/class.ilComponent.php");
		$comp = ilComponent::getComponentObject($_GET["ctype"], $_GET["cname"]);

		// plugins table
		include_once("./Services/Component/classes/class.ilPluginsTableGUI.php");
		$plugins_table = new ilPluginsTableGUI($this, "showPluginSlot",
			$_GET["ctype"], $_GET["cname"], $_GET["slot_id"]);
		$tpl->setContent($plugins_table->getHTML());

		// set content and title
		$tpl->setTitle($comp->getComponentType()."/".$comp->getName().": ".
			$lng->txt("cmps_plugin_slot")." \"".$comp->getPluginSlotName($_GET["slot_id"])."\"");
		$tpl->setDescription("");
	}
	
	/**
	* Refresh plugins information
	*/
	function refreshPluginsInformation()
	{
		global $ilCtrl;
die ("ilObjComponentSettigsGUI::refreshPluginsInformation: deprecated");
		include_once("./Services/Component/classes/class.ilPlugin.php");
		ilPlugin::refreshPluginXmlInformation();
		
		if ($_GET["mode"] == IL_COMP_SERVICE)
		{
			$ilCtrl->redirect($this, "listServices");
		}
		else
		{
			$ilCtrl->redirect($this, "listModules");
		}
	}
	
	/**
	* Activate a plugin.
	*/
	function activatePlugin()
	{
		global $ilCtrl, $lng;

		include_once("./Services/Component/classes/class.ilPlugin.php");
		$pl = ilPlugin::getPluginObject($_GET["ctype"], $_GET["cname"],
			$_GET["slot_id"], $_GET["pname"]);

		try
		{
			$result = $pl->activate();
			if ($result !== true)
			{
				ilUtil::sendFailure($result, true);
			}
			else
			{
				ilUtil::sendSuccess($lng->txt("cmps_plugin_activated"), true);
			}
		}
		catch(ilPluginException $e)
		{
			ilUtil::sendFailure($e->getMessage, true);
		}
			
		$ilCtrl->setParameter($this, "ctype", $_GET["ctype"]);
		$ilCtrl->setParameter($this, "cname", $_GET["cname"]);
		$ilCtrl->setParameter($this, "slot_id", $_GET["slot_id"]);
		$ilCtrl->redirect($this, "showPluginSlot");
	}
	
	/**
	* Update a plugin.
	*/
	function updatePlugin()
	{

		include_once("./Services/Component/classes/class.ilPlugin.php");
		$pl = ilPlugin::getPluginObject($_GET["ctype"], $_GET["cname"],
			$_GET["slot_id"], $_GET["pname"]);

		$result = $pl->update();
		
		if ($result !== true)
		{
			ilUtil::sendFailure($pl->message, true);
		}
		else
		{
			ilUtil::sendSuccess($pl->message, true);
		}
		
		// reinitialize control class
		global $ilCtrl;
		$ilCtrl->initBaseClass("iladministrationgui");
		$_GET["cmd"] = "jumpToPluginSlot";
		$ilCtrl->setParameterByClass("iladministrationgui", "ctype", $_GET["ctype"]);
		$ilCtrl->setParameterByClass("iladministrationgui", "cname", $_GET["cname"]);
		$ilCtrl->setParameterByClass("iladministrationgui", "slot_id", $_GET["slot_id"]);
		$ilCtrl->setTargetScript("ilias.php");
//		$ilCtrl->callBaseClass();
		ilUtil::redirect("ilias.php?admin_mode=settings&baseClass=ilAdministrationGUI&cmd=jumpToPluginSlot&".
			"ref_id=".$_GET["ref_id"]."&ctype=".$_GET["ctype"]."&cname=".$_GET["cname"]."&slot_id=".$_GET["slot_id"]);
		//$ilCtrl->redirectByClass("iladministrationgui", );
	}

	/**
	* Deactivate a plugin.
	*/
	function deactivatePlugin()
	{
		global $ilCtrl, $lng;

		include_once("./Services/Component/classes/class.ilPlugin.php");
		$pl = ilPlugin::getPluginObject($_GET["ctype"], $_GET["cname"],
			$_GET["slot_id"], $_GET["pname"]);
			
		$result = $pl->deactivate();
		
		if ($result !== true)
		{
			ilUtil::sendFailure($result, true);
		}
		else
		{
			ilUtil::sendSuccess($lng->txt("cmps_plugin_deactivated"), true);
		}
			
		$ilCtrl->setParameter($this, "ctype", $_GET["ctype"]);
		$ilCtrl->setParameter($this, "cname", $_GET["cname"]);
		$ilCtrl->setParameter($this, "slot_id", $_GET["slot_id"]);
		$ilCtrl->redirect($this, "showPluginSlot");
	}

	/**
	* Refresh Languages
	*/
	function refreshLanguages()
	{
		global $ilCtrl;

		include_once("./Services/Component/classes/class.ilPlugin.php");
		$pl = ilPlugin::getPluginObject($_GET["ctype"], $_GET["cname"],
			$_GET["slot_id"], $_GET["pname"]);
			
		$result = $pl->updateLanguages();
		
		if ($result !== true)
		{
			ilUtil::sendFailure($result, true);
		}
			
		$ilCtrl->setParameter($this, "ctype", $_GET["ctype"]);
		$ilCtrl->setParameter($this, "cname", $_GET["cname"]);
		$ilCtrl->setParameter($this, "slot_id", $_GET["slot_id"]);
		$ilCtrl->redirect($this, "showPluginSlot");
	}

	/**
	* Update plugin DB
	*/
	function updatePluginDB()
	{
		global $ilDB;
		
		include_once("./Services/Component/classes/class.ilPluginDBUpdate.php");
		$dbupdate = new ilPluginDBUpdate($_GET["ctype"], $_GET["cname"],
			$_GET["slot_id"], $_GET["pname"], $ilDB, true);
			
		$dbupdate->applyUpdate();
		
		if ($dbupdate->updateMsg == "no_changes")
		{
			$message = $this->lng->txt("no_changes").". ".$this->lng->txt("database_is_uptodate");
		}
		else
		{
			foreach ($dbupdate->updateMsg as $row)
			{
				$message .= $this->lng->txt($row["msg"]).": ".$row["nr"]."<br/>";
			}
		}

		ilUtil::sendInfo($message, true);

		$ilCtrl->setParameter($this, "ctype", $_GET["ctype"]);
		$ilCtrl->setParameter($this, "cname", $_GET["cname"]);
		$ilCtrl->setParameter($this, "slot_id", $_GET["slot_id"]);
		$ilCtrl->redirect($this, "showPluginSlot");
	}


}
?>