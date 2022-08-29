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
final class ilSamlSettingsGUI
{
    private const VIEW_MODE_GLOBAL = 1;
    private const VIEW_MODE_SINGLE = 2;

    public const DEFAULT_CMD = 'listIdps';

    private const PERMISSION_WRITE = 'write';

    private const REQUEST_PARAM_SAML_IDP_ID = 'saml_idp_id';

    private const MESSAGE_TYPE_FAILURE = 'failure';
    private const MESSAGE_TYPE_SUCCESS = 'success';

    private const LNG_SAVED_SUCCESSFULLY = 'saved_successfully';
    private const LNG_AUTH_SAML_USER_MAPPING = 'auth_saml_user_mapping';
    private const LNG_LOGIN_FORM = 'login_form';
    private const LNG_CANCEL = 'cancel';

    private const CMD_SAVE_NEW_IDP = 'saveNewIdp';
    private const CMD_SAVE_SETTINGS = 'saveSettings';
    private const CMD_SHOW_IDP_SETTINGS = 'showIdpSettings';
    private const CMT_SAVE_IDP_SETTINGS = 'saveIdpSettings';
    private const CMD_SAVE = 'save';
    private const CMD_SAVE_USER_ATTRIBUTE_MAPPING = 'saveUserAttributeMapping';

    private const PROP_UPDATE_SUFFIX = '_update';

    private const METADATA_STORAGE_KEY = 'metadata';

    /**
     * @var string[]
     */
    private const GLOBAL_COMMANDS = [
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
    private const GLOBAL_ENTITY_COMMANDS = [
        'deactivateIdp',
        'activateIdp',
        'confirmDeleteIdp',
        'deleteIdp',
    ];

    /**
     * @var string[]
     */
    private const IGNORED_USER_FIELDS = [
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

    private ilCtrlInterface $ctrl;
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $tpl;
    private ilAccessHandler $access;
    private RBACServices $rbac;
    private ilErrorHandling $error_handler;
    private ilTabsGUI $tabs;
    private ilToolbarGUI $toolbar;
    private GlobalHttpState $httpState;
    private Refinery $refinery;
    private ilHelpGUI $help;
    private ?ilExternalAuthUserAttributeMapping $mapping = null;
    private ?ilSamlIdp $idp = null;
    private ?ilSamlAuth $samlAuth = null;

    public function __construct(private int $ref_id)
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
    }

    private function ensureAccess(string $operation): void
    {
        if (!$this->rbac->system()->checkAccess($operation, $this->ref_id)) {
            $this->error_handler->raiseError($this->lng->txt('msg_no_perm_read'), $this->error_handler->WARNING);
        }
    }

    private function ensureWriteAccess(): void
    {
        $this->ensureAccess(self::PERMISSION_WRITE);
    }

    private function ensureReadAccess(): void
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
        if ($this->httpState->wrapper()->query()->has(self::REQUEST_PARAM_SAML_IDP_ID)) {
            $idpId = (int) $this->httpState->wrapper()->query()->retrieve(
                self::REQUEST_PARAM_SAML_IDP_ID,
                $this->refinery->kindlyTo()->int()
            );
        } elseif ($this->httpState->wrapper()->post()->has(self::REQUEST_PARAM_SAML_IDP_ID)) {
            $idpId = (int) $this->httpState->wrapper()->post()->retrieve(
                self::REQUEST_PARAM_SAML_IDP_ID,
                $this->refinery->kindlyTo()->int()
            );
        }

        return $idpId;
    }

