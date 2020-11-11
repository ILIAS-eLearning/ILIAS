<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");


/**
* Accessibility Settings.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjAccessibilitySettingsGUI: ilPermissionGUI, ilAccessibilityDocumentGUI
* @ilCtrl_IsCalledBy ilObjAccessibilitySettingsGUI: ilAdministrationGUI
*
* @ingroup ServicesAccessibility
*/
class ilObjAccessibilitySettingsGUI extends ilObjectGUI
{
    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->type = 'accs';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('acc');
        $this->lng->loadLanguageModule('adm');
        $this->lng->loadLanguageModule('meta');
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;
        $ilAccess = $this->access;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilaccessibilitydocumentgui':
                $this->tabs_gui->activateTab('acc_ctrl_cpt');

                $tableDataProviderFactory = new ilAccessibilityTableDataProviderFactory();
                $tableDataProviderFactory->setDatabaseAdapter($this->dic->database());

                $documentGui = new ilAccessibilityDocumentGUI(
                    $this->object,
                    $this->dic['acc.criteria.type.factory'],
                    $this->dic->ui()->mainTemplate(),
                    $this->dic->user(),
                    $this->dic->ctrl(),
                    $this->dic->language(),
                    $this->dic->rbac()->system(),
                    $this->dic['ilErr'],
                    $this->dic->logger()->acc(),
                    $this->dic->toolbar(),
                    $this->dic->http(),
                    $this->dic->ui()->factory(),
                    $this->dic->ui()->renderer(),
                    $this->dic->filesystem(),
                    $this->dic->upload(),
                    $tableDataProviderFactory,
                    new ilAccessibilityTrimmedDocumentPurifier(new ilAccessibilityDocumentHtmlPurifier())
                );

                $this->ctrl->forwardCommand($documentGui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editAccessibilitySettings";
                }

                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getSettingsForm()
    {
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $this->form = new ilPropertyFormGUI();
        $this->form->setTitle($this->lng->txt('settings'));

        $cb = new ilCheckboxInputGUI($this->lng->txt('adm_acc_ctrl_cpt_enable'), 'acc_ctrl_cpt_status');
        $cb->setValue(1);
        $cb->setChecked(ilObjAccessibilitySettings::getControlConceptStatus());
        $cb->setInfo($this->lng->txt('adm_acc_ctrl_cpt_desc'));
        $this->form->addItem($cb);

        $ti = new ilTextInputGUI($this->lng->txt("adm_accessibility_contacts"), "accessibility_support_contacts");
        $ti->setMaxLength(500);
        $ti->setValue(ilAccessibilitySupportContacts::getList());
        $ti->setInfo($this->lng->txt("adm_accessibility_contacts_info"));
        $this->form->addItem($ti);

        $se = new ilFormSectionHeaderGUI();
        $se ->setTitle($this->lng->txt('obj_accs_captcha'));
        $this->form->addItem($se);

        require_once 'Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php';
        ilAdministrationSettingsFormHandler::addFieldsToForm(
            ilAdministrationSettingsFormHandler::FORM_ACCESSIBILITY,
            $this->form,
            $this
        );

        $this->form->addCommandButton("saveAccessibilitySettings", $this->lng->txt("save"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        return $this->form;
    }

    /**
     * Save accessibility settings form
     */
    public function saveAccessibilitySettings()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $this->getSettingsForm();
        if ($this->form->checkInput()) {
            // Accessibility Control Concept status
            ilObjAccessibilitySettings::saveControlConceptStatus((bool) $this->form->getInput('acc_ctrl_cpt_status'));
            // Accessibility support contacts
            ilAccessibilitySupportContacts::setList($_POST["accessibility_support_contacts"]);

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editAccessibilitySettings");
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    protected function editAccessibilitySettings(ilPropertyFormGUI $form = null)
    {
        $this->tabs_gui->setTabActive('acc_settings');
        if (!$form) {
            $this->form = $this->getSettingsForm();
        }
        
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs()
    {
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;

        if ($rbacsystem->checkAccess("read", $this->object->getRefId())) {
            $ilTabs->addTab('acc_settings', $this->lng->txt('settings'), $this->ctrl->getLinkTarget($this, 'editAccessibilitySettings'));
        }

        if ($rbacsystem->checkAccess("read", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "acc_access_keys",
                $this->ctrl->getLinkTarget($this, "editAccessKeys"),
                array("editAccessKeys", "view")
            );
        }

        if ($rbacsystem->checkAccess("read", $this->object->getRefId())) {
            $ilTabs->addTab(
                'acc_ctrl_cpt',
                $this->lng->txt('acc_ctrl_cpt_txt'),
                $this->ctrl->getLinkTargetByClass('ilaccessibilitydocumentgui')
            );
        }

        if ($rbacsystem->checkAccess("edit_permission", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    /**
    * Edit access keys
    */
    public function editAccessKeys()
    {
        $tpl = $this->tpl;

        $this->tabs_gui->setTabActive('acc_access_keys');
        
        include_once("./Services/Accessibility/classes/class.ilAccessKeyTableGUI.php");
        $table = new ilAccessKeyTableGUI($this, "editAccessKeys");
        
        $tpl->setContent($table->getHTML());
    }
    
    /**
    * Save access keys
    */
    public function saveAccessKeys()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;
        
        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
            include_once("./Services/Accessibility/classes/class.ilAccessKey.php");
            ilAccessKey::writeKeys(ilUtil::stripSlashesArray($_POST["acckey"]));
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "editAccessKeys");
    }
}
