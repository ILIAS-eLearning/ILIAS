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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\DI\RBACServices;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Data\Factory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\User\Profile\Profile;

final class ilSamlSettingsGUI implements ilCtrlSecurityInterface, ilSamlCommands
{
    private const int VIEW_MODE_GLOBAL = 1;
    private const int VIEW_MODE_SINGLE = 2;

    public const string DEFAULT_CMD = self::CMD_LIST_IDPS;

    private const string PERMISSION_WRITE = 'write';

    private const string REQUEST_PARAM_SAML_IDP_ID = 'saml_idp_id';
    private const string REQUEST_PARAM_SAML_IDP_IDS = 'saml_idp_ids';

    private const string MESSAGE_TYPE_FAILURE = 'failure';
    private const string MESSAGE_TYPE_SUCCESS = 'success';

    private const string LNG_SAVED_SUCCESSFULLY = 'saved_successfully';
    private const string LNG_AUTH_SAML_USER_MAPPING = 'auth_saml_user_mapping';
    private const string LNG_LOGIN_FORM = 'login_form';
    private const string LNG_CANCEL = 'cancel';
    private const string LNG_SAVE = 'save';

    private const string PROP_UPDATE_SUFFIX = '_update';

    private const string METADATA_STORAGE_KEY = 'metadata';
    private const string METADATA_ENTITY_ID = 'entity_id';

