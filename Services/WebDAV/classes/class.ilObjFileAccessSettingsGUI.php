<?php
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
* @version $Id$
*
* @ilCtrl_Calls ilObjFileAccessSettingsGUI: ilPermissionGUI
*
* @extends ilObjectGUI
* @package webdav
*/

include_once "classes/class.ilObjectGUI.php";

class ilObjFileAccessSettingsGUI extends ilObjectGUI
{
	private $disk_quota_obj;

	/**
	* Constructor
	* @access public
	*/
	function ilObjFileAccessSettingsGUI($a_data,$a_id,$a_call_by_reference)
	{
		global $tree;

		$this->type = "facs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
		$this->folderSettings = new ilSetting('fold');

		// Load the disk quota settings object
		require_once 'Services/WebDAV/classes/class.ilObjDiskQuotaSettings.php';
		$this->disk_quota_obj = new ilObjDiskQuotaSettings($a_id, $a_call_by_reference);
		$this->disk_quota_obj->read();
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
			$ilErr->raiseError($lng->txt('no_permission'),$ilErr->WARNING);
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
					$cmd = "editDownloadingSettings";
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
			$this->tabs_gui->addTarget('downloading_settings',
				$this->ctrl->getLinkTarget($this, "editDownloadingSettings"),
				array("editDownloadingSettings", "view"));

			$this->tabs_gui->addTarget('webdav',
				$this->ctrl->getLinkTarget($this, "editWebDAVSettings"),
				array("editWebDAVSettings", "view"));

			$this->tabs_gui->addTarget("disk_quota",
				$this->ctrl->getLinkTarget($this, "editDiskQuotaSettings"),
				array("editDiskQuota", "view"));
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
	public function editDownloadingSettings()
	{
		global $rbacsystem, $ilErr, $ilTabs;

		$this->tabs_gui->setTabActive('downloading_settings');

		if (! $rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$ilErr->raiseError($lng->txt("no_permission"),$ilErr->WARNING);
		}

		global $tpl, $ilCtrl, $lng, $tree, $settings;

		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
		require_once("./Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("./Services/Form/classes/class.ilRadioOption.php");
		require_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");

		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("settings"));
		
		// Backwards compatibility with ILIAS 3.9: Use the name of the
		// uploaded file as the filename for the downloaded file instead
		// of the title of the file object.
		$dl_prop = new ilCheckboxInputGUI($lng->txt("download_with_uploaded_filename"), "download_with_uploaded_filename");
		$dl_prop->setValue('1');
		// default value should reflect previous behaviour (-> 0)
		$dl_prop->setChecked($this->object->isDownloadWithUploadedFilename() == 1);
		$dl_prop->setInfo($lng->txt('download_with_uploaded_filename_info'));
		$form->addItem($dl_prop);

		// Show download action for folder
		$dl_prop = new ilCheckboxInputGUI($lng->txt("enable_download_folder"), "enable_download_folder");
		$dl_prop->setValue('1');
		// default value should reflect previous behaviour (-> 0)
		$dl_prop->setChecked($this->folderSettings->get("enable_download_folder", 0) == 1);
		$dl_prop->setInfo($lng->txt('enable_download_folder_info'));
		$form->addItem($dl_prop);

		
		// Inline file extensions
		$tai_prop = new ilTextAreaInputGUI($lng->txt('inline_file_extensions'), 'inline_file_extensions');
		$tai_prop->setValue($this->object->getInlineFileExtensions());
		$tai_prop->setInfo($lng->txt('inline_file_extensions_info'));
		$tai_prop->setCols(80);
		$tai_prop->setRows(5);
		$form->addItem($tai_prop);


		// command buttons
		$form->addCommandButton('saveDownloadingSettings', $lng->txt('save'));
		$form->addCommandButton('view', $lng->txt('cancel'));

		$tpl->setContent($form->getHTML());
	}

	/**
	* Save settings
	*/
	public function saveDownloadingSettings()
	{
		global $rbacsystem, $ilErr, $ilCtrl, $lng;

		if (! $rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$ilErr->raiseError($lng->txt("no_permission"),$ilErr->WARNING);
		}

		$this->object->setDownloadWithUploadedFilename(ilUtil::stripSlashes($_POST['download_with_uploaded_filename']));
		$this->object->setInlineFileExtensions(ilUtil::stripSlashes($_POST['inline_file_extensions']));
		$this->object->update();

		$this->folderSettings->set("enable_download_folder", $_POST["enable_download_folder"] == 1);

		ilUtil::sendInfo($lng->txt('settings_saved'),true);
		$ilCtrl->redirect($this, "editDownloadingSettings");
	}

	/**
	* Edit settings.
	*/
	public function editWebDAVSettings()
	{
		global $rbacsystem, $ilErr, $ilTabs;
		global $tpl, $ilCtrl, $lng, $tree, $settings;


		$this->tabs_gui->setTabActive('webdav');

		if (! $rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$ilErr->raiseError($lng->txt("no_permission"),$ilErr->WARNING);
		}

		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
		require_once("./Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("./Services/Form/classes/class.ilRadioOption.php");
		require_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");
		require_once("./Services/WebDAV/classes/class.ilDAVServer.php");

		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("settings"));

		// Enable webdav
		$ilDAVServer = new ilDAVServer();
		$isPearAuthHTTPInstalled = @include_once("Auth/HTTP.php");
		$cb_prop = new ilCheckboxInputGUI($lng->txt("enable_webdav"), "enable_webdav");
		$cb_prop->setValue('1');
		$cb_prop->setChecked($this->object->isWebdavEnabled() && $isPearAuthHTTPInstalled);
		$cb_prop->setDisabled(! $isPearAuthHTTPInstalled);
		$cb_prop->setInfo($isPearAuthHTTPInstalled ?
			sprintf($lng->txt('enable_webdav_info'),$ilDAVServer->getMountURI($tree->getRootId(),0,null,null,true)) :
			$lng->txt('webdav_pear_auth_http_needed')
		);
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

		// command buttons
		$form->addCommandButton('saveWebDAVSettings', $lng->txt('save'));
		$form->addCommandButton('view', $lng->txt('cancel'));

		$tpl->setContent($form->getHTML());
	}

	/**
	* Save settings
	*/
	public function saveWebDAVSettings()
	{
		global $rbacsystem, $ilErr, $ilCtrl, $lng;

		if (! $rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$ilErr->raiseError($lng->txt("no_permission"),$ilErr->WARNING);
		}

		$this->object->setWebdavEnabled($_POST['enable_webdav'] == '1');
//		$this->object->setWebdavActionsVisible($_POST['webdav_actions_visible'] == '1');
		$this->object->setCustomWebfolderInstructionsEnabled($_POST['custom_webfolder_instructions_choice']=='custom');
		$this->object->setCustomWebfolderInstructions(ilUtil::stripSlashes($_POST['custom_webfolder_instructions']));
		$this->object->update();
		
		ilUtil::sendInfo($lng->txt('settings_saved'),true);
		$ilCtrl->redirect($this, "editWebDAVSettings");
	}
	
	/**
	* called by prepare output
	*/
	function setTitleAndDescription()
	{
		parent::setTitleAndDescription();
		$this->tpl->setDescription($this->object->getDescription());
	}


	// DISK QUOTA --------------------------------------------------------------
	/**
	* Add disk quota subtabs
	*/
	function addDiskQuotaSubtabs($a_active_subtab)
	{
		global $ilCtrl, $ilTabs;

		include_once("./Services/COPage/classes/class.ilPageEditorSettings.php");

		$ilTabs->addSubTabTarget("settings",
			 $ilCtrl->getLinkTarget($this, "editDiskQuotaSettings"),
			 array("editDiskQuotaSettings"));

		require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
		if (ilDiskQuotaActivationChecker::_isActive())
		{
			$ilTabs->addSubTabTarget("disk_quota_report",
				 $ilCtrl->getLinkTarget($this, "viewDiskQuotaReport"),
				 array("viewDiskQuotaReport"));
		}

		$ilTabs->addSubTabTarget("disk_quota_reminder_mail",
			 $ilCtrl->getLinkTarget($this, "editDiskQuotaMailTemplate"),
			 array("editDiskQuotaMailTemplate"));

		$ilTabs->setSubTabActive($a_active_subtab);
	}


	/**
	* Edit disk quota settings.
	*/
	public function editDiskQuotaSettings()
	{
		global $rbacsystem, $ilErr, $ilSetting, $tpl, $lng, $ilCtrl;


		if (! $rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$ilErr->raiseError($lng->txt("no_permission"),$ilErr->WARNING);
		}

		$this->tabs_gui->setTabActive('disk_quota');
		$this->addDiskQuotaSubtabs('settings');

		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
		require_once("./Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("./Services/Form/classes/class.ilRadioOption.php");
		require_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");
		require_once("./Services/WebDAV/classes/class.ilDAVServer.php");

		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("settings"));


		// Enable disk quota
		$cb_prop = new ilCheckboxInputGUI($lng->txt("enable_disk_quota"), "enable_disk_quota");
		$cb_prop->setValue('1');
		$cb_prop->setChecked($this->disk_quota_obj->isDiskQuotaEnabled());
		$cb_prop->setInfo($lng->txt('enable_disk_quota_info'));
		$form->addItem($cb_prop);

		// Enable disk quota reminder mail
		$cb_prop = new ilCheckboxInputGUI($lng->txt("enable_disk_quota_reminder_mail"), "enable_disk_quota_reminder_mail");
		$cb_prop->setValue('1');
		$cb_prop->setChecked($this->disk_quota_obj->isDiskQuotaReminderMailEnabled());
		$cb_prop->setInfo($lng->txt('disk_quota_reminder_mail_desc'));
		$form->addItem($cb_prop);



		// command buttons
		$form->addCommandButton('saveDiskQuotaSettings', $lng->txt('save'));
		$form->addCommandButton('editDiskQuotaSettings', $lng->txt('cancel'));

		$tpl->setContent($form->getHTML());
	}

	/**
	* Save disk quota settings.
	*/
	public function saveDiskQuotaSettings()
	{
		global $rbacsystem, $ilErr, $ilCtrl, $lng;

		if (! $rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$ilErr->raiseError($lng->txt("no_permission"),$ilErr->WARNING);
		}

		$this->disk_quota_obj->setDiskQuotaEnabled($_POST['enable_disk_quota'] == '1');
		$this->disk_quota_obj->setDiskQuotaReminderMailEnabled($_POST['enable_disk_quota_reminder_mail'] == '1');
		$this->disk_quota_obj->update();


		ilUtil::sendInfo($lng->txt('settings_saved'),true);
		$ilCtrl->redirect($this, "editDiskQuotaSettings");
	}

	/**
	* The disk quota report list shows user accounts, their disk quota and their
    * disk usage, as well as the last time a reminder was sent.
	*/
	public function viewDiskQuotaReport()
	{
		global $rbacsystem, $ilErr, $ilSetting, $lng;

		if (! $rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$ilErr->raiseError($lng->txt("no_permission"),$ilErr->WARNING);
		}

		$this->tabs_gui->setTabActive('disk_quota');
		$this->addDiskQuotaSubtabs('disk_quota_report');

		// nothing to do if disk quota is not active
		require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
		if (! ilDiskQuotaActivationChecker::_isActive())
		{
			return;
		}

		// get the form
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.disk_quota_report.html');

		// get the date of the last update
		require_once("./Services/WebDAV/classes/class.ilDiskQuotaChecker.php");
		$last_update = ilDiskQuotaChecker::_lookupDiskUsageReportLastUpdate();
		if ($last_update == null)
		{
			// nothing to do if disk usage report has not been run
			$this->tpl->setVariable('LAST_UPDATE_TEXT',$lng->txt('disk_quota_report_not_run_yet'));
			return;
		}
		else
		{
			$this->tpl->setVariable('LAST_UPDATE_TEXT',$lng->txt('last_update').': '.ilFormat::formatDate($last_update,'datetime',true));
		}

		// Filter
		$_SESSION['quota_usage_filter'] = isset($_POST['usage_filter']) ? $_POST['usage_filter'] : $_SESSION['quota_usage_filter'];
		if ($_SESSION['quota_usage_filter'] == 0)
		{
			$_SESSION['quota_usage_filter'] = 4;
		}
		$_SESSION['quota_access_filter'] = isset($_POST['access_filter']) ? $_POST['access_filter'] : $_SESSION['quota_access_filter'];
		if ($_SESSION['quota_access_filter'] == 0)
		{
			$_SESSION['quota_access_filter'] = 1;
		}
		$usage_action[1] = $lng->txt('all_users');
		$usage_action[2] = $lng->txt('filter_users_without_disk_usage');
		$usage_action[3] = $lng->txt('filter_users_with_disk_usage');
		$usage_action[4] = $lng->txt('filter_users_with_exceeded_disk_quota');
		$access_action[1] = $lng->txt('all_users');
		$access_action[2] = $lng->txt('filter_users_with_access');
		$access_action[3] = $lng->txt('filter_users_without_access');

		$select_usage_filter = ilUtil::formSelect($_SESSION['quota_usage_filter'],"usage_filter",$usage_action,false,true);
		$select_access_filter = ilUtil::formSelect($_SESSION['quota_access_filter'],"access_filter",$access_action,false,true);

		$this->tpl->setCurrentBlock("filter");
		$this->tpl->setVariable("FILTER_TXT_FILTER",$lng->txt('filter'));
		$this->tpl->setVariable("SELECT_USAGE_FILTER",$select_usage_filter);
		$this->tpl->setVariable("SELECT_ACCESS_FILTER",$select_access_filter);
		$this->tpl->setVariable("FILTER_ACTION",$this->ctrl->getLinkTarget($this, 'viewDiskQuotaReport'));
		$this->tpl->setVariable("FILTER_NAME",'view');
		$this->tpl->setVariable("FILTER_VALUE",$lng->txt('apply_filter'));
		$this->tpl->parseCurrentBlock();

		// load templates for table
	 	$a_tpl = new ilTemplate('tpl.table.html',true,true);
		$a_tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		// create table
		require_once './Services/Table/classes/class.ilTableGUI.php';
		$tbl = new ilTableGUI(0, false);

		// title & header columns
		$header_vars = array('login','firstname','lastname','email','access_until','last_login','disk_quota','disk_usage', 'last_reminder');
		$tbl->setHeaderNames(
			array(
				$lng->txt('login'),
				$lng->txt('firstname'),
				$lng->txt('lastname'),
				$lng->txt('email'),
				$lng->txt('access_until'),
				$lng->txt('last_login'),
				$lng->txt('disk_quota'),
				$lng->txt('disk_usage'),
				$lng->txt('last_reminder')
			)
		);
		$tbl->setHeaderVars(
			$header_vars,
			$this->ctrl->getParameterArray($this,'viewDiskQuotaReport',false)
		);

		$tbl->enable("numinfo_header");
		$tbl->setFormName("cmd");
		$tbl->setSelectAllCheckbox("id");

		// sorting 
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);

		// fetch the data
		$data = ilDiskQuotaChecker::_fetchDiskQuotaReport(
			$_SESSION['quota_usage_filter'],
			$_SESSION['quota_access_filter'],
			$header_vars[$tbl->getOrderColumn()], $tbl->getOrderDirection());

		// paging
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($data));

