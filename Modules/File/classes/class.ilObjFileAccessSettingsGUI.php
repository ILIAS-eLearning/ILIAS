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

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\File\Sanitation\DownloadSanitationReportUserInteraction;
use ILIAS\File\Sanitation\SanitationReportJob;

include_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
 * Class ilObjFileAccessSettingsGUI
 *
 * @author       Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
 *
 * @version      $Id$
 *
 * @ilCtrl_Calls ilObjFileAccessSettingsGUI: ilPermissionGUI, ilFMSettingsGUI
 *
 * @extends      ilObjectGUI
 * @package      webdav
 */
class ilObjFileAccessSettingsGUI extends ilObjectGUI
{
    const INSTALL_README_PATH = '/docs/configuration/install.md';
    const CMD_EDIT_DOWNLOADING_SETTINGS = 'editDownloadingSettings';
    const CMD_EDIT_WEBDAV_SETTINGS = 'editWebDAVSettings';
    const CMD_SANITIZE = 'sanitize';
    /**
     * @var \ilSetting
     */
    protected $folderSettings;
    /**
     * @var \ilObjDiskQuotaSettings
     */
    private $disk_quota_obj;


    /**
     * Constructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        $this->type = "facs";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
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
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilias = $DIC['ilias'];
        $lng = $DIC['lng'];

        $lng->loadLanguageModule("file");

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$ilAccess->checkAccess('read', '', $this->object->getRefId())) {
            $ilias->raiseError($lng->txt('no_permission'), $ilias->error_obj->MESSAGE);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret =&$this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilfmsettingsgui':
                $this->tabs_gui->setTabActive('fm_settings_tab');
                include_once './Services/WebServices/FileManager/classes/class.ilFMSettingsGUI.php';
                $fmg = new ilFMSettingsGUI($this);
                $this->ctrl->forwardCommand($fmg);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = self::CMD_EDIT_DOWNLOADING_SETTINGS;
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
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        $GLOBALS['DIC']['lng']->loadLanguageModule('fm');

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'downloading_settings',
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT_DOWNLOADING_SETTINGS),
                array(self::CMD_EDIT_DOWNLOADING_SETTINGS, "view")
            );

            $this->tabs_gui->addTarget(
                'upload_settings',
                $this->ctrl->getLinkTarget($this, "editUploadSettings"),
                array("editUploadSettings", "view")
            );

            $this->tabs_gui->addTarget(
                'preview_settings',
                $this->ctrl->getLinkTarget($this, "editPreviewSettings"),
                array("editPreviewSettings", "view")
            );

            $this->tabs_gui->addTarget('webdav', $this->ctrl->getLinkTarget($this, "editWebDAVSettings"), array("editWebDAVSettings", "view"));

            $this->tabs_gui->addTarget(
                'fm_settings_tab',
                $this->ctrl->getLinkTargetByClass('ilFMSettingsGUI', 'settings'),
                array(),
                'ilfmsettingsgui'
            );

            $this->tabs_gui->addTarget("disk_quota", $this->ctrl->getLinkTarget($this, "editDiskQuotaSettings"), array("editDiskQuota", "view"));
        }
        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget("perm_settings", $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"), array(), 'ilpermissiongui');
        }
    }


    /**
     * Edit settings.
     */
    protected function initDownloadingSettingsForm()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

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

        // multi download
        $dl_prop = new ilCheckboxInputGUI($lng->txt("enable_multi_download"), "enable_multi_download");
        $dl_prop->setValue('1');
        // default value should reflect previous behaviour (-> 0)
        $dl_prop->setChecked($this->folderSettings->get("enable_multi_download", 0) == 1);
        $dl_prop->setInfo($lng->txt('enable_multi_download_info'));
        $form->addItem($dl_prop);

        // background task

        $lng->loadLanguageModule("bgtask");
        $dl_bg = new ilCheckboxInputGUI($lng->txt("bgtask_setting"), "enable_bg");
        $dl_bg->setInfo($lng->txt("bgtask_setting_info"));
        $dl_bg->setChecked($this->folderSettings->get("bgtask_download", 0));
        $form->addItem($dl_bg);

        $dl_bgtc = new ilNumberInputGUI($lng->txt("bgtask_setting_threshold_count"), "bg_tcount");
        $dl_bgtc->setInfo($lng->txt("bgtask_setting_threshold_count_info"));
        $dl_bgtc->setRequired(true);
        $dl_bgtc->setSize(10);
        $dl_bgtc->setMinValue(1);
        $dl_bgtc->setSuffix($lng->txt("files"));
        $dl_bgtc->setValue($this->folderSettings->get("bgtask_download_tcount", null));
        $dl_bg->addSubItem($dl_bgtc);

