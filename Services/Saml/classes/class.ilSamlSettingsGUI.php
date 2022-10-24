<?php

declare(strict_types=1);

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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\DI\RBACServices;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Data\Factory;

/**
 * Class ilSamlSettingsGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlSettingsGUI
{
    private const VIEW_MODE_GLOBAL = 1;
    private const VIEW_MODE_SINGLE = 2;

    public const DEFAULT_CMD = 'listIdps';

    /**
     * @var string[]
     */
    protected static array $globalCommands = [
        self::DEFAULT_CMD,
        'showAddIdpForm',
        'showSettings',
        'saveSettings',
        'showNewIdpForm',
        'saveNewIdp',
    ];

    /**
     * @var string[]
     */
    protected static array $globalEntityCommands = [
        'deactivateIdp',
        'activateIdp',
        'confirmDeleteIdp',
        'deleteIdp',
    ];

    /**
     * @var string[]
     */
    protected static array $ignoredUserFields = [
        'mail_incoming_mail',
        'preferences',
        'hide_own_online_status',
        'show_users_online',
        'hits_per_page',
        'roles',
        'upload',
        'password',
        'username',
        'language',
        'skin_style',
        'interests_general',
        'interests_help_offered',
        'interests_help_looking',
        'bs_allow_to_contact_me',
        'chat_osc_accept_msg',
        'chat_broadcast_typing',
    ];

    protected int $ref_id;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilAccessHandler $access;
    protected RBACServices $rbac;
    protected ilErrorHandling $error_handler;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;
    protected GlobalHttpState $httpState;
    protected Refinery $refinery;
    protected ilHelpGUI $help;
    protected ?ilExternalAuthUserAttributeMapping $mapping = null;
    protected ?ilSamlIdp $idp = null;
    protected ?ilSamlAuth $samlAuth = null;

    public function __construct(int $ref_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbac = $DIC->rbac();
        $this->error_handler = $DIC['ilErr'];
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC['ilToolbar'];
        $this->help = $DIC['ilHelp'];
        $this->httpState = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->lng->loadLanguageModule('auth');
        $this->ref_id = $ref_id;
    }

    protected function ensureAccess(string $operation): void
    {
        if (!$this->rbac->system()->checkAccess($operation, $this->getRefId())) {
            $this->error_handler->raiseError($this->lng->txt('msg_no_perm_read'), $this->error_handler->WARNING);
        }
    }

    protected function ensureWriteAccess(): void
    {
        $this->ensureAccess('write');
    }

    protected function ensureReadAccess(): void
    {
        $this->ensureAccess('read');
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    private function getIdpIdOrZero(): int
    {
        $idpId = 0;
        if ($this->httpState->wrapper()->query()->has('saml_idp_id')) {
            $idpId = (int) $this->httpState->wrapper()->query()->retrieve(
                'saml_idp_id',
                $this->refinery->kindlyTo()->int()
            );
        } elseif ($this->httpState->wrapper()->post()->has('saml_idp_id')) {
            $idpId = (int) $this->httpState->wrapper()->post()->retrieve(
                'saml_idp_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        return $idpId;
    }

    protected function initIdp(): void
    {
        try {
            $this->idp = ilSamlIdp::getInstanceByIdpId($this->getIdpIdOrZero());
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('auth_saml_unknow_idp'), true);
            $this->ctrl->setParameter($this, 'saml_idp_id', null);
            $this->ctrl->redirect($this, self::DEFAULT_CMD);
        }
    }

    public function executeCommand(): void
    {
        $this->ensureReadAccess();

        try {
            $factory = new ilSamlAuthFactory();
            $this->samlAuth = $factory->auth();
        } catch (Throwable $e) {
            if ('Database error: could not find driver' === $e->getMessage()) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('auth_saml_err_sqlite_driver'));
            } else {
                $this->tpl->setOnScreenMessage('failure', $e->getMessage());
            }
        }

        $this->help->setScreenIdComponent('auth');

        switch ($this->ctrl->getNextClass()) {
            default:
                $cmd = $this->ctrl->getCmd();
                if ($cmd === null || $cmd === '' || !method_exists($this, $cmd)) {
                    $cmd = self::DEFAULT_CMD;
                }

                $ipdId = $this->getIdpIdOrZero();
                if ($ipdId > 0) {
                    $this->ctrl->saveParameter($this, 'saml_idp_id');
                }

                if (!in_array(strtolower($cmd), array_map('strtolower', self::$globalCommands), true)) {
                    if (0 === $ipdId) {
                        $this->ctrl->redirect($this, self::DEFAULT_CMD);
                    }

                    $this->initIdp();
                    $this->initUserAttributeMapping();
                }

                if (
                    in_array(strtolower($cmd), array_map('strtolower', self::$globalCommands), true) ||
                    in_array(strtolower($cmd), array_map('strtolower', self::$globalEntityCommands), true)
                ) {
                    $this->setSubTabs(self::VIEW_MODE_GLOBAL);
                } else {
                    $this->setSubTabs(self::VIEW_MODE_SINGLE);
                }

                $this->$cmd();
                break;
        }
    }

    protected function listIdps(): void
    {
        if ($this->samlAuth && $this->rbac->system()->checkAccess('write', $this->ref_id)) {
            $addIdpButton = ilLinkButton::getInstance();
            $addIdpButton->setCaption('auth_saml_add_idp_btn');
            $addIdpButton->setUrl($this->ctrl->getLinkTarget($this, 'showNewIdpForm'));
            $this->toolbar->addStickyItem($addIdpButton);
        }

        $table = new ilSamlIdpTableGUI(
            $this,
            self::DEFAULT_CMD,
            $this->rbac->system()->checkAccess('write', $this->getRefId())
        );
        $this->tpl->setContent($table->getHTML());
    }

    protected function deactivateIdp(): void
    {
        $this->ensureWriteAccess();

        $this->idp->setActive(false);
        $this->idp->persist();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        $this->listIdps();
    }

    protected function activateIdp(): void
    {
        $this->ensureWriteAccess();

        $this->idp->setActive(true);
        $this->idp->persist();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        $this->listIdps();
    }

    protected function setSubTabs(int $a_view_mode): void
    {
        switch ($a_view_mode) {
            case self::VIEW_MODE_GLOBAL:
                $this->tabs->addSubTabTarget(
                    'auth_saml_idps',
                    $this->ctrl->getLinkTarget($this, self::DEFAULT_CMD),
                    array_merge(self::$globalEntityCommands, [self::DEFAULT_CMD, 'showNewIdpForm', 'saveNewIdp']),
                    self::class
                );

                $this->tabs->addSubTabTarget(
                    'settings',
                    $this->ctrl->getLinkTarget($this, 'showSettings'),
                    ['showSettings', 'saveSettings'],
                    self::class
                );
                break;

            case self::VIEW_MODE_SINGLE:
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, self::DEFAULT_CMD)
                );

                $this->tabs->addSubTabTarget(
                    'auth_saml_idp_settings',
                    $this->ctrl->getLinkTarget($this, 'showIdpSettings'),
                    ['showIdpSettings', 'saveIdpSettings'],
                    self::class
                );

                $this->tabs->addSubTabTarget(
                    'auth_saml_user_mapping',
                    $this->ctrl->getLinkTarget($this, 'showUserAttributeMappingForm'),
                    ['showUserAttributeMappingForm', 'saveUserAttributeMapping'],
                    self::class
                );
                break;
        }
    }

    private function initUserAttributeMapping(): void
    {
        $this->mapping = new ilExternalAuthUserAttributeMapping('saml', $this->idp->getIdpId());
    }

    protected function getUserAttributeMappingForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveUserAttributeMapping'));
        $form->setTitle($this->lng->txt('auth_saml_user_mapping'));

        $usr_profile = new ilUserProfile();
        foreach ($usr_profile->getStandardFields() as $id => $definition) {
            if (in_array($id, self::$ignoredUserFields, true)) {
                continue;
            }

            $this->addAttributeRuleFieldToForm($form, $this->lng->txt($id), $id);
        }

        foreach (ilUserDefinedFields::_getInstance()->getDefinitions() as $definition) {
            $this->addAttributeRuleFieldToForm($form, $definition['field_name'], 'udf_' . $definition['field_id']);
        }

        if (!$this->access->checkAccess('write', '', $this->getRefId())) {
            foreach ($form->getItems() as $item) {
                $item->setDisabled(true);
            }
        } else {
            $form->addCommandButton('saveUserAttributeMapping', $this->lng->txt('save'));
        }

        return $form;
    }

    protected function addAttributeRuleFieldToForm(
        ilPropertyFormGUI $form,
        string $field_label,
        string $field_name
    ): void {
        $field = new ilTextInputGUI($field_label, $field_name);
        $form->addItem($field);

        $update_automatically = new ilCheckboxInputGUI('', $field_name . '_update');
        $update_automatically->setOptionTitle($this->lng->txt('auth_saml_update_field_info'));
        $update_automatically->setValue('1');
        $form->addItem($update_automatically);
    }

    protected function saveUserAttributeMapping(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getUserAttributeMappingForm();
        if ($form->checkInput()) {
            $this->mapping->delete();

            $usr_profile = new ilUserProfile();
            foreach ($usr_profile->getStandardFields() as $id => $definition) {
                if (in_array($id, self::$ignoredUserFields, true)) {
                    continue;
                }

                $rule = $this->mapping->getEmptyRule();
                $rule->setAttribute($id);
                $rule->setExternalAttribute((string) $form->getInput($rule->getAttribute()));
                $rule->updateAutomatically((bool) $form->getInput($rule->getAttribute() . '_update'));
                $this->mapping[$rule->getAttribute()] = $rule;
            }

            foreach (ilUserDefinedFields::_getInstance()->getDefinitions() as $definition) {
                $rule = $this->mapping->getEmptyRule();
                $rule->setAttribute('udf_' . $definition['field_id']);
                $rule->setExternalAttribute((string) $form->getInput($rule->getAttribute()));
                $rule->updateAutomatically((bool) $form->getInput($rule->getAttribute() . '_update'));
                $this->mapping[$rule->getAttribute()] = $rule;
            }

            $this->mapping->save();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        }

        $form->setValuesByPost();

        $this->showUserAttributeMappingForm($form);
    }

    protected function showUserAttributeMappingForm(ilPropertyFormGUI $form = null): void
    {
        $this->tabs->setSubTabActive('auth_saml_user_mapping');

        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getUserAttributeMappingForm();
            $data = array();
            foreach ($this->mapping as $rule) {
                $data[$rule->getAttribute()] = $rule->getExternalAttribute();
                $data[$rule->getAttribute() . '_update'] = $rule->isAutomaticallyUpdated();
            }
            $form->setValuesByArray($data);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function getSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));
        $form->setTitle($this->lng->txt('auth_saml_configure'));

        $show_login_form = new ilCheckboxInputGUI($this->lng->txt('auth_saml_login_form'), 'login_form');
        $show_login_form->setInfo($this->lng->txt('auth_saml_login_form_info'));
        $show_login_form->setValue('1');
        $form->addItem($show_login_form);

        if (!$this->access->checkAccess('write', '', $this->getRefId())) {
            foreach ($form->getItems() as $item) {
                $item->setDisabled(true);
            }
        } else {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
        }

        return $form;
    }

    /**
     * @return array<int, string>
     */
    private function prepareRoleSelection(): array
    {
        $global_roles = array_map('intval', ilUtil::_sortIds(
            $this->rbac->review()->getGlobalRoles(),
            'object_data',
            'title',
            'obj_id'
        ));

        $select[0] = $this->lng->txt('links_select_one');
        foreach ($global_roles as $role_id) {
            $select[$role_id] = ilObject::_lookupTitle($role_id);
        }

        return $select;
    }

    protected function saveSettings(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getSettingsForm();
        if ($form->checkInput()) {
            ilSamlSettings::getInstance()->setLoginFormStatus((bool) $form->getInput('login_form'));
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        }

        $form->setValuesByPost();

        $this->showSettings($form);
    }

    protected function showSettings(ilPropertyFormGUI $form = null): void
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getSettingsForm();
            $form->setValuesByArray([
                'login_form' => ilSamlSettings::getInstance()->isDisplayedOnLoginPage(),
            ]);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function getIdpSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveIdpSettings'));
        $form->setTitle(sprintf($this->lng->txt('auth_saml_configure_idp'), $this->idp->getEntityId()));

        $idp = new ilTextInputGUI($this->lng->txt('auth_saml_idp'), 'entity_id');
        $idp->setDisabled(true);
        $form->addItem($idp);

        $this->addMetadataElement($form);

        $local = new ilCheckboxInputGUI($this->lng->txt('auth_allow_local'), 'allow_local_auth');
        $local->setValue('1');
        $local->setInfo($this->lng->txt('auth_allow_local_info'));
        $form->addItem($local);

        $uid_claim = new ilTextInputGUI($this->lng->txt('auth_saml_uid_claim'), 'uid_claim');
        $uid_claim->setInfo($this->lng->txt('auth_saml_uid_claim_info'));
        $uid_claim->setRequired(true);
        $form->addItem($uid_claim);

        $sync = new ilCheckboxInputGUI($this->lng->txt('auth_saml_sync'), 'sync_status');
        $sync->setInfo($this->lng->txt('auth_saml_sync_info'));
        $sync->setValue('1');

        $username_claim = new ilTextInputGUI($this->lng->txt('auth_saml_username_claim'), 'login_claim');
        $username_claim->setInfo($this->lng->txt('auth_saml_username_claim_info'));
        $username_claim->setRequired(true);
        $sync->addSubItem($username_claim);

        $role = new ilSelectInputGUI($this->lng->txt('auth_saml_role_select'), 'default_role_id');
        $role->setOptions($this->prepareRoleSelection());
        $role->setRequired(true);
        $sync->addSubItem($role);

        $migr = new ilCheckboxInputGUI($this->lng->txt('auth_saml_migration'), 'account_migr_status');
        $migr->setInfo($this->lng->txt('auth_saml_migration_info'));
        $migr->setValue('1');
        $sync->addSubItem($migr);
        $form->addItem($sync);

        if (!$this->access->checkAccess('write', '', $this->getRefId())) {
            foreach ($form->getItems() as $item) {
                $item->setDisabled(true);
            }
        } else {
            $form->addCommandButton('saveIdpSettings', $this->lng->txt('save'));
        }
        $form->addCommandButton(self::DEFAULT_CMD, $this->lng->txt('cancel'));

        return $form;
    }

    protected function showIdpSettings(ilPropertyFormGUI $form = null): void
    {
        $this->tabs->setSubTabActive('auth_saml_idp_settings');

        if (null === $form) {
            $form = $this->getIdpSettingsForm();
            $data = $this->idp->toArray();
            $this->populateWithMetadata($this->idp, $data);
            $form->setValuesByArray($data);
        } else {
            $form->setValuesByPost();
        }

        $this->help->setSubScreenId('edit_idp');

        $this->tpl->setContent($form->getHTML());
    }

    protected function saveIdpSettings(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getIdpSettingsForm();
        if ($form->checkInput()) {
            $this->idp->bindForm($form);
            $this->idp->persist();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));

            $this->storeMetadata($this->idp, $form->getInput('metadata'));
        }

        $this->showIdpSettings($form);
    }

    protected function getIdpForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveNewIdp'));
        $form->setTitle($this->lng->txt('auth_saml_add_idp_btn'));

        $this->addMetadataElement($form);

        $form->addCommandButton('saveNewIdp', $this->lng->txt('save'));
        $form->addCommandButton('listIdps', $this->lng->txt('cancel'));

        return $form;
    }

    protected function saveNewIdp(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getIdpForm();
        if ($form->checkInput()) {
            $idp = new ilSamlIdp();
            $idp->bindForm($form);
            $idp->persist();

            $this->storeMetadata($idp, $form->getInput('metadata'));

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            $this->ctrl->setParameter($this, 'saml_idp_id', $idp->getIdpId());
            $this->ctrl->redirect($this, 'showIdpSettings');
        }

        $this->showNewIdpForm($form);
    }

    protected function showNewIdpForm(ilPropertyFormGUI $form = null): void
    {
        $this->ensureWriteAccess();

        if (null === $form) {
            $form = $this->getIdpForm();
        } else {
            $form->setValuesByPost();
        }

        $this->help->setSubScreenId('create_idp');

        $this->tpl->setContent($form->getHTML());
    }

    protected function addMetadataElement(ilPropertyFormGUI $form): void
    {
        $metadata = new ilSamlIdpMetadataInputGUI(
            $this->lng->txt('auth_saml_add_idp_md_label'),
            'metadata',
            new ilSamlIdpXmlMetadataParser(
                new Factory(),
                new ilSamlIdpXmlMetadataErrorFormatter()
            )
        );
        $metadata->setInfo($this->lng->txt('auth_saml_add_idp_md_info'));
        $metadata->setRows(20);
        $metadata->setRequired(true);

        $purifier = new ilHtmlPurifierComposite();
        $purifier->addPurifier(new ilSamlIdpMetadataPurifier());

        $metadata->setPurifier($purifier);
        $metadata->usePurifier(true);
        $form->addItem($metadata);
    }

    protected function populateWithMetadata(ilSamlIdp $idp, array &$data): void
    {
        $idpDisco = $this->samlAuth->getIdpDiscovery();

        $data['metadata'] = $idpDisco->fetchIdpMetadata($idp->getIdpId());
    }

    protected function storeMetadata(ilSamlIdp $idp, string $metadata): void
    {
        $idpDisco = $this->samlAuth->getIdpDiscovery();
        $idpDisco->storeIdpMetadata($idp->getIdpId(), $metadata);
    }

    protected function confirmDeleteIdp(): void
    {
        $this->ensureWriteAccess();

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'deleteIdp'));
        $confirmation->setConfirm($this->lng->txt('confirm'), 'deleteIdp');
        $confirmation->setCancel($this->lng->txt('cancel'), self::DEFAULT_CMD);
        $confirmation->setHeaderText($this->lng->txt('auth_saml_sure_delete_idp'));
        $confirmation->addItem('saml_idp_ids', (string) $this->idp->getIdpId(), $this->idp->getEntityId());

        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function deleteIdp(): void
    {
        $this->ensureWriteAccess();

        $idpDisco = $this->samlAuth->getIdpDiscovery();
        $idpDisco->deleteIdpMetadata($this->idp->getIdpId());

        $this->idp->delete();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('auth_saml_deleted_idp'), true);

        $this->ctrl->setParameter($this, 'saml_idp_id', null);
        $this->ctrl->redirect($this, self::DEFAULT_CMD);
    }
}
