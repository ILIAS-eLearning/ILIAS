<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for LTI provider object settings.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLTIProviderObjectSettingGUI
{
    const ROLE_ADMIN = 'admin';
    const ROLE_TUTOR = 'tutor';
    const ROLE_MEMBER = 'member';
    
    
    /**
     * @var ilCtrl
     */
    protected $ctrl = null;

    /**
     * @var ilLogger
     */
    protected $logger = null;
    
    /**
     * @var ilLanguage
     */
    protected $lng = null;
    
    /**
     * @var ilTemplate
     */
    protected $tpl = null;
    
    /**
     * @var int
     */
    protected $ref_id = null;
    
    /**
     * Custom roles for selection
     * @var int[]
     */
    protected $custom_roles = [];
    
    /**
     * @var bool
     */
    protected $use_lti_roles = true;
    
    /**
     * @param int ref_id
     */
    public function __construct($a_ref_id)
    {
        $this->ref_id = $a_ref_id;
        $this->logger = $GLOBALS['DIC']->logger()->lti();
        $this->ctrl = $GLOBALS['DIC']->ctrl();
        $this->tpl = $GLOBALS['DIC']->ui()->mainTemplate();

        $this->lng = $GLOBALS['DIC']->language();
        $this->lng->loadLanguageModule('lti');
    }
    
    /**
     * Check if user has access to lti settings
     * @param int ref_id
     * @param int user_id
     */
    public function hasSettingsAccess()
    {
        if (!ilObjLTIAdministration::isEnabledForType(ilObject::_lookupType($this->ref_id, true))) {
            $this->logger->debug('No LTI consumers activated for object type: ' . ilObject::_lookupType($this->ref_id, true));
            return false;
        }
        $access = $GLOBALS['DIC']->rbac()->system();
        return $access->checkAccess(
            'release_objects',
            ilObjLTIAdministration::lookupLTISettingsRefId()
        );
    }
    
    /**
     * Set custom roles for mapping to LTI roles
     * @param type $a_roles
     */
    public function setCustomRolesForSelection($a_roles)
    {
        $this->custom_roles = $a_roles;
    }
    
    /**
     * Offer LTI roles for mapping
     * @param bool $a_stat
     */
    public function offerLTIRolesForSelection($a_stat)
    {
        $this->use_lti_roles = $a_stat;
    }
    
    
    /**
     * Ctrl execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd('settings');
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }
    
    /**
     * Show settings
     * @param ilPropertyFormGUI $form
     */
    protected function settings(ilPropertyFormGUI $form = null)
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initObjectSettingsForm();
        }
        $this->tpl->setContent($form->getHTML());
    }
    
    
    /**
     * Init object settings form
     */
    protected function initObjectSettingsForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('lti_object_release_settings_form'));
        
        foreach (ilObjLTIAdministration::getEnabledConsumersForType(ilObject::_lookupType($this->ref_id, true)) as $global_consumer) {
            $object_info = new ilLTIProviderObjectSetting($this->ref_id, $global_consumer->getExtConsumerId());
            
            $this->logger->debug($object_info->getAdminRole());
            
            
            // meta data for external consumers
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($global_consumer->getTitle());
            $section->setInfo($global_consumer->getDescription());
            $form->addItem($section);

            $connector = new ilLTIDataConnector();
            
            $active_consumer = ilLTIToolConsumer::fromGlobalSettingsAndRefId(
                $global_consumer->getExtConsumerId(),
                $this->ref_id,
                $connector
            );
            
            $active = new ilCheckboxInputGUI($GLOBALS['lng']->txt('lti_obj_active'), 'lti_active_' . $global_consumer->getExtConsumerId());
            $active->setInfo($GLOBALS['lng']->txt('lti_obj_active_info'));
            $active->setValue(1);
            $form->addItem($active);

            if ($active_consumer->getEnabled()) { // and enabled
                $active->setChecked(true);
                
                $url = new ilNonEditableValueGUI($this->lng->txt('lti_launch_url'), 'url');
                $url->setValue(ILIAS_HTTP_PATH . '/lti.php?client_id=' . CLIENT_ID);
                $active->addSubItem($url);
                
                $key = new ilNonEditableValueGUI($this->lng->txt('lti_consumer_key'), 'key');
                $key->setValue($active_consumer->getKey());
                $active->addSubItem($key);
                
                $secret = new ilNonEditableValueGUI($this->lng->txt('lti_consumer_secret'), 'secret');
                $secret->setValue($active_consumer->getSecret());
                $active->addSubItem($secret);
            }
            
            if ($this->custom_roles) {
                $admin = new ilSelectInputGUI(
                    $this->lng->txt('lti_admin'),
                    'lti_admin_' . $global_consumer->getExtConsumerId()
                );
                $admin->setOptions($this->getRoleSelection());
                $admin->setValue($object_info->getAdminRole() ? $object_info->getAdminRole() : 0);
                $active->addSubItem($admin);

                $tutor = new ilSelectInputGUI(
                    $this->lng->txt('lti_tutor'),
                    'lti_tutor_' . $global_consumer->getExtConsumerId()
                );
                $tutor->setOptions($this->getRoleSelection());
                $tutor->setValue($object_info->getTutorRole() ? $object_info->getTutorRole() : 0);
                $active->addSubItem($tutor);

                $member = new ilSelectInputGUI(
                    $this->lng->txt('lti_member'),
                    'lti_member_' . $global_consumer->getExtConsumerId()
                );
                $member->setOptions($this->getRoleSelection());
                $member->setValue($object_info->getMemberRole() ? $object_info->getMemberRole() : 0);
                $active->addSubItem($member);
            }
        }
        
        $form->addCommandButton('updateSettings', $this->lng->txt('save'));
        return $form;
    }
    
    /**
     * Update settings (activate deactivate lti access)
     */
    protected function updateSettings()
    {
        $form = $this->initObjectSettingsForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->settings($form);
            return;
        }
        
        $connector = new ilLTIDataConnector();
        foreach (ilObjLTIAdministration::getEnabledConsumersForType(ilObject::_lookupType($this->ref_id, true)) as $global_consumer) {
            $this->saveRoleSelection($form, $global_consumer->getExtConsumerId());
            
            $consumer = ilLTIToolConsumer::fromGlobalSettingsAndRefId(
                $global_consumer->getExtConsumerId(),
                $this->ref_id,
                $connector
            );
            if (!$form->getInput('lti_active_' . $global_consumer->getExtConsumerId())) {
                // not active anymore => delete consumer
                if ($consumer->getEnabled()) {
                    $this->logger->info('Deleting lti consumer for object reference: ' . $this->ref_id);
                    $consumer->delete();
                }
            } else {
                // new activation
                if (!$consumer->getEnabled()) {
                    $this->logger->info('Created new lti release for: ' . $this->ref_id);
                    $consumer->setExtConsumerId($global_consumer->getExtConsumerId());
                    $consumer->createSecret();
                    $consumer->setRefId($this->ref_id);
                    $consumer->setEnabled(true);
                    $consumer->saveLTI($connector);
                }
            }
        }
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'settings');
    }
    
    /**
     * Save role selection for consumer
     * @param ilPropertyFormGUI $form
     * @param type $global_consumer_id
     */
    protected function saveRoleSelection(ilPropertyFormGUI $form, $global_consumer_id)
    {
        $object_info = new ilLTIProviderObjectSetting($this->ref_id, $global_consumer_id);
        
        $admin_role = $form->getInput('lti_admin_' . $global_consumer_id);
        if ($admin_role > 0) {
            $object_info->setAdminRole($admin_role);
        }
        $tutor_role = $form->getInput('lti_tutor_' . $global_consumer_id);
        if ($tutor_role > 0) {
            $object_info->setTutorRole($tutor_role);
        }
        $member_role = $form->getInput('lti_member_' . $global_consumer_id);
        if ($member_role > 0) {
            $object_info->setMemberRole($member_role);
        }
        $object_info->save();
    }
    
    
    /**
     * Get role selection
     * @return array
     */
    protected function getRoleSelection()
    {
        $options = [];
        $options[0] = $this->lng->txt('select_one');
        foreach ($this->custom_roles as $role_id) {
            $title = ilObjRole::_getTranslation(ilObjRole::_lookupTitle($role_id));
            $options[$role_id] = $title;
        }
        return $options;
    }
}