		// footer
		$tbl->setFooter("tblfooter",$lng->txt("previous"),$lng->txt("next"));
		
		// render table
		$tbl->setTemplate($a_tpl);

		// render rows
		$count = 0;
		for ($i = $tbl->getOffset(); $i < count($data) && $i < $tbl->getOffset() + $tbl->getLimit(); $i++)
		{
			$row = $data[$i];

			// build columns
			foreach ($header_vars as $key)
			{
				switch ($key)
				{
					case 'login' :
						//build link
						$this->ctrl->setParameterByClass("ilobjusergui", "ref_id", "7");
						$this->ctrl->setParameterByClass("ilobjusergui", "obj_id", $row["usr_id"]);
						$link = $this->ctrl->getLinkTargetByClass("ilobjusergui", "view");
						$tbl_content_cell = '<a href="'.$link.'">'.htmlspecialchars($row[$key]).'</a>';
						break;
					case 'disk_quota' :
						if ($row['role_id'] == SYSTEM_ROLE_ID)
						{
							$tbl_content_cell = "<span class=\"smallgreen\">".$lng->txt('access_unlimited').'</span>';
						}
						else
						{
							$tbl_content_cell = ilFormat::formatSize($row[$key],'short');
						}
						break;
					case 'disk_usage' :
						if ($row['last_update'] == null)
						{
							$tbl_content_cell = $lng->txt('unknown');
						}
						else if ($row['disk_usage'] > $row['disk_quota'])
						{
						 $tbl_content_cell = "<span class=\"smallred\">".ilFormat::formatSize($row[$key],'short').'</span>';
						}
						else
						{
						 $tbl_content_cell = ilFormat::formatSize($row[$key],'short');
						}
						break;
					case 'access_until' :
						if (! $row['active'])
						{
							 $tbl_content_cell = "<span class=\"smallred\">".$lng->txt('inactive').'</span>';
						}
						else if ($row['time_limit_unlimited'])
						{
							$tbl_content_cell = "<span class=\"smallgreen\">".$lng->txt('access_unlimited').'</span>';
						}
						else if ($row['expired'])
						{
							 $tbl_content_cell = "<span class=\"smallred\">".$lng->txt('access_expired').'</span>';
						}
						else
						{
							$tbl_content_cell = ilFormat::formatDate($row[$key]);
						}
						break;
					case 'last_login' :
					case 'last_reminder' :
						if ($row[$key] == null)
						{
							$tbl_content_cell = $lng->txt('no_date');
						}
						else
						{
							$tbl_content_cell = ilFormat::formatDate($row[$key]);
						}
						break;
					default :
						 $tbl_content_cell = htmlspecialchars($row[$key]);
				}
				/*
				if (is_array($tbl_content_cell))
				{
					$tbl->tpl->setCurrentBlock("tbl_cell_subtitle");
					$tbl->tpl->setVariable("TBL_CELL_SUBTITLE",$tbl_content_cell[1]);
					$tbl->tpl->parseCurrentBlock();
					$tbl_content_cell = "<b>".$tbl_content_cell[0]."</b>";
				}*/

				$tbl->tpl->setCurrentBlock("tbl_content_cell");
				$tbl->tpl->setVariable("TBL_CONTENT_CELL",$tbl_content_cell);

				$tbl->tpl->parseCurrentBlock();
			}

			$tbl->tpl->setCurrentBlock("tbl_content_row");
			$rowcolor = ilUtil::switchColor($count,"tblrow1","tblrow2");
			$tbl->tpl->setVariable("ROWCOLOR", $rowcolor);
			$tbl->tpl->parseCurrentBlock();

			$count++;
		}
		$tbl->render();

