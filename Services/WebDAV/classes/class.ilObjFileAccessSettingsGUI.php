<?php
// BEGIN WebDAV
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

/**
* Class ilObjFileAccessSettingsGUI
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
*
* @version $Id: class.ilObjFileAccessSettingsGUI.php 13125 2007-01-29 15:37:36Z smeyer $
*
* @ilCtrl_Calls ilObjFileAccessSettingsGUI: ilPermissionGUI
*
* @extends ilObjectGUI
* @package webdav
*/

include_once "classes/class.ilObjectGUI.php";

class ilObjFileAccessSettingsGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjFileAccessSettingsGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "facs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
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
				$this->tabs_gui->setTabActive('settings');
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
			$this->tabs_gui->addTarget("settings",
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
	* Edit settings.
	*/
	public function editSettings()
	{
		global $rbacsystem, $ilErr;

		if (! $rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt("no_permission"),$ilErr->WARNING);
		}

		global $tpl, $ilCtrl, $lng, $tree;

		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
		require_once("./Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("./Services/Form/classes/class.ilRadioOption.php");
		require_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");
		require_once("./Services/WebDAV/classes/class.ilDAVServer.php");

		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("settings"));

		$ilDAVServer = new ilDAVServer();
		
		// Enable webdav
		$isPearAuthHTTPInstalled = include_once("Auth/HTTP.php");
		$cb_prop = new ilCheckboxInputGUI($lng->txt("enable_webdav"), "enable_webdav");
		$cb_prop->setValue('1');
		$cb_prop->setChecked($this->object->isWebdavEnabled() && $isPearAuthHTTPInstalled);
		$cb_prop->setDisabled(! $isPearAuthHTTPInstalled);
		$cb_prop->setInfo($isPearAuthHTTPInstalled ?
			sprintf($lng->txt('enable_webdav_info'),$ilDAVServer->getMountURI($tree->getRootId())) :
			$lng->txt('webdav_pear_auth_http_needed')
		);
		$form->addItem($cb_prop);

		// Show webdav actions next to each item
		$cb_prop = new ilCheckboxInputGUI($lng->txt("display_webdav_actions"), "webdav_actions_visible");
		$cb_prop->setValue('1');
		$cb_prop->setChecked($this->object->isWebdavActionsVisible() && $isPearAuthHTTPInstalled);
		$cb_prop->setDisabled(! $isPearAuthHTTPInstalled);
		$cb_prop->setInfo(sprintf($lng->txt('display_webdav_actions_info')));
		$form->addItem($cb_prop);

		// Webdav help text
		if ($isPearAuthHTTPInstalled)
		{
			$rgi_prop = new ilRadioGroupInputGUI($lng->txt('webfolder_instructions'), 'custom_webfolder_instructions_choice');
			$rgi_prop->addOption(new ilRadioOption($lng->txt('use_default_instructions'), 'default'));
			$rgi_prop->addOption(new ilRadioOption($lng->txt('use_customized_instructions'), 'custom'));
			$rgi_prop->setValue($this->object->isCustomWebfolderInstructionsEnabled() ? 'custom':'default');
			$rgi_prop->setDisabled(! $isPearAuthHTTPInstalled);
			$form->addItem($rgi_prop);
			$tai_prop = new ilTextAreaInputGUI('', 'custom_webfolder_instructions');
			$tai_prop->setValue($this->object->getCustomWebfolderInstructions());
			$tai_prop->setInfo($lng->txt("webfolder_instructions_info"));
			$tai_prop->setCols(80);
			$tai_prop->setRows(20);
			$tai_prop->setDisabled(! $isPearAuthHTTPInstalled);
			$form->addItem($tai_prop);
		}

		// Inline file extensions
		$tai_prop = new ilTextAreaInputGUI($lng->txt('inline_file_extensions'), 'inline_file_extensions');
		$tai_prop->setValue($this->object->getInlineFileExtensions());
		$tai_prop->setInfo($lng->txt('inline_file_extensions_info'));
		$tai_prop->setCols(80);
		$tai_prop->setRows(5);
		$form->addItem($tai_prop);


		// command buttons
		$form->addCommandButton('saveSettings', $lng->txt('save'));
		$form->addCommandButton('view', $lng->txt('cancel'));

		$tpl->setContent($form->getHTML());
	}

	/**
	* Save settings
	*/
	public function saveSettings()
	{
		global $rbacsystem, $ilErr;

		if (! $rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt("no_permission"),$ilErr->WARNING);
		}

		$this->object->setWebdavEnabled($_POST['enable_webdav'] == '1');
		$this->object->setWebdavActionsVisible($_POST['webdav_actions_visible'] == '1');
		$this->object->setCustomWebfolderInstructionsEnabled($_POST['custom_webfolder_instructions_choice']=='custom');
		$this->object->setCustomWebfolderInstructions($_POST['custom_webfolder_instructions']);
		$this->object->setInlineFileExtensions($_POST['inline_file_extensions']);
		$this->object->update();

		$this->editSettings();
	}

	/**
	* called by prepare output
	*/
	function setTitleAndDescription()
	{
		parent::setTitleAndDescription();
		$this->tpl->setDescription($this->object->getDescription());
	}
	
} 
// END WebDAV
?>