    /** @var list<string> */
    private const array IGNORED_USER_FIELDS = [
        'mail_incoming_mail',
        'preferences',
        'hide_own_online_status',
        'show_users_online',
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

    private readonly ilCtrlInterface $ctrl;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly RBACServices $rbac;
    private readonly ilErrorHandling $error_handler;
    private readonly ilTabsGUI $tabs;
    private readonly ilToolbarGUI $toolbar;
    private readonly GlobalHttpState $http_state;
    private readonly Refinery $refinery;
    private readonly ilHelpGUI $help;
    private ?ilExternalAuthUserAttributeMapping $mapping = null;
    private ?ilSamlIdp $idp = null;
    private ?ilSamlAuth $saml_auth = null;
    private readonly \ILIAS\UI\Factory $ui_factory;
    private readonly \ILIAS\UI\Renderer $ui_renderer;
    private readonly Profile $profile;

    public function __construct(private readonly int $ref_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->rbac = $DIC->rbac();
        $this->error_handler = $DIC['ilErr'];
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC['ilToolbar'];
        $this->help = $DIC['ilHelp'];
        $this->http_state = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->profile = $DIC['user']->getProfile();

        $this->lng->loadLanguageModule('auth');
    }

    private function ensureAccess(string $operation): void
    {
        if (!$this->rbac->system()->checkAccess($operation, $this->ref_id)) {
            $this->error_handler->raiseError($this->lng->txt('msg_no_perm_read'), $this->error_handler->WARNING);
        }
    }

    public function getUnsafeGetCommands(): array
    {
        return [
            self::CMD_TABLE_ACTIONS,
        ];
    }

    public function getSafePostCommands(): array
    {
        return [];
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
        if ($this->http_state->wrapper()->query()->has(self::REQUEST_PARAM_SAML_IDP_ID)) {
            $idpId = $this->http_state->wrapper()->query()->retrieve(
                self::REQUEST_PARAM_SAML_IDP_ID,
                $this->refinery->kindlyTo()->int()
            );
        } elseif ($this->http_state->wrapper()->post()->has(self::REQUEST_PARAM_SAML_IDP_ID)) {
            $idpId = $this->http_state->wrapper()->post()->retrieve(
                self::REQUEST_PARAM_SAML_IDP_ID,
                $this->refinery->kindlyTo()->int()
            );
        }

        if ($this->getTableAction() && $this->http_state->wrapper()->query()->has('saml_idps_idp_id')) {
            $idpIds = $this->http_state->wrapper()->query()->retrieve(
                'saml_idps_idp_id',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
            if (count($idpIds) === 1) {
                $idpId = current($idpIds);
            }
        }

        if ($this->http_state->wrapper()->post()->has(self::REQUEST_PARAM_SAML_IDP_IDS)) {
            $idpIds = $this->http_state->wrapper()->post()->retrieve(
                self::REQUEST_PARAM_SAML_IDP_IDS,
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
            if (count($idpIds) === 1) {
                $idpId = current($idpIds);
            }
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
            $this->saml_auth = $factory->auth();
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Database error: could not find driver') {
                $this->tpl->setOnScreenMessage(
                    self::MESSAGE_TYPE_FAILURE,
                    $this->lng->txt('auth_saml_err_sqlite_driver')
                );
            } else {
                $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_FAILURE, $e->getMessage());
            }
        }

        $this->help->setScreenIdComponent('auth');

        $cmd = $this->ctrl->getCmd();
        if ($cmd === null || $cmd === '' || !method_exists($this, $cmd . 'Command')) {
            $cmd = self::DEFAULT_CMD;
        }
        $verified_command = $cmd . 'Command';

        $idp_id = $this->getIdpIdOrZero();

        if ($idp_id > 0) {
            $this->ctrl->setParameter($this, self::REQUEST_PARAM_SAML_IDP_ID, $idp_id);
        }

        if (!in_array(strtolower($cmd), array_map('strtolower', self::GLOBAL_COMMANDS), true)) {
            if ($idp_id === 0) {
                $this->ctrl->redirect($this, self::DEFAULT_CMD);
            }

            $this->initIdp();
            $this->initUserAttributeMapping();
        }

        if ($this->shouldRenderGlobalCommandSubTabs($cmd)) {
            $this->setSubTabs(self::VIEW_MODE_GLOBAL);
        } else {
            $this->setSubTabs(self::VIEW_MODE_SINGLE);
        }

        $this->$verified_command();
    }

    private function shouldRenderGlobalCommandSubTabs(string $cmd): bool
    {
        $is_global_command = in_array(strtolower($cmd), array_map('strtolower', self::GLOBAL_COMMANDS), true);
        $is_global_entity_command = in_array(
            strtolower($cmd),
            array_map('strtolower', self::GLOBAL_ENTITY_COMMANDS),
            true
        );

        $is_global_table_action = in_array(
            strtolower($this->getTableAction() ?? ''),
            array_map('strtolower', self::GLOBAL_ENTITY_TABLE_ACTIONS),
            true
        );

        return $is_global_command || $is_global_entity_command || $is_global_table_action;
    }

    private function listIdpsCommand(): void
    {
        if ($this->saml_auth && $this->rbac->system()->checkAccess(self::PERMISSION_WRITE, $this->ref_id)) {
            $this->toolbar->addStickyItem(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('auth_saml_add_idp_btn'),
                    $this->ctrl->getLinkTarget($this, self::CMD_SHOW_NEW_IDP_FORM)
                )
            );
        }

        $federation_md_url = rtrim(
            ILIAS_HTTP_PATH,
            '/'
        ) . '/metadata.php?client_id=' . CLIENT_ID;
        $info = $this->ui_factory->messageBox()->info(
            sprintf(
                $this->lng->txt('auth_saml_idps_info'),
                'auth/saml/config/config.php',
                'auth/saml/config/authsources.php',
                $this->ui_renderer->render(
                    $this->ui_factory->link()->standard(
                        'https://simplesamlphp.org/docs/stable/simplesamlphp-sp',
                        'https://simplesamlphp.org/docs/stable/simplesamlphp-sp'
                    )
                ),
                $this->ui_renderer->render($this->ui_factory->link()->standard($federation_md_url, $federation_md_url))
            )
        );

        $table = new ilSamlIdpTableGUI(
            $this,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->ctrl,
            $this->http_state->request(),
            new Factory(),
            self::CMD_TABLE_ACTIONS,
            $this->rbac->system()->checkAccess(self::PERMISSION_WRITE, $this->ref_id)
        );
        $this->tpl->setContent($this->ui_renderer->render([$info, $table->get()]));
    }

