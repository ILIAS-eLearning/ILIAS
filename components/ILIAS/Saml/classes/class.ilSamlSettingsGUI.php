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

final class ilSamlSettingsGUI
{
    private const int VIEW_MODE_GLOBAL = 1;
    private const int VIEW_MODE_SINGLE = 2;

    public const string DEFAULT_CMD = 'listIdps';

    private const string PERMISSION_WRITE = 'write';

    private const string REQUEST_PARAM_SAML_IDP_ID = 'saml_idp_id';
    private const string REQUEST_PARAM_SAML_IDP_IDS = 'saml_idp_ids';

    private const string MESSAGE_TYPE_FAILURE = 'failure';
    private const string MESSAGE_TYPE_SUCCESS = 'success';

    private const string LNG_SAVED_SUCCESSFULLY = 'saved_successfully';
    private const string LNG_AUTH_SAML_USER_MAPPING = 'auth_saml_user_mapping';
    private const string LNG_LOGIN_FORM = 'login_form';
    private const string LNG_CANCEL = 'cancel';

    private const string CMD_SHOW_SETTINGS = 'showSettings';
    private const string CMD_SAVE_NEW_IDP = 'saveNewIdp';
    private const string CMD_SAVE_SETTINGS = 'saveSettings';
    private const string CMD_SHOW_IDP_SETTINGS = 'showIdpSettings';
    private const string CMT_SAVE_IDP_SETTINGS = 'saveIdpSettings';
    private const string CMD_SAVE = 'save';
    private const string CMD_SAVE_USER_ATTRIBUTE_MAPPING = 'saveUserAttributeMapping';

    private const string PROP_UPDATE_SUFFIX = '_update';

    private const string METADATA_STORAGE_KEY = 'metadata';

    /**
     * @var string[]
     */
    private const array GLOBAL_COMMANDS = [
        self::DEFAULT_CMD,
        'showAddIdpForm',
        self::CMD_SHOW_SETTINGS,
        self::CMD_SAVE_SETTINGS,
        'showNewIdpForm',
        'saveNewIdp',
    ];

    /**
     * @var string[]
     */
    private const array GLOBAL_ENTITY_COMMANDS = [
        'deactivateIdp',
        'activateIdp',
        'confirmDeleteIdp',
        'deleteIdp',
    ];