		// Add table to page
		$this->tpl->setVariable("USER_TABLE",$a_tpl->get());
	}

	/**
	* Edit disk quota settings.
	*/
	public function editDiskQuotaMailTemplate()
	{
		global $rbacsystem, $ilErr, $ilSetting, $tpl, $lng, $ilCtrl;

		if (! $rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$ilErr->raiseError($lng->txt("no_permission"),$ilErr->WARNING);
		}

		$this->tabs_gui->setTabActive('disk_quota');
		$this->addDiskQuotaSubtabs('disk_quota_reminder_mail');

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.disk_quota_reminder_mail.html');
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("IMG_MAIL", ilUtil::getImagePath("icon_mail.gif"));

		$lng->loadLanguageModule("meta");
		$lng->loadLanguageModule("mail");
		$this->tpl->setVariable("TXT_NEW_USER_ACCOUNT_MAIL", $lng->txt("disk_quota_reminder_mail"));
		$this->tpl->setVariable("TXT_NEW_USER_ACCOUNT_MAIL_DESC", $lng->txt("disk_quota_reminder_mail_desc"));

		// placeholder help text
		$this->tpl->setVariable("TXT_USE_PLACEHOLDERS", $lng->txt("mail_nacc_use_placeholder"));
		$this->tpl->setVariable("TXT_MAIL_SALUTATION", $lng->txt("mail_nacc_salutation"));
		$this->tpl->setVariable("TXT_FIRST_NAME", $lng->txt("firstname"));
		$this->tpl->setVariable("TXT_LAST_NAME", $lng->txt("lastname"));
		$this->tpl->setVariable("TXT_EMAIL", $lng->txt("email"));
		$this->tpl->setVariable("TXT_LOGIN", $lng->txt("mail_nacc_login"));
		$this->tpl->setVariable("TXT_DISK_QUOTA", $lng->txt("disk_quota"));
		$this->tpl->setVariable("TXT_DISK_USAGE", $lng->txt("disk_usage"));
		$this->tpl->setVariable("TXT_DISK_USAGE_DETAILS", $lng->txt("disk_usage_details"));
		$this->tpl->setVariable("TXT_ADMIN_MAIL", $lng->txt("mail_nacc_admin_mail"));
		$this->tpl->setVariable("TXT_ILIAS_URL", $lng->txt("mail_nacc_ilias_url"));
		$this->tpl->setVariable("TXT_CLIENT_NAME", $lng->txt("mail_nacc_client_name"));

		$langs = $lng->getInstalledLanguages();
		foreach($langs as $lang_key)
		{
			$amail = $this->disk_quota_obj->_lookupReminderMailTemplate($lang_key);
			$this->tpl->setCurrentBlock("mail_block");
			$add = "";
			if ($lang_key == $lng->getDefaultLanguage())
			{
				$add = " (".$lng->txt("default").")";
			}
			$this->tpl->setVariable("TXT_LANGUAGE",
				$lng->txt("meta_l_".$lang_key).$add);
			$this->tpl->setVariable("TXT_BODY", $lng->txt("message_content"));
			$this->tpl->setVariable("TA_BODY", "body_".$lang_key);
			$this->tpl->setVariable("VAL_BODY",
				ilUtil::prepareFormOutput($amail["body"]));
			$this->tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));
			$this->tpl->setVariable("INPUT_SUBJECT", "subject_".$lang_key);
			$this->tpl->setVariable("VAL_SUBJECT",
				ilUtil::prepareFormOutput($amail["subject"]));
			$this->tpl->setVariable("TXT_SAL_G", $lng->txt("mail_salutation_general"));
			$this->tpl->setVariable("INPUT_SAL_G", "sal_g_".$lang_key);
			$this->tpl->setVariable("VAL_SAL_G",
				ilUtil::prepareFormOutput($amail["sal_g"]));
			$this->tpl->setVariable("TXT_SAL_M", $lng->txt("mail_salutation_male"));
			$this->tpl->setVariable("INPUT_SAL_M", "sal_m_".$lang_key);
			$this->tpl->setVariable("VAL_SAL_M",
				ilUtil::prepareFormOutput($amail["sal_m"]));
			$this->tpl->setVariable("TXT_SAL_F", $lng->txt("mail_salutation_female"));
			$this->tpl->setVariable("INPUT_SAL_F", "sal_f_".$lang_key);
			$this->tpl->setVariable("VAL_SAL_F",
				ilUtil::prepareFormOutput($amail["sal_f"]));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("TXT_CANCEL", $lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SAVE", $lng->txt("save"));
	}
	function cancelDiskQuotaMailTemplate()
	{
		$this->ctrl->redirect($this, "editDiskQuotaSettings");
	}

	function saveDiskQuotaMailTemplate()
	{
		global $lng;

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$langs = $lng->getInstalledLanguages();
		foreach($langs as $lang_key)
		{
			$this->disk_quota_obj->_writeReminderMailTemplate($lang_key,
				ilUtil::stripSlashes($_POST["subject_".$lang_key]),
				ilUtil::stripSlashes($_POST["sal_g_".$lang_key]),
				ilUtil::stripSlashes($_POST["sal_f_".$lang_key]),
				ilUtil::stripSlashes($_POST["sal_m_".$lang_key]),
				ilUtil::stripSlashes($_POST["body_".$lang_key]));
		}
		$this->ctrl->redirect($this, "editDiskQuotaMailTemplate");
	}

} 
?>