    private function getTableAction(): ?string
    {
        return $this->http_state->wrapper()->query()->retrieve(
            'saml_idps_table_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(null)
            ])
        );
    }

    private function handleTableActionsCommand(): void
    {
        match ($this->getTableAction()) {
            self::TABLE_ACTION_SHOW_IDP_SETTINGS => $this->ctrl->redirect($this, self::CMD_SHOW_IDP_SETTINGS),
            self::TABLE_ACTION_ACTIVATE_IDP => $this->activateIdp(),
            self::TABLE_ACTION_DEACTIVATE_IDP => $this->deactivateIdp(),
            self::TABLE_ACTION_CONFIRM_DELETE_IDP => $this->confirmDeleteIdp(),
            default => $this->ctrl->redirect($this, self::DEFAULT_CMD),
        };
    }

    private function deactivateIdp(): void
    {
        $this->ensureWriteAccess();

        $this->idp->setActive(false);
        $this->idp->persist();

        $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY), true);
        $this->ctrl->redirect($this, self::DEFAULT_CMD);
    }

    private function activateIdp(): void
    {
        $this->ensureWriteAccess();

        $this->idp->setActive(true);
        $this->idp->persist();

        $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY), true);
        $this->ctrl->redirect($this, self::DEFAULT_CMD);
    }

    private function setSubTabs(int $a_view_mode): void
    {
        switch ($a_view_mode) {
            case self::VIEW_MODE_GLOBAL:
                $this->tabs->addSubTabTarget(
                    'auth_saml_idps',
                    $this->ctrl->getLinkTarget($this, self::DEFAULT_CMD),
                    array_merge(
                        self::GLOBAL_ENTITY_COMMANDS,
                        [self::DEFAULT_CMD, self::CMD_SHOW_NEW_IDP_FORM, self::CMD_SAVE_NEW_IDP]
                    ),
                    self::class,
                    '',
                    ($this->getTableAction() === self::TABLE_ACTION_CONFIRM_DELETE_IDP)
                );

                $this->tabs->addSubTabTarget(
                    'settings',
                    $this->ctrl->getLinkTarget($this, self::CMD_SHOW_SETTINGS),
                    [self::CMD_SHOW_SETTINGS, self::CMD_SAVE_SETTINGS],
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
                    [self::CMD_SHOW_IDP_SETTINGS, self::CMD_SAVE_IDP_SETTINGS],
                    self::class
                );

                $this->tabs->addSubTabTarget(
                    self::LNG_AUTH_SAML_USER_MAPPING,
                    $this->ctrl->getLinkTarget($this, self::CMD_SHOW_USER_ATTRIBUTE_MAPPING_FORM),
                    [self::CMD_SHOW_USER_ATTRIBUTE_MAPPING_FORM, self::CMD_SAVE_USER_ATTRIBUTE_MAPPING],
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

        foreach ($this->profile->getFields() as $field) {
            if (in_array($field->getIdentifier(), self::IGNORED_USER_FIELDS, true)) {
                continue;
            }

            $id = $field->getIdentifier();
            if ($field->isCustom()) {
                $id = "udf_{$id}";
            }

            $this->addAttributeRuleFieldToForm($form, $field->getLabel($this->lng), $id);
        }

        if ($this->rbac->system()->checkAccess(self::PERMISSION_WRITE, $this->ref_id)) {
            $form->addCommandButton(self::CMD_SAVE_USER_ATTRIBUTE_MAPPING, $this->lng->txt(self::LNG_SAVE));
        } else {
            foreach ($form->getItems() as $item) {
                $item->setDisabled(true);
            }
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

    private function saveUserAttributeMappingCommand(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getUserAttributeMappingForm();
        if ($form->checkInput()) {
            $this->mapping->delete();

            foreach ($this->profile->getFields() as $field) {
                if (in_array($field->getIdentifier(), self::IGNORED_USER_FIELDS, true)) {
                    continue;
                }

                $id = $field->getIdentifier();
                if ($field->isCustom()) {
                    $id = "udf_{$id}";
                }

                $rule = $this->mapping->getEmptyRule();
                $rule->setAttribute($id);
                $rule->setExternalAttribute((string) $form->getInput($rule->getAttribute()));
                $rule->updateAutomatically((bool) $form->getInput($rule->getAttribute() . self::PROP_UPDATE_SUFFIX));
                $this->mapping[$rule->getAttribute()] = $rule;
            }

            $this->mapping->save();

            $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY));
        }

        $form->setValuesByPost();

        $this->showUserAttributeMappingFormCommand($form);
    }

    private function showUserAttributeMappingFormCommand(?ilPropertyFormGUI $form = null): void
    {
        $this->tabs->setSubTabActive(self::LNG_AUTH_SAML_USER_MAPPING);

        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getUserAttributeMappingForm();
            $data = [];
            foreach ($this->mapping as $rule) {
                $data[$rule->getAttribute()] = $rule->getExternalAttribute();
                $data[$rule->getAttribute() . self::PROP_UPDATE_SUFFIX] = $rule->isAutomaticallyUpdated();
            }
            $form->setValuesByArray($data);
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @param array<string, mixed> $values
     */
    private function getSettingsForm(array $values = []): StandardForm
    {
        $access = $this->rbac->system()->checkAccess(self::PERMISSION_WRITE, $this->ref_id);
        $form = $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, $access ? self::CMD_SAVE_SETTINGS : self::CMD_SHOW_SETTINGS),
            [
                self::LNG_LOGIN_FORM => $this->ui_factory
                    ->input()
                    ->field()
                    ->checkbox(
                        $this->lng->txt('auth_saml_login_form'),
                        $this->lng->txt('auth_saml_login_form_info')
                    )
                    ->withValue((bool) ($values[self::LNG_LOGIN_FORM] ?? true))
                    ->withDisabled(!$access),
            ]
        );

        if (!$access) {
            $form = $form->withSubmitLabel($this->lng->txt('refresh'));
        }

        return $form;
    }

    /**
     * @return array<int, string>
     */
    private function prepareRoleSelection(): array
    {
        $select = [];
        $global_roles = array_map(
            intval(...),
            ilUtil::_sortIds(
                $this->rbac->review()->getGlobalRoles(),
                'object_data',
                'title',
                'obj_id'
            )
        );

        $select[0] = $this->lng->txt('links_select_one');
        foreach ($global_roles as $role_id) {
            $select[$role_id] = ilObject::_lookupTitle($role_id);
        }

        return $select;
    }

    private function saveSettingsCommand(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getSettingsForm()->withRequest($this->http_state->request());
        if (!$form->getError()) {
            $data = $form->getData();
            ilSamlSettings::getInstance()->setLoginFormStatus($data[self::LNG_LOGIN_FORM]);
            $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY));
        }

        $this->showSettingsCommand($form);
    }

    private function showSettingsCommand(?StandardForm $form = null): void
    {
        if (!$form) {
            $form = $this->getSettingsForm([
                self::LNG_LOGIN_FORM => ilSamlSettings::getInstance()->isDisplayedOnLoginPage()
            ]);
        }

        $title = $this->ui_factory->item()->standard($this->lng->txt('auth_saml_configure'));
        $this->tpl->setContent($this->ui_renderer->render([
            $title,
            $form
        ]));
    }

    private function metadataValidationConstraint(
        ?string &$medadata_entity_id = null
    ): \ILIAS\Refinery\Constraint {
        $xml_parser_error = null;

        return $this->refinery->custom()->constraint(
            function (string $value) use (&$xml_parser_error, &$medadata_entity_id): bool {
                try {
                    $parser = new ilSamlIdpXmlMetadataParser(
                        new Factory(),
                        new ilSamlIdpXmlMetadataErrorFormatter()
                    );

                    $result = $parser->parse($value);
                    if ($result->isError()) {
                        $xml_parser_error = $result->error();
                        return false;
                    }

                    $medadata_entity_id = $result->value();

                    return true;
                } catch (Throwable) {
                    $xml_parser_error = $this->lng->txt('auth_saml_add_idp_md_error');
                    return false;
                }
            },
            implode(' ', [$this->lng->txt('auth_saml_add_idp_md_error'), $xml_parser_error])
        );
    }

    private function getIdpSettingsForm(array $values = []): StandardForm
    {
        $ui_field = $this->ui_factory->input()->field();
        $metadata_entity_id = $values[self::METADATA_ENTITY_ID] ?? null;

        /** @var list<FormInput> $inputs */
        $inputs = [
            $ui_field->text(
                $this->lng->txt('auth_saml_idp')
            )->withValue($values[self::METADATA_ENTITY_ID] ?? '')->withDisabled(true),
            self::METADATA_STORAGE_KEY => $ui_field
                ->textarea(
                    $this->lng->txt('auth_saml_add_idp_md_label'),
                    $this->lng->txt('auth_saml_add_idp_md_info')
                )
                ->withValue($values[self::METADATA_STORAGE_KEY] ?? '')
                ->withRequired(true)
                ->withoutStripTags()
                ->withDedicatedName(self::METADATA_STORAGE_KEY)
                ->withAdditionalTransformation($this->metadataValidationConstraint($metadata_entity_id)),
            'allow_local_auth' => $ui_field->checkbox(
                $this->lng->txt('auth_allow_local'),
                $this->lng->txt('auth_allow_local_info')
            )->withValue((bool) ($values['allow_local_auth'] ?? true)),
            'uid_claim' => $ui_field->text(
                $this->lng->txt('auth_saml_uid_claim'),
                $this->lng->txt('auth_saml_uid_claim_info')
            )->withValue($values['uid_claim'] ?? '')->withRequired(true),
            'sync_status' => $ui_field->optionalGroup(
                [
                    'login_claim' => $ui_field->text(
                        $this->lng->txt('auth_saml_username_claim'),
                        $this->lng->txt('auth_saml_username_claim_info')
                    )->withRequired(true),
                    'default_role_id' => $ui_field->select(
                        $this->lng->txt('auth_saml_role_select'),
                        $this->prepareRoleSelection()
                    )->withRequired(true),
                    'account_migr_status' => $ui_field->checkbox(
                        $this->lng->txt('auth_saml_migration'),
                        $this->lng->txt('auth_saml_migration_info')
                    )
                ],
                $this->lng->txt('auth_saml_sync'),
                $this->lng->txt('auth_saml_sync_info')
            )->withValue(
                (isset($values['sync_status']) && $values['sync_status'])
                    ? [
                    'login_claim' => $values['login_claim'] ?? '',
                    'default_role_id' => $values['default_role_id'] ?? array_key_first($this->prepareRoleSelection()),
                    'account_migr_status' => (bool) ($values['account_migr_status'] ?? true)
                ]
                    : null
            )
        ];

        $write_access = $this->rbac->system()->checkAccess(self::PERMISSION_WRITE, $this->ref_id);
        $inputs = array_map(static function (FormInput $input) use ($write_access) {
            if (!$write_access) {
                $input = $input->withDisabled(true);
            }

            return $input;
        }, $inputs);

        $this->ctrl->setParameter($this, self::REQUEST_PARAM_SAML_IDP_ID, $this->idp->getIdpId());

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, self::CMD_SAVE_IDP_SETTINGS),
            $inputs
        );
    }

    private function showIdpSettingsCommand(?StandardForm $form = null): void
    {
        $this->tabs->setSubTabActive('auth_saml_idp_settings');

        if (!$form) {
            $data = $this->idp->toArray();
            $this->populateWithMetadata($this->idp, $data);
            $form = $this->getIdpSettingsForm($data);
        }

        $this->help->setSubScreenId('edit_idp');

        $title = $this->ui_factory->item()->standard(
            sprintf($this->lng->txt('auth_saml_configure_idp'), $this->idp->getEntityId())
        );

        $this->tpl->setContent($this->ui_renderer->render([
            $title,
            $form
        ]));
    }

    private function saveIdpSettingsCommand(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getIdpSettingsForm()->withRequest($this->http_state->request());

        if (!$form->getError()) {
            $this->idp->bindForm($form);
            $this->idp->persist();

            $this->storeMetadata($this->idp, $form->getData()[self::METADATA_STORAGE_KEY] ?? '');
            $this->tpl->setOnScreenMessage(
                self::MESSAGE_TYPE_SUCCESS,
                $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY),
                true
            );
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_IDP_SETTINGS);
    }

    private function getIdpForm(): StandardForm
    {
        $medadata_entity_id = '';

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, self::CMD_SAVE_NEW_IDP),
            [
                self::METADATA_STORAGE_KEY => $this->ui_factory
                    ->input()
                    ->field()
                    ->textarea(
                        $this->lng->txt('auth_saml_add_idp_md_label'),
                        $this->lng->txt('auth_saml_add_idp_md_info')
                    )
                    ->withValue($values[self::METADATA_STORAGE_KEY] ?? '')
                    ->withRequired(true)
                    ->withoutStripTags()
                    ->withDedicatedName(self::METADATA_STORAGE_KEY)
                    ->withAdditionalTransformation($this->metadataValidationConstraint($medadata_entity_id)),
                self::METADATA_ENTITY_ID => $this->ui_factory
                    ->input()
                    ->field()
                    ->hidden()
                    ->withAdditionalTransformation(
                        $this->refinery->custom()->transformation(
                            static function (string $value) use (&$medadata_entity_id): string {
                                $sanitized_value = ilUtil::stripSlashes(trim($medadata_entity_id));
                                if ($sanitized_value !== $medadata_entity_id) {
                                    $sanitized_value = ilUtil::stripSlashes(str_replace('<', '< ', $medadata_entity_id));
                                }

                                return trim($sanitized_value);
                            }
                        )
                    )
            ]
        );
    }

    private function saveNewIdpCommand(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getIdpForm()->withRequest($this->http_state->request());
        if (!$form->getError()) {
            $idp = new ilSamlIdp();
            $idp->bindForm($form);
            $idp->persist();
            $this->storeMetadata($idp, $form->getData()[self::METADATA_STORAGE_KEY] ?? '');

            $this->tpl->setOnScreenMessage(
                self::MESSAGE_TYPE_SUCCESS,
                $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY),
                true
            );
            $this->ctrl->setParameter($this, self::REQUEST_PARAM_SAML_IDP_ID, $idp->getIdpId());
            $this->ctrl->redirect($this, self::TABLE_ACTION_SHOW_IDP_SETTINGS);
        }

        $this->showNewIdpFormCommand($form);
    }

    private function showNewIdpFormCommand(?StandardForm $form = null): void
    {
        $this->ensureWriteAccess();

        if ($form === null) {
            $form = $this->getIdpForm();
        } else {
            $form = $form->withRequest($this->http_state->request());
        }

        $this->help->setSubScreenId('create_idp');

        $title = $this->ui_factory->item()->standard($this->lng->txt('auth_saml_add_idp_btn'));
        $this->tpl->setContent($this->ui_renderer->render([
            $title,
            $form
        ]));
    }

    private function populateWithMetadata(ilSamlIdp $idp, array &$data): void
    {
        $idp_disco = $this->saml_auth->getIdpDiscovery();

        $data[self::METADATA_STORAGE_KEY] = $idp_disco->fetchIdpMetadata($idp->getIdpId());
    }

    private function storeMetadata(ilSamlIdp $idp, string $metadata): void
    {
        $idp_disco = $this->saml_auth->getIdpDiscovery();
        $idp_disco->storeIdpMetadata($idp->getIdpId(), $metadata);
    }

    private function confirmDeleteIdp(): void
    {
        $this->ensureWriteAccess();

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, self::CMD_DELETE_IDP));
        $confirmation->setConfirm($this->lng->txt('confirm'), self::CMD_DELETE_IDP);
        $confirmation->setCancel($this->lng->txt(self::LNG_CANCEL), self::DEFAULT_CMD);
        $confirmation->setHeaderText($this->lng->txt('auth_saml_sure_delete_idp'));
        $confirmation->addItem(
            self::REQUEST_PARAM_SAML_IDP_IDS,
            (string) $this->idp->getIdpId(),
            $this->idp->getEntityId()
        );

        $this->tpl->setContent($confirmation->getHTML());
    }

    private function deleteIdpCommand(): void
    {
        $this->ensureWriteAccess();

        $idp_disco = $this->saml_auth->getIdpDiscovery();
        $idp_disco->deleteIdpMetadata($this->idp->getIdpId());

        $this->idp->delete();

        $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt('auth_saml_deleted_idp'), true);

        $this->ctrl->setParameter($this, self::REQUEST_PARAM_SAML_IDP_ID, null);
        $this->ctrl->redirect($this, self::DEFAULT_CMD);
    }
}
