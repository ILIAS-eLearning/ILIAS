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

use ILIAS\FileUpload\FileUpload;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\HTTP\Services;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilOpenIdConnectSettingsGUI
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilOpenIdConnectSettingsGUI
{
    private const STAB_SETTINGS = 'settings';
    private const STAB_SCOPES = 'scopes';
    private const STAB_PROFILE = 'profile';
    private const STAB_ROLES = 'roles';
    private const VALUE_STRING = '_value';
    private const UPDATE_STRING = '_update';
    private const UDF_STRING = 'udf_';
    private const DEFAULT_CMD = 'settings';
    private const DEFAULT_VALUES = 1;
    private const SAVED_VALUES = 2;
    private const POST_VALUE = 'Mode';
    private int $ref_id;
    /** @var array $body */
    private $body;
    private readonly ilOpenIdConnectSettings $settings;
    private readonly ilLanguage $lng;
    private readonly ilCtrl $ctrl;
    private readonly ilLogger $logger;
    private readonly ilAccessHandler $access;
    private readonly ilRbacReview $review;
    private readonly ilErrorHandling $error;
    private readonly ilGlobalTemplateInterface $mainTemplate;
    private readonly ilTabsGUI $tabs;
    private readonly FileUpload $upload;

    private ilToolbarGUI $toolbar;
    private ?ilUserDefinedFields $udf = null;
    private ilGlobalTemplateInterface $tpl;
    private int $mapping_template = 2;
    private ServerRequestInterface $request;
    private ilOpenIdAttributeMappingTemplate $attribute_mapping_template;
    private Factory $ui;
    private Renderer $renderer;
    private Services $http;
    private Factory $factory;
    private \ILIAS\Refinery\Factory $refinery;

    private string $failed_validation_messages = '';

    public function __construct(int $a_ref_id)
    {
        global $DIC;

        $this->ref_id = $a_ref_id;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('auth');

        $this->mainTemplate = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->logger = $DIC->logger()->auth();

        $this->access = $DIC->access();
        $this->review = $DIC->rbac()->review();
        $this->error = $DIC['ilErr'];
        $this->upload = $DIC->upload();
        $this->body = $DIC->http()->request()->getParsedBody();
        $this->settings = ilOpenIdConnectSettings::getInstance();
        $http_wrapper = $DIC->http()->wrapper();
        $this->toolbar = $DIC->toolbar();
        $refinery = $DIC->refinery();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();
        $this->ui = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->factory = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->attribute_mapping_template = new ilOpenIdAttributeMappingTemplate();


        if ($http_wrapper->query()->has(self::POST_VALUE) && $http_wrapper->query()->retrieve(self::POST_VALUE, $refinery->kindlyTo()->int())) {
            $this->mapping_template = $http_wrapper->query()->retrieve(self::POST_VALUE, $refinery->kindlyTo()->int());
        }

    }

    protected function checkAccess(string $a_permission): void
    {
        if (!$this->checkAccessBool($a_permission)) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->WARNING);
        }
    }

    protected function checkAccessBool(string $a_permission): bool
    {
        return $this->access->checkAccess($a_permission, '', $this->ref_id);
    }

    public function executeCommand(): void
    {
        $this->checkAccess('read');

        switch ($this->ctrl->getNextClass()) {
            default:
                $cmd = $this->ctrl->getCmd(self::DEFAULT_CMD);
                $this->$cmd();
                break;
        }
    }

    protected function settings(ilPropertyFormGUI $form = null): void
    {
        $this->checkAccess('read');
        $this->setSubTabs(self::STAB_SETTINGS);

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSettingsForm();
        }

        $this->mainTemplate->setContent($form->getHTML());
    }

    protected function initSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('auth_oidc_settings_title'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        // activation
        $activation = new ilCheckboxInputGUI(
            $this->lng->txt('auth_oidc_settings_activation'),
            'activation'
        );
        $activation->setChecked($this->settings->getActive());
        $form->addItem($activation);

        // provider
        $provider = new ilTextInputGUI(
            $this->lng->txt('auth_oidc_settings_provider'),
            'provider'
        );
        $provider->setRequired(true);
        $provider->setValue($this->settings->getProvider());
        $form->addItem($provider);

        $client_id = new ilTextInputGUI(
            $this->lng->txt('auth_oidc_settings_client_id'),
            'client_id'
        );
        $client_id->setRequired(true);
        $client_id->setValue($this->settings->getClientId());
        $form->addItem($client_id);

        // secret
        $secret = new ilPasswordInputGUI(
            $this->lng->txt('auth_oidc_settings_secret'),
            'secret'
        );
        $secret->setSkipSyntaxCheck(true);
        $secret->setRetype(false);
        $secret->setRequired(false);
        if ($this->settings->getSecret() !== '') {
            $secret->setValue('******');
        }
        $form->addItem($secret);

        // login element
        $login_element = new ilRadioGroupInputGUI(
            $this->lng->txt('auth_oidc_settings_le'),
            'le'
        );
        $login_element->setRequired(true);
        $login_element->setValue((string) $this->settings->getLoginElementType());
        $form->addItem($login_element);

        // le -> type text
        $text_option = new ilRadioOption(
            $this->lng->txt('auth_oidc_settings_txt'),
            (string) ilOpenIdConnectSettings::LOGIN_ELEMENT_TYPE_TXT
        );
        $login_element->addOption($text_option);

        // le -> type text -> text
        $text = new ilTextInputGUI(
            '',
            'le_text'
        );
        $text->setValue($this->settings->getLoginElemenText());
        $text->setMaxLength(120);
        $text->setInfo($this->lng->txt('auth_oidc_settings_txt_val_info'));
        $text_option->addSubItem($text);

        // le -> type img
        $img_option = new ilRadioOption(
            $this->lng->txt('auth_oidc_settings_img'),
            (string) ilOpenIdConnectSettings::LOGIN_ELEMENT_TYPE_IMG
        );
        $login_element->addOption($img_option);

        $image = new ilImageFileInputGUI(
            '',
            'le_img'
        );
        $image->setAllowDeletion(false);

        if ($this->settings->hasImageFile()) {
            $image->setImage($this->settings->getImageFilePath());
        }
        $image->setInfo($this->lng->txt('auth_oidc_settings_img_file_info'));
        $img_option->addSubItem($image);

        // login options
        $login_options = new ilRadioGroupInputGUI(
            $this->lng->txt('auth_oidc_settings_login_options'),
            'login_prompt'
        );
        $login_options->setValue((string) $this->settings->getLoginPromptType());

        // enforce login
        $enforce = new ilRadioOption(
            $this->lng->txt('auth_oidc_settings_login_option_enforce'),
            (string) ilOpenIdConnectSettings::LOGIN_ENFORCE
        );
        $enforce->setInfo($this->lng->txt('auth_oidc_settings_login_option_enforce_info'));
        $login_options->addOption($enforce);

        // default login
        $default = new ilRadioOption(
            $this->lng->txt('auth_oidc_settings_login_option_default'),
            (string) ilOpenIdConnectSettings::LOGIN_STANDARD
        );
        $default->setInfo($this->lng->txt('auth_oidc_settings_login_option_default_info'));
        $login_options->addOption($default);

        $form->addItem($login_options);

        // logout scope
        $logout_scope = new ilRadioGroupInputGUI(
            $this->lng->txt('auth_oidc_settings_logout_scope'),
            'logout_scope'
        );
        $logout_scope->setValue((string) $this->settings->getLogoutScope());

        // scope global
        $global_scope = new ilRadioOption(
            $this->lng->txt('auth_oidc_settings_logout_scope_global'),
            (string) ilOpenIdConnectSettings::LOGOUT_SCOPE_GLOBAL
        );
        $global_scope->setInfo($this->lng->txt('auth_oidc_settings_logout_scope_global_info'));
        $logout_scope->addOption($global_scope);

        // ilias scope
        $ilias_scope = new ilRadioOption(
            $this->lng->txt('auth_oidc_settings_logout_scope_local'),
            (string) ilOpenIdConnectSettings::LOGOUT_SCOPE_LOCAL
        );
        $ilias_scope->setInfo($this->lng->txt('auth_oidc_settings_logout_scope_local_info'));
        $logout_scope->addOption($ilias_scope);

        $form->addItem($logout_scope);

        $use_custom_session = new ilCheckboxInputGUI(
            $this->lng->txt('auth_oidc_settings_custom_session_duration_type'),
            'custom_session'
        );
        $use_custom_session->setOptionTitle(
            $this->lng->txt('auth_oidc_settings_custom_session_duration_option')
        );
        $use_custom_session->setChecked($this->settings->isCustomSession());
        $form->addItem($use_custom_session);

        // session duration
        $session = new ilNumberInputGUI(
            $this->lng->txt('auth_oidc_settings_session_duration'),
            'session_duration'
        );
        $session->setValue((string) $this->settings->getSessionDuration());
        $session->setSuffix($this->lng->txt('minutes'));
        $session->setMinValue(5);
        $session->setMaxValue(1440);
        $session->setRequired(true);
        $use_custom_session->addSubItem($session);

        if ($this->checkAccessBool('write')) {
            // save button
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
        }

        // User sync settings --------------------------------------------------------------
        $user_sync = new ilFormSectionHeaderGUI();
        $user_sync->setTitle($this->lng->txt('auth_oidc_settings_section_user_sync'));
        $form->addItem($user_sync);

        $sync = new ilCheckboxInputGUI(
            $this->lng->txt('auth_oidc_settings_user_sync'),
            'sync'
        );
        $sync->setChecked($this->settings->isSyncAllowed());
        $sync->setInfo($this->lng->txt('auth_oidc_settings_user_sync_info'));
        $sync->setValue("1");
        $form->addItem($sync);

        $roles = new ilSelectInputGUI(
            $this->lng->txt('auth_oidc_settings_default_role'),
            'role'
        );
        $roles->setValue($this->settings->getRole());
        $roles->setInfo($this->lng->txt('auth_oidc_settings_default_role_info'));
        $roles->setOptions($this->prepareRoleSelection());
        $roles->setRequired(true);
        $sync->addSubItem($roles);

        $user_attr = new ilTextInputGUI(
            $this->lng->txt('auth_oidc_settings_user_attr'),
            'username'
        );
        $user_attr->setValue($this->settings->getUidField());
        $user_attr->setRequired(true);
        $form->addItem($user_attr);

        return $form;
    }

    protected function saveSettings(): void
    {
        $this->checkAccess('write');

        $form = $this->initSettingsForm();
        if (!$form->checkInput()) {
            $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $form->setValuesByPost();
            $this->settings($form);
            return;
        }

        $this->settings->setActive((bool) $form->getInput('activation'));
        $this->settings->setProvider((string) $form->getInput('provider'));
        $this->settings->setClientId((string) $form->getInput('client_id'));
        if ((string) $form->getInput('secret') !== '' && strcmp($form->getInput('secret'), '******') !== 0) {
            $this->settings->setSecret((string) $form->getInput('secret'));
        }

        $this->settings->setLoginElementType((int) $form->getInput('le'));
        $this->settings->setLoginElementText((string) $form->getInput('le_text'));
        $this->settings->setLoginPromptType((int) $form->getInput('login_prompt'));
        $this->settings->setLogoutScope((int) $form->getInput('logout_scope'));
        $this->settings->useCustomSession((bool) $form->getInput('custom_session'));
        $this->settings->setSessionDuration((int) $form->getInput('session_duration'));
        $this->settings->allowSync((bool) $form->getInput('sync'));
        $this->settings->setRole((int) $form->getInput('role'));
        $this->settings->setUidField((string) $form->getInput('username'));

        $fileData = (array) $form->getInput('le_img');

        if ((string) ($fileData['tmp_name'] ?? '') !== '') {
            $this->saveImageFromHttpRequest();
        }

        $this->settings->save();

        $this->mainTemplate->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'settings');
    }

    protected function saveImageFromHttpRequest(): void
    {
        try {
            if (!$this->upload->hasBeenProcessed()) {
                $this->upload->process();
            }
            foreach ($this->upload->getResults() as $single_file_upload) {
                if ($single_file_upload->isOK()) {
                    $this->settings->deleteImageFile();
                    $this->upload->moveFilesTo(
                        ilOpenIdConnectSettings::FILE_STORAGE,
                        \ILIAS\FileUpload\Location::WEB
                    );
                    $this->settings->setLoginElementImage($single_file_upload->getName());
                }
            }
        } catch (\ILIAS\Filesystem\Exception\IllegalStateException $e) {
            $this->logger->warning('Upload failed with message: ' . $e->getMessage());
        }
    }

    /**
     * @param bool $a_with_select_option
     * @return array<string, string>
     */
    protected function prepareRoleSelection(bool $a_with_select_option = true): array
    {
        $global_roles = ilUtil::_sortIds(
            $this->review->getGlobalRoles(),
            'object_data',
            'title',
            'obj_id'
        );

        $select = [];
        if ($a_with_select_option) {
            $select[0] = $this->lng->txt('links_select_one');
        }
        foreach ($global_roles as $role_id) {
            if ($role_id === ANONYMOUS_ROLE_ID) {
                continue;
            }
            $select[(string) $role_id] = ilObject::_lookupTitle((int) $role_id);
        }

        return $select;
    }

    protected function profile(ilPropertyFormGUI $form = null): void
    {
        $this->checkAccess('read');
        $this->chooseMapping();
        $this->userMapping();
    }

    protected function scopes(ilPropertyFormGUI $form = null): void
    {
        $this->checkAccess('read');
        $this->setSubTabs(self::STAB_SCOPES);
        $form = $this->initScopesForm();
        $this->tpl->setContent($this->renderer->render($form));
    }
    protected function initScopesForm(ilPropertyFormGUI $form = null)
    {
        $this->checkAccess('read');

        $form = new ilPropertyFormGUI();
        $ui_container = [];
        $ui_container = $this->buildScopeSelection($ui_container);

        $form = $this->ui->input()->container()->form()->standard($this->ctrl->getFormAction($this, 'saveScopes'), $ui_container);

        return $form;
    }

    protected function buildScopeSelection(array $ui_container): array {
        $disabled_input = $this->ui->input()->field()
                                   ->text($this->lng->txt('auth_oidc_settings_default_scopes'), '')
                                   ->withValue(ilOpenIdConnectSettings::DEFAULT_SCOPE)
                                   ->withDedicatedName('default_scope')
                                   ->withDisabled(true);

        $scopeValues = $this->settings->getAdditionalScopes();
        $tag_input = $this->ui->input()->field()->tag(
            $this->lng->txt('auth_oidc_settings_additional_scopes'),
            $scopeValues
        )->withValue($scopeValues)->withDedicatedName('custom_scope')
                              ->withByline($this->lng->txt('auth_oidc_settings_additional_scopes_info'));
        ;
        $group1 = $this->ui->input()->field()->group(
            [],
            $this->lng->txt('auth_oidc_settings_validate_scope_default')
        );
        $group2 = $this->ui->input()->field()->group(
            [
                $this->lng->txt('auth_oidc_settings_discovery_url') =>  $this->ui->input()->field()->text($this->lng->txt('auth_oidc_settings_discovery_url'))
                                                                                 ->withValue($this->settings->getCustomDiscoveryUrl() ?? '')
            ],
            $this->lng->txt('auth_oidc_settings_validate_scope_custom')
        );
        $group3 = $this->ui->input()->field()->group(
            [],
            $this->lng->txt('auth_oidc_settings_validate_scope_none')
        );
        $url_validation = $this->ui->input()->field()->switchableGroup(
            [
                (string) ilOpenIdConnectSettings::URL_VALIDATION_PROVIDER => $group1,
                (string) ilOpenIdConnectSettings::URL_VALIDATION_CUSTOM => $group2,
                (string) ilOpenIdConnectSettings::URL_VALIDATION_NONE => $group3,
            ],
            $this->lng->txt('auth_oidc_settings_validate_scopes')
        )->withDedicatedName('validate_scopes')->withValue($this->settings->getValidateScopes());
        $group = $this->ui->input()->field()->group(
            [$disabled_input, $tag_input, $url_validation]
        );
        $ui_container[] = $group;
        return $ui_container;
    }

    protected function saveScopes(): void
    {
        $this->checkAccess('write');
        $validation = false;
        $type = null;
        $url = null;
        $custom_scopes = [];

        $form = $this->initScopesForm();
        if ($this->request->getMethod() == "POST") {
            $request_form = $form->withRequest($this->request);
            $result = $request_form->getData();
            if (is_null($result)) {
                $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
                $this->scopes();
                return;
            } else {
                foreach ($form->getInputs() as $group => $groups) {
                    foreach ($groups->getInputs() as $key => $input) {
                        $dedicated_name = $input->getDedicatedName();
                        $result_data = $result[$group][$key];
                        if($dedicated_name === 'validate_scopes') {
                            $type = (int) $result_data[0];
                            $url = array_pop($result_data[1]);
                        } elseif($dedicated_name === 'custom_scope') {
                            $custom_scopes = $result_data;
                        }
                    }
                }
            }
            if($url === null && $type === ilOpenIdConnectSettings::URL_VALIDATION_PROVIDER) {
                $url = $this->settings->getProvider();
            }
            $validation = $this->validateDiscoveryUrl($type, $url, $custom_scopes);
        }

        if ($validation) {
            $this->settings->setAdditionalScopes((array) $custom_scopes);
            $this->settings->setValidateScopes((int) $type);
            if (ilOpenIdConnectSettings::URL_VALIDATION_CUSTOM === $this->settings->getValidateScopes()) {
                $this->settings->setCustomDiscoveryUrl($url);
            }
            $this->settings->save();
            $this->mainTemplate->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'scopes');
        }

        if(strlen($this->failed_validation_messages) > 0) {
            $this->failed_validation_messages = $this->lng->txt('err_check_input') . '<br/>' . $this->failed_validation_messages;
        } else {
            $this->failed_validation_messages = $this->lng->txt('err_check_input');
        }
        $this->mainTemplate->setOnScreenMessage('failure', $this->failed_validation_messages , true);
        $this->ctrl->redirect($this, 'scopes');
    }

    protected function validateDiscoveryUrl(int $type, ?string $url, array $scopes) : bool {
        try {
            switch ($type) {
                case ilOpenIdConnectSettings::URL_VALIDATION_PROVIDER:
                    $discoveryURL = $url . '/.well-known/openid-configuration';
                    break;
                case ilOpenIdConnectSettings::URL_VALIDATION_CUSTOM:
                    $discoveryURL = $url;
                    break;
                default:
                    $discoveryURL = null;
                    break;
            }
            $validation_result = !is_null($discoveryURL) ? $this->settings->validateScopes($discoveryURL, (array) $scopes) : [];
            if (!empty($validation_result)) {
                if (ilOpenIdConnectSettings::VALIDATION_ISSUE_INVALID_SCOPE === $validation_result[0]) {
                    $this->mainTemplate->setOnScreenMessage(
                        'failure',
                        sprintf($this->lng->txt('auth_oidc_settings_invalid_scopes'), implode(",", $validation_result[1]))
                    );
                } else {
                    $this->mainTemplate->setOnScreenMessage(
                        'failure',
                        sprintf($this->lng->txt('auth_oidc_settings_discovery_error'), $validation_result[1])
                    );
                }
                $this->scopes();
                return false;
            }
        } catch (ilCurlConnectionException $e) {
            $this->mainTemplate->setOnScreenMessage(
                'failure',
                $e->getMessage(),
                true
            );
            $this->failed_validation_messages = $e->getMessage();
            $this->scopes();
            return false;
        }
        return true;
    }

    /**
     * @throws ilCtrlException
     */
    protected function saveProfile(): void
    {
        $this->checkAccessBool('write');

        $form = $this->initUserMappingForm();
        if ($this->request->getMethod() == "POST"
            && $this->request->getQueryParams()["opic"] == "opic_user_data_mapping") {
            $request_form = $form->withRequest($this->request);
            $result = $request_form->getData();
            if (is_null($result)) {
                $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
                $this->profile();
                return;
            } else {
                foreach ($this->settings->getProfileMappingFields() as $field => $lng_key) {
                    $this->updateProfileMappingFieldValue($field);
                }
                foreach ($this->udf->getDefinitions() as $definition) {
                    $field = self::UDF_STRING . $definition['field_id'];
                    $this->updateProfileMappingFieldValue($field);
                }
            }
        }

        $this->settings->save();
        $this->mainTemplate->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::STAB_PROFILE);
    }

    /**
     * @throws ilCtrlException
     */
    private function updateProfileMappingFieldValue(string $field) : void {
        $form = $this->initUserMappingForm();
        $request_form = $form->withRequest($this->request);
        $result = $request_form->getData();
        foreach ($form->getInputs() as $group => $groups) {
            foreach ($groups->getInputs() as $key => $input) {
                $dedicated_name = $input->getDedicatedName();
                $result_data = $result[$group][$key];

                if ($dedicated_name === $field . self::VALUE_STRING) {
                    $this->settings->setProfileMappingFieldValue(
                        $field,
                        $result_data
                    );
                } elseif ($dedicated_name === $field . self::UPDATE_STRING) {
                    $this->settings->setProfileMappingFieldUpdate(
                        $field,
                        (bool) $result_data
                    );
                }
            }
        }
    }

    protected function roles(ilPropertyFormGUI $form = null): void
    {
        $this->checkAccess('read');
        $this->setSubTabs(self::STAB_ROLES);

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initRolesForm();
        }
        $this->mainTemplate->setContent($form->getHTML());
    }

    protected function initRolesForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('auth_oidc_role_mapping_table'));
        $form->setFormAction($this->ctrl->getFormAction($this, self::STAB_ROLES));

        foreach ($this->prepareRoleSelection(false) as $role_id => $role_title) {
            $role_map = new ilTextInputGUI(
                $role_title,
                'role_map_' . $role_id
            );
            $role_map->setInfo($this->lng->txt('auth_oidc_role_info'));
            $role_map->setValue($this->settings->getRoleMappingValueForId((int) $role_id));
            $form->addItem($role_map);

            $update = new ilCheckboxInputGUI(
                '',
                'role_map_update_' . $role_id
            );
            $update->setOptionTitle($this->lng->txt('auth_oidc_update_role_info'));
            $update->setValue("1");
            $update->setChecked(!$this->settings->getRoleMappingUpdateForId((int) $role_id));
            $form->addItem($update);
        }

        if ($this->checkAccessBool('write')) {
            $form->addCommandButton('saveRoles', $this->lng->txt('save'));
        }
        return $form;
    }

    protected function saveRoles(): void
    {
        $this->checkAccess('write');
        $form = $this->initRolesForm();
        if ($form->checkInput()) {
            $this->logger->dump($this->body, ilLogLevel::DEBUG);


            $role_settings = [];
            $role_valid = true;
            foreach ($this->prepareRoleSelection(false) as $role_id => $role_title) {
                $role_settings[(int) $role_id]['update'] = !$form->getInput('role_map_update_' . $role_id);
                $role_settings[(int) $role_id]['value'] = '';

                $input_role = trim($form->getInput('role_map_' . $role_id));
                if ($input_role === '') {
                    continue;
                }

                $role_params = explode('::', $input_role);
                $this->logger->dump($role_params, ilLogLevel::DEBUG);

                if (count($role_params) !== 2) {
                    if ($form->getItemByPostVar('role_map_' . $role_id)) {
                        $form->getItemByPostVar('role_map_' . $role_id)->setAlert($this->lng->txt('msg_wrong_format'));
                    }
                    $role_valid = false;
                    continue;
                }
                $role_settings[(int) $role_id]['value'] = $input_role;
            }

            if (!$role_valid) {
                $form->setValuesByPost();
                $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
                $this->roles($form);
                return;
            }

            $this->settings->setRoleMappings($role_settings);
            $this->settings->save();
            $this->mainTemplate->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'roles');
        }

        $form->setValuesByPost();
        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $this->roles($form);
    }

    protected function setSubTabs(string $active_tab): void
    {
        $this->tabs->addSubTab(
            self::STAB_SETTINGS,
            $this->lng->txt('auth_oidc_' . self::STAB_SETTINGS),
            $this->ctrl->getLinkTarget($this, self::STAB_SETTINGS)
        );

        $this->tabs->addSubTab(
            self::STAB_SCOPES,
            $this->lng->txt('auth_oidc_' . self::STAB_SCOPES),
            $this->ctrl->getLinkTarget($this, self::STAB_SCOPES)
        );

        $this->tabs->addSubTab(
            self::STAB_PROFILE,
            $this->lng->txt('auth_oidc_' . self::STAB_PROFILE),
            $this->ctrl->getLinkTarget($this, self::STAB_PROFILE)
        );
        $this->tabs->addSubTab(
            self::STAB_ROLES,
            $this->lng->txt('auth_oidc_' . self::STAB_ROLES),
            $this->ctrl->getLinkTarget($this, self::STAB_ROLES)
        );

        $this->tabs->activateSubTab($active_tab);
    }

    public function chooseMapping(): void
    {
        $this->showInfoMessage();
        $this->setSubTabs(self::STAB_PROFILE);
        if ((int) $this->mapping_template === 2) {
            $this->userMapping();
            return;
        }

        $this->initAttributeMapping();
    }

    private function showInfoMessage()
    {
        $url_text = $this->lng->txt('auth_oidc_here');

        if ($this->mapping_template === 2) {
            $url = $this->renderer->render($this->factory->link()->standard(
                $this->lng->txt('auth_oidc_here'),
                'https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims')
                                                         ->withOpenInNewViewport(true));
            $message = sprintf($this->lng->txt('auth_odic_scope_tab_info'), $url);
        } else {
            $url = $this->renderer->render($this->factory->link()->standard(
                $this->lng->txt('auth_oidc_here'), $this->ctrl->getLinkTarget($this, self::STAB_SCOPES)));
            $tab_name = $this->lng->txt('auth_oidc_configured_scopes');
            $message = sprintf($this->lng->txt('auth_odic_scope_info'), $url, $tab_name);
        }

        $this->renderer->render($this->factory->link()->standard(
            $url_text, $url));
        $this->mainTemplate->setOnScreenMessage('info', $message);
    }

    /**
     * @throws ilCtrlException
     */
    private function initAttributeMapping() : void
    {
        $mapping = $this->attribute_mapping_template->getMappingRulesByAdditionalScopes($this->settings->getAdditionalScopes());
        if(count($mapping) > 0 )
        {
            $this->settings->clearProfileMaps();
        }
        foreach($mapping as $field => $item) {
            $this->settings->setProfileMappingFieldValue(
                $field,
                $item
            );
        }

        $this->userMapping();
    }

    /**
     * @throws ilCtrlException
     */
    private function initUserMappingForm() : Standard
    {
        $ui_container = [];
        $this->initUserDefinedFields();

        foreach ($this->settings->getProfileMappingFields() as $mapping => $lang) {
            $ui_container = $this->buildUserMappingInputForUserData($lang, $mapping, $ui_container);
        }

        foreach ($this->udf->getDefinitions() as $definition) {
            $ui_container = $this->buildUserMappingInputFormUDF($definition, $ui_container);
        }
        $this->ctrl->setParameter(
            $this,
            "opic",
            "opic_user_data_mapping"
        );
        return $this->ui->input()->container()->form()
                        ->standard(
                            $this->ctrl->getFormAction($this, 'saveProfile'), $ui_container
                        );
    }

    /**
     * @param mixed $definition
     * @param array $ui_container
     * @return array
     */
    protected function buildUserMappingInputFormUDF(mixed $definition, array $ui_container) : array
    {
        $value = $this->settings->getProfileMappingFieldValue(self::UDF_STRING . $definition['field_id']);
        $update = $this->settings->getProfileMappingFieldUpdate(self::UDF_STRING . $definition['field_id']);

        $text_input = $this->ui->input()->field()
                               ->text($definition['field_name'], '')
                               ->withValue($value)
                               ->withDedicatedName(self::UDF_STRING . $definition['field_id'] . self::VALUE_STRING);
        $checkbox_input = $this->ui->input()->field()->checkbox("", $this->lng->txt('auth_oidc_update_field_info'))
                                   ->withValue($update)
                                   ->withDedicatedName(self::UDF_STRING . $definition['field_id'] . self::UPDATE_STRING);
        $group = $this->ui->input()->field()->group(
            [$text_input, $checkbox_input]
        );
        $ui_container[] = $group;
        return $ui_container;
    }

    /**
     * @param string     $lang
     * @param int|string $mapping
     * @param array      $ui_container
     * @return array
     */
    protected function buildUserMappingInputForUserData(string $lang, int|string $mapping, array $ui_container) : array
    {
        $value = $this->settings->getProfileMappingFieldValue($mapping);
        $update = $this->settings->getProfileMappingFieldUpdate($mapping);

        $text_input = $this->ui->input()->field()
                               ->text($lang, '')
                               ->withValue($value)
                               ->withDedicatedName($mapping . self::VALUE_STRING);
        $checkbox_input = $this->ui->input()->field()->checkbox("", $this->lng->txt('auth_oidc_update_field_info'))
                                   ->withValue($update)
                                   ->withDedicatedName($mapping . self::UPDATE_STRING);
        $group = $this->ui->input()->field()->group(
            [$text_input, $checkbox_input]
        );
        $ui_container[] = $group;
        return $ui_container;
    }

    private function initUserDefinedFields(): void
    {
        if($this->udf === null) {
            $this->udf = ilUserDefinedFields::_getInstance();
        }
    }

    /**
     * @throws ilCtrlException
     */
    public function userMapping($form = null): void
    {
        if($form === null) {
            $form = $this->initUserMappingForm();
        }

        $request_wrapper = $this->http->wrapper()->query();
        $active = 2;

        $target = $this->http->request()->getRequestTarget();
        if ($request_wrapper->has(self::POST_VALUE) && $request_wrapper->retrieve(self::POST_VALUE, $this->refinery->kindlyTo()->int())) {
            $active = $request_wrapper->retrieve(self::POST_VALUE, $this->refinery->kindlyTo()->int());
        }

        $actions = array(
            $this->lng->txt("auth_oidc_saved_values") => "$target&".self::POST_VALUE."=" . self::SAVED_VALUES,
            $this->lng->txt(ilOpenIdAttributeMappingTemplate::OPEN_ID_CONFIGURED_SCOPES) => "$target&" . self::POST_VALUE."=" . self::DEFAULT_VALUES,
        );

        $aria_label = "change_the_currently_displayed_mode";
        $active_label =  $this->lng->txt("auth_oidc_saved_values");
        if($active !== 2) {
            $active_label = $this->lng->txt(ilOpenIdAttributeMappingTemplate::OPEN_ID_CONFIGURED_SCOPES);
        }
        $view_control = $this->factory->viewControl()->mode($actions, $aria_label)->withActive($active_label);
        $html = $this->renderer->render($view_control);
        $this->tpl->setContent($html . $this->renderer->render($form));
    }

}
