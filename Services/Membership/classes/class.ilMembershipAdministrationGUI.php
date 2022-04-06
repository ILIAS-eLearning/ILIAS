<?php declare(strict_types=1);

    
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
 * Membership Administration Settings
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesMembership
 */
abstract class ilMembershipAdministrationGUI extends ilObjectGUI
{
    protected const SUB_TAB_GENERAL_SETTINGS = 'settings';
    protected const SUB_TAB_PRINT_VIEW = 'print_view';

    public function __construct($a_data, int $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        $this->type = $this->getType();
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("grp");
        $this->lng->loadLanguageModule('mem');
    }

    abstract protected function getType() : string;

    abstract protected function getParentObjType() : string;

    abstract protected function getAdministrationFormId() : int;

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt("no_permission"), $this->error->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive("perm_settings");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilmemberexportsettingsgui':
                $this->setSubTabs('settings', self::SUB_TAB_PRINT_VIEW);
                $settings_gui = new ilMemberExportSettingsGUI($this->getParentObjType());
                $this->ctrl->forwardCommand($settings_gui);
                break;

            case 'iluseractionadmingui':
                $gui = new ilUserActionAdminGUI($this->object->getRefId());
                $gui->setActionContext(new ilGalleryUserActionContext());
                $this->setSubTabs('settings', "actions");
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if (!$cmd || $cmd === "view") {
                    $cmd = "editSettings";
                }
                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs() : void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($this->rbac_system->checkAccess("edit_permission", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"),
                array(),
                "ilpermissiongui"
            );
        }
    }

    public function editSettings(?ilPropertyFormGUI $a_form = null) : void
    {
        $this->setSubTabs('settings', self::SUB_TAB_GENERAL_SETTINGS);
        $this->tabs_gui->setTabActive('settings');

        if ($a_form === null) {
            $a_form = $this->initFormSettings();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    public function saveSettings() : void
    {
        $this->checkPermission("write");
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            if ($this->save($form)) {
                $this->settings->set(
                    'mail_' . $this->getParentObjType() . '_member_notification',
                    (string) $form->getInput('mail_member_notification')
                );

                $this->settings->set(
                    'mail_' . $this->getParentObjType() . '_admin_notification',
                    (string) $form->getInput('mail_admin_notification')
                );

                $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
                $this->ctrl->redirect($this, "editSettings");
            }
        }
        $form->setValuesByPost();
        $this->editSettings($form);
    }

    protected function initFormSettings() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "saveSettings"));
        $form->setTitle($this->lng->txt("settings"));

        $this->addFieldsToForm($form);

        $this->lng->loadLanguageModule("mail");

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            $this->getAdministrationFormId(),
            $form,
            $this
        );

        $sec = new ilFormSectionHeaderGUI();
        $sec->setTitle($this->lng->txt('mail_notification_membership_section'));
        $form->addItem($sec);

        // member notification
        $cn = new ilCheckboxInputGUI(
            $this->lng->txt('mail_enable_' . $this->getParentObjType() . '_member_notification'),
            'mail_member_notification'
        );
        $cn->setInfo($this->lng->txt('mail_enable_' . $this->getParentObjType() . '_member_notification_info'));
        $cn->setChecked((bool) $this->settings->get('mail_' . $this->getParentObjType() . '_member_notification', '1'));
        $form->addItem($cn);

        // default admin membership notification
        $an = new ilCheckboxInputGUI(
            $this->lng->txt('mail_enable_' . $this->getParentObjType() . '_admin_notification'),
            'mail_admin_notification'
        );
        $an->setInfo($this->lng->txt('mail_enable_' . $this->getParentObjType() . '_admin_notification_info'));
        $an->setChecked((bool) $this->settings->get('mail_' . $this->getParentObjType() . '_admin_notification', '1'));
        $form->addItem($an);

        if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton("saveSettings", $this->lng->txt("save"));
            $form->addCommandButton("view", $this->lng->txt("cancel"));
        }
        return $form;
    }

    public function addToExternalSettingsForm(int $a_form_id) : array
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_MAIL:

                $this->lng->loadLanguageModule("mail");

                $fields = array(
                    'mail_enable_' . $this->getParentObjType() . '_member_notification' => array(
                        $this->settings->get('mail_' . $this->getParentObjType() . '_member_notification', '1'),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ),
                    'mail_enable_' . $this->getParentObjType() . '_admin_notification' => array(
                        $this->settings->get('mail_' . $this->getParentObjType() . '_admin_notification', '1'),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    )
                );
                return [
                    [
                        "editSettings",
                        $fields
                    ]
                ];
        }
        return [];
    }

    protected function addFieldsToForm(ilPropertyFormGUI $a_form) : void
    {
    }

    protected function save(ilPropertyFormGUI $a_form) : bool
    {
        return true;
    }

    protected function setSubTabs(string $a_main_tab, string $a_active_tab) : void
    {
        if ($a_main_tab === 'settings') {
            $this->tabs_gui->addSubTab(
                self::SUB_TAB_GENERAL_SETTINGS,
                $this->lng->txt('mem_settings_tab_' . self::SUB_TAB_GENERAL_SETTINGS),
                $this->ctrl->getLinkTarget($this, 'editSettings')
            );
            $this->tabs_gui->addSubTab(
                self::SUB_TAB_PRINT_VIEW,
                $this->lng->txt('mem_settings_tab_' . self::SUB_TAB_PRINT_VIEW),
                $this->ctrl->getLinkTargetByClass('ilMemberExportSettingsGUI', 'printViewSettings')
            );
            $this->tabs_gui->addSubTab(
                "actions",
                $this->lng->txt("mmbr_gallery_user_actions"),
                $this->ctrl->getLinkTargetByClass("iluseractionadmingui")
            );

            $this->tabs_gui->activateTab($a_main_tab);
            $this->tabs_gui->activateSubTab($a_active_tab);
        }
    }
}
