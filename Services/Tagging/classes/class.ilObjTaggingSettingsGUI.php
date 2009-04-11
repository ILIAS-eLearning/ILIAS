<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* Media Cast Settings.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjTaggingSettingsGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjTaggingSettingsGUI: ilAdministrationGUI
*
* @ingroup ServicesTagging
*/
class ilObjTaggingSettingsGUI extends ilObjectGUI
{
    private static $ERROR_MESSAGE;
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = 'tags';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule('tagging');
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
					$cmd = "editSettings";
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
			$this->tabs_gui->addTarget("tagging_edit_settings",
				$this->ctrl->getLinkTarget($this, "editSettings"),
				array("editSettings", "view"));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
				array(),'ilpermissiongui');
		}
	}

	/**
	* Edit mediacast settings.
	*/
	public function editSettings()
	{
		$this->tabs_gui->setTabActive('tagging_edit_settings');		
		$this->initFormSettings();
		return true;
	}

	/**
	* Save mediacast settings
	*/
	public function saveSettings()
	{
		global $ilCtrl, $ilSetting;

		$tags_set = new ilSetting("tags");
		$tags_set->set("enable", ilUtil::stripSlashes($_POST["enable_tagging"]));
		$ilSetting->set("block_activated_pdtag", $_POST["enable_tagging"]);

		ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
		$ilCtrl->redirect($this, "view");
	}

	/**
	* Save mediacast settings
	*/
	public function cancel()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "view");
	}
		
	/**
	 * Init settings property form
	 *
	 * @access protected
	 */
	protected function initFormSettings()
	{
	    global $lng;
		
		$tags_set = new ilSetting("tags");
		
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('tagging_settings'));
		$form->addCommandButton('saveSettings',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));

		// enable tagging
		$cb_prop = new ilCheckboxInputGUI($lng->txt("tagging_enable_tagging"),
			"enable_tagging");
		$cb_prop->setValue("1");
		$cb_prop->setChecked($tags_set->get("enable"));

		$form->addItem($cb_prop);
		$this->tpl->setContent($form->getHTML());
	}
}
?>