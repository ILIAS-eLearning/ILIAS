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

declare(strict_types=1);

use ILIAS\Data\Factory;
use ILIAS\UI\Component\Table\Table;
use ILIAS\User\Profile\Profile;

class ilLDAPSettingsGUI implements ilCtrlSecurityInterface
{
    private int $ref_id;
    private ilLDAPServer $server;
    private ilLDAPAttributeMapping $mapping;
    private ?ilPropertyFormGUI $form_gui = null;
    private ?ilPropertyFormGUI $form = null;

    private ilCtrl $ctrl;
    private ilTabsGUI $tabs_gui;
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $tpl;
    private ilErrorHandling $ilErr;
    private ilAccessHandler $ilAccess;
    private ilRbacReview $rbacReview;
    private ilRbacSystem $rbacSystem;
    private ilToolbarGUI $toolbar;
    private ilGlobalTemplateInterface $main_tpl;
    private ilComponentRepository $component_repository;
    private ?ilLDAPRoleAssignmentRule $role_mapping_rule = null;
    private ?ilLDAPRoleAssignmentRule $rule = null;
    private ?ilLDAPRoleGroupMappingSettings $role_mapping = null;
    /** @var null|list<int> */
    private ?array $rule_ids = null;
    private ?int $rule_id = null;
    private ?int $role_id = null;
    /** @var null|list<int> */
    private ?array $mappings = null;
    private ?string $mapping_template = null;
    private ?int $ldap_server_id = null;
    /** @var null|list<int> */
    private ?array $server_ids = null;
    private ?int $mapping_id = null;
    private ?string $role_bind_user = null;
    private ?string $role_bind_pass = null;
    private bool $role_sync_active = false;
    private array $attribute_mappings = [];
    private readonly \ILIAS\UI\Factory $ui_factory;
    private readonly \ILIAS\UI\Renderer $ui_renderer;
    private readonly \ILIAS\HTTP\GlobalHttpState $http;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly ilObjectDataCache $object_data_cache;
    private readonly Profile $profile;

