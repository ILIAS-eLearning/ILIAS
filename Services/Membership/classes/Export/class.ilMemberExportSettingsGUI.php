<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Export settings gui
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilMemberExportSettingsGUI
{
    const TYPE_PRINT_VIEW_SETTINGS = 'print_view';
    const TYPE_EXPORT_SETTINGS = 'member_export';
    const TYPE_PRINT_VIEW_MEMBERS = 'prv_members';
    

    private $parent_type = '';
    private $parent_obj_id = 0;
    
    
    /**
     * Constructor
     */
    public function __construct($a_parent_type, $a_parent_obj_id = 0)
    {
        $this->parent_type = $a_parent_type;
        $this->parent_obj_id = $a_parent_obj_id;
        
        $this->ctrl = $GLOBALS['DIC']['ilCtrl'];
        $this->lng = $GLOBALS['DIC']['lng'];
        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('mem');
    }
    
    /**
     * Get language
     * @return ilLanguage
     */
    private function getLang()
    {
        return $this->lng;
    }
    
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd('printViewSettings');


        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
        return true;
    }
    

    /**
     * Show print view settings
     */
    protected function printViewSettings(ilPropertyFormGUI $form = null)
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initForm(self::TYPE_PRINT_VIEW_SETTINGS);
        }
        
        $GLOBALS['DIC']['tpl']->setContent($form->getHTML());
    }
    
    /**
     * init settings form
     */
    protected function initForm($a_type)
    {
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->getLang()->txt('mem_' . $a_type . '_form'));
        
        // profile fields
        $fields['name'] = $GLOBALS['DIC']['lng']->txt('name');
        $fields['login'] = $GLOBALS['DIC']['lng']->txt('login');
        $fields['email'] = $GLOBALS['DIC']['lng']->txt('email');
        
        include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        include_once 'Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php';
        include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
        include_once('Services/User/classes/class.ilUserDefinedFields.php');

        $field_info = ilExportFieldsInfo::_getInstanceByType($this->parent_type);
        $field_info->sortExportFields();

        foreach ($field_info->getExportableFields() as $field) {
            switch ($field) {
                case 'username':
                case 'firstname':
                case 'lastname':
                case 'email':
                    continue 2;
            }
            
            // Check if default enabled
            $fields[$field] = $GLOBALS['DIC']['lng']->txt($field);
        }
        
        
        // udf
        include_once './Services/User/classes/class.ilUserDefinedFields.php';
        $udf = ilUserDefinedFields::_getInstance();
        $exportable = array();
        if ($this->parent_type == 'crs') {
            $exportable = $udf->getCourseExportableFields();
        } elseif ($this->parent_type == 'grp') {
            $exportable = $udf->getGroupExportableFields();
        }
        foreach ((array) $exportable as $field_id => $udf_data) {
            $fields['udf_' . $field_id] = $udf_data['field_name'];
        }

        
        $ufields = new ilCheckboxGroupInputGUI($GLOBALS['DIC']['lng']->txt('user_detail'), 'preset');
        foreach ($fields as $id => $name) {
            $ufields->addOption(new ilCheckboxOption($name, $id));
        }
        $form->addItem($ufields);
        
        
        include_once './Services/PrivacySecurity/classes/class.ilPrivacySettings.php';
        $privacy = ilPrivacySettings::_getInstance();
        if ($this->parent_type == 'crs') {
            if ($privacy->enabledCourseAccessTimes()) {
                $ufields->addOption(new ilCheckboxOption($GLOBALS['DIC']['lng']->txt('last_access'), 'access'));
            }
        }
        if ($this->parent_type == 'grp') {
            if ($privacy->enabledGroupAccessTimes()) {
                $ufields->addOption(new ilCheckboxOption($GLOBALS['DIC']['lng']->txt('last_access'), 'access'));
            }
        }
        $ufields->addOption(new ilCheckboxOption($GLOBALS['DIC']['lng']->txt('crs_status'), 'status'));
        $ufields->addOption(new ilCheckboxOption($GLOBALS['DIC']['lng']->txt('crs_passed'), 'passed'));
        
        
        $blank = new ilTextInputGUI($GLOBALS['DIC']['lng']->txt('event_blank_columns'), 'blank');
        $blank->setMulti(true);
        $form->addItem($blank);
        
        $roles = new ilCheckboxGroupInputGUI($GLOBALS['DIC']['lng']->txt('event_user_selection'), 'selection_of_users');
        
        $roles->addOption(new ilCheckboxOption($GLOBALS['DIC']['lng']->txt('event_tbl_admin'), 'role_adm'));
        if ($this->parent_type == 'crs') {
            $roles->addOption(new ilCheckboxOption($GLOBALS['DIC']['lng']->txt('event_tbl_tutor'), 'role_tut'));
        }
        $roles->addOption(new ilCheckboxOption($GLOBALS['DIC']['lng']->txt('event_tbl_member'), 'role_mem'));
        
        if (!$this->parent_obj_id) {
            $subscriber = new ilCheckboxOption($GLOBALS['DIC']['lng']->txt('event_user_selection_include_requests'), 'subscr');
            $roles->addOption($subscriber);

            $waiting_list = new ilCheckboxOption($GLOBALS['DIC']['lng']->txt('event_user_selection_include_waiting_list'), 'wlist');
            $roles->addOption($waiting_list);
        }
        $form->addItem($roles);
        
        switch ($a_type) {
            case self::TYPE_PRINT_VIEW_SETTINGS:
                $form->addCommandButton('savePrintViewSettings', $this->getLang()->txt('save'));
                break;
        }
        
        include_once "Services/User/classes/class.ilUserFormSettings.php";
        $identifier = $this->parent_type . 's_pview';
        if ($this->parent_obj_id) {
            $identifier_for_object = $identifier . '_' . $this->parent_obj_id;
        }
            
        $settings = new ilUserFormSettings($identifier_for_object, -1);
        if (!$settings->hasStoredEntry()) {
            // use default settings
            $settings = new ilUserFormSettings($identifier, -1);
        }
        $settings->exportToForm($form);
        
        return $form;
    }
    
    /**
     * Save print view settings
     */
    protected function savePrintViewSettings()
    {
        $form = $this->initForm(self::TYPE_PRINT_VIEW_SETTINGS);
        if ($form->checkInput()) {
            $form->setValuesByPost();
            
            include_once "Services/User/classes/class.ilUserFormSettings.php";
            
            ilUserFormSettings::deleteAllForPrefix('crs_memlist');
            ilUserFormSettings::deleteAllForPrefix('grp_memlist');
            
            $identifier = $this->parent_type . 's_pview';
            if ($this->parent_obj_id) {
                $identifier .= '_' . $this->parent_obj_id;
            }
            
            $settings = new ilUserFormSettings($identifier, -1);
            $settings->importFromForm($form);
            $settings->store();
            
            ilUtil::sendSuccess($GLOBALS['DIC']['lng']->txt('settings_saved'));
            $GLOBALS['DIC']['ilCtrl']->redirect($this, 'printViewSettings');
        }
    }
}
