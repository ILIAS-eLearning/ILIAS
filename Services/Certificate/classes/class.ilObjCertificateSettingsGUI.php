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
include_once("./Services/Object/classes/class.ilObjectGUI.php");


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
    /**
     * @var ilAccessHandler
     */
    protected $hierarchical_access;

    /**
     * @var ilRbacSystem
     */
    protected $access;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    private static $ERROR_MESSAGE;

    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->type = 'cert';
        $this->lng->loadLanguageModule("certificate");
        $this->lng->loadLanguageModule("trac");

        $this->access              = $DIC['rbacsystem'];
        $this->error               = $DIC['ilErr'];
        $this->hierarchical_access = $DIC['ilAccess'];
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->hierarchical_access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
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
        if ($this->access->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "settings"),
                array("settings", "view")
            );
        }

        if ($this->access->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    /**
     * Edit settings.
     */
    public function settings()
    {
        $this->tabs_gui->setTabActive('settings');
        $form_settings = new ilSetting("certificate");

        include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('certificate_settings'));

        $active = new ilCheckboxInputGUI($this->lng->txt("active"), "active");
        $active->setChecked($form_settings->get("active"));
        $form->addItem($active);

        $info = new ilNonEditableValueGUI($this->lng->txt("info"), "info");
        $info->setValue($this->lng->txt("certificate_usage"));
        $form->addItem($info);

        $bgimage = new ilImageFileInputGUI($this->lng->txt("certificate_background_image"), "background");
        $bgimage->setRequired(false);
        if (count($_POST)) {
            // handle the background upload
            if (strlen($_FILES["background"]["tmp_name"])) {
                if ($bgimage->checkInput()) {
                    $result = $this->object->uploadBackgroundImage($_FILES["background"]["tmp_name"]);
                    if ($result == false) {
                        $bgimage->setAlert($this->lng->txt("certificate_error_upload_bgimage"));
                    }
                }
            }
        }
        if (strlen($this->object->hasBackgroundImage())) {
            require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
            ilWACSignedPath::setTokenMaxLifetimeInSeconds(15);
            $bgimage->setImage(ilWACSignedPath::signFile($this->object->getBackgroundImageThumbPathWeb()));
        }
        $bgimage->setInfo($this->lng->txt("default_background_info"));
        $form->addItem($bgimage);
        $format = new ilSelectInputGUI($this->lng->txt("certificate_page_format"), "pageformat");
        $defaultformats = array(
            "a4" => $this->lng->txt("certificate_a4"), // (297 mm x 210 mm)
            "a4landscape" => $this->lng->txt("certificate_a4_landscape"), // (210 mm x 297 mm)",
            "a5" => $this->lng->txt("certificate_a5"), // (210 mm x 148.5 mm)
            "a5landscape" => $this->lng->txt("certificate_a5_landscape"), // (148.5 mm x 210 mm)
            "letter" => $this->lng->txt("certificate_letter"), // (11 inch x 8.5 inch)
            "letterlandscape" => $this->lng->txt("certificate_letter_landscape") // (11 inch x 8.5 inch)
        );
        $format->setOptions($defaultformats);
        $format->setValue($form_settings->get("pageformat"));
        $format->setInfo($this->lng->txt("certificate_page_format_info"));
        $form->addItem($format);


        if ($this->hierarchical_access->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton('save', $this->lng->txt('save'));
        }

        if (!\ilObjUserTracking::_enabledLearningProgress()) {
            ilAdministrationSettingsFormHandler::addFieldsToForm(
                ilAdministrationSettingsFormHandler::FORM_CERTIFICATE,
                $form,
                $this
            );
        }

        $persistentCertificateMode = new ilRadioGroupInputGUI($this->lng->txt('persistent_certificate_mode'), 'persistent_certificate_mode');
        $persistentCertificateMode->setRequired(true);

        $cronJobMode = new ilRadioOption($this->lng->txt('persistent_certificate_mode_cron'), 'persistent_certificate_mode_cron');
        $cronJobMode->setInfo($this->lng->txt('persistent_certificate_mode_cron_info'));

        $instantMode = new ilRadioOption($this->lng->txt('persistent_certificate_mode_instant'), 'persistent_certificate_mode_instant');
        $instantMode->setInfo($this->lng->txt('persistent_certificate_mode_instant_info'));

        $persistentCertificateMode->addOption($cronJobMode);
        $persistentCertificateMode->addOption($instantMode);

        $persistentCertificateMode->setValue($form_settings->get('persistent_certificate_mode', 'persistent_certificate_mode_cron'));

        $form->addItem($persistentCertificateMode);


        $this->tpl->setContent($form->getHTML());

        if (strcmp($this->ctrl->getCmd(), "save") == 0) {
            if ($_POST["background_delete"]) {
                $this->object->deleteBackgroundImage();
            }
        }
    }

    public function save()
    {
        $form_settings = new ilSetting("certificate");

        $mode = $_POST["persistent_certificate_mode"];
        $previousMode = $form_settings->get('persistent_certificate_mode', 'persistent_certificate_mode_cron');
        if ($mode !== $previousMode && $mode === 'persistent_certificate_mode_instant') {
            $cron = new ilCertificateCron();
            $cron->init();
            $cron->run();
        }

        $form_settings->set("pageformat", $_POST["pageformat"]);
        $form_settings->set("active", $_POST["active"]);
        $form_settings->set("persistent_certificate_mode", $mode);

        ilUtil::sendSuccess($this->lng->txt("settings_saved"));
        $this->settings();
    }
}
