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
    
    protected ?\ilGlobalPageTemplate $tpl = null;
    
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

    private ilRbacSystem $rbacSystem;
    private ilRbacReview $rbacReview;
    private ilRbacAdmin $rbacAdmin;

    /**
     * @param int ref_id
     */
    public function __construct(int $a_ref_id)
    {
        global $DIC;
        $this->ref_id = $a_ref_id;
        $this->logger = ilLoggerFactory::getLogger('ltis');
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC['tpl'];
        $this->rbacSystem = $DIC->rbac()->system();
        $this->rbacReview = $DIC->rbac()->review();
        $this->rbacAdmin = $DIC->rbac()->admin();

        $this->lng = $DIC->language();
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
        return $this->rbacSystem->checkAccess(
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
            $a_roles = $this->rbacReview->getLocalRoles($this->ref_id);
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
            
            $active_consumer = ilLTIPlatform::fromGlobalSettingsAndRefId(
                $global_consumer->getExtConsumerId(),
                $this->ref_id,
                $connector
            );
            $active = new ilCheckboxInputGUI($this->lng->txt('lti_obj_active'), 'lti_active_' . $global_consumer->getExtConsumerId());
            $active->setInfo($this->lng->txt('lti_obj_active_info'));
            $active->setValue("1");//check
            if ($active_consumer->getEnabled()) { // and enabled
                $active->setChecked(true);
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
            $form->addItem($active);

            $version = new ilRadioGroupInputGUI($this->lng->txt('lti_obj_version'), 'version_' . $global_consumer->getExtConsumerId());
            $version->setRequired(true);
            if (!is_null($active_consumer->ltiVersion)) {
                $version->setValue($active_consumer->ltiVersion);
            }
//            $version->setInfo($this->lng->txt('lti_obj_version_info'));
            $op1 = new ilRadioOption($this->lng->txt("lti_obj_version_13"), ILIAS\LTI\ToolProvider\Util::LTI_VERSION1P3);
            $sh = new ilNonEditableValueGUI($this->lng->txt('lti_13_step1'), '');
            $sh->setValue($this->lng->txt("lti_13_step1_info"));
            $op1->addSubItem($sh);
            $url = new ilNonEditableValueGUI($this->lng->txt('lti_launch_url'), 'url');
            $url->setValue(ILIAS_HTTP_PATH . '/lti.php?client_id=' . CLIENT_ID);
            $op1->addSubItem($url);
//                    $url = new ilNonEditableValueGUI($this->lng->txt('lti_13_initiate_url'), 'url');
//                    $url->setValue(ILIAS_HTTP_PATH . '/lti.php?client_id=' . CLIENT_ID);
//                    $version->addSubItem($url);
//                    $url = new ilNonEditableValueGUI($this->lng->txt('lti_13_redirection_url'), 'url');
//                    $url->setValue(ILIAS_HTTP_PATH . '/lti.php?client_id=' . CLIENT_ID);
//                    $active->addSubItem($url);
            $sh = new ilNonEditableValueGUI($this->lng->txt('lti_13_step2'), '');
            $sh->setValue($this->lng->txt("lti_13_step2_info"));
            $op1->addSubItem($sh);
            $tf = new ilTextInputGUI($this->lng->txt('lti_13_platform_id'), 'platform_id_' . $global_consumer->getExtConsumerId());
            $tf->setValue($active_consumer->platformId);
            $op1->addSubItem($tf);
            $tf = new ilTextInputGUI($this->lng->txt('lti_13_client_id'), 'client_id_' . $global_consumer->getExtConsumerId());
            $tf->setValue($active_consumer->clientId);
            $op1->addSubItem($tf);
            $tf = new ilTextInputGUI($this->lng->txt('lti_13_deployment_id'), 'deployment_id_' . $global_consumer->getExtConsumerId());
            $tf->setValue($active_consumer->deploymentId);
            $op1->addSubItem($tf);
            $version->addOption($op1);

            $op0 = new ilRadioOption($this->lng->txt("lti_obj_version_11"), ILIAS\LTI\ToolProvider\Util::LTI_VERSION1);
            $url = new ilNonEditableValueGUI($this->lng->txt('lti_launch_url'), 'url');
            $url->setValue(ILIAS_HTTP_PATH . '/lti.php?client_id=' . CLIENT_ID);
            $op0->addSubItem($url);
            $key = new ilNonEditableValueGUI($this->lng->txt('lti_consumer_key'), 'key_' . $global_consumer->getExtConsumerId());
            if (is_null($active_consumer->getKey())) {
                $active_consumer->setKey(\ILIAS\LTI\ToolProvider\Util::getRandomString(10));//create $id .
            }
            $key->setValue($active_consumer->getKey());
            $op0->addSubItem($key);
            $secret = new ilNonEditableValueGUI($this->lng->txt('lti_consumer_secret'), 'secret_' . $global_consumer->getExtConsumerId());
            if (is_null($active_consumer->getSecret())) {
                $active_consumer->createSecret();
            }
            $secret->setValue($active_consumer->getSecret());
            $op0->addSubItem($secret);
            $version->addOption($op0);

            $active->addSubItem($version);
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
            
            $consumer = ilLTIPlatform::fromGlobalSettingsAndRefId(
                $global_consumer->getExtConsumerId(),
                $this->ref_id,
                $connector
            );
            if (!$form->getInput('lti_active_' . $global_consumer->getExtConsumerId())) {
                // not active anymore
                if ($consumer->getEnabled()) {
                    $this->logger->info('Deleting lti consumer for object reference: ' . $this->ref_id);
                    $consumer->setEnabled(false);
                    $consumer->saveLTI($connector);
                }
            } else {
                $consumer->ltiVersion = $form->getInput('version_' . $global_consumer->getExtConsumerId());
                $this->logger->info('Created new lti release for: ' . $this->ref_id);
                $consumer->setExtConsumerId($global_consumer->getExtConsumerId());
                $consumer->setKey((string) $form->getInput('key_' . $global_consumer->getExtConsumerId()));
                $consumer->setSecret((string) $form->getInput('secret_' . $global_consumer->getExtConsumerId()));
                $consumer->setRefId($this->ref_id);
                $consumer->setEnabled(true);
                if ($form->getInput('platform_id_' . $global_consumer->getExtConsumerId())) {
                    $consumer->platformId = (string) $form->getInput('platform_id_' . $global_consumer->getExtConsumerId());
                } else {
                    $consumer->platformId = null;
                }
                if ($form->getInput('client_id_' . $global_consumer->getExtConsumerId())) {
                    $consumer->clientId = $form->getInput('client_id_' . $global_consumer->getExtConsumerId());
                } else {
                    $consumer->clientId = null;
                }
                if ($form->getInput('deployment_id_' . $global_consumer->getExtConsumerId())) {
                    $consumer->deploymentId = $form->getInput('deployment_id_' . $global_consumer->getExtConsumerId());
                } else {
                    $consumer->deploymentId = null;
                }
                $consumer->saveLTI($connector);
            }
        }
        
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'settings');
    }
    
    /**
     * Save role selection for consumer
     * @param ilPropertyFormGUI $form
     * @param string $global_consumer_id
     */
    protected function saveRoleSelection(ilPropertyFormGUI $form, string $global_consumer_id) : void
    {
        $object_info = new ilLTIProviderObjectSetting($this->ref_id, (int) $global_consumer_id);
        
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
        $a_global_role = ilObject::_getIdsForTitle("il_lti_global_role", "role", false);
        if (is_array($a_global_role) && !empty($a_global_role)) {
            if (count($this->rbacReview->getRolesOfObject($this->ref_id, false)) == 0) {
                $rbacadmin = $this->rbacAdmin;
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
