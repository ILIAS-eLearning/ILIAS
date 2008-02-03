<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
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
				include_once("./classes/class.ilPermissionGUI.php");
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
				array("listModules", "view"));

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

		$comp_set = new ilSetting("cmps");

		include_once("./Services/Component/classes/class.ilComponentsTableGUI.php");
		$comp_table = new ilComponentsTableGUI($this, "listModules");
		
		$this->tpl->setContent($comp_table->getHTML());
	}

	/**
	* List Services
	*/
	public function listServices()
	{
		global $ilCtrl, $lng, $ilSetting;

		include_once("./Services/Component/classes/class.ilComponentsTableGUI.php");
		$comp_table = new ilComponentsTableGUI($this, "listServices", IL_COMP_SERVICE);
		
		$this->tpl->setContent($comp_table->getHTML());
	}

	/**
	* Save Options.
	*/
	function saveOptions()
	{
		global $ilSetting, $ilCtrl;
		
		// disable creation
		if (is_array($_POST["obj_dis_creation"]))
		{
			foreach($_POST["obj_dis_creation"] as $k => $v)
			{
				$ilSetting->set("obj_dis_creation_".$k, $v);
			}
		}

		// disable creation
		if (is_array($_POST["obj_pos"]))
		{
			foreach($_POST["obj_pos"] as $k => $v)
			{
				$ilSetting->set("obj_dis_creation_".$k, $_POST["obj_dis_creation"][$k]);
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
		
		$tpl->setContent($ptpl->get());
		$tpl->setTitle($comp->getComponentType()."/".$comp->getName().": ".
			$lng->txt("cmps_plugin_slot")." \"".$comp->getPluginSlotName($_GET["slot_id"])."\"");
		$tpl->setDescription("");
	}
}
?>