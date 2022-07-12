<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Accessibility Settings.
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjAccessibilitySettingsGUI: ilPermissionGUI, ilAccessibilityDocumentGUI
 * @ilCtrl_IsCalledBy ilObjAccessibilitySettingsGUI: ilAdministrationGUI
 */
class ilObjAccessibilitySettingsGUI extends ilObjectGUI
{
    protected ilPropertyFormGUI $form;
    protected \ILIAS\DI\Container $dic;
    protected ilTabsGUI $tabs;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->dic = $DIC;
        $this->rbacsystem = $DIC->rbac()->system();
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

    public function executeCommand() : void
    {
        $rbacsystem = $this->rbacsystem;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$rbacsystem->checkAccess('read', $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilaccessibilitydocumentgui':
                $this->tabs_gui->activateTab('acc_ctrl_cpt');

                $tableDataProviderFactory = new ilAccessibilityTableDataProviderFactory();
                $tableDataProviderFactory->setDatabaseAdapter($this->dic->database());

                /** @var ilObjAccessibilitySettings $settings */
                $settings = $this->object;
                $documentGui = new ilAccessibilityDocumentGUI(
                    $settings,
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
    }

    protected function getSettingsForm() : ilPropertyFormGUI
    {
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
    public function saveAccessibilitySettings() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $rbacsystem = $this->rbacsystem;

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('permission_denied'));
        }

        $this->getSettingsForm();
        if ($this->form->checkInput()) {
            // Accessibility Control Concept status
            ilObjAccessibilitySettings::saveControlConceptStatus((bool) $this->form->getInput('acc_ctrl_cpt_status'));
            // Accessibility support contacts
            ilAccessibilitySupportContacts::setList(
                $this->form->getInput("accessibility_support_contacts")
            );

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editAccessibilitySettings");
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
        }
    }

    protected function editAccessibilitySettings(ilPropertyFormGUI $form = null) : void
    {
        $this->tabs_gui->setTabActive('acc_settings');
        if (!$form) {
            $this->form = $this->getSettingsForm();
        }
        
        $this->tpl->setContent($this->form->getHTML());
    }

    public function getAdminTabs() : void
    {
        $rbacsystem = $this->rbacsystem;
        $ilTabs = $this->tabs;

        if ($rbacsystem->checkAccess("read", $this->object->getRefId())) {
            $ilTabs->addTab('acc_settings', $this->lng->txt('settings'), $this->ctrl->getLinkTarget($this, 'editAccessibilitySettings'));
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
}
