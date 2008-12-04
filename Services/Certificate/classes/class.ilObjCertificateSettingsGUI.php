<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* Certificate Settings.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjCertificateSettingsGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjCertificateSettingsGUI: ilAdministrationGUI
*
* @ingroup ServicesCertificate
*/
class ilObjCertificateSettingsGUI extends ilObjectGUI
{
    private static $ERROR_MESSAGE;
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		$this->type = 'cert';
		$this->lng->loadLanguageModule("certificate");
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
					$cmd = "settings";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Get tabs
	 */
	public function getAdminTabs()
	{
		global $rbacsystem, $ilAccess;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "settings"),
				array("settings", "view"));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
				array(),'ilpermissiongui');
		}
	}

	/**
	* Edit settings.
	*/
	public function settings()
	{
		global $lng;

		$this->tabs_gui->setTabActive('settings');
		$form_settings = new ilSetting("certificate");
		
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('certificate_settings'));

		$info = new ilNonEditableValueGUI($this->lng->txt("info"), "info");
		$info->setValue($this->lng->txt("certificate_usage"));
		$form->addItem($info);

		$bgimage = new ilImageFileInputGUI($this->lng->txt("certificate_background_image"), "background");
		$bgimage->setRequired(FALSE);
		if (count($_POST)) 
		{
			// handle the background upload
			if (strlen($_FILES["background"]["tmp_name"]))
			{
				if ($bgimage->checkInput())
				{
					$result = $this->object->uploadBackgroundImage($_FILES["background"]["tmp_name"]);
					if ($result == FALSE)
					{
						$bgimage->setAlert($this->lng->txt("certificate_error_upload_bgimage"));
					}
				}
			}
		}
		if (strlen($this->object->hasBackgroundImage())) $bgimage->setImage($this->object->getBackgroundImageThumbPathWeb());
		$bgimage->setInfo($this->lng->txt("default_background_info"));
		$form->addItem($bgimage);


		$form->addCommandButton('save',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));

		$this->tpl->setContent($form->getHTML());

		if (strcmp($this->ctrl->getCmd(), "save") == 0)
		{
			if ($_POST["background_delete"])
			{
				$this->object->deleteBackgroundImage();
			}
		}
	}
	
	public function save()
	{
		$this->settings();
	}
	
	public function cancel()
	{
		$this->ctrl->redirect($this, "settings");
	}
}
?>