        $dl_bgts = new ilNumberInputGUI($lng->txt("bgtask_setting_threshold_size"), "bg_tsize");
        $dl_bgts->setInfo($lng->txt("bgtask_setting_threshold_size_info"));
        $dl_bgts->setRequired(true);
        $dl_bgts->setSize(10);
        $dl_bgts->setMinValue(1);
        $dl_bgts->setSuffix($lng->txt("lang_size_mb"));
        $dl_bgts->setValue($this->folderSettings->get("bgtask_download_tsize", null));
        $dl_bg->addSubItem($dl_bgts);

        $dl_bgl = new ilNumberInputGUI($lng->txt("bgtask_setting_limit"), "bg_limit");
        $dl_bgl->setInfo($lng->txt("bgtask_setting_limit_info"));
        $dl_bgl->setRequired(true);
        $dl_bgl->setSize(10);
        $dl_bgl->setMinValue(1);
        $dl_bgl->setSuffix($lng->txt("lang_size_mb"));
        $dl_bgl->setValue($this->folderSettings->get("bgtask_download_limit", null));
        $dl_bg->addSubItem($dl_bgl);

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

        return $form;
    }


    /**
     * Edit settings.
     */
    public function editDownloadingSettings(ilPropertyFormGUI $a_form = null)
    {
        global $DIC, $ilErr;

        // $DIC->toolbar()->addButton($this->lng->txt('generate_sanitize_report'), $this->ctrl->getLinkTarget($this, self::CMD_SANITIZE));

        $this->tabs_gui->setTabActive('downloading_settings');

        if (!$DIC->rbac()->system()->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($DIC->language()->txt("no_permission"), $ilErr->WARNING);
        }

        if (!$a_form) {
            $a_form = $this->initDownloadingSettingsForm();
        }

        $DIC->ui()->mainTemplate()->setContent($a_form->getHTML());
    }


    public function sanitize()
    {
        global $DIC;

        $report = $DIC->backgroundTasks()->taskFactory()->createTask(SanitationReportJob::class);
        $ui = $DIC->backgroundTasks()->taskFactory()->createTask(DownloadSanitationReportUserInteraction::class, [$report]);

        $bucket = new BasicBucket();
        $bucket->setUserId($DIC->user()->getId());
        $bucket->setTask($ui);
        $bucket->setTitle("File Sanitiation Report");
        $bucket->setDescription("");

        $DIC->backgroundTasks()->taskManager()->run($bucket);
        $this->ctrl->redirect($this, self::CMD_EDIT_DOWNLOADING_SETTINGS);
    }


    /**
     * Save settings
     */
    public function saveDownloadingSettings()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            ilUtil::sendFailure($DIC->language()->txt("no_permission"), true);
            $DIC->ctrl()->redirect($this, self::CMD_EDIT_DOWNLOADING_SETTINGS);
        }

        $form = $this->initDownloadingSettingsForm();
        if ($form->checkInput()) {
            $this->object->setDownloadWithUploadedFilename(ilUtil::stripSlashes($_POST['download_with_uploaded_filename']));
            $this->object->setInlineFileExtensions(ilUtil::stripSlashes($_POST['inline_file_extensions']));
            $this->object->update();

            $this->folderSettings->set("enable_download_folder", $_POST["enable_download_folder"] == 1);
            $this->folderSettings->set("enable_multi_download", $_POST["enable_multi_download"] == 1);

            $this->folderSettings->set("bgtask_download", (bool) $_POST["enable_bg"]);
            if ((bool) $_POST["enable_bg"]) {
                $this->folderSettings->set("bgtask_download_limit", (int) $_POST["bg_limit"]);
                $this->folderSettings->set("bgtask_download_tcount", (int) $_POST["bg_tcount"]);
                $this->folderSettings->set("bgtask_download_tsize", (int) $_POST["bg_tsize"]);
            }

            ilUtil::sendSuccess($DIC->language()->txt('settings_saved'), true);
            $DIC->ctrl()->redirect($this, self::CMD_EDIT_DOWNLOADING_SETTINGS);
        }

        $form->setValuesByPost();
        $this->editDownloadingSettings($form);
    }


    protected function initWebDAVSettingsForm()
    {
        global $DIC;

        $lng = $DIC->language();

        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        require_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
        require_once("./Services/Form/classes/class.ilRadioGroupInputGUI.php");
        require_once("./Services/Form/classes/class.ilRadioOption.php");
        require_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($DIC->ctrl()->getFormAction($this));
        $form->setTitle($lng->txt("settings"));

        // Enable webdav
        $cb_prop = new ilCheckboxInputGUI($lng->txt("enable_webdav"), "enable_webdav");
        $cb_prop->setValue('1');
        $cb_prop->setChecked($this->object->isWebdavEnabled());
        $cb_prop->setInfo($this->getAdditionalWebDAVInformation());
        $form->addItem($cb_prop);

        $rgi_prop = new ilRadioGroupInputGUI($lng->txt('webfolder_instructions'), 'custom_webfolder_instructions_choice');
        $rgi_prop->addOption(new ilRadioOption($lng->txt('use_default_instructions'), 'default'));
        $rgi_prop->addOption(new ilRadioOption($lng->txt('use_customized_instructions'), 'custom'));
        $rgi_prop->setValue($this->object->isCustomWebfolderInstructionsEnabled() ? 'custom' : 'default');
        $form->addItem($rgi_prop);
        $tai_prop = new ilTextAreaInputGUI('', 'custom_webfolder_instructions');
        $tai_prop->setValue($this->object->getCustomWebfolderInstructions());
        $tai_prop->setInfo($lng->txt("webfolder_instructions_info"));
        $tai_prop->setRows(20);
        $form->addItem($tai_prop);

        // command buttons
        $form->addCommandButton('saveWebDAVSettings', $lng->txt('save'));
        $form->addCommandButton('view', $lng->txt('cancel'));

        return $form;
    }


    /**
     * Edit settings.
     */
    public function editWebDAVSettings()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->tabs_gui->setTabActive('webdav');

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($lng->txt("no_permission"), $ilErr->WARNING);
        }

        $form = $this->initWebDAVSettingsForm();
        $tpl->setContent($form->getHTML());
    }


    /**
     * Save settings
     */
    public function saveWebDAVSettings()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            ilUtil::sendFailure($DIC->language()->txt('no_permission'), true);
            $DIC->ctrl()->redirect($this, self::CMD_EDIT_WEBDAV_SETTINGS);
        }

        $form = $this->initWebDAVSettingsForm();
        if ($form->checkInput()) {
            $this->object->setWebdavEnabled($_POST['enable_webdav'] == '1');
            //		$this->object->setWebdavActionsVisible($_POST['webdav_actions_visible'] == '1');
            $this->object->setCustomWebfolderInstructionsEnabled($_POST['custom_webfolder_instructions_choice'] == 'custom');
            $this->object->setCustomWebfolderInstructions(ilUtil::stripSlashes($_POST['custom_webfolder_instructions'], false));
            $this->object->update();
            ilUtil::sendSuccess($lng->txt('settings_saved'), true);
            $ilCtrl->redirect($this, self::CMD_EDIT_WEBDAV_SETTINGS);
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }


    public function getAdditionalWebDAVInformation()
    {
        global $DIC;
        $lng = $DIC->language();

        return $furtherInformation = sprintf($lng->txt('webdav_additional_information'), $this->getInstallationDocsLink());
    }


    public function getInstallationDocsLink()
    {
        return ilUtil::_getHttpPath() . self::INSTALL_README_PATH;
    }


    /**
     * called by prepare output
     */
    public function setTitleAndDescription()
    {
        parent::setTitleAndDescription();
        $this->tpl->setDescription($this->object->getDescription());
    }


    // DISK QUOTA --------------------------------------------------------------


    /**
     * Add disk quota subtabs
     */
    public function addDiskQuotaSubtabs($a_active_subtab)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];

        include_once("./Services/COPage/classes/class.ilPageEditorSettings.php");

        $ilTabs->addSubTabTarget("settings", $ilCtrl->getLinkTarget($this, "editDiskQuotaSettings"), array("editDiskQuotaSettings"));

        require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
        if (ilDiskQuotaActivationChecker::_isActive()) {
            $ilTabs->addSubTabTarget("disk_quota_report", $ilCtrl->getLinkTarget($this, "viewDiskQuotaReport"), array("viewDiskQuotaReport"));
        }

        $ilTabs->addSubTabTarget(
            "disk_quota_reminder_mail",
            $ilCtrl->getLinkTarget($this, "editDiskQuotaMailTemplate"),
            array("editDiskQuotaMailTemplate")
        );

        $ilTabs->setSubTabActive($a_active_subtab);
    }


    /**
     * Edit disk quota settings.
     */
    public function editDiskQuotaSettings()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $ilSetting = $DIC['ilSetting'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($lng->txt("no_permission"), $ilErr->WARNING);
        }

        $this->tabs_gui->setTabActive('disk_quota');
        $this->addDiskQuotaSubtabs('settings');

        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        require_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
        require_once("./Services/Form/classes/class.ilRadioGroupInputGUI.php");
        require_once("./Services/Form/classes/class.ilRadioOption.php");
        require_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");

        $lng->loadLanguageModule("file");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("settings"));

        include_once "Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php";
        ilAdministrationSettingsFormHandler::addFieldsToForm(ilAdministrationSettingsFormHandler::FORM_FILES_QUOTA, $form, $this);

        /*
        // command buttons
        $form->addCommandButton('saveDiskQuotaSettings', $lng->txt('save'));
        $form->addCommandButton('editDiskQuotaSettings', $lng->txt('cancel'));
        */

        $tpl->setContent($form->getHTML());
    }


    /**
     * Save disk quota settings.
     */
    public function saveDiskQuotaSettings()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $ilErr->raiseError($lng->txt("no_permission"), $ilErr->WARNING);
        }

        // ilUtil::sendInfo($lng->txt('settings_saved'),true);
        $ilCtrl->redirect($this, "editDiskQuotaSettings");
    }


    /**
     * The disk quota report list shows user accounts, their disk quota and their
     * disk usage, as well as the last time a reminder was sent.
     */
    public function viewDiskQuotaReport()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $ilSetting = $DIC['ilSetting'];
        $lng = $DIC['lng'];

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($lng->txt("no_permission"), $ilErr->WARNING);
        }

        $this->tabs_gui->setTabActive('disk_quota');
        $this->addDiskQuotaSubtabs('disk_quota_report');

        // nothing to do if disk quota is not active
        require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
        if (!ilDiskQuotaActivationChecker::_isActive()) {
            return;
        }

        // get the form
        $this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.disk_quota_report.html', "Services/WebDAV");

        // get the date of the last update
        require_once("./Services/WebDAV/classes/class.ilDiskQuotaChecker.php");
        $last_update = ilDiskQuotaChecker::_lookupDiskUsageReportLastUpdate();
        if ($last_update == null) {
            // nothing to do if disk usage report has not been run
            $this->tpl->setVariable('LAST_UPDATE_TEXT', $lng->txt('disk_quota_report_not_run_yet'));

            return;
        } else {
            $this->tpl->setVariable(
                'LAST_UPDATE_TEXT',
                $lng->txt('last_update') . ': ' . ilDatePresentation::formatDate(new ilDateTime($last_update, IL_CAL_DATETIME))
            );
        }

        // Filter
        $_SESSION['quota_usage_filter'] = isset($_POST['usage_filter']) ? $_POST['usage_filter'] : $_SESSION['quota_usage_filter'];
        if ($_SESSION['quota_usage_filter'] == 0) {
            $_SESSION['quota_usage_filter'] = 4;
        }
        $_SESSION['quota_access_filter'] = isset($_POST['access_filter']) ? $_POST['access_filter'] : $_SESSION['quota_access_filter'];
        if ($_SESSION['quota_access_filter'] == 0) {
            $_SESSION['quota_access_filter'] = 1;
        }
        $usage_action[1] = $lng->txt('all_users');
        $usage_action[2] = $lng->txt('filter_users_without_disk_usage');
        $usage_action[3] = $lng->txt('filter_users_with_disk_usage');
        $usage_action[4] = $lng->txt('filter_users_with_exceeded_disk_quota');
        $access_action[1] = $lng->txt('all_users');
        $access_action[2] = $lng->txt('filter_users_with_access');
        $access_action[3] = $lng->txt('filter_users_without_access');

        $select_usage_filter = ilUtil::formSelect($_SESSION['quota_usage_filter'], "usage_filter", $usage_action, false, true);
        $select_access_filter = ilUtil::formSelect($_SESSION['quota_access_filter'], "access_filter", $access_action, false, true);

        $this->tpl->setCurrentBlock("filter");
        $this->tpl->setVariable("FILTER_TXT_FILTER", $lng->txt('filter'));
        $this->tpl->setVariable("SELECT_USAGE_FILTER", $select_usage_filter);
        $this->tpl->setVariable("SELECT_ACCESS_FILTER", $select_access_filter);
        $this->tpl->setVariable("FILTER_ACTION", $this->ctrl->getLinkTarget($this, 'viewDiskQuotaReport'));
        $this->tpl->setVariable("FILTER_NAME", 'view');
        $this->tpl->setVariable("FILTER_VALUE", $lng->txt('apply_filter'));
        $this->tpl->parseCurrentBlock();

        // load templates for table
        $a_tpl = new ilTemplate('tpl.table.html', true, true);
        $a_tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

        // create table
        require_once './Services/Table/classes/class.ilTableGUI.php';
        $tbl = new ilTableGUI(0, false);
        $tbl->setTitle('');

        // title & header columns
        $header_vars = array('login', 'firstname', 'lastname', 'email', 'access_until', 'last_login', 'disk_quota', 'disk_usage', 'last_reminder');
        $tbl->setHeaderNames(array(
            $lng->txt('login'),
            $lng->txt('firstname'),
            $lng->txt('lastname'),
            $lng->txt('email'),
            $lng->txt('access_until'),
            $lng->txt('last_login'),
            $lng->txt('disk_quota'),
            $lng->txt('disk_usage'),
            $lng->txt('last_reminder'),
        ));
        $tbl->setHeaderVars($header_vars, $this->ctrl->getParameterArray($this, 'viewDiskQuotaReport', false));

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
            $header_vars[$tbl->getOrderColumn()],
            $tbl->getOrderDirection()
        );

        // paging
        $tbl->setLimit($_GET["limit"]);
        $tbl->setOffset($_GET["offset"]);
        $tbl->setMaxCount(count($data));

        // footer
        $tbl->setFooter("tblfooter", $lng->txt("previous"), $lng->txt("next"));

        // render table
        $tbl->setTemplate($a_tpl);

        // render rows
        $count = 0;
        for ($i = $tbl->getOffset(); $i < count($data) && $i < $tbl->getOffset() + $tbl->getLimit(); $i++) {
            $row = $data[$i];

            // build columns
            foreach ($header_vars as $key) {
                switch ($key) {
                    case 'login':
                        //build link
                        $this->ctrl->setParameterByClass("ilobjusergui", "ref_id", "7");
                        $this->ctrl->setParameterByClass("ilobjusergui", "obj_id", $row["usr_id"]);
                        $link = $this->ctrl->getLinkTargetByClass("ilobjusergui", "view");
                        $tbl_content_cell = '<a href="' . $link . '">' . htmlspecialchars($row[$key]) . '</a>';
                        break;
                    case 'disk_quota':
                        if ($row['role_id'] == SYSTEM_ROLE_ID) {
                            $tbl_content_cell = "<span class=\"smallgreen\">" . $lng->txt('access_unlimited') . '</span>';
                        } else {
                            $tbl_content_cell = ilUtil::formatSize($row[$key], 'short');
                        }
                        break;
                    case 'disk_usage':
                        if ($row['last_update'] == null) {
                            $tbl_content_cell = $lng->txt('unknown');
                        } else {
                            if ($row['disk_usage'] > $row['disk_quota']) {
                                $tbl_content_cell = "<span class=\"smallred\">" . ilUtil::formatSize($row[$key], 'short') . '</span>';
                            } else {
                                $tbl_content_cell = ilUtil::formatSize($row[$key], 'short');
                            }
                        }
                        break;
                    case 'access_until':
                        if (!$row['active']) {
                            $tbl_content_cell = "<span class=\"smallred\">" . $lng->txt('inactive') . '</span>';
                        } else {
                            if ($row['time_limit_unlimited']) {
                                $tbl_content_cell = "<span class=\"smallgreen\">" . $lng->txt('access_unlimited') . '</span>';
                            } else {
                                if ($row['expired']) {
                                    $tbl_content_cell = "<span class=\"smallred\">" . $lng->txt('access_expired') . '</span>';
                                } else {
                                    $tbl_content_cell = ilDatePresentation::formatDate(new ilDateTime($row[$key], IL_CAL_DATETIME));
                                }
                            }
                        }
                        break;
                    case 'last_login':
                    case 'last_reminder':
                        if ($row[$key] == null) {
                            $tbl_content_cell = $lng->txt('no_date');
                        } else {
                            $tbl_content_cell = ilDatePresentation::formatDate(new ilDateTime($row[$key], IL_CAL_DATETIME));
                        }
                        break;
                    default:
                        $tbl_content_cell = htmlspecialchars($row[$key]);
                }

                $tbl->getTemplateObject()->setCurrentBlock("tbl_content_cell");
                $tbl->getTemplateObject()->setVariable("TBL_CONTENT_CELL", $tbl_content_cell);

                $tbl->getTemplateObject()->parseCurrentBlock();
            }

            $tbl->getTemplateObject()->setCurrentBlock("tbl_content_row");
            $rowcolor = ilUtil::switchColor($count, "tblrow1", "tblrow2");
            $tbl->getTemplateObject()->setVariable("ROWCOLOR", $rowcolor);
            $tbl->getTemplateObject()->parseCurrentBlock();

            $count++;
        }
        $tbl->render();

        // Add table to page
        $this->tpl->setVariable("USER_TABLE", $a_tpl->get());
    }


    protected function initDiskQuotaMailTemplateForm()
    {
        global $DIC;
        $lng = $DIC['lng'];

        $lng->loadLanguageModule("meta");
        $lng->loadLanguageModule("mail");

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->setTitle($lng->txt("disk_quota_reminder_mail"));
        $form->setDescription($lng->txt("disk_quota_reminder_mail_desc"));

        foreach ($lng->getInstalledLanguages() as $lang_key) {
            $lang_def = ($lang_key == $lng->getDefaultLanguage()) ? " (" . $lng->txt("default") . ")" : "";

            $sec = new ilFormSectionHeaderGUI();
            $sec->setTitle($lng->txt("meta_l_" . $lang_key) . $lang_def);
            $form->addItem($sec);

            $subj = new ilTextInputGUI($lng->txt("subject"), "subject_" . $lang_key);
            $subj->setRequired(true);
            $form->addItem($subj);

            $sal_g = new ilTextInputGUI($lng->txt("mail_salutation_general"), "sal_g_" . $lang_key);
            $sal_g->setRequired(true);
            $form->addItem($sal_g);

            $sal_f = new ilTextInputGUI($lng->txt("mail_salutation_female"), "sal_f_" . $lang_key);
            $sal_f->setRequired(true);
            $form->addItem($sal_f);

            $sal_m = new ilTextInputGUI($lng->txt("mail_salutation_male"), "sal_m_" . $lang_key);
            $sal_m->setRequired(true);
            $form->addItem($sal_m);

            $body = new ilTextAreaInputGUI($lng->txt("message_content"), "body_" . $lang_key);
            $body->setRequired(true);
            $body->setRows(10);
            $form->addItem($body);

            // current values
            $amail = ilObjDiskQuotaSettings::_lookupReminderMailTemplate($lang_key);
            $subj->setValue($amail["subject"]);
            $sal_g->setValue($amail["sal_g"]);
            $sal_f->setValue($amail["sal_f"]);
            $sal_m->setValue($amail["sal_m"]);
            $body->setValue($amail["body"]);
        }

        $form->addCommandButton("saveDiskQuotaMailTemplate", $lng->txt("save"));
        $form->addCommandButton("editDiskQuotaSettings", $lng->txt("cancel"));

        return $form;
    }


    /**
     * Edit disk quota settings.
     */
    public function editDiskQuotaMailTemplate(ilPropertyFormGUI $a_form = null)
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($lng->txt("no_permission"), $ilErr->WARNING);
        }

        $this->tabs_gui->setTabActive("disk_quota");
        $this->addDiskQuotaSubtabs("disk_quota_reminder_mail");

        if (!$a_form) {
            $a_form = $this->initDiskQuotaMailTemplateForm();
        }

        $tpl = new ilTemplate("tpl.disk_quota_reminder_mail.html", true, true, "Services/WebDAV");
        $tpl->setVariable("TXT_USE_PLACEHOLDERS", $lng->txt("mail_nacc_use_placeholder"));
        $tpl->setVariable("TXT_MAIL_SALUTATION", $lng->txt("mail_nacc_salutation"));
        $tpl->setVariable("TXT_FIRST_NAME", $lng->txt("firstname"));
        $tpl->setVariable("TXT_LAST_NAME", $lng->txt("lastname"));
        $tpl->setVariable("TXT_EMAIL", $lng->txt("email"));
        $tpl->setVariable("TXT_LOGIN", $lng->txt("mail_nacc_login"));
        $tpl->setVariable("TXT_DISK_QUOTA", $lng->txt("disk_quota"));
        $tpl->setVariable("TXT_DISK_USAGE", $lng->txt("disk_usage"));
        $tpl->setVariable("TXT_DISK_USAGE_DETAILS", $lng->txt("disk_usage_details"));
        $tpl->setVariable("TXT_ADMIN_MAIL", $lng->txt("mail_nacc_admin_mail"));
        $tpl->setVariable("TXT_ILIAS_URL", $lng->txt("mail_nacc_ilias_url"));
        $tpl->setVariable("TXT_CLIENT_NAME", $lng->txt("mail_nacc_client_name"));

        include_once "Services/UIComponent/Panel/classes/class.ilPanelGUI.php";
        $legend = ilPanelGUI::getInstance();
        $legend->setHeadingStyle(ilPanelGUI::HEADING_STYLE_BLOCK);
        $legend->setHeading($lng->txt("mail_nacc_use_placeholder"));
        $legend->setBody($tpl->get());

        $this->tpl->setContent($a_form->getHTML() . $legend->getHTML());
    }


    public function saveDiskQuotaMailTemplate()
    {
        global $DIC;
        $lng = $DIC['lng'];

        $form = $this->initDiskQuotaMailTemplateForm();
        if ($form->checkInput()) {
            foreach ($lng->getInstalledLanguages() as $lang_key) {
                $this->disk_quota_obj->_writeReminderMailTemplate(
                    $lang_key,
                    $form->getInput("subject_" . $lang_key),
                    $form->getInput("sal_g_" . $lang_key),
                    $form->getInput("sal_f_" . $lang_key),
                    $form->getInput("sal_m_" . $lang_key),
                    $form->getInput("body_" . $lang_key)
                );
            }

            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "editDiskQuotaMailTemplate");
        }

        $form->setValuesByPost();
        $this->editDiskQuotaMailTemplate($form);
    }


    /**
     * Initializes the upload settings form.
     */
    private function initUploadSettingsForm()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("settings"));

        require_once("Services/FileUpload/classes/class.ilFileUploadSettings.php");

        // drag and drop file upload
        $chk_enabled = new ilCheckboxInputGUI($lng->txt("enable_dnd_upload"), "enable_dnd_upload");
        $chk_enabled->setValue('1');
        $chk_enabled->setChecked(ilFileUploadSettings::isDragAndDropUploadEnabled());
        $chk_enabled->setInfo($lng->txt('enable_dnd_upload_info'));
        $form->addItem($chk_enabled);

        // drag and drop file upload in repository
        $chk_repo = new ilCheckboxInputGUI($lng->txt("enable_repository_dnd_upload"), "enable_repository_dnd_upload");
        $chk_repo->setValue('1');
        $chk_repo->setChecked(ilFileUploadSettings::isRepositoryDragAndDropUploadEnabled());
        $chk_repo->setInfo($lng->txt('enable_repository_dnd_upload_info'));
        $chk_enabled->addSubItem($chk_repo);

        // concurrent uploads
        $num_prop = new ilNumberInputGUI($lng->txt("concurrent_uploads"), "concurrent_uploads");
        $num_prop->setDecimals(0);
        $num_prop->setMinValue(1);
        $num_prop->setMinvalueShouldBeGreater(false);
        $num_prop->setMaxValue(ilFileUploadSettings::CONCURRENT_UPLOADS_MAX);
        $num_prop->setMaxvalueShouldBeLess(false);
        $num_prop->setMaxLength(5);
        $num_prop->setSize(10);
        $num_prop->setValue(ilFileUploadSettings::getConcurrentUploads());
        $num_prop->setInfo($lng->txt('concurrent_uploads_info'));
        $chk_enabled->addSubItem($num_prop);

        include_once("./Services/Utilities/classes/class.ilFileUtils.php");

        // default white list
        $ne = new ilNonEditableValueGUI($this->lng->txt("file_suffix_default_white"), "");
        $ne->setValue(implode(", ", ilFileUtils::getDefaultValidExtensionWhiteList()));
        $ne->setInfo($this->lng->txt("file_suffix_default_white_info"));
        $form->addItem($ne);

        // file suffix custom black list
        $ta = new ilTextAreaInputGUI($this->lng->txt("file_suffix_custom_black"), "suffix_repl_additional");
        $ta->setInfo($this->lng->txt("file_suffix_custom_black_info"));
        $ta->setRows(5);
        $form->addItem($ta);

        // file suffix custom white list
        $ta = new ilTextAreaInputGUI($this->lng->txt("file_suffix_custom_white"), "suffix_custom_white_list");
        $ta->setInfo($this->lng->txt("file_suffix_custom_white_info"));
        $ta->setRows(5);
        $form->addItem($ta);

        // resulting overall white list
        $ne = new ilNonEditableValueGUI($this->lng->txt("file_suffix_overall_white"), "");
        $ne->setValue(implode(", ", ilFileUtils::getValidExtensions()));
        $ne->setInfo($this->lng->txt("file_suffix_overall_white_info"));
        $form->addItem($ne);

        // command buttons
        $form->addCommandButton('saveUploadSettings', $lng->txt('save'));
        $form->addCommandButton('view', $lng->txt('cancel'));

        return $form;
    }


    /**
     * Edit upload settings.
     */
    public function editUploadSettings()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilSetting = $DIC['ilSetting'];

        $this->tabs_gui->setTabActive('upload_settings');

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($lng->txt("no_permission"), $ilErr->WARNING);
        }

        // get form
        $form = $this->initUploadSettingsForm();

        require_once("Services/FileUpload/classes/class.ilFileUploadSettings.php");

        // set current values
        $val = array();
        $val["enable_dnd_upload"] = ilFileUploadSettings::isDragAndDropUploadEnabled();
        $val["enable_repository_dnd_upload"] = ilFileUploadSettings::isRepositoryDragAndDropUploadEnabled();
        $val["concurrent_uploads"] = ilFileUploadSettings::getConcurrentUploads();
        $val["suffix_repl_additional"] = $ilSetting->get("suffix_repl_additional");
        $val["suffix_custom_white_list"] = $ilSetting->get("suffix_custom_white_list");
        $form->setValuesByArray($val);

        // set content
        $tpl->setContent($form->getHTML());
    }


    /**
     * Save upload settings
     */
    public function saveUploadSettings()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilSetting = $DIC['ilSetting'];

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $ilErr->raiseError($lng->txt("no_permission"), $ilErr->WARNING);
        }

        // get form
        $form = $this->initUploadSettingsForm();
        if ($form->checkInput()) {
            require_once("Services/FileUpload/classes/class.ilFileUploadSettings.php");
            ilFileUploadSettings::setDragAndDropUploadEnabled($_POST["enable_dnd_upload"] == 1);
            ilFileUploadSettings::setRepositoryDragAndDropUploadEnabled($_POST["enable_repository_dnd_upload"] == 1);
            ilFileUploadSettings::setConcurrentUploads($_POST["concurrent_uploads"]);

            // file suffic replacements
            $ilSetting->set("suffix_repl_additional", $_POST["suffix_repl_additional"]);
            $ilSetting->set("suffix_custom_white_list", $_POST["suffix_custom_white_list"]);

            ilUtil::sendSuccess($lng->txt('settings_saved'), true);
            $ilCtrl->redirect($this, "editUploadSettings");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }


    /**
     * Initializes the preview settings form.
     */
    private function initPreviewSettingsForm()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("settings"));

        require_once("Services/Preview/classes/class.ilPreviewSettings.php");

        // drag and drop file upload in repository
        $chk_prop = new ilCheckboxInputGUI($lng->txt("enable_preview"), "enable_preview");
        $chk_prop->setValue('1');
        $chk_prop->setChecked(ilPreviewSettings::isPreviewEnabled());
        $chk_prop->setInfo($lng->txt('enable_preview_info'));
        $form->addItem($chk_prop);

        $num_prop = new ilNumberInputGUI($lng->txt("max_previews_per_object"), "max_previews_per_object");
        $num_prop->setDecimals(0);
        $num_prop->setMinValue(1);
        $num_prop->setMinvalueShouldBeGreater(false);
        $num_prop->setMaxValue(ilPreviewSettings::MAX_PREVIEWS_MAX);
        $num_prop->setMaxvalueShouldBeLess(false);
        $num_prop->setMaxLength(5);
        $num_prop->setSize(10);
        $num_prop->setValue(ilPreviewSettings::getMaximumPreviews());
        $num_prop->setInfo($lng->txt('max_previews_per_object_info'));
        $form->addItem($num_prop);

        // command buttons
        $form->addCommandButton('savePreviewSettings', $lng->txt('save'));
        $form->addCommandButton('view', $lng->txt('cancel'));

        return $form;
    }


    /**
     * Edit preview settings.
     */
    public function editPreviewSettings()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];

        $this->tabs_gui->setTabActive('preview_settings');

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($lng->txt("no_permission"), $ilErr->WARNING);
        }

        // set warning if ghostscript not installed
        include_once("./Services/Preview/classes/class.ilGhostscriptRenderer.php");
        if (!ilGhostscriptRenderer::isGhostscriptInstalled()) {
            ilUtil::sendInfo($lng->txt("ghostscript_not_configured"));
        }

        // get form
        $form = $this->initPreviewSettingsForm();

        // set current values
        require_once("Services/Preview/classes/class.ilPreviewSettings.php");

        $val = array();
        $val["enable_preview"] = ilPreviewSettings::isPreviewEnabled();
        $val["max_previews_per_object"] = ilPreviewSettings::getMaximumPreviews();
        $form->setValuesByArray($val);

        $html = $form->getHTML();

        // build renderer HTML
        require_once("Services/Preview/classes/class.ilRendererFactory.php");
        require_once("Services/Preview/classes/class.ilRendererTableGUI.php");

        $renderers = ilRendererFactory::getRenderers();

        $table = new ilRendererTableGUI($this, array("editPreviewSettings", "view"));
        $table->setMaxCount(sizeof($renderers));
        $table->setData($renderers);

        $html .= "<br/>" . $table->getHTML();

        // set content
        $tpl->setContent($html);
    }


    /**
     * Save preview settings
     */
    public function savePreviewSettings()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];

        $this->tabs_gui->setTabActive('preview_settings');

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $ilErr->raiseError($lng->txt("no_permission"), $ilErr->WARNING);
        }

        // get form
        $form = $this->initPreviewSettingsForm();
        if ($form->checkInput()) {
            require_once("Services/Preview/classes/class.ilPreviewSettings.php");
            ilPreviewSettings::setPreviewEnabled($_POST["enable_preview"] == 1);
            ilPreviewSettings::setMaximumPreviews($_POST["max_previews_per_object"]);

            ilUtil::sendSuccess($lng->txt('settings_saved'), true);
            $ilCtrl->redirect($this, "editPreviewSettings");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }


    public function addToExternalSettingsForm($a_form_id)
    {
        global $DIC;
        $ilSetting = $DIC['ilSetting'];

        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_SECURITY:

                $fields = array('file_suffix_repl' => $ilSetting->get("suffix_repl_additional"));

                return array(array("editUploadSettings", $fields));
        }
    }
}
