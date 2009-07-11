<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject2GUI.php");
include_once("./Services/Component/classes/class.ilPlugin.php");

/*
* Object GUI class for plugins
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesRepository
*/
abstract class ilObjectPluginGUI extends ilObject2GUI
{
	/**
	* Constructor.
	*/
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id, true);
		$this->plugin =
			ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj",
				ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $this->getType()));
		if (!is_object($this->plugin))
		{
			die("ilObjectPluginGUI: Could not instantiate plugin object for type ".$this->getType().".");
		}
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl, $tpl, $ilAccess, $lng, $ilNavigationHistory, $ilTabs;

		// get standard template (includes main menu and general layout)
		$tpl->getStandardTemplate();

		// set title
		if (!$this->getCreationMode())
		{
			$tpl->setTitle($this->object->getTitle());
			$tpl->setTitleIcon($this->plugin->getImagePath("icon_".$this->object->getType()."_b.gif"),
				$lng->txt("icon")." ".$this->txt("obj_".$this->object->getType()));
				
			// set tabs
			$this->setTabs();
			$this->setLocator();
			
			// add entry to navigation history
			if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
			{
				$ilNavigationHistory->addItem($_GET["ref_id"],
					$ilCtrl->getLinkTarget($this, $this->getStandardCmd()), $this->getType());
			}

		}
		else
		{
			// show info of parent
			$tpl->setTitle(ilObject::_lookupTitle(ilObject::_lookupObjId($_GET["ref_id"])));
			$tpl->setTitleIcon(
				ilObject::_getIcon(ilObject::_lookupObjId($_GET["ref_id"]), "big"),
				$lng->txt("obj_".ilObject::_lookupType($_GET["ref_id"], true)));
			$this->setLocator();

		}

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->checkPermission("visible");
				$this->infoScreen();	// forwards command
				break;

			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$ilTabs->setTabActive("perm_settings");
				$ret = $ilCtrl->forwardCommand($perm_gui);
			break;

			default:
				if(!$cmd)
				{
					$cmd = $this->getStandardCmd();
				}
				if ($cmd == "infoScreen")
				{
					$ilCtrl->setCmd("showSummary");
					$ilCtrl->setCmdClass("ilinfoscreengui");
					$this->infoScreen();
				}
				else
				{
					if ($this->getCreationMode())
					{
						$this->$cmd();
					}
					else
					{
						$this->performCommand($cmd);
					}
				}
				break;
		}

		if (!$this->getCreationMode())
		{
			$tpl->show();
		}
	}

	/**
	* Add object to locator
	*/
	function addLocatorItems()
	{
		global $ilLocator;

		if (!$this->getCreationMode())
		{
			$ilLocator->addItem($this->object->getTitle(),
				$this->ctrl->getLinkTarget($this, $this->getStandardCmd()), "", $_GET["ref_id"]);
		}
	}


	final public function cloneAll() { return parent::cloneAllObject(); }
	
	/**
	* Get plugin object
	*
	* @return	object	plugin object
	*/
	final private function getPlugin()
	{
		return $this->plugin;
	}
	
	/**
	* Wrapper for txt function
	*/
	final protected function txt($a_var)
	{
		return $this->getPlugin()->txt($a_var);
	}
	
	/**
	* Init object creation form
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initEditForm($a_mode = "edit", $a_new_type = "")
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setTarget("_top");
	
		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// description
		$ta = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$this->form->addItem($ta);
	
		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("save", $this->txt($a_new_type."_add"));
			$this->form->addCommandButton("cancelCreation", $lng->txt("cancel"));
			$this->form->setTitle($this->txt($a_new_type."_new"));
		}
		else
		{
			$this->form->addCommandButton("update", $lng->txt("save"));
			$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("edit"));
		}
	                
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	 
	}

	/**
	* After saving
	* @access	public
	*/
	function afterSave($newObj)
	{
		global $ilCtrl;
		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);

		$ilCtrl->initBaseClass("ilObjPluginDispatchGUI");
		$ilCtrl->setTargetScript("ilias.php");
		$ilCtrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
		
//var_dump($ilCtrl->call_node);
//var_dump($ilCtrl->forward);
//var_dump($ilCtrl->parent);
//var_dump($ilCtrl->root_class);

		$ilCtrl->setParameterByClass(get_class($this), "ref_id", $newObj->getRefId());
		$ilCtrl->redirectByClass(array("ilobjplugindispatchgui", get_class($this)), $this->getAfterCreationCmd());
	}
	
	/**
	* Cmd that will be redirected to after creation of a new object.
	*/
	abstract function getAfterCreationCmd();
	
	abstract function getStandardCmd();
	
//	abstract function performCommand();
	
	/**
	* Add info screen tab
	*/
	function addInfoTab()
	{
		global $ilAccess, $ilTabs;
		
		// info screen
		if ($ilAccess->checkAccess('visible', "", $this->object->getRefId()))
		{
			$ilTabs->addTarget("info_short",
				$this->ctrl->getLinkTargetByClass(
				"ilinfoscreengui", "showSummary"),
				"showSummary");
		}
	}

	/**
	* Add permission tab
	*/
	function addPermissionTab()
	{
		global $ilAccess, $ilTabs, $ilCtrl;
		
		// edit permissions
		if($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId()))
		{
			$ilTabs->addTarget("perm_settings",
				$ilCtrl->getLinkTargetByClass("ilpermissiongui", "perm"),
				array("perm","info","owner"), 'ilpermissiongui');
		}
	}
	
	
	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess, $ilUser, $lng, $ilCtrl, $tpl, $ilTabs;
		
		$ilTabs->setTabActive("info_short");
		
		$this->checkPermission("visible");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();

		// general information
		$lng->loadLanguageModule("meta");

		$this->addInfoItems($info);

		// forward the command
		$ret = $ilCtrl->forwardCommand($info);
		//$tpl->setContent($ret);
	}

	/**
	* Add items to info screen
	*/
	function addInfoItems($info)
	{
	}

	/**
	* Goto redirection
	*/
	function _goto($a_target)
	{
		global $ilCtrl, $ilAccess, $lng;
		
		$t = explode("_", $a_target[0]);
		$ref_id = (int) $t[0];
		$class_name = $a_target[1];
		
		if ($ilAccess->checkAccess("read", "", $ref_id))
		{
			$ilCtrl->initBaseClass("ilObjPluginDispatchGUI");
			$ilCtrl->setTargetScript("ilias.php");
			$ilCtrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
			$ilCtrl->setParameterByClass($class_name, "ref_id", $ref_id);
			$ilCtrl->redirectByClass(array("ilobjplugindispatchgui", $class_name), "");
		}
		else if($ilAccess->checkAccess("visible", "", $ref_id))
		{
			$ilCtrl->initBaseClass("ilObjPluginDispatchGUI");
			$ilCtrl->setTargetScript("ilias.php");
			$ilCtrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
			$ilCtrl->setParameterByClass($class_name, "ref_id", $ref_id);
			$ilCtrl->redirectByClass(array("ilobjplugindispatchgui", $class_name), "infoScreen");
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))));
			include("repository.php");
			exit;
		}
	}
	
}
