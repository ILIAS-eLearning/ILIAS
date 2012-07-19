<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject2GUI.php");

/**
 * Help settings gui class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjHelpSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjHelpSettingsGUI: ilAdministrationGUI
 *
 * @ingroup ServicesHelp
 */
class ilObjHelpSettingsGUI extends ilObject2GUI
{
	/**
	 * Constructor (please note that we, due to the general call in
	 * ilAdministrationGUI, we simulate the old ilObjectGUI constructor here)
	 *
	 * @param
	 * @return
	 */
	function __construct($a_data, $a_ref_id)
	{
		parent::__construct($a_ref_id);
	}
	
	
	/**
	 * Get type
	 */
	function getType()
	{
		return "hlps";
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $rbacsystem, $ilErr, $ilAccess;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		if (!$ilAccess->checkAccess('read','',$this->object->getRefId()))
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
					$cmd = "editSettings";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	* Edit news settings.
	*/
	public function editSettings()
	{
		global $ilCtrl, $lng, $ilSetting;
		
//		$news_set = new ilSetting("news");
//		$feed_set = new ilSetting("feed");

		$this->tpl->setContent("Alpha");
	}

	
}
?>