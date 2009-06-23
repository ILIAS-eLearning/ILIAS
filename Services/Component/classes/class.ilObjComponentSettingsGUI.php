<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./classes/class.ilObjectGUI.php");


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
		global $rbacsystem,$ilErr,$ilAccess;

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
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "listModules";
				}

				$this->$cmd();
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
		global $rbacsystem, $ilAccess;
		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("cmps_modules",
				$this->ctrl->getLinkTarget($this, "listModules"),
				array("listModules", "view", "showPluginSlot"));

			$this->tabs_gui->addTarget("cmps_services",
				$this->ctrl->getLinkTarget($this, "listServices"),
				array("listServices"));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
				array(),'ilpermissiongui');
		}
	}

	/**
	* List Modules
	*/
	public function listModules()
	{
		global $ilCtrl, $lng, $ilSetting;
		
		$this->tabs_gui->setTabActive('cmps_modules');

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
		
		$this->tabs_gui->setTabActive('cmps_services');

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
	* Save Options.
	*/
	function saveOptions()
	{
		global $ilSetting, $ilCtrl;

		// disable creation
		if (is_array($_POST["obj_pos"]))
		{
			foreach($_POST["obj_pos"] as $k => $v)
			{
				$ilSetting->set("obj_dis_creation_".$k, (int) $_POST["obj_dis_creation"][$k]);
			}
		}
		
		// add new position
		if (is_array($_POST["obj_pos"]))
		{
			reset($_POST["obj_pos"]);
			foreach($_POST["obj_pos"] as $k => $v)
			{
				$ilSetting->set("obj_add_new_pos_".$k, $v);
			}
		}
		
		$ilCtrl->redirect($this, "listModules");
	}
	
	/**
	* Show information about a plugin slot.
	*/
	function showPluginSlot()
	{
		global $tpl,$lng;
		
		//slot_id
		$ptpl = new ilTemplate("tpl.plugin_slot.html", true, true,
			"Services/Component");
		
		include_once("./Services/Component/classes/class.ilComponent.php");
		$comp = ilComponent::getComponentObject($_GET["ctype"], $_GET["cname"]);
		$ptpl->setVariable("TXT_COMPONENT", $lng->txt("cmps_component"));
		$ptpl->setVariable("VAL_COMPONENT_NAME", $comp->getComponentType()."/".$comp->getName());
		$ptpl->setVariable("VAL_COMPONENT_ID", $comp->getId());
		
		$ptpl->setVariable("TXT_PLUGIN_SLOT", $lng->txt("cmps_plugin_slot"));
		$ptpl->setVariable("VAL_PLUGIN_SLOT", $comp->getPluginSlotName($_GET["slot_id"]));
		$ptpl->setVariable("VAL_PLUGIN_SLOT_ID", $_GET["slot_id"]);
		
		// directories
		$ptpl->setVariable("TXT_PLUGIN_DIR", $lng->txt("cmps_plugin_dirs"));
		$ptpl->setVariable("VAL_PLUGIN_DIR",
			$comp->getPluginSlotDirectory($_GET["slot_id"])."/&lt;Plugin_Name&gt;");
		$ptpl->setVariable("TXT_MAIN_DIR", $lng->txt("cmps_main_dir"));
		$ptpl->setVariable("TXT_PLUGIN_FILE", $lng->txt("cmps_plugin_file"));
		$ptpl->setVariable("TXT_LANG_FILES", $lng->txt("cmps_lang_files"));
		$ptpl->setVariable("TXT_DB_UPDATE", $lng->txt("cmps_db_update"));
		$ptpl->setVariable("VAL_PLUGIN_FILE",
			"&lt;".$lng->txt("cmps_main_dir")."&gt;".
			"/classes/class.il&lt;Plugin_Name&gt;Plugin.php");
		$ptpl->setVariable("VAL_LANG_FILES",
			"&lt;".$lng->txt("cmps_main_dir")."&gt;".
			"/lang/ilias_&lt;Language ID&gt;.lang");
		$ptpl->setVariable("VAL_DB_UPDATE",
			"&lt;".$lng->txt("cmps_main_dir")."&gt;".
			"/sql/dbupdate.php");

		$ptpl->setVariable("TXT_PLUGIN_LANG_PREFIX", $lng->txt("cmps_plugin_lang_prefixes"));
		$ptpl->setVariable("VAL_PLUGIN_LANG_PREFIX",
			$comp->getPluginSlotLanguagePrefix($_GET["slot_id"])."&lt;Plugin_ID&gt;_");

		$ptpl->setVariable("TXT_PLUGIN_DB_PREFIX", $lng->txt("cmps_plugin_db_prefixes"));
		$ptpl->setVariable("VAL_PLUGIN_DB_PREFIX",
			$comp->getPluginSlotLanguagePrefix($_GET["slot_id"])."&lt;Plugin_ID&gt;_");

		// plugins table
		include_once("./Services/Component/classes/class.ilPluginsTableGUI.php");
		$plugins_table = new ilPluginsTableGUI($this, "showPluginSlot",
			$_GET["ctype"], $_GET["cname"], $_GET["slot_id"]);
		$ptpl->setVariable("PLUGIN_LIST", $plugins_table->getHTML());

		// set content and title
		$tpl->setContent($ptpl->get());
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
		global $ilCtrl;

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
			
		$ilCtrl->setParameter($this, "ctype", $_GET["ctype"]);
		$ilCtrl->setParameter($this, "cname", $_GET["cname"]);
		$ilCtrl->setParameter($this, "slot_id", $_GET["slot_id"]);
		$ilCtrl->redirect($this, "showPluginSlot");
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