    public function __construct(int $a_auth_ref_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs_gui = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('ldap');
        $this->lng->loadLanguageModule('ui');
        $this->ilErr = $DIC['ilErr'];
        $this->ilAccess = $DIC->access();
        $this->component_repository = $DIC['component.repository'];
        $this->rbacReview = $DIC->rbac()->review();
        $this->rbacSystem = $DIC->rbac()->system();
        $this->toolbar = $DIC->toolbar();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->object_data_cache = $DIC['ilObjDataCache'];
        $this->profile = $DIC['user']->getProfile();

        $this->tpl = $DIC->ui()->mainTemplate();

        $http_wrapper = $DIC->http()->wrapper();
        $is_post_request = $DIC->http()->request()->getMethod() === 'POST';
        $refinery = $DIC->refinery();

        if ($http_wrapper->query()->has('rule_id')) {
            $this->rule_id = $http_wrapper->query()->retrieve(
                'rule_id',
                $refinery->kindlyTo()->int()
            );
        }
        if ($http_wrapper->query()->has('ldap_server_id')) {
            $this->ldap_server_id = $http_wrapper->query()->retrieve(
                'ldap_server_id',
                $refinery->kindlyTo()->int()
            );
        } elseif ($http_wrapper->query()->has('ldap_servers_server_id')) {
            $this->ldap_server_id = $http_wrapper->query()->retrieve(
                'ldap_servers_server_id',
                $this->refinery->in()->series([
                    $refinery->kindlyTo()->listOf(
                        $refinery->kindlyTo()->int()
                    ),
                    $this->refinery->custom()->constraint(
                        fn($value): bool => count($value) === 1,
                        $this->lng->txt('select_one')
                    ),
                    $this->refinery->custom()->transformation(
                        fn($value): int => $value[0]
                    )
                ])
            );
        }
        $this->initServer();

        if ($http_wrapper->query()->has('mapping_id')) {
            $this->mapping_id = $http_wrapper->query()->retrieve(
                'mapping_id',
                $refinery->kindlyTo()->int()
            );
        }
        if ($http_wrapper->query()->has('ldap_role_mapping_mapping_ids')) {
            $this->mappings = $http_wrapper->query()->retrieve(
                'ldap_role_mapping_mapping_ids',
                $refinery->kindlyTo()->listOf($refinery->kindlyTo()->string())
            );
            if ($this->mappings === ['ALL_OBJECTS']) {
                $mapping_instance = ilLDAPRoleGroupMappingSettings::_getInstanceByServerId($this->server->getServerId());
                $this->mappings = array_map(static function (array $mapping): int {
                    return $mapping['mapping_id'];
                }, $mapping_instance->getMappings());
            }
            $this->mappings = $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())->transform($this->mappings);
            if (count($this->mappings) === 1) {
                $this->mapping_id = current($this->mappings);
            }
        }
        if ($http_wrapper->query()->has('ldap_role_assignment_rule_ids')) {
            $this->rule_ids = $http_wrapper->query()->retrieve(
                'ldap_role_assignment_rule_ids',
                $refinery->kindlyTo()->listOf($refinery->kindlyTo()->string())
            );
            if ($this->rule_ids === ['ALL_OBJECTS']) {
                $rule_objs = ilLDAPRoleAssignmentRule::_getRules($this->server->getServerId());
                $this->rule_ids = array_map(static function (ilLDAPRoleAssignmentRule $rule): int {
                    return $rule->getRuleId();
                }, $rule_objs);
            }
            $this->rule_ids = $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())->transform($this->rule_ids);
            if (count($this->rule_ids) === 1) {
                $this->rule_id = current($this->rule_ids);
            }
        }
        if ($is_post_request) {
            if ($http_wrapper->post()->has('rule_ids')) {
                $this->rule_ids = $http_wrapper->post()->retrieve(
                    'rule_ids',
                    $refinery->kindlyTo()->listOf($refinery->kindlyTo()->int())
                );
            }
            if ($http_wrapper->post()->has('role_id')) {
                $this->role_id = $http_wrapper->post()->retrieve(
                    'role_id',
                    $refinery->kindlyTo()->int()
                );
            }
            if ($http_wrapper->post()->has('rule_id')) {
                $this->rule_id = $http_wrapper->post()->retrieve(
                    'rule_id',
                    $refinery->kindlyTo()->int()
                );
            }
            if ($http_wrapper->post()->has('mappings')) {
                $this->mappings = $http_wrapper->post()->retrieve(
                    'mappings',
                    $refinery->kindlyTo()->listOf($refinery->kindlyTo()->int())
                );
            }
            if ($http_wrapper->post()->has('server_ids')) {
                $this->server_ids = $http_wrapper->post()->retrieve(
                    'server_ids',
                    $refinery->kindlyTo()->listOf($refinery->kindlyTo()->int())
                );
            }
            if ($http_wrapper->post()->has('mapping_template')) {
                $this->mapping_template = $http_wrapper->post()->retrieve(
                    'mapping_template',
                    $refinery->kindlyTo()->string()
                );
            }
            if ($http_wrapper->post()->has('role_bind_user')) {
                $this->role_bind_user = ilUtil::stripSlashes($http_wrapper->post()->retrieve(
                    'role_bind_user',
                    $refinery->kindlyTo()->string()
                ));
            }
            if ($http_wrapper->post()->has('role_bind_pass')) {
                $this->role_bind_pass = ilUtil::stripSlashes($http_wrapper->post()->retrieve(
                    'role_bind_pass',
                    $refinery->kindlyTo()->string()
                ));
            }
            if ($http_wrapper->post()->has('role_sync_active')) {
                $this->role_sync_active = $http_wrapper->post()->retrieve(
                    'role_sync_active',
                    $refinery->kindlyTo()->bool()
                );
            }
            foreach ($this->getAttributeMappingKeys() as $key) {
                if ($http_wrapper->post()->has($key . '_value')) {
                    $this->attribute_mappings[$key] = [ilUtil::stripSlashes($http_wrapper->post()->retrieve(
                        $key . '_value',
                        $refinery->kindlyTo()->string()
                    )), $http_wrapper->post()->has($key . '_update')];
                } else {
                    $this->attribute_mappings[$key] = ['', false];
                }
            }
        }

        $this->ref_id = $a_auth_ref_id;

        if ($this->ctrl->getCmd() !== 'addServerSettings') {
            $this->ctrl->setParameter($this, 'ldap_server_id', $this->ldap_server_id);
        }
    }

    public function executeCommand(): bool
    {
        $cmd = $this->ctrl->getCmd();

        if ($cmd === null || $cmd === '' || !method_exists($this, $cmd . 'Cmd')) {
            $cmd = 'serverList';
        }

        if (!$this->rbacSystem->checkAccess('read', $this->ref_id)) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }

        $verified_command = $cmd . 'Cmd';
        $this->$verified_command();

        return true;
    }

    private function checkAccess(string $permission): void
    {
        if (!$this->ilAccess->checkAccess($permission, '', $this->ref_id)) {
            $this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilErr->WARNING);
        }
    }

    /**
     * @return list<string>
     */
    private function getAttributeMappingKeys(): array
    {
        $keys = [];
        foreach (array_keys($this->getMappingFields()) as $key) {
            $keys[] = $key;
        }
        foreach ($this->profile->getFields() as $field) {
            if (!$field->isCustom()) {
                continue;
            }

            $keys[] = 'udf_' . $field->getIdentifier();
        }

        return $keys;
    }


    private function getServer(): ilLDAPServer
    {
        return $this->server;
    }

    private function roleAssignmentsCmd(): void
    {
        $this->setSubTabs();
        $this->tabs_gui->activateTab('role_assignments');

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.ldap_role_assignments.html', 'components/ILIAS/LDAP');

        $this->initFormRoleAssignments('create');
        $this->role_mapping_rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId(0);
        $this->tpl->setVariable('NEW_ASSIGNMENT_TBL', $this->form->getHTML());

        $rules = ilLDAPRoleAssignmentRule::_getRules($this->server->getServerId());
        if ($rules !== []) {
            $this->tpl->setVariable('RULES_TBL', $this->ui_renderer->render($this->getRoleAssignmentTable()));
        }
    }

    private function editRoleAssignment(): void
    {
        if (!$this->rule_id) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->roleAssignmentsCmd();
            return;
        }

        $this->setSubTabs();
        $this->tabs_gui->activateTab('role_assignments');

        $this->ctrl->setParameter($this, 'rule_id', $this->rule_id);
        $this->initFormRoleAssignments(
            'edit'
        );
        $this->role_mapping_rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($this->rule_id);
        $this->setValuesByArray();
        $this->tpl->setContent($this->form->getHTML());
    }

    private function setValuesByArray(): void
    {
        $role_id = $this->role_mapping_rule->getRoleId();
        if ($this->rbacReview->isGlobalRole($role_id)) {
            $val['role_name'] = 0;
            $val['role_id'] = $role_id;
        } else {
            $val['role_name'] = 1;
            $val['role_search'] = ilObject::_lookupTitle($role_id);
        }
        $val['add_missing'] = (int) $this->role_mapping_rule->isAddOnUpdateEnabled();
        $val['remove_deprecated'] = (int) $this->role_mapping_rule->isRemoveOnUpdateEnabled();
        $val['type'] = $this->role_mapping_rule->getType();
        $val['dn'] = $this->role_mapping_rule->getDN();
        $val['at'] = $this->role_mapping_rule->getMemberAttribute();
        $val['isdn'] = $this->role_mapping_rule->isMemberAttributeDN();
        $val['name'] = $this->role_mapping_rule->getAttributeName();
        $val['value'] = $this->role_mapping_rule->getAttributeValue();
        $val['plugin_id'] = $this->role_mapping_rule->getPluginId();

        $this->form->setValuesByArray($val);
    }

    private function updateRoleAssignmentCmd(): void
    {
        if (!$this->ilAccess->checkAccess('write', '', $this->ref_id)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->roleAssignmentsCmd();
            return;
        }

        $this->initFormRoleAssignments('edit');

        $formWithoutErrors = $this->form->checkInput();
        $roleErrorMessage = $this->checkRoleAssignmentInput($this->rule_id);
        if (!$formWithoutErrors || $roleErrorMessage !== '') {
            if ($roleErrorMessage !== '') {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt($roleErrorMessage));
            }

            $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.ldap_role_assignments.html', 'components/ILIAS/LDAP');

            $this->form->setValuesByPost();
            $this->tpl->setVariable('NEW_ASSIGNMENT_TBL', $this->form->getHTML());
            $this->tabs_gui->activateSubTab('role_assignments');
            return;
        }

        if ($this->roleSelectionRequired()) {
            $this->ctrl->redirect($this, 'showRoleSelection');
        } else {
            $this->rule->update();

            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'roleAssignments');
        }
    }

    private function confirmDeleteRules(): void
    {
        $this->checkAccess('write');

        if (!$this->rule_ids) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->roleAssignmentsCmd();
            return;
        }

        $this->setSubTabs();
        $this->tabs_gui->activateTab('role_assignments');

        $c_gui = new ilConfirmationGUI();
        $c_gui->setFormAction($this->ctrl->getFormAction($this, 'deleteRules'));
        $c_gui->setHeaderText($this->lng->txt('ldap_confirm_del_role_ass'));
        $c_gui->setCancel($this->lng->txt('cancel'), 'roleAssignments');
        $c_gui->setConfirm($this->lng->txt('confirm'), 'deleteRules');
        foreach ($this->rule_ids as $rule_id) {
            $rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($rule_id);
            $c_gui->addItem('rule_ids[]', (string) $rule_id, $rule->conditionToString());
        }

        $this->tpl->setContent($c_gui->getHTML());
    }

    private function deleteRulesCmd(): void
    {
        $this->checkAccess('write');

        if (!$this->rule_ids) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_once'));
            $this->roleAssignmentsCmd();
            return;
        }

        foreach ($this->rule_ids as $rule_id) {
            $rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($rule_id);
            $rule->delete();
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('ldap_deleted_rule'), true);
        $this->ctrl->redirect($this, 'roleAssignments');
    }

    private function addRoleAssignmentCmd(): void
    {
        if (!$this->ilAccess->checkAccess('write', '', $this->ref_id)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'));
            $this->roleAssignmentsCmd();
            return;
        }

        $this->setSubTabs();
        $this->initFormRoleAssignments('create');

        $formWithoutErrors = $this->form->checkInput();
        $roleErrorMessage = $this->checkRoleAssignmentInput();
        if (!$formWithoutErrors || $roleErrorMessage !== '') {
            if ($roleErrorMessage !== '') {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt($roleErrorMessage));
            }

            $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.ldap_role_assignments.html', 'components/ILIAS/LDAP');

            $this->form->setValuesByPost();
            $this->tpl->setVariable('NEW_ASSIGNMENT_TBL', $this->form->getHTML());
            $this->tpl->setVariable('RULES_TBL', $this->ui_renderer->render($this->getRoleAssignmentTable()));
            $this->tabs_gui->activateSubTab('role_assignments');
            return;
        }

        if ($this->roleSelectionRequired()) {
            $this->ctrl->redirect($this, 'showRoleSelection');
        } else {
            $this->rule->create();

            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'roleAssignments');
        }
    }

    private function roleSelectionRequired(): bool
    {
        if ($this->rule->getRoleId() > 0) {
            return false;
        }

        $rule = [];
        $rule['server_id'] = $this->getServer()->getServerId();
        $rule['rule_id'] = $this->rule_id ?: 0;
        $rule['role_search'] = $this->form->getInput('role_search');
        $rule['add_on_update'] = $this->form->getInput('add_missing');
        $rule['remove_on_update'] = $this->form->getInput('remove_deprecated');
        $rule['type'] = $this->form->getInput('type');
        $rule['dn'] = $this->form->getInput('dn');
        $rule['at'] = $this->form->getInput('at');
        $rule['isdn'] = $this->form->getInput('isdn');
        $rule['name'] = $this->form->getInput('name');
        $rule['value'] = $this->form->getInput('value');
        $rule['plugin'] = $this->form->getInput('plugin_id');
        ilSession::set('ldap_role_ass', serialize($rule));
        $this->ctrl->saveParameter($this, 'rule_id');

        return true;
    }

    private function showRoleSelectionCmd(): void
    {
        $this->checkAccess('read');

        $this->setSubTabs();
        $this->tabs_gui->activateTab('role_assignments');
        $this->ctrl->saveParameter($this, 'rule_id');

        $parser = new ilQueryParser(unserialize(ilSession::get('ldap_role_ass'), ['allowed_classes' => false])['role_search']);
        $parser->setMinWordLength(1);
        $parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
        $parser->parse();

        $object_search = new ilLikeObjectSearch($parser);
        $object_search->setFilter(['role']);
        $res = $object_search->performSearch();

        $entries = $res->getEntries();

        $table = new ilRoleSelectionTableGUI($this, 'showRoleSelection');
        $table->setTitle($this->lng->txt('ldap_role_selection'));
        if ($this->rbacSystem->checkAccess('write', $this->ref_id)) {
            $table->addMultiCommand('saveRoleSelection', $this->lng->txt('ldap_choose_role'));
        }
        $table->parse($entries);

        $this->tpl->setContent($table->getHTML());
    }

    private function saveRoleSelectionCmd(): void
    {
        if (!$this->ilAccess->checkAccess('write', '', $this->ref_id)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->roleAssignmentsCmd();
            return;
        }

        if (!$this->role_id) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showRoleSelectionCmd();
            return;
        }

        $this->loadRoleAssignmentRule($this->rule_id ?? 0, false);
        $this->rule->setRoleId($this->role_id);

        if ($this->rule_id) {
            $this->rule->update();
        } else {
            $this->rule->create();
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->setParameter($this, 'rule_id', null);
        $this->ctrl->redirect($this, 'roleAssignments');
    }

    private function checkRoleAssignmentInput(int $a_rule_id = 0): string
    {
        $this->loadRoleAssignmentRule($a_rule_id);
        $this->rule->validate();

        return $this->ilErr->getMessage();
    }

    private function getRoleAssignmentTable(): ?Table
    {
        $rules = ilLDAPRoleAssignmentRule::_getRules($this->server->getServerId());
        if ($rules !== []) {
            return (new LDAPRoleAssignmentTable(
                $this->http->request(),
                $this->lng,
                $this->ui_factory,
                (new Factory())
                    ->uri(
                        ilUtil::_getHttpPath() . '/' . $this->ctrl->getLinkTarget($this, 'handleRoleAssignmentTableActions')
                    ),
                $this->server->getServerId(),
                $this->rbacSystem->checkAccess('write', $this->ref_id)
            ))->getComponent();
        }

        return null;
    }

    private function loadRoleAssignmentRule(int $a_rule_id, bool $load_from_form = true): void
    {
        if (is_object($this->rule)) {
            return;
        }

        $this->rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($a_rule_id);

        if ($load_from_form) {
            if ($this->form->getInput('role_name') === '0') {
                $this->rule->setRoleId((int) $this->form->getInput('role_id'));
            } elseif ($this->form->getInput('role_search')) {
                $parser = new ilQueryParser('"' . $this->form->getInput('role_search') . '"');

                // TODO: Handle minWordLength
                $parser->setMinWordLength(1);
                $parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
                $parser->parse();

                $object_search = new ilLikeObjectSearch($parser);
                $object_search->setFilter(['role']);
                $res = $object_search->performSearch();

                $entries = $res->getEntries();
                if (count($entries) === 1) {
                    $role = current($entries);
                    $this->rule->setRoleId($role['obj_id']);
                } elseif (count($entries) > 1) {
                    $this->rule->setRoleId(-1);
                }
            }

            $this->rule->setAttributeName($this->form->getInput('name'));
            $this->rule->setAttributeValue($this->form->getInput('value'));
            $this->rule->setDN($this->form->getInput('dn'));
            $this->rule->setMemberAttribute($this->form->getInput('at'));
            $this->rule->setMemberIsDN((bool) $this->form->getInput('isdn'));
            $this->rule->enableAddOnUpdate((bool) $this->form->getInput('add_missing'));
            $this->rule->enableRemoveOnUpdate((bool) $this->form->getInput('remove_deprecated'));
            $this->rule->setPluginId((int) $this->form->getInput('plugin_id'));
            $this->rule->setType((int) $this->form->getInput('type'));
            $this->rule->setServerId($this->getServer()->getServerId());
            return;
        }

        $this->rule->setServerId($this->getServer()->getServerId());
        $rule = unserialize(ilSession::get('ldap_role_ass'), ['allowed_classes' => false]);

        $this->rule->enableAddOnUpdate((bool) ($rule['add_on_update'] ?? false));
        $this->rule->enableRemoveOnUpdate((bool) ($rule['remove_on_update'] ?? false));
        $this->rule->setType((int) ilUtil::stripSlashes(
            $rule['type'] ?? (string) ilLDAPRoleAssignmentRule::TYPE_ATTRIBUTE
        ));
        $this->rule->setDN(ilUtil::stripSlashes($rule['dn'] ?? ''));
        $this->rule->setMemberAttribute(ilUtil::stripSlashes($rule['at'] ?? ''));
        $this->rule->setMemberIsDN((bool) (ilUtil::stripSlashes($rule['isdn'] ?? false)));
        $this->rule->setAttributeName(ilUtil::stripSlashes($rule['name'] ?? ''));
        $this->rule->setAttributeValue(ilUtil::stripSlashes($rule['value'] ?? ''));
        $this->rule->setPluginId((int) ilUtil::stripSlashes((string) ($rule['plugin'] ?? '0')));
    }

    private function deleteRoleMappingCmd(): void
    {
        $this->checkAccess('write');

        if (!$this->mappings) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->roleMappingCmd();
            return;
        }

        $this->initRoleMapping();

        foreach ($this->mappings as $mapping_id) {
            $this->role_mapping->delete($mapping_id);
        }
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('ldap_deleted_role_mapping'));
        $this->roleMappingCmd();
    }

    private function chooseMappingCmd(): void
    {
        $this->checkAccess('read');

        if (!$this->mapping_template) {
            $this->userMappingCmd();
            return;
        }

        $this->initAttributeMapping();
        $this->mapping->clearRules();

        foreach (ilLDAPAttributeMappingUtils::_getMappingRulesByClass($this->mapping_template) as $key => $value) {
            $this->mapping->setRule($key, $value, false);
        }
        $this->userMappingCmd();
    }

    private function saveMappingCmd(): void
    {
        $this->checkAccess('write');

        $this->initAttributeMapping();
        $this->tabs_gui->activateTab('role_mapping');

        foreach (array_keys($this->getMappingFields()) as $key) {
            $this->mapping->setRule(
                $key,
                $this->attribute_mappings[$key][0],
                $this->attribute_mappings[$key][1]
            );
        }
        foreach ($this->profile->getFields() as $field) {
            if (!$field->isCustom()) {
                continue;
            }

            $key = 'udf_' . $field->getIdentifier();
            $this->mapping->setRule(
                $key,
                $this->attribute_mappings[$key][0],
                $this->attribute_mappings[$key][1]
            );
        }

        $this->mapping->save();
        $this->userMappingCmd();

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
    }

    private function getServerTableAction(): string
    {
        return $this->http->wrapper()->query()->retrieve(
            'ldap_servers_table_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );
    }

    private function handleServerTableActionsCmd(): void
    {
        match ($this->getServerTableAction()) {
            'editServerSettings' => $this->editServerSettingsCmd(),
            'activateServer' => $this->activateServer(),
            'deactivateServer' => $this->deactivateServer(),
            'confirmDeleteServerSettings' => $this->confirmDeleteServerSettings(),
            default => $this->ctrl->redirect($this, 'serverList')
        };
    }

    private function getRoleAssigmentsTableAction(): string
    {
        return $this->http->wrapper()->query()->retrieve(
            'ldap_role_assignment_table_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );
    }

    private function handleRoleAssignmentTableActionsCmd(): void
    {
        match ($this->getRoleAssigmentsTableAction()) {
            'confirmDeleteRules' => $this->confirmDeleteRules(),
            'editRoleAssignment' => $this->editRoleAssignment(),
            default => $this->ctrl->redirect($this, 'serverList')
        };
    }

    private function getRoleMappingsTableAction(): string
    {
        return $this->http->wrapper()->query()->retrieve(
            'ldap_role_mapping_table_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );
    }

    private function handleRoleMappingTableActionsCmd(): void
    {
        match ($this->getRoleMappingsTableAction()) {
            'confirmDeleteRoleMapping' => $this->confirmDeleteRoleMapping(),
            'addRoleMapping' => $this->addRoleMappingCmd(),
            'editRoleMapping' => $this->editRoleMapping(),
            default => $this->ctrl->redirect($this, 'serverList'),
        };
    }

    private function serverListCmd(): void
    {
        if (!$this->rbacSystem->checkAccess('read', $this->ref_id)) {
            $this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilErr->WARNING);
        }

        if (!ilLDAPServer::checkLDAPLib() && $this->server->isActive()) {
            $this->main_tpl->setOnScreenMessage('failure', 'Missing LDAP libraries. Please ensure that the PHP LDAP module is installed on your server.');
        }

        if ($this->rbacSystem->checkAccess('write', $this->ref_id)) {
            $this->toolbar->addButton(
                $this->lng->txt('add_ldap_server'),
                $this->ctrl->getLinkTarget($this, 'addServerSettings')
            );
        }

        $this->ctrl->setParameter($this, 'ldap_server_id', null);

        $table = new \ILIAS\LDAP\Server\UI\ServerTable(
            ilLDAPServer::_getAllServer(),
            $this,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->ctrl,
            $this->http->request(),
            (new Factory())
                ->uri(
                    ilUtil::_getHttpPath() . '/' . $this->ctrl->getLinkTarget($this, 'handleServerTableActions')
                ),
            $this->rbacSystem->checkAccess('write', $this->ref_id),
            $this->rbacSystem->checkAccess('read', $this->ref_id)
        );

        $this->tpl->setContent($this->ui_renderer->render($table->getComponent()));
    }

    private function setServerFormValues(): void
    {
        $this->form_gui->setValuesByArray([
            'active' => $this->server->isActive(),
            'ds' => !$this->server->isAuthenticationEnabled(),
            'server_name' => $this->server->getName(),
            'server_url' => $this->server->getUrlString(),
            'version' => $this->server->getVersion(),
            'base_dn' => $this->server->getBaseDN(),
            'referrals' => $this->server->isActiveReferrer(),
            'tls' => $this->server->isActiveTLS(),
            'binding_type' => $this->server->getBindingType(),
            'bind_dn' => $this->server->getBindUser(),
            'bind_pass' => $this->server->getBindPassword(),
            'bind_pass_retype' => $this->server->getBindPassword(),
            'search_base' => $this->server->getSearchBase(),
            'user_scope' => $this->server->getUserScope(),
            'user_attribute' => $this->server->getUserAttribute(),
            'filter' => $this->server->getFilter(),
            'group_dn' => $this->server->getGroupDN(),
            'group_scope' => $this->server->getGroupScope(),
            'group_filter' => $this->server->getGroupFilter(),
            'group_member' => $this->server->getGroupMember(),
            'memberisdn' => $this->server->enabledGroupMemberIsDN(),
            'group' => $this->server->getGroupName(),
            'group_attribute' => $this->server->getGroupAttribute(),
            'group_optional' => $this->server->isMembershipOptional(),
            'group_user_filter' => $this->server->getGroupUserFilter(),
            'sync_on_login' => $this->server->enabledSyncOnLogin(),
            'sync_per_cron' => $this->server->enabledSyncPerCron(),
            'global_role' => ilLDAPAttributeMapping::_lookupGlobalRole($this->server->getServerId()),
            'migration' => (int) $this->server->isAccountMigrationEnabled(),
            'name_filter' => $this->server->getUsernameFilter(),
            'escape_dn' => $this->server->enabledEscapeDN()
        ]);
    }

    private function initForm(): void
    {
        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'save'));
        $this->form_gui->setTitle($this->lng->txt('ldap_configure'));

        $active = new ilCheckboxInputGUI($this->lng->txt('auth_ldap_enable'), 'active');
        $active->setValue('1');
        $this->form_gui->addItem($active);

        $ds = new ilCheckboxInputGUI($this->lng->txt('ldap_as_ds'), 'ds');
        $ds->setValue('1');
        $ds->setInfo($this->lng->txt('ldap_as_ds_info'));
        $this->form_gui->addItem($ds);

        $servername = new ilTextInputGUI($this->lng->txt('ldap_server_name'), 'server_name');
        $servername->setRequired(true);
        $servername->setInfo($this->lng->txt('ldap_server_name_info'));
        $servername->setSize(32);
        $servername->setMaxLength(32);
        $this->form_gui->addItem($servername);

        $namefilter = new ilTextInputGUI($this->lng->txt('ldap_username_filter'), 'name_filter');
        $namefilter->setInfo($this->lng->txt('ldap_username_filter_info'));
        $namefilter->setSize(64);
        $namefilter->setMaxLength(255);
        $this->form_gui->addItem($namefilter);

        $serverurl = new ilTextInputGUI($this->lng->txt('ldap_server'), 'server_url');
        $serverurl->setRequired(true);
        $serverurl->setInfo($this->lng->txt('ldap_server_url_info'));
        $serverurl->setSize(64);
        $serverurl->setMaxLength(255);
        $this->form_gui->addItem($serverurl);

        $version = new ilSelectInputGUI($this->lng->txt('ldap_version'), 'version');
        $version->setOptions([2 => 2, 3 => 3]);
        $version->setInfo($this->lng->txt('ldap_server_version_info'));
        $this->form_gui->addItem($version);

        $basedsn = new ilTextInputGUI($this->lng->txt('basedn'), 'base_dn');
        $basedsn->setRequired(true);
        $basedsn->setSize(64);
        $basedsn->setMaxLength(255);
        $this->form_gui->addItem($basedsn);

        $referrals = new ilCheckboxInputGUI($this->lng->txt('ldap_referrals'), 'referrals');
        $referrals->setValue('1');
        $referrals->setInfo($this->lng->txt('ldap_referrals_info'));
        $this->form_gui->addItem($referrals);

        $section_security = new ilFormSectionHeaderGUI();
        $section_security->setTitle($this->lng->txt('ldap_server_security_settings'));
        $this->form_gui->addItem($section_security);

        $tls = new ilCheckboxInputGUI($this->lng->txt('ldap_tls'), 'tls');
        $tls->setValue('1');
        $this->form_gui->addItem($tls);

        $binding = new ilRadioGroupInputGUI($this->lng->txt('ldap_server_binding'), 'binding_type');
        $anonymous = new ilRadioOption($this->lng->txt('ldap_bind_anonymous'), (string) ilLDAPServer::LDAP_BIND_ANONYMOUS);
        $binding->addOption($anonymous);
        $user = new ilRadioOption($this->lng->txt('ldap_bind_user'), (string) ilLDAPServer::LDAP_BIND_USER);
        $dn = new ilTextInputGUI($this->lng->txt('ldap_server_bind_dn'), 'bind_dn');
        $dn->setSize(64);
        $dn->setMaxLength(255);
        $user->addSubItem($dn);
        $pass = new ilPasswordInputGUI($this->lng->txt('ldap_server_bind_pass'), 'bind_pass');
        $pass->setSkipSyntaxCheck(true);
        $pass->setSize(12);
        $pass->setMaxLength(100);
        $user->addSubItem($pass);
        $binding->addOption($user);
        $this->form_gui->addItem($binding);

        $section_auth = new ilFormSectionHeaderGUI();
        $section_auth->setTitle($this->lng->txt('ldap_authentication_settings'));
        $this->form_gui->addItem($section_auth);

        $search_base = new ilTextInputGUI($this->lng->txt('ldap_user_dn'), 'search_base');
        $search_base->setInfo($this->lng->txt('ldap_search_base_info'));
        $search_base->setSize(64);
        $search_base->setMaxLength(255);
        $this->form_gui->addItem($search_base);

        $user_scope = new ilSelectInputGUI($this->lng->txt('ldap_user_scope'), 'user_scope');
        $user_scope->setOptions([
            ilLDAPServer::LDAP_SCOPE_ONE => $this->lng->txt('ldap_scope_one'),
            ilLDAPServer::LDAP_SCOPE_SUB => $this->lng->txt('ldap_scope_sub')
        ]);
        $user_scope->setInfo($this->lng->txt('ldap_user_scope_info'));
        $this->form_gui->addItem($user_scope);

        $user_attribute = new ilTextInputGUI($this->lng->txt('ldap_user_attribute'), 'user_attribute');
        $user_attribute->setSize(16);
        $user_attribute->setMaxLength(64);
        $user_attribute->setRequired(true);
        $this->form_gui->addItem($user_attribute);

        $filter = new ilTextInputGUI($this->lng->txt('ldap_search_filter'), 'filter');
        $filter->setInfo($this->lng->txt('ldap_filter_info'));
        $filter->setSize(64);
        $filter->setMaxLength(512);
        $this->form_gui->addItem($filter);

        $section_restrictions = new ilFormSectionHeaderGUI();
        $section_restrictions->setTitle($this->lng->txt('ldap_group_restrictions'));
        $this->form_gui->addItem($section_restrictions);

        $group_dn = new ilTextInputGUI($this->lng->txt('ldap_group_search_base'), 'group_dn');
        $group_dn->setInfo($this->lng->txt('ldap_group_dn_info'));
        $group_dn->setSize(64);
        $group_dn->setMaxLength(255);
        $this->form_gui->addItem($group_dn);

        $group_scope = new ilSelectInputGUI($this->lng->txt('ldap_group_scope'), 'group_scope');
        $group_scope->setOptions([
            ilLDAPServer::LDAP_SCOPE_ONE => $this->lng->txt('ldap_scope_one'),
            ilLDAPServer::LDAP_SCOPE_SUB => $this->lng->txt('ldap_scope_sub')
        ]);
        $group_scope->setInfo($this->lng->txt('ldap_group_scope_info'));
        $this->form_gui->addItem($group_scope);

        $group_filter = new ilTextInputGUI($this->lng->txt('ldap_group_filter'), 'group_filter');
        $group_filter->setInfo($this->lng->txt('ldap_group_filter_info'));
        $group_filter->setSize(64);
        $group_filter->setMaxLength(255);
        $this->form_gui->addItem($group_filter);

        $group_member = new ilTextInputGUI($this->lng->txt('ldap_group_member'), 'group_member');
        $group_member->setInfo($this->lng->txt('ldap_group_member_info'));
        $group_member->setSize(32);
        $group_member->setMaxLength(255);
        $this->form_gui->addItem($group_member);

        $group_member_isdn = new ilCheckboxInputGUI($this->lng->txt('ldap_memberisdn'), 'memberisdn');
        $this->form_gui->addItem($group_member_isdn);

        $escapedn = new ilCheckboxInputGUI($this->lng->txt('ldap_escapedn'), 'escape_dn');
        $escapedn->setValue('1');
        $escapedn->setInfo($this->lng->txt('ldap_escapedn_info'));
        $this->form_gui->addItem($escapedn);

        $group = new ilTextInputGUI($this->lng->txt('ldap_group_name'), 'group');
        $group->setInfo($this->lng->txt('ldap_group_name_info'));
        $group->setSize(32);
        $group->setMaxLength(255);
        $this->form_gui->addItem($group);

        $group_atrr = new ilTextInputGUI($this->lng->txt('ldap_group_attribute'), 'group_attribute');
        $group_atrr->setInfo($this->lng->txt('ldap_group_attribute_info'));
        $group_atrr->setSize(16);
        $group_atrr->setMaxLength(64);
        $this->form_gui->addItem($group_atrr);

        $group_optional = new ilCheckboxInputGUI($this->lng->txt('ldap_group_membership'), 'group_optional');
        $group_optional->setOptionTitle($this->lng->txt('ldap_group_member_optional'));
        $group_optional->setInfo($this->lng->txt('ldap_group_optional_info'));
        $group_optional->setValue('1');
        $group_user_filter = new ilTextInputGUI($this->lng->txt('ldap_group_user_filter'), 'group_user_filter');
        $group_user_filter->setSize(64);
        $group_user_filter->setMaxLength(255);
        $group_optional->addSubItem($group_user_filter);
        $this->form_gui->addItem($group_optional);

        $section_sync = new ilFormSectionHeaderGUI();
        $section_sync->setTitle($this->lng->txt('ldap_user_sync'));
        $this->form_gui->addItem($section_sync);

        $ci_gui = new ilCustomInputGUI($this->lng->txt('ldap_moment_sync'));
        $sync_on_login = new ilCheckboxInputGUI($this->lng->txt('ldap_sync_login'), 'sync_on_login');
        $sync_on_login->setValue('1');
        $ci_gui->addSubItem($sync_on_login);
        $sync_per_cron = new ilCheckboxInputGUI($this->lng->txt('ldap_sync_cron'), 'sync_per_cron');
        $sync_per_cron->setValue('1');
        $ci_gui->addSubItem($sync_per_cron);
        $ci_gui->setInfo($this->lng->txt('ldap_user_sync_info'));
        $this->form_gui->addItem($ci_gui);

        $global_role = new ilSelectInputGUI($this->lng->txt('ldap_global_role_assignment'), 'global_role');
        $global_role->setOptions($this->prepareGlobalRoleSelection());
        $global_role->setInfo($this->lng->txt('ldap_global_role_info'));
        $this->form_gui->addItem($global_role);

        $migr = new ilCheckboxInputGUI($this->lng->txt('auth_ldap_migration'), 'migration');
        $migr->setInfo($this->lng->txt('auth_ldap_migration_info'));
        $migr->setValue('1');
        $this->form_gui->addItem($migr);

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            ilAdministrationSettingsFormHandler::FORM_LDAP,
            $this->form_gui,
            ilAdministrationSettingsFormHandler::getSettingsGUIInstance('auth')
        );

        if ($this->rbacSystem->checkAccess('write', $this->ref_id)) {
            $this->form_gui->addCommandButton('save', $this->lng->txt('save'));
        } else {
            foreach ($this->form_gui->getItems() as $item) {
                if ($item instanceof ilFormSectionHeaderGUI) {
                    continue;
                }
                $item->setDisabled(true);
            }
        }

        if ($this->ctrl->getCmd() === 'addServerSettings') {
            $this->form_gui->addCommandButton('serverList', $this->lng->txt('cancel'));
        }
    }

    private function saveCmd(): void
    {
        $this->checkAccess('write');

        $this->setSubTabs();
        $this->tabs_gui->setTabActive('settings');

        $this->initForm();
        if ($this->form_gui->checkInput()) {
            $this->server->toggleActive((bool) $this->form_gui->getInput('active'));
            $this->server->enableAuthentication(!(bool) $this->form_gui->getInput('ds'));
            $this->server->setName($this->form_gui->getInput('server_name'));
            $this->server->setUrl($this->form_gui->getInput('server_url'));
            $this->server->setVersion((int) $this->form_gui->getInput('version'));
            $this->server->setBaseDN($this->form_gui->getInput('base_dn'));
            $this->server->toggleReferrer((bool) $this->form_gui->getInput('referrals'));
            $this->server->toggleTLS((bool) $this->form_gui->getInput('tls'));
            $this->server->setBindingType((int) $this->form_gui->getInput('binding_type'));
            $this->server->setBindUser($this->form_gui->getInput('bind_dn'));
            $this->server->setBindPassword($this->form_gui->getInput('bind_pass'));
            $this->server->setSearchBase($this->form_gui->getInput('search_base'));
            $this->server->setUserScope((int) $this->form_gui->getInput('user_scope'));
            $this->server->setUserAttribute($this->form_gui->getInput('user_attribute'));
            $this->server->setFilter($this->form_gui->getInput('filter'));
            $this->server->setGroupDN($this->form_gui->getInput('group_dn'));
            $this->server->setGroupScope((int) $this->form_gui->getInput('group_scope'));
            $this->server->setGroupFilter($this->form_gui->getInput('group_filter'));
            $this->server->setGroupMember($this->form_gui->getInput('group_member'));
            $this->server->enableGroupMemberIsDN((bool) $this->form_gui->getInput('memberisdn'));
            $this->server->setGroupName($this->form_gui->getInput('group'));
            $this->server->setGroupAttribute($this->form_gui->getInput('group_attribute'));
            $this->server->setGroupUserFilter($this->form_gui->getInput('group_user_filter'));
            $this->server->toggleMembershipOptional((bool) $this->form_gui->getInput('group_optional'));
            $this->server->enableSyncOnLogin((bool) $this->form_gui->getInput('sync_on_login'));
            $this->server->enableSyncPerCron((bool) $this->form_gui->getInput('sync_per_cron'));
            $this->server->setGlobalRole((int) $this->form_gui->getInput('global_role'));
            $this->server->enableAccountMigration((bool) $this->form_gui->getInput('migration'));
            $this->server->setUsernameFilter($this->form_gui->getInput('name_filter'));
            $this->server->enableEscapeDN((bool) $this->form_gui->getInput('escape_dn'));
            if (!$this->server->validate()) {
                $this->main_tpl->setOnScreenMessage('failure', $this->ilErr->getMessage());
                $url_err = $this->server->getUrlValidationError();
                if (!empty($url_err)) {
                    $this->form_gui->getItemByPostVar('server_url')?->setAlert(
                        $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform($url_err)
                    );
                }
                $this->form_gui->setValuesByPost();
                $this->tpl->setContent($this->form_gui->getHTML());
                return;
            }

            if ($this->server->getServerId()) {
                $this->server->update();
            } else {
                $this->server->create();
            }

            $this->initAttributeMapping();
            $this->mapping->setRule('global_role', (string) $this->form_gui->getInput('global_role'), false);
            $this->mapping->save();

            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->setParameter($this, 'ldap_server_id', null);
            $this->ctrl->redirect($this, 'serverList');
        }

        $this->form_gui->setValuesByPost();
        $this->tpl->setContent($this->form_gui->getHTML());
    }

    private function backToServerListCmd(): never
    {
        $this->ctrl->setParameter($this, 'ldap_server_id', null);
        $this->ctrl->redirect($this, 'serverList');
    }

    private function setSubTabs(): void
    {
        $this->tabs_gui->clearTargets();

        $this->tabs_gui->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, 'backToServerList')
        );

        $this->tabs_gui->addTab(
            'settings',
            $this->lng->txt('ldap_settings'),
            $this->ctrl->getLinkTarget($this, 'editServerSettings')
        );

        // Disable all other tabs, if server hasn't been configured.
        if (!count(ilLDAPServer::_getServerList())) {
            return;
        }

        $this->tabs_gui->addTab(
            'user_mapping',
            $this->lng->txt('ldap_user_mapping'),
            $this->ctrl->getLinkTarget($this, 'userMapping')
        );

        $this->tabs_gui->addTab(
            'role_assignments',
            $this->lng->txt('ldap_role_assignments'),
            $this->ctrl->getLinkTarget($this, 'roleAssignments')
        );

        $this->tabs_gui->addTab(
            'role_mapping',
            $this->lng->txt('ldap_role_mapping'),
            $this->ctrl->getLinkTarget($this, 'roleMapping')
        );
    }

    private function initServer(): void
    {
        $server_id = 0;
        if ($this->ldap_server_id) {
            $server_id = $this->ldap_server_id;
        }

        $this->server = new ilLDAPServer($server_id);
    }

    private function initAttributeMapping(): void
    {
        $this->mapping = ilLDAPAttributeMapping::_getInstanceByServerId($this->getServer()->getServerId());
    }

    private function initRoleMapping(): void
    {
        $this->role_mapping = ilLDAPRoleGroupMappingSettings::_getInstanceByServerId($this->getServer()->getServerId());
    }

    /**
     * @return array<int, string>
     */
    private function prepareGlobalRoleSelection(): array
    {
        $global_roles = ilUtil::_sortIds(
            $this->rbacReview->getGlobalRoles(),
            'object_data',
            'title',
            'obj_id'
        );

        $select[0] = $this->lng->txt('links_select_one');
        foreach ($global_roles as $role_id) {
            $select[$role_id] = ilObject::_lookupTitle((int) $role_id);
        }

        return $select;
    }

    /**
     * @return array<string, string>
     */
    private function getMappingFields(): array
    {
        return [
            'gender' => $this->lng->txt('gender'),
            'firstname' => $this->lng->txt('firstname'),
            'lastname' => $this->lng->txt('lastname'),
            'title' => $this->lng->txt('person_title'),
            'institution' => $this->lng->txt('institution'),
            'department' => $this->lng->txt('department'),
            'street' => $this->lng->txt('street'),
            'city' => $this->lng->txt('city'),
            'zipcode' => $this->lng->txt('zipcode'),
            'country' => $this->lng->txt('country'),
            'phone_office' => $this->lng->txt('phone_office'),
            'phone_home' => $this->lng->txt('phone_home'),
            'phone_mobile' => $this->lng->txt('phone_mobile'),
            'fax' => $this->lng->txt('fax'),
            'email' => $this->lng->txt('email'),
            'second_email' => $this->lng->txt('second_email'),
            'hobby' => $this->lng->txt('hobby'),
            'matriculation' => $this->lng->txt('matriculation')
        ];
    }

    private function initFormRoleAssignments(string $a_mode): void
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        $role = new ilRadioGroupInputGUI($this->lng->txt('ldap_ilias_role'), 'role_name');
        $role->setInfo($this->lng->txt('ldap_role_name_info'));
        $role->setRequired(true);
        $this->form->addItem($role);

        $global = new ilRadioOption($this->lng->txt('ldap_global_role'), '0');
        $role->addOption($global);

        $role_select = new ilSelectInputGUI('', 'role_id');
        $role_select->setOptions($this->prepareGlobalRoleSelection());
        $global->addSubItem($role_select);

        $local = new ilRadioOption($this->lng->txt('ldap_local_role'), '1');
        $role->addOption($local);

        $role_search = new ilRoleAutoCompleteInputGUI('', 'role_search', $this, 'addRoleAutoCompleteObject');
        $role_search->setSize(40);
        $local->addSubItem($role_search);

        $update = new ilNonEditableValueGUI($this->lng->txt('ldap_update_roles'), 'update_roles');
        $update->setValue($this->lng->txt('ldap_check_role_assignment'));

        $add = new ilCheckboxInputGUI('', 'add_missing');
        $add->setOptionTitle($this->lng->txt('ldap_add_missing'));
        $update->addSubItem($add);

        $remove = new ilCheckboxInputGUI('', 'remove_deprecated');
        $remove->setOptionTitle($this->lng->txt('ldap_remove_deprecated'));
        $update->addSubItem($remove);

        $this->form->addItem($update);

        $group = new ilRadioGroupInputGUI($this->lng->txt('ldap_assignment_type'), 'type');
        $group->setRequired(true);

        $radio_group = new ilRadioOption($this->lng->txt('ldap_role_by_group'), (string) ilLDAPRoleAssignmentRule::TYPE_GROUP);

        $dn = new ilTextInputGUI($this->lng->txt('ldap_group_dn'), 'dn');
        $dn->setSize(32);
        $dn->setMaxLength(512);
        $dn->setInfo($this->lng->txt('ldap_role_grp_dn_info'));
        $radio_group->addSubItem($dn);
        $at = new ilTextInputGUI($this->lng->txt('ldap_role_grp_at'), 'at');
        $at->setSize(16);
        $at->setMaxLength(128);
        $radio_group->addSubItem($at);
        $isdn = new ilCheckboxInputGUI($this->lng->txt('ldap_role_grp_isdn'), 'isdn');
        $isdn->setInfo($this->lng->txt('ldap_group_member_info'));
        $radio_group->addSubItem($isdn);
        $radio_group->setInfo($this->lng->txt('ldap_role_grp_info'));

        $group->addOption($radio_group);

        $radio_attribute = new ilRadioOption($this->lng->txt('ldap_role_by_attribute'), (string) ilLDAPRoleAssignmentRule::TYPE_ATTRIBUTE);
        $name = new ilTextInputGUI($this->lng->txt('ldap_role_at_name'), 'name');
        $name->setSize(32);
        $name->setMaxLength(128);
        $radio_attribute->addSubItem($name);

        $val = new ilTextInputGUI($this->lng->txt('ldap_role_at_value'), 'value');
        $val->setSize(32);
        $val->setMaxLength(128);
        $radio_attribute->addSubItem($val);
        $radio_attribute->setInfo($this->lng->txt('ldap_role_at_info'));

        $group->addOption($radio_attribute);

        $pl_active = $this->component_repository->getPluginSlotById('ldaphk')->hasActivePlugins();
        $pl = new ilRadioOption($this->lng->txt('ldap_plugin'), '3');

        $pl->setInfo($this->lng->txt('ldap_plugin_info'));
        $pl->setDisabled(!$pl_active);

        $id = new ilNumberInputGUI($this->lng->txt('ldap_plugin_id'), 'plugin_id');
        $id->setDisabled(!$pl_active);
        $id->setSize(3);
        $id->setMaxLength(3);
        $id->setMaxValue(999);
        $id->setMinValue(1);
        $pl->addSubItem($id);

        $group->addOption($pl);
        $this->form->addItem($group);

        if ($this->rbacSystem->checkAccess('write', $this->ref_id)) {
            switch ($a_mode) {
                case 'edit':
                    $this->form->setTitle($this->lng->txt('ldap_edit_role_ass_rule'));
                    $this->form->addCommandButton('updateRoleAssignment', $this->lng->txt('save'));
                    break;
                case 'create':
                    $this->form->setTitle($this->lng->txt('ldap_add_role_ass_rule'));
                    $this->form->addCommandButton('addRoleAssignment', $this->lng->txt('ldap_btn_add_role_ass'));
                    break;
            }
        } else {
            foreach ($this->form->getItems() as $item) {
                if ($item instanceof ilFormSectionHeaderGUI) {
                    continue;
                }
                $item->setDisabled(true);
            }
        }
    }

    private function addRoleAutoCompleteObjectCmd(): void
    {
        ilRoleAutoCompleteInputGUI::echoAutoCompleteList();
    }

    private function userMappingToolbar(): void
    {
        $select_form = new ilSelectInputGUI('mapping_template');
        $select_form->setPostVar('mapping_template');
        $options = [
            '' => $this->lng->txt('ldap_mapping_template'),
            'inetOrgPerson' => 'inetOrgPerson',
            'organizationalPerson' => 'organizationalPerson',
            'person' => 'person',
            'ad_2003' => 'Active Directory (Win 2003)'
        ];
        $select_form->setOptions($options);
        $select_form->setValue($this->mapping_template);

        $this->toolbar->addInputItem($select_form);
        $this->toolbar->addFormButton($this->lng->txt('show'), 'chooseMapping');
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'chooseMapping'));
    }

    private function initUserMappingForm(): ilPropertyFormGUI
    {
        $propertie_form = new ilPropertyFormGUI();
        $propertie_form->setTitle($this->lng->txt('ldap_mapping_table'));
        $propertie_form->setFormAction($this->ctrl->getFormAction($this, 'saveMapping'));

        foreach ($this->getMappingFields() as $mapping => $lang) {
            $text_form = new ilTextInputGUI($lang);
            $text_form->setPostVar($mapping . '_value');
            $text_form->setValue($this->mapping->getValue($mapping));
            $text_form->setSize(32);
            $text_form->setMaxLength(255);
            $propertie_form->addItem($text_form);

            $checkbox_form = new ilCheckboxInputGUI('');
            $checkbox_form->setPostVar($mapping . '_update');
            $checkbox_form->setChecked($this->mapping->enabledUpdate($mapping));
            $checkbox_form->setOptionTitle($this->lng->txt('ldap_update_field_info'));
            $propertie_form->addItem($checkbox_form);
        }

        foreach ($this->profile->getFields() as $field) {
            if (!$field->isCustom()) {
                continue;
            }

            $text_form = new ilTextInputGUI($field->getLabel($this->lng));
            $text_form->setPostVar('udf_' . $field->getIdentifier() . '_value');
            $text_form->setValue($this->mapping->getValue('udf_' . $field->getIdentifier()));
            $text_form->setSize(32);
            $text_form->setMaxLength(255);
            $propertie_form->addItem($text_form);

            $checkbox_form = new ilCheckboxInputGUI("");
            $checkbox_form->setPostVar('udf_' . $field->getIdentifier() . '_update');
            $checkbox_form->setChecked($this->mapping->enabledUpdate('udf_' . $field->getIdentifier()));
            $checkbox_form->setOptionTitle($this->lng->txt('ldap_update_field_info'));
            $propertie_form->addItem($checkbox_form);
        }

        if ($this->rbacSystem->checkAccess('write', $this->ref_id)) {
            $propertie_form->addCommandButton('saveMapping', $this->lng->txt('save'));
        } else {
            foreach ($propertie_form->getItems() as $item) {
                if ($item instanceof ilFormSectionHeaderGUI) {
                    continue;
                }
                if ($item instanceof ilRadioGroupInputGUI) {
                    $item->setDisabled(true);
                }
                $item->setDisabled(true);
            }
        }

        return $propertie_form;
    }

    private function roleMappingCmd(): void
    {
        $this->checkAccess('read');

        $this->setSubTabs();
        $this->tabs_gui->setTabActive('role_mapping');

        $propertie_form = new ilPropertyFormGUI();
        $propertie_form->setTitle($this->lng->txt('ldap_role_settings'));
        $propertie_form->setFormAction($this->ctrl->getFormAction($this, 'saveSyncronizationSettings'));
        $role_active = new ilCheckboxInputGUI($this->lng->txt('ldap_role_active'));
        $role_active->setPostVar('role_sync_active');
        $role_active->setChecked($this->server->enabledRoleSynchronization());
        $propertie_form->addItem($role_active);
        $binding = new ilCombinationInputGUI($this->lng->txt('ldap_server_binding'));
        $binding->setInfo($this->lng->txt('ldap_role_bind_user_info'));
        $user = new ilTextInputGUI('');
        $user->setPostVar('role_bind_user');
        $user->setValue($this->server->getRoleBindDN());
        $user->setSize(50);
        $user->setMaxLength(255);
        $binding->addCombinationItem('0', $user, $this->lng->txt('ldap_role_bind_user'));

        $pass = new ilPasswordInputGUI('');
        $pass->setPostVar('role_bind_pass');
        $pass->setValue($this->server->getRoleBindPassword());
        $pass->setSize(12);
        $pass->setMaxLength(100);
        $pass->setRetype(false);
        $binding->addCombinationItem('1', $pass, $this->lng->txt('ldap_role_bind_pass'));

        $propertie_form->addItem($binding);

        if ($this->rbacSystem->checkAccess('write', $this->ref_id)) {
            $this->toolbar->addButton(
                $this->lng->txt('ldap_new_role_assignment'),
                $this->ctrl->getLinkTarget($this, 'addRoleMapping')
            );
            $propertie_form->addCommandButton('saveSyncronizationSettings', $this->lng->txt('save'));
        } else {
            foreach ($propertie_form->getItems() as $item) {
                if ($item instanceof ilFormSectionHeaderGUI) {
                    continue;
                }
                $item->setDisabled(true);
            }
        }

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.ldap_role_mappings.html', 'components/ILIAS/LDAP');
        $this->tpl->setVariable('NEW_ASSIGNMENT_TBL', $propertie_form->getHTML());

        $this->tpl->setVariable(
            'RULES_TBL',
            $this->ui_renderer->render((new LDAPRoleMappingTable(
                $this->http->request(),
                $this->lng,
                $this->ui_factory,
                $this->server->getServerId(),
                $this->object_data_cache,
                $this->rbacReview,
                (new Factory())
                    ->uri(ilUtil::_getHttpPath() . '/' . $this->ctrl->getLinkTarget($this, 'handleRoleMappingTableActions')),
                $this->rbacSystem->checkAccess('write', $this->ref_id),
            ))->getComponent())
        );
    }

    private function editRoleMapping(): void
    {
        $mapping = new ilLDAPRoleGroupMappingSetting($this->mapping_id);
        $mapping->read();

        $propertie_form = $this->initRoleMappingForm('updateRoleMapping');
        $propertie_form->setTitle($this->lng->txt('ldap_edit_role_assignment'));
        if ($propertie_form->getItemByPostVar('url')) {
            $propertie_form->getItemByPostVar('url')->setValue($mapping->getURL());
        }
        if ($propertie_form->getItemByPostVar('dn')) {
            $propertie_form->getItemByPostVar('dn')->setValue($mapping->getDN());
        }
        if ($propertie_form->getItemByPostVar('member')) {
            $propertie_form->getItemByPostVar('member')->setValue($mapping->getMemberAttribute());
        }
        if ($propertie_form->getItemByPostVar('memberisdn')) {
            $propertie_form->getItemByPostVar('memberisdn')->setChecked($mapping->getMemberISDN());
        }
        if ($propertie_form->getItemByPostVar('role')) {
            $propertie_form->getItemByPostVar('role')->setValue($mapping->getRoleName());
        }
        if ($propertie_form->getItemByPostVar('info')) {
            $propertie_form->getItemByPostVar('info')->setValue($mapping->getMappingInfo());
        }
        if ($propertie_form->getItemByPostVar('info_type')) {
            $propertie_form->getItemByPostVar('info_type')->setChecked($mapping->getMappingInfoType());
        }

        $this->tpl->setContent($propertie_form->getHTML());
    }

    private function createRoleMappingCmd(): void
    {
        $this->checkAccess('write');

        $propertie_form = $this->initRoleMappingForm('createRoleMapping');
        if ($propertie_form->checkInput() && $this->rbacReview->roleExists($propertie_form->getInput('role'))) {
            $mapping = new ilLDAPRoleGroupMappingSetting(0);
            $mapping->setServerId($this->server->getServerId());
            $mapping->setURL($propertie_form->getInput('url'));
            $mapping->setDN($propertie_form->getInput('dn'));
            $mapping->setMemberAttribute($propertie_form->getInput('member'));
            $mapping->setMemberISDN((bool) $propertie_form->getInput('memberisdn'));
            $mapping->setRoleByName($propertie_form->getInput('role'));
            $mapping->setMappingInfo($propertie_form->getInput('info'));
            $mapping->setMappingInfoType((bool) $propertie_form->getInput('info_type'));
            $mapping->save();

            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'roleMapping');
        } else {
            if (!$this->rbacReview->roleExists($propertie_form->getInput('role'))) {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('ldap_role_not_exists') . ' ' .
                        $propertie_form->getInput('role'));
            }
            $propertie_form->setValuesByPost();

            $this->tpl->setContent($propertie_form->getHTML());
        }
    }

    private function confirmDeleteRoleMapping(): void
    {
        $this->checkAccess('write');

        if (!$this->mappings) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'roleMapping');
            return;
        }

        $this->setSubTabs();
        $this->tabs_gui->activateTab('role_mapping');

        $c_gui = new ilConfirmationGUI();
        $c_gui->setFormAction($this->ctrl->getFormAction($this, 'deleteRoleMapping'));
        $c_gui->setHeaderText($this->lng->txt('ldap_confirm_del_role_ass'));
        $c_gui->setCancel($this->lng->txt('cancel'), 'roleMapping');
        $c_gui->setConfirm($this->lng->txt('confirm'), 'deleteRoleMapping');

        foreach ($this->mappings as $id) {
            $mapping = new ilLDAPRoleGroupMappingSetting($id);
            $mapping->read();
            $txt = $this->lng->txt('obj_role') . ': ' . $mapping->getRoleName() . ', ';
            $txt .= $this->lng->txt('ldap_group_dn') . ': ' . $mapping->getDN() . ', ';
            $txt .= $this->lng->txt('ldap_server_short') . ' ' . $mapping->getURL() . ', ';
            $txt .= $this->lng->txt('ldap_group_member_short') . ' ' . $mapping->getMemberAttribute();
            $c_gui->addItem('mappings[]', (string) $id, $txt);
        }

        $this->tpl->setContent($c_gui->getHTML());
    }

    private function addServerSettingsCmd(): void
    {
        $this->checkAccess('write');

        $this->ctrl->clearParameters($this);

        $this->initForm();

        $this->tpl->setContent($this->form_gui->getHTML());
    }

    private function editServerSettingsCmd(): void
    {
        $this->checkAccess('read');

        $this->setSubTabs();
        $this->tabs_gui->setTabActive('settings');

        $this->initForm();
        $this->setServerFormValues();

        $this->tpl->setContent($this->form_gui->getHTML());
    }

    private function confirmDeleteServerSettings(): void
    {
        $this->checkAccess('write');

        if (!$this->ldap_server_id) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->serverListCmd();
            return;
        }

        $c_gui = new ilConfirmationGUI();
        $c_gui->setFormAction($this->ctrl->getFormAction($this, 'deleteServerSettings'));
        $c_gui->setHeaderText($this->lng->txt('ldap_confirm_del_server_settings'));
        $c_gui->setCancel($this->lng->txt('cancel'), 'serverList');
        $c_gui->setConfirm($this->lng->txt('confirm'), 'deleteServerSettings');
        $setting = new ilLDAPServer($this->ldap_server_id);
        $c_gui->addItem('server_ids[]', (string) $setting->getServerId(), $setting->getName());

        $this->tpl->setContent($c_gui->getHTML());
    }

    private function deleteServerSettingsCmd(): void
    {
        $this->checkAccess('write');

        if (!$this->server_ids) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->serverListCmd();
            return;
        }

        foreach ($this->server_ids as $server_id) {
            $setting = new ilLDAPServer($server_id);
            $setting->delete();
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('deleted'), true);
        $this->ctrl->redirect($this, 'serverlist');
    }

    private function userMappingCmd(): void
    {
        $this->initAttributeMapping();

        $this->setSubTabs();
        $this->tabs_gui->activateTab('user_mapping');
        $this->userMappingToolbar();

        $propertie_form = $this->initUserMappingForm();

        $this->tpl->setContent($propertie_form->getHTML());
    }

    private function activateServer(): void
    {
        $this->checkAccess('write');

        $this->server->toggleActive(true);
        $this->server->update();

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('saved_successfully'), true);
        $this->ctrl->setParameter($this, 'ldap_server_id', null);
        $this->ctrl->redirect($this, 'serverList');
    }

    private function deactivateServer(): void
    {
        $this->checkAccess('write');

        $this->server->toggleActive(false);
        $this->server->update();

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('saved_successfully'), true);
        $this->ctrl->setParameter($this, 'ldap_server_id', null);
        $this->ctrl->redirect($this, 'serverList');
    }

    private function initRoleMappingForm(string $command): ilPropertyFormGUI
    {
        $this->setSubTabs();
        $this->tabs_gui->activateTab('role_mapping');

        if ($this->mapping_id) {
            $this->ctrl->setParameter($this, 'mapping_id', $this->mapping_id);
        }

        $propertie_form = new ilPropertyFormGUI();
        $propertie_form->setTitle($this->lng->txt('ldap_mapping_table'));
        $propertie_form->setFormAction($this->ctrl->getFormAction($this, $command));

        $url = new ilTextInputGUI($this->lng->txt('ldap_server'));
        $url->setPostVar('url');
        $url->setSize(50);
        $url->setMaxLength(255);
        $url->setRequired(true);

        $group_dn = new ilTextInputGUI($this->lng->txt('ldap_group_dn'));
        $group_dn->setPostVar('dn');
        $group_dn->setSize(50);
        $group_dn->setMaxLength(255);
        $group_dn->setInfo($this->lng->txt('ldap_dn_info'));
        $group_dn->setRequired(true);

        $member = new ilTextInputGUI($this->lng->txt('ldap_group_member'));
        $member->setPostVar('member');
        $member->setSize(32);
        $member->setMaxLength(255);
        $member->setInfo($this->lng->txt('ldap_member_info'));
        $member->setRequired(true);

        $member_isdn = new ilCheckboxInputGUI('');
        $member_isdn->setPostVar('memberisdn');
        $member_isdn->setOptionTitle($this->lng->txt('ldap_memberisdn'));

        $role = new ilTextInputGUI($this->lng->txt('ldap_ilias_role'));
        $role->setPostVar('role');
        $role->setSize(32);
        $role->setMaxLength(255);
        $role->setInfo($this->lng->txt('ldap_role_info'));
        $role->setRequired(true);

        $info = new ilTextAreaInputGUI($this->lng->txt('ldap_info_text'));
        $info->setPostVar('info');
        $info->setCols(50);
        $info->setRows(3);
        $info->setInfo($this->lng->txt('ldap_info_text_info'));

        $info_type = new ilCheckboxInputGUI('');
        $info_type->setPostVar('info_type');
        $info_type->setOptionTitle($this->lng->txt('ldap_mapping_info_type'));

        $propertie_form->addItem($url);
        $propertie_form->addItem($group_dn);
        $propertie_form->addItem($member);
        $propertie_form->addItem($member_isdn);
        $propertie_form->addItem($role);
        $propertie_form->addItem($info);
        $propertie_form->addItem($info_type);

        if ($this->rbacSystem->checkAccess('write', $this->ref_id)) {
            $propertie_form->addCommandButton($command, $this->lng->txt('save'));
        } else {
            foreach ($propertie_form->getItems() as $item) {
                if ($item instanceof ilFormSectionHeaderGUI) {
                    continue;
                }

                $item->setDisabled(true);
            }
        }
        $propertie_form->addCommandButton('roleMapping', $this->lng->txt('cancel'));

        return $propertie_form;
    }

    private function addRoleMappingCmd(): void
    {
        $propertie_form = $this->initRoleMappingForm('createRoleMapping');
        if ($propertie_form->getItemByPostVar('url')) {
            $propertie_form->getItemByPostVar('url')->setValue($this->server->getUrl());
        }

        if ($this->mapping_id) {
            $mapping = new ilLDAPRoleGroupMappingSetting($this->mapping_id);
            $mapping->read();
            if ($propertie_form->getItemByPostVar('url')) {
                $propertie_form->getItemByPostVar('url')->setValue($mapping->getURL());
            }
            if ($propertie_form->getItemByPostVar('dn')) {
                $propertie_form->getItemByPostVar('dn')->setValue($mapping->getDN());
            }
            if ($propertie_form->getItemByPostVar('member')) {
                $propertie_form->getItemByPostVar('member')->setValue($mapping->getMemberAttribute());
            }
            if ($propertie_form->getItemByPostVar('memberisdn')) {
                $propertie_form->getItemByPostVar('memberisdn')->setChecked($mapping->getMemberISDN());
            }
            if ($propertie_form->getItemByPostVar('role')) {
                $propertie_form->getItemByPostVar('role')->setValue($mapping->getRoleName());
            }
            if ($propertie_form->getItemByPostVar('info')) {
                $propertie_form->getItemByPostVar('info')->setValue($mapping->getMappingInfo());
            }
            if ($propertie_form->getItemByPostVar('info_type')) {
                $propertie_form->getItemByPostVar('info_type')->setChecked($mapping->getMappingInfoType());
            }
        }

        $this->tpl->setContent($propertie_form->getHTML());
    }

    private function updateRoleMappingCmd(): void
    {
        $this->checkAccess('write');
        $propertie_form = $this->initRoleMappingForm('updateRoleMapping');

        if ($propertie_form->checkInput() &&
            $this->rbacReview->roleExists($propertie_form->getInput('role'))) {
            $mapping = new ilLDAPRoleGroupMappingSetting($this->mapping_id);
            $mapping->setServerId($this->server->getServerId());
            $mapping->setURL($propertie_form->getInput('url'));
            $mapping->setDN($propertie_form->getInput('dn'));
            $mapping->setMemberAttribute($propertie_form->getInput('member'));
            $mapping->setMemberISDN((bool) $propertie_form->getInput('memberisdn'));
            $mapping->setRoleByName($propertie_form->getInput('role'));
            $mapping->setMappingInfo($propertie_form->getInput('info'));
            $mapping->setMappingInfoType((bool) $propertie_form->getInput('info_type'));
            $mapping->update();

            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'roleMapping');
        } else {
            if (!$this->rbacReview->roleExists($propertie_form->getInput('role'))) {
                $this->main_tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('ldap_role_not_exists') . ' ' . $propertie_form->getInput('role')
                );
            }
            $propertie_form->setValuesByPost();

            $this->tpl->setContent($propertie_form->getHTML());
        }
    }

    private function saveSyncronizationSettingsCmd(): void
    {
        $this->checkAccess('write');

        $this->server->setRoleBindDN($this->role_bind_user);
        $this->server->setRoleBindPassword($this->role_bind_pass);
        $this->server->enableRoleSynchronization($this->role_sync_active);

        if ($this->server->getServerId()) {
            $this->server->update();
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'roleMapping');
    }

    public function getUnsafeGetCommands(): array
    {
        return [
            'handleServerTableActions',
            'handleRoleAssignmentTableActions',
            'handleRoleMappingTableActions',
        ];
    }

    public function getSafePostCommands(): array
    {
        return [];
    }
}