    private function initIdp(): void
    {
        try {
            $this->idp = ilSamlIdp::getInstanceByIdpId($this->getIdpIdOrZero());
        } catch (Exception) {
            $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_FAILURE, $this->lng->txt('auth_saml_unknow_idp'), true);
            $this->ctrl->setParameter($this, self::REQUEST_PARAM_SAML_IDP_ID, null);
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
                $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_FAILURE, $this->lng->txt('auth_saml_err_sqlite_driver'));
            } else {
                $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_FAILURE, $e->getMessage());
            }
        }

        $this->help->setScreenIdComponent('auth');
        $cmd = $this->ctrl->getCmd();
        if ($cmd === null || $cmd === '' || !method_exists($this, $cmd)) {
            $cmd = self::DEFAULT_CMD;
        }
        $ipdId = $this->getIdpIdOrZero();
        if ($ipdId > 0) {
            $this->ctrl->saveParameter($this, self::REQUEST_PARAM_SAML_IDP_ID);
        }
        if (!in_array(strtolower($cmd), array_map('strtolower', self::GLOBAL_COMMANDS), true)) {
            if (0 === $ipdId) {
                $this->ctrl->redirect($this, self::DEFAULT_CMD);
            }

            $this->initIdp();
            $this->initUserAttributeMapping();
        }
        if (
            in_array(strtolower($cmd), array_map('strtolower', self::GLOBAL_COMMANDS), true) ||
            in_array(strtolower($cmd), array_map('strtolower', self::GLOBAL_ENTITY_COMMANDS), true)
        ) {
            $this->setSubTabs(self::VIEW_MODE_GLOBAL);
        } else {
            $this->setSubTabs(self::VIEW_MODE_SINGLE);
        }
        $this->$cmd();
    }

    private function listIdps(): void
    {
        if ($this->samlAuth && $this->rbac->system()->checkAccess(self::PERMISSION_WRITE, $this->ref_id)) {
            $addIdpButton = ilLinkButton::getInstance();
            $addIdpButton->setCaption('auth_saml_add_idp_btn');
            $addIdpButton->setUrl($this->ctrl->getLinkTarget($this, 'showNewIdpForm'));
            $this->toolbar->addStickyItem($addIdpButton);
        }

        $table = new ilSamlIdpTableGUI(
            $this,
            self::DEFAULT_CMD,
            $this->rbac->system()->checkAccess(self::PERMISSION_WRITE, $this->ref_id)
        );
        $this->tpl->setContent($table->getHTML());
    }

    private function deactivateIdp(): void
    {
        $this->ensureWriteAccess();

        $this->idp->setActive(false);
        $this->idp->persist();

        $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY));
        $this->listIdps();
    }

    private function activateIdp(): void
    {
        $this->ensureWriteAccess();

        $this->idp->setActive(true);
        $this->idp->persist();

        $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY));
        $this->listIdps();
    }

    private function setSubTabs(int $a_view_mode): void
    {
        switch ($a_view_mode) {
            case self::VIEW_MODE_GLOBAL:
                $this->tabs->addSubTabTarget(
                    'auth_saml_idps',
                    $this->ctrl->getLinkTarget($this, self::DEFAULT_CMD),
                    array_merge(self::GLOBAL_ENTITY_COMMANDS, [self::DEFAULT_CMD, 'showNewIdpForm', self::CMD_SAVE_NEW_IDP]),
                    self::class
                );

                $this->tabs->addSubTabTarget(
                    'settings',
                    $this->ctrl->getLinkTarget($this, 'showSettings'),
                    ['showSettings', self::CMD_SAVE_SETTINGS],
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
                    $this->ctrl->getLinkTarget($this, self::CMD_SHOW_IDP_SETTINGS),
                    [self::CMD_SHOW_IDP_SETTINGS, self::CMT_SAVE_IDP_SETTINGS],
                    self::class
                );

                $this->tabs->addSubTabTarget(
                    self::LNG_AUTH_SAML_USER_MAPPING,
                    $this->ctrl->getLinkTarget($this, 'showUserAttributeMappingForm'),
                    ['showUserAttributeMappingForm', self::CMD_SAVE_USER_ATTRIBUTE_MAPPING],
                    self::class
                );
                break;
        }
    }

    private function initUserAttributeMapping(): void
    {
        $this->mapping = new ilExternalAuthUserAttributeMapping('saml', $this->idp->getIdpId());
    }

    private function getUserAttributeMappingForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_SAVE_USER_ATTRIBUTE_MAPPING));
        $form->setTitle($this->lng->txt(self::LNG_AUTH_SAML_USER_MAPPING));

        $usr_profile = new ilUserProfile();
        foreach (array_keys($usr_profile->getStandardFields()) as $id) {
            if (in_array($id, self::IGNORED_USER_FIELDS, true)) {
                continue;
            }

            $this->addAttributeRuleFieldToForm($form, $this->lng->txt($id), $id);
        }

        foreach (ilUserDefinedFields::_getInstance()->getDefinitions() as $definition) {
            $this->addAttributeRuleFieldToForm($form, $definition['field_name'], 'udf_' . $definition['field_id']);
        }

        if (!$this->access->checkAccess(self::PERMISSION_WRITE, '', $this->ref_id)) {
            foreach ($form->getItems() as $item) {
                $item->setDisabled(true);
            }
        } else {
            $form->addCommandButton(self::CMD_SAVE_USER_ATTRIBUTE_MAPPING, $this->lng->txt(self::CMD_SAVE));
        }

        return $form;
    }

    private function addAttributeRuleFieldToForm(
        ilPropertyFormGUI $form,
        string $field_label,
        string $field_name
    ): void {
        $field = new ilTextInputGUI($field_label, $field_name);
        $form->addItem($field);

        $update_automatically = new ilCheckboxInputGUI('', $field_name . self::PROP_UPDATE_SUFFIX);
        $update_automatically->setOptionTitle($this->lng->txt('auth_saml_update_field_info'));
        $update_automatically->setValue('1');
        $form->addItem($update_automatically);
    }

    private function saveUserAttributeMapping(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getUserAttributeMappingForm();
        if ($form->checkInput()) {
            $this->mapping->delete();

            $usr_profile = new ilUserProfile();
            foreach (array_keys($usr_profile->getStandardFields()) as $id) {
                if (in_array($id, self::IGNORED_USER_FIELDS, true)) {
                    continue;
                }

                $rule = $this->mapping->getEmptyRule();
                $rule->setAttribute($id);
                $rule->setExternalAttribute((string) $form->getInput($rule->getAttribute()));
                $rule->updateAutomatically((bool) $form->getInput($rule->getAttribute() . self::PROP_UPDATE_SUFFIX));
                $this->mapping[$rule->getAttribute()] = $rule;
            }

            foreach (ilUserDefinedFields::_getInstance()->getDefinitions() as $definition) {
                $rule = $this->mapping->getEmptyRule();
                $rule->setAttribute('udf_' . $definition['field_id']);
                $rule->setExternalAttribute((string) $form->getInput($rule->getAttribute()));
                $rule->updateAutomatically((bool) $form->getInput($rule->getAttribute() . self::PROP_UPDATE_SUFFIX));
                $this->mapping[$rule->getAttribute()] = $rule;
            }

            $this->mapping->save();

            $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY));
        }

        $form->setValuesByPost();

        $this->showUserAttributeMappingForm($form);
    }

    private function showUserAttributeMappingForm(ilPropertyFormGUI $form = null): void
    {
        $this->tabs->setSubTabActive(self::LNG_AUTH_SAML_USER_MAPPING);

        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getUserAttributeMappingForm();
            $data = array();
            foreach ($this->mapping as $rule) {
                $data[$rule->getAttribute()] = $rule->getExternalAttribute();
                $data[$rule->getAttribute() . self::PROP_UPDATE_SUFFIX] = $rule->isAutomaticallyUpdated();
            }
            $form->setValuesByArray($data);
        }

        $this->tpl->setContent($form->getHTML());
    }

    private function getSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_SAVE_SETTINGS));
        $form->setTitle($this->lng->txt('auth_saml_configure'));

        $show_login_form = new ilCheckboxInputGUI($this->lng->txt('auth_saml_login_form'), self::LNG_LOGIN_FORM);
        $show_login_form->setInfo($this->lng->txt('auth_saml_login_form_info'));
        $show_login_form->setValue('1');
        $form->addItem($show_login_form);

        if (!$this->access->checkAccess(self::PERMISSION_WRITE, '', $this->ref_id)) {
            foreach ($form->getItems() as $item) {
                $item->setDisabled(true);
            }
        } else {
            $form->addCommandButton(self::CMD_SAVE_SETTINGS, $this->lng->txt(self::CMD_SAVE));
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

    private function saveSettings(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getSettingsForm();
        if ($form->checkInput()) {
            ilSamlSettings::getInstance()->setLoginFormStatus((bool) $form->getInput(self::LNG_LOGIN_FORM));
            $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY));
        }

        $form->setValuesByPost();

        $this->showSettings($form);
    }

    private function showSettings(ilPropertyFormGUI $form = null): void
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getSettingsForm();
            $form->setValuesByArray([
                self::LNG_LOGIN_FORM => ilSamlSettings::getInstance()->isDisplayedOnLoginPage(),
            ]);
        }

        $this->tpl->setContent($form->getHTML());
    }

    private function getIdpSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMT_SAVE_IDP_SETTINGS));
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

        if (!$this->access->checkAccess(self::PERMISSION_WRITE, '', $this->ref_id)) {
            foreach ($form->getItems() as $item) {
                $item->setDisabled(true);
            }
        } else {
            $form->addCommandButton(self::CMT_SAVE_IDP_SETTINGS, $this->lng->txt(self::CMD_SAVE));
        }
        $form->addCommandButton(self::DEFAULT_CMD, $this->lng->txt(self::LNG_CANCEL));

        return $form;
    }

    private function showIdpSettings(ilPropertyFormGUI $form = null): void
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

    private function saveIdpSettings(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getIdpSettingsForm();
        if ($form->checkInput()) {
            $this->idp->bindForm($form);
            $this->idp->persist();
            $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY));

            $this->storeMetadata($this->idp, $form->getInput(self::METADATA_STORAGE_KEY));
        }

        $this->showIdpSettings($form);
    }

    private function getIdpForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_SAVE_NEW_IDP));
        $form->setTitle($this->lng->txt('auth_saml_add_idp_btn'));

        $this->addMetadataElement($form);

        $form->addCommandButton(self::CMD_SAVE_NEW_IDP, $this->lng->txt(self::CMD_SAVE));
        $form->addCommandButton('listIdps', $this->lng->txt(self::LNG_CANCEL));

        return $form;
    }

    private function saveNewIdp(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getIdpForm();
        if ($form->checkInput()) {
            $idp = new ilSamlIdp();
            $idp->bindForm($form);
            $idp->persist();

            $this->storeMetadata($idp, $form->getInput(self::METADATA_STORAGE_KEY));

            $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY), true);
            $this->ctrl->setParameter($this, self::REQUEST_PARAM_SAML_IDP_ID, $idp->getIdpId());
            $this->ctrl->redirect($this, self::CMD_SHOW_IDP_SETTINGS);
        }

        $this->showNewIdpForm($form);
    }

    private function showNewIdpForm(ilPropertyFormGUI $form = null): void
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

    private function addMetadataElement(ilPropertyFormGUI $form): void
    {
        $metadata = new ilSamlIdpMetadataInputGUI(
            $this->lng->txt('auth_saml_add_idp_md_label'),
            self::METADATA_STORAGE_KEY,
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

    private function populateWithMetadata(ilSamlIdp $idp, array &$data): void
    {
        $idpDisco = $this->samlAuth->getIdpDiscovery();

        $data[self::METADATA_STORAGE_KEY] = $idpDisco->fetchIdpMetadata($idp->getIdpId());
    }

    private function storeMetadata(ilSamlIdp $idp, string $metadata): void
    {
        $idpDisco = $this->samlAuth->getIdpDiscovery();
        $idpDisco->storeIdpMetadata($idp->getIdpId(), $metadata);
    }

    private function confirmDeleteIdp(): void
    {
        $this->ensureWriteAccess();

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'deleteIdp'));
        $confirmation->setConfirm($this->lng->txt('confirm'), 'deleteIdp');
        $confirmation->setCancel($this->lng->txt(self::LNG_CANCEL), self::DEFAULT_CMD);
        $confirmation->setHeaderText($this->lng->txt('auth_saml_sure_delete_idp'));
        $confirmation->addItem('saml_idp_ids', (string) $this->idp->getIdpId(), $this->idp->getEntityId());

        $this->tpl->setContent($confirmation->getHTML());
    }

    private function deleteIdp(): void
    {
        $this->ensureWriteAccess();

        $idpDisco = $this->samlAuth->getIdpDiscovery();
        $idpDisco->deleteIdpMetadata($this->idp->getIdpId());

        $this->idp->delete();

        $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt('auth_saml_deleted_idp'), true);

        $this->ctrl->setParameter($this, self::REQUEST_PARAM_SAML_IDP_ID, null);
        $this->ctrl->redirect($this, self::DEFAULT_CMD);
    }
}