    /**
     * @var string[]
     */
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
    private readonly GlobalHttpState $httpState;
    private readonly Refinery $refinery;
    private readonly ilHelpGUI $help;
    private ?ilExternalAuthUserAttributeMapping $mapping = null;
    private ?ilSamlIdp $idp = null;
    private ?ilSamlAuth $samlAuth = null;
    private readonly \ILIAS\UI\Factory $ui_factory;
    private readonly \ILIAS\UI\Renderer $ui_renderer;

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
        $this->httpState = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

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
            $idpId = $this->httpState->wrapper()->query()->retrieve(
                self::REQUEST_PARAM_SAML_IDP_ID,
                $this->refinery->kindlyTo()->int()
            );
        } elseif ($this->httpState->wrapper()->post()->has(self::REQUEST_PARAM_SAML_IDP_ID)) {
            $idpId = $this->httpState->wrapper()->post()->retrieve(
                self::REQUEST_PARAM_SAML_IDP_ID,
                $this->refinery->kindlyTo()->int()
            );
        }

        if ($this->httpState->wrapper()->query()->has('saml_idps_table_action')) {
            if ($this->httpState->wrapper()->query()->has('saml_idps_idp_id')) {
                $idpIds = $this->httpState->wrapper()->query()->retrieve(
                    'saml_idps_idp_id',
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
                );
                if (count($idpIds) === 1) {
                    $idpId = current($idpIds);
                }
            }
        }

        if ($this->httpState->wrapper()->post()->has(self::REQUEST_PARAM_SAML_IDP_IDS)) {
            $idpIds = $this->httpState->wrapper()->post()->retrieve(
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
            $this->samlAuth = $factory->auth();
        } catch (Throwable $e) {
            if ('Database error: could not find driver' === $e->getMessage()) {
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
        if ($cmd === null || $cmd === '' || !method_exists($this, $cmd)) {
            $cmd = self::DEFAULT_CMD;
        }
        $ipdId = $this->getIdpIdOrZero();
        if ($ipdId > 0) {
            $this->ctrl->setParameter($this, self::REQUEST_PARAM_SAML_IDP_ID, $ipdId);
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
            $this->toolbar->addStickyItem(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('auth_saml_add_idp_btn'),
                    $this->ctrl->getLinkTarget($this, 'showNewIdpForm')
                )
            );
        }

        $federationMdUrl = rtrim(
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
                $this->ui_renderer->render($this->ui_factory->link()->standard($federationMdUrl, $federationMdUrl))
            )
        );

        $table = new ilSamlIdpTableGUI(
            $this,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->ctrl,
            $this->httpState->request(),
            new Factory(),
            'handleTableActions',
            $this->rbac->system()->checkAccess(self::PERMISSION_WRITE, $this->ref_id)
        );
        $this->tpl->setContent($this->ui_renderer->render([$info, $table->get()]));
    }

    private function handleTableActions(): void
    {
        $action = $this->httpState->wrapper()->query()->retrieve(
            'saml_idps_table_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );
        match ($action) {
            'showIdpSettings' => $this->showIdpSettings(),
            'activateIdp' => $this->activateIdp(),
            'deactivateIdp' => $this->deactivateIdp(),
            'confirmDeleteIdp' => $this->confirmDeleteIdp(),
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
                        [self::DEFAULT_CMD, 'showNewIdpForm', self::CMD_SAVE_NEW_IDP]
                    ),
                    self::class
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

        if (!$this->rbac->system()->checkAccess(self::PERMISSION_WRITE, $this->ref_id)) {
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

    private function showUserAttributeMappingForm(?ilPropertyFormGUI $form = null): void
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
                self::LNG_LOGIN_FORM => $this->ui_factory->input()->field()->checkbox(
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
            'intval',
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

    private function saveSettings(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getSettingsForm()->withRequest($this->httpState->request());
        if (!$form->getError()) {
            $data = $form->getData();
            ilSamlSettings::getInstance()->setLoginFormStatus($data[self::LNG_LOGIN_FORM]);
            $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY));
        }

        $this->showSettings($form);
    }

    private function showSettings(?StandardForm $form = null): void
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

    private function getIdpSettingsForm(array $values = []): StandardForm
    {
        $ui_field = $this->ui_factory->input()->field();

        /** @var FormInput[] $inputs */
        $inputs = [
            $ui_field->text(
                $this->lng->txt('auth_saml_idp')
            )->withValue($values['entity_id'] ?? "")->withDisabled(true),
            self::METADATA_STORAGE_KEY => $ui_field->textarea(
                $this->lng->txt('auth_saml_add_idp_md_label'),
                $this->lng->txt('auth_saml_add_idp_md_info')
            )
                ->withoutStripTags()
                ->withValue($values[self::METADATA_STORAGE_KEY] ?? "")
                ->withRequired(true)
                ->withDedicatedName(self::METADATA_STORAGE_KEY),
            'allow_local_auth' => $ui_field->checkbox(
                $this->lng->txt('auth_allow_local'),
                $this->lng->txt('auth_allow_local_info')
            )->withValue((bool) ($values['allow_local_auth'] ?? true)),
            'uid_claim' => $ui_field->text(
                $this->lng->txt('auth_saml_uid_claim'),
                $this->lng->txt('auth_saml_uid_claim_info')
            )->withValue($values['uid_claim'] ?? "")->withRequired(true),
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
                    'login_claim' => $values['login_claim'] ?? "",
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
            $this->ctrl->getFormAction($this, self::CMT_SAVE_IDP_SETTINGS),
            $inputs
        );
    }

    private function showIdpSettings(?StandardForm $form = null): void
    {
        $this->tabs->setSubTabActive('auth_saml_idp_settings');

        if (!$form) {

            $data = $this->idp->toArray();
            $this->populateWithMetadata($this->idp, $data);
            $form = $this->getIdpSettingsForm($data);
        }

        $this->help->setSubScreenId('edit_idp');

        $title = $this->ui_factory->item()->standard(sprintf($this->lng->txt('auth_saml_configure_idp'), $this->idp->getEntityId()));
        $this->tpl->setContent($this->ui_renderer->render([
            $title,
            $form
        ]));
    }

    private function saveIdpSettings(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getIdpSettingsForm()->withRequest($this->httpState->request());

        if (!$form->getError()) {
            $this->idp->bindForm($form);
            $this->idp->persist();

            $this->storeMetadata($this->idp, $form->getData()[self::METADATA_STORAGE_KEY] ?? '');
            $this->tpl->setOnScreenMessage(self::MESSAGE_TYPE_SUCCESS, $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_IDP_SETTINGS);
        $this->showIdpSettings($form);
    }

    private function getIdpForm(): StandardForm
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, self::CMD_SAVE_NEW_IDP),
            [
                self::METADATA_STORAGE_KEY => $this->ui_factory->input()->field()->textarea(
                    $this->lng->txt('auth_saml_add_idp_md_label'),
                    $this->lng->txt('auth_saml_add_idp_md_info')
                )
                    ->withValue($values[self::METADATA_STORAGE_KEY] ?? "")
                    ->withRequired(true)
                    ->withDedicatedName(self::METADATA_STORAGE_KEY),
            ]
        );
    }

    private function saveNewIdp(): void
    {
        $this->ensureWriteAccess();

        $form = $this->getIdpForm();
        if (!$form->getError()) {
            $idp = new ilSamlIdp();
            $idp->bindForm($form);
            $idp->persist();
            $this->storeMetadata($this->idp, $form->getData()[self::METADATA_STORAGE_KEY] ?? '');

            $this->tpl->setOnScreenMessage(
                self::MESSAGE_TYPE_SUCCESS,
                $this->lng->txt(self::LNG_SAVED_SUCCESSFULLY),
                true
            );
            $this->ctrl->setParameter($this, self::REQUEST_PARAM_SAML_IDP_ID, $idp->getIdpId());
            $this->ctrl->redirect($this, self::CMD_SHOW_IDP_SETTINGS);
        }

        $this->showNewIdpForm($form);
    }

    private function showNewIdpForm(?StandardForm $form = null): void
    {
        $this->ensureWriteAccess();

        if (null === $form) {
            $form = $this->getIdpForm();
        } else {
            $form = $form->withRequest($this->httpState->request());
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
        $confirmation->addItem(self::REQUEST_PARAM_SAML_IDP_IDS, (string) $this->idp->getIdpId(), $this->idp->getEntityId());

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
