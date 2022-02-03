<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    protected ?ilCtrl $ctrl = null;

    /**
     * @var ilLogger
     */
    protected ?ilLogger $logger = null;
    
    /**
     * @var ilLanguage
     */
    protected ?ilLanguage $lng = null;
    
    /**
     * @var ilTemplate
     */
    protected ?ilTemplate $tpl = null;
    
    /**
     * @var int
     */
    protected ?int $ref_id = null;
    
    /**
     * Custom roles for selection
     * @var int[]
     */
    protected array $custom_roles = [];
    
    /**
     * @var bool
     */
    protected bool $use_lti_roles = true;
    
    /**
     * @param int ref_id
     */
    public function __construct(int $a_ref_id)
    {
        $this->ref_id = $a_ref_id;
        $this->logger = ilLoggerFactory::getLogger('lti');
        $this->ctrl = $GLOBALS['DIC']->ctrl();
        $this->tpl = $GLOBALS['DIC']['tpl'];

        $this->lng = $GLOBALS['DIC']->language();
        $this->lng->loadLanguageModule('lti');
    }

    /**
     * Check if user has access to lti settings
     * @return bool
     */
    public function hasSettingsAccess() : bool
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
     * @param array $a_roles
     */
    public function setCustomRolesForSelection(array $a_roles) : void
    {
        if (empty($a_roles)) {
            $this->checkLocalRole();
            $a_roles = $GLOBALS['DIC']->rbac()->review()->getLocalRoles($this->ref_id);
        }
        $this->custom_roles = $a_roles;
    }
    
    /**
     * Offer LTI roles for mapping
     */
    public function offerLTIRolesForSelection(bool $a_stat) : void
    {
        $this->use_lti_roles = $a_stat;
    }
    
    
    /**
     * Ctrl execute command
     */
    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd('settings');
        $next_class = $this->ctrl->getNextClass($this);

//        switch ($next_class) {
//            default:
        $this->$cmd();
//                break;
//        }
    }
    
    /**
     * Show settings
     * @param ilPropertyFormGUI $form
     */
    protected function settings(ilPropertyFormGUI $form = null) : void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initObjectSettingsForm();
        }
        $this->tpl->setContent($form->getHTML());
    }
    
    
    /**
     * Init object settings form
     */
    protected function initObjectSettingsForm() : \ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('lti_object_release_settings_form'));
        
        foreach (ilObjLTIAdministration::getEnabledConsumersForType(ilObject::_lookupType($this->ref_id, true)) as $global_consumer) {
            $object_info = new ilLTIProviderObjectSetting($this->ref_id, $global_consumer->getExtConsumerId());
            
            $this->logger->debug((string) $object_info->getAdminRole());
            
            
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
            $active->setValue("1");
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
    protected function updateSettings() : void
    {
        $form = $this->initObjectSettingsForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->settings($form);
            return;
        }
        
        $connector = new ilLTIDataConnector();
        foreach (ilObjLTIAdministration::getEnabledConsumersForType(ilObject::_lookupType($this->ref_id, true)) as $global_consumer) {
            $this->saveRoleSelection($form, (string) $global_consumer->getExtConsumerId());
            
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
     * @param string $global_consumer_id
     */
    protected function saveRoleSelection(ilPropertyFormGUI $form, string $global_consumer_id) : void
    {
        $object_info = new ilLTIProviderObjectSetting($this->ref_id, $global_consumer_id);
        
        $admin_role = (int) $form->getInput('lti_admin_' . $global_consumer_id);
        if ($admin_role > 0) {
            $object_info->setAdminRole($admin_role);
        }
        $tutor_role = (int) $form->getInput('lti_tutor_' . $global_consumer_id);
        if ($tutor_role > 0) {
            $object_info->setTutorRole($tutor_role);
        }
        $member_role = (int) $form->getInput('lti_member_' . $global_consumer_id);
        if ($member_role > 0) {
            $object_info->setMemberRole($member_role);
        }
        $object_info->save();
    }
    
    
    /**
     * Get role selection
     * @return string[]
     */
    protected function getRoleSelection() : array
    {
        $options = [];
        $options[0] = $this->lng->txt('select_one');
        foreach ($this->custom_roles as $role_id) {
            $title = ilObjRole::_getTranslation(ilObjRole::_lookupTitle($role_id));
            $options[$role_id] = $title;
        }
        return $options;
    }
    
    /**
     * check for local roles for lti objects which are not grp or crs
     */
    protected function checkLocalRole() : void
    {
        global $DIC;
        $a_global_role = ilObject::_getIdsForTitle("il_lti_global_role", "role", false);
        if (is_array($a_global_role) && !empty($a_global_role)) {
            $rbacreview = $DIC['rbacreview'];
            if (count($rbacreview->getRolesOfObject($this->ref_id, false)) == 0) {
                $rbacadmin = $DIC['rbacadmin'];
                $type = ilObject::_lookupType($this->ref_id, true);
                $role = new ilObjRole();
                $role->setTitle("il_lti_learner");
                $role->setDescription("LTI Learner of " . $type . " obj_no." . ilObject::_lookupObjectId($this->ref_id));
                $role->create();
                $rbacadmin->assignRoleToFolder($role->getId(), $this->ref_id, 'y');
                $rbacadmin->grantPermission($role->getId(), ilRbacReview::_getOperationIdsByName(array('visible','read')), $this->ref_id);
                // $rbacadmin->setRolePermission($role->getId(), ilObject::_lookupType($this->ref_id, true), [2,3], $this->ref_id);
                if ($type == "sahs" || $type == "lm" || $type == "svy" || $type == "tst") {
                    $role = new ilObjRole();
                    $role->setTitle("il_lti_instructor");
                    $role->setDescription("LTI Instructor of " . $type . " obj_no." . ilObject::_lookupObjectId($this->ref_id));
                    $role->create();
                    $rbacadmin->assignRoleToFolder($role->getId(), $this->ref_id, 'y');
                    $ops = ilRbacReview::_getOperationIdsByName(array('visible','read','read_learning_progress'));
                    if ($type == "svy") {
                        $ops[] = ilRbacReview::_getOperationIdsByName(array('read_results'))[0];
                    }
                    if ($type == "tst") {
                        $ops[] = ilRbacReview::_getOperationIdsByName(array('tst_results'))[0];
                        $ops[] = ilRbacReview::_getOperationIdsByName(array('tst_statistics'))[0];
                    }
                    $rbacadmin->grantPermission($role->getId(), $ops, $this->ref_id);
                }
            }
        }
    }
}
