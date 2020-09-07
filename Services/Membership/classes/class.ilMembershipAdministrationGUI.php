<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObjectGUI.php" ;
include_once "./Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php" ;

/**
 * Membership Administration Settings
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ingroup ServicesMembership
 */
abstract class ilMembershipAdministrationGUI extends ilObjectGUI
{
    const SUB_TAB_GENERAL_SETTINGS = 'settings';
    const SUB_TAB_PRINT_VIEW = 'print_view';
    
    
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        $this->type = $this->getType();
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("grp");
        $this->lng->loadLanguageModule('mem');
    }
    
    abstract protected function getType();
    
    abstract protected function getParentObjType();
    
    abstract protected function getAdministrationFormId();

    public function executeCommand()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$ilAccess->checkAccess("read", "", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("no_permission"), $ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive("perm_settings");
                include_once "Services/AccessControl/classes/class.ilPermissionGUI.php";
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            
            case 'ilmemberexportsettingsgui':
                $this->setSubTabs('settings', self::SUB_TAB_PRINT_VIEW);
                include_once './Services/Membership/classes/Export/class.ilMemberExportSettingsGUI.php';
                $settings_gui = new ilMemberExportSettingsGUI($this->getParentObjType());
                $this->ctrl->forwardCommand($settings_gui);
                break;

            case 'iluseractionadmingui':
                include_once("./Services/User/Actions/classes/class.ilUserActionAdminGUI.php");
                include_once("./Services/User/Gallery/classes/class.ilGalleryUserActionContext.php");
                $gui = new ilUserActionAdminGUI();
                $gui->setActionContext(new ilGalleryUserActionContext());
                $this->setSubTabs('settings', "actions");
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if (!$cmd || $cmd == "view") {
                    $cmd = "editSettings";
                }
                $this->$cmd();
                break;
        }
        return true;
    }

    public function getAdminTabs()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($rbacsystem->checkAccess("edit_permission", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"),
                array(),
                "ilpermissiongui"
            );
        }
    }
    
    public function editSettings(ilPropertyFormGUI $a_form = null)
    {
        $this->setSubTabs('settings', self::SUB_TAB_GENERAL_SETTINGS);
        $this->tabs_gui->setTabActive('settings');
                
        if (!$a_form) {
            $a_form = $this->initFormSettings();
        }
        $this->tpl->setContent($a_form->getHTML());
        return true;
    }

    public function saveSettings()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $this->checkPermission("write");
        
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            if ($this->save($form)) {
                $ilSetting->set(
                    'mail_' . $this->getParentObjType() . '_member_notification',
                    (int) $form->getInput('mail_member_notification')
                );

                $ilSetting->set(
                    'mail_' . $this->getParentObjType() . '_admin_notification',
                    (int) $form->getInput('mail_admin_notification')
                );
                
                ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
                $this->ctrl->redirect($this, "editSettings");
            }
        }
        
        $form->setValuesByPost();
        $this->editSettings($form);
    }

    protected function initFormSettings()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $ilAccess = $DIC['ilAccess'];
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
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
        $cn = new ilCheckboxInputGUI($this->lng->txt('mail_enable_' . $this->getParentObjType() . '_member_notification'), 'mail_member_notification');
        $cn->setInfo($this->lng->txt('mail_enable_' . $this->getParentObjType() . '_member_notification_info'));
        $cn->setChecked($ilSetting->get('mail_' . $this->getParentObjType() . '_member_notification', true));
        $form->addItem($cn);

        // default admin membership notification
        $an = new ilCheckboxInputGUI($this->lng->txt('mail_enable_' . $this->getParentObjType() . '_admin_notification'), 'mail_admin_notification');
        $an->setInfo($this->lng->txt('mail_enable_' . $this->getParentObjType() . '_admin_notification_info'));
        $an->setChecked($ilSetting->get('mail_' . $this->getParentObjType() . '_admin_notification', true));
        $form->addItem($an);

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton("saveSettings", $this->lng->txt("save"));
            $form->addCommandButton("view", $this->lng->txt("cancel"));
        }

        return $form;
    }
    
    public function addToExternalSettingsForm($a_form_id)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_MAIL:
                
                $this->lng->loadLanguageModule("mail");
                
                $fields = array(
                    'mail_enable_' . $this->getParentObjType() . '_member_notification' => array($ilSetting->get('mail_' . $this->getParentObjType() . '_member_notification', true), ilAdministrationSettingsFormHandler::VALUE_BOOL),
                    'mail_enable_' . $this->getParentObjType() . '_admin_notification' => array($ilSetting->get('mail_' . $this->getParentObjType() . '_admin_notification', true), ilAdministrationSettingsFormHandler::VALUE_BOOL)
                );

                return array(array("editSettings", $fields));
        }
    }
        
    protected function addFieldsToForm(ilPropertyFormGUI $a_form)
    {
    }
            
    protected function save(ilPropertyFormGUI $a_form)
    {
        return true;
    }
    
    /**
     * Set sub tabs
     * @param string $main_tab
     * @param type $a_active_tab
     */
    protected function setSubTabs($a_main_tab, $a_active_tab)
    {
        if ($a_main_tab == 'settings') {
            $GLOBALS['DIC']['ilTabs']->addSubTab(
                self::SUB_TAB_GENERAL_SETTINGS,
                $GLOBALS['DIC']['lng']->txt('mem_settings_tab_' . self::SUB_TAB_GENERAL_SETTINGS),
                $GLOBALS['DIC']['ilCtrl']->getLinkTarget($this, 'editSettings')
            );
            $GLOBALS['DIC']['ilTabs']->addSubTab(
                self::SUB_TAB_PRINT_VIEW,
                $GLOBALS['DIC']['lng']->txt('mem_settings_tab_' . self::SUB_TAB_PRINT_VIEW),
                $GLOBALS['DIC']['ilCtrl']->getLinkTargetByClass('ilMemberExportSettingsGUI', 'printViewSettings')
            );
            $GLOBALS['DIC']['ilTabs']->addSubTab(
                "actions",
                $GLOBALS['DIC']['lng']->txt("mmbr_gallery_user_actions"),
                $GLOBALS['DIC']['ilCtrl']->getLinkTargetByClass("iluseractionadmingui")
            );

            $GLOBALS['DIC']['ilTabs']->activateTab($a_main_tab);
            $GLOBALS['DIC']['ilTabs']->activateSubTab($a_active_tab);
        }
    }
}
