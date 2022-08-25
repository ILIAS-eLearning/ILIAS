<?php

declare(strict_types=1);

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

use ILIAS\FileUpload\FileUpload;

/**
 * Class ilOpenIdConnectSettingsGUI
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilOpenIdConnectSettingsGUI
{
    private const STAB_SETTINGS = 'settings';
    private const STAB_PROFILE = 'profile';
    private const STAB_ROLES = 'roles';

    private const DEFAULT_CMD = 'settings';
    private int $ref_id;
    /** @var array $body */
    private $body;
    private ilOpenIdConnectSettings $settings;
    private ilLanguage $lng;
    private ilCtrl $ctrl;
    private ilLogger $logger;
    private ilAccessHandler $access;
    private ilRbacReview $review;
    private ilErrorHandling $error;
    private ilGlobalTemplateInterface $mainTemplate;
    private ilTabsGUI $tabs;
    private FileUpload $upload;

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

        $default_scope = new ilTextInputGUI(
            $this->lng->txt('auth_oidc_settings_additional_scopes'),
            "default_scope"
        );
        $default_scope->setValue(ilOpenIdConnectSettings::DEFAULT_SCOPE);
        $default_scope->setDisabled(true);
        $form->addItem($default_scope);

        $scopes = new ilTextInputGUI(
            "",
            "scopes"
        );
        $scopes->setMulti(true);
        $scopeValues = $this->settings->getAdditionalScopes();
        if (isset($scopeValues[0])) {
            $scopes->setValue($scopeValues[0]);
        }
        $scopes->setMultiValues($scopeValues);
        $form->addItem($scopes);

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

        $scopes = [];
        if (!empty($form->getInput('scopes'))) {
            $scopes = $form->getInput('scopes');
            foreach ($scopes as $key => $value) {
                if (empty($value)) {
                    array_splice($scopes, $key, 1);
                }
            }
        }

        $invalid_scopes = $this->settings->validateScopes((string) $form->getInput('provider'), (array) $scopes);
        if (!empty($invalid_scopes)) {
            $this->mainTemplate->setOnScreenMessage(
                'failure',
                sprintf($this->lng->txt('auth_oidc_settings_invalid_scopes'), implode(",", $invalid_scopes))
            );
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
        $this->settings->setAdditionalScopes((array) $scopes);
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
        $this->setSubTabs(self::STAB_PROFILE);

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initProfileForm();
        }
        $this->mainTemplate->setContent($form->getHTML());
    }

    protected function initProfileForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('auth_oidc_mapping_table'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveProfile'));

        foreach ($this->settings->getProfileMappingFields() as $field => $lng_key) {
            $text_form = new ilTextInputGUI($this->lng->txt($lng_key));
            $text_form->setPostVar($field . "_value");
            $text_form->setValue($this->settings->getProfileMappingFieldValue($field));
            $form->addItem($text_form);

            $checkbox_form = new ilCheckboxInputGUI('');
            $checkbox_form->setValue("1");
            $checkbox_form->setPostVar($field . "_update");
            $checkbox_form->setChecked($this->settings->getProfileMappingFieldUpdate($field));
            $checkbox_form->setOptionTitle($this->lng->txt('auth_oidc_update_field_info'));
            $form->addItem($checkbox_form);
        }

        if ($this->checkAccessBool('write')) {
            $form->addCommandButton('saveProfile', $this->lng->txt('save'));
        }

        return $form;
    }

    protected function saveProfile(): void
    {
        $this->checkAccessBool('write');

        $form = $this->initProfileForm();
        if (!$form->checkInput()) {
            $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $form->setValuesByPost();
            $this->profile($form);
            return;
        }

        foreach ($this->settings->getProfileMappingFields() as $field => $lng_key) {
            $this->settings->setProfileMappingFieldValue(
                $field,
                $form->getInput($field . '_value')
            );
            $this->settings->setProfileMappingFieldUpdate(
                $field,
                (bool) $form->getInput($field . '_update')
            );
        }
        $this->settings->save();
        $this->mainTemplate->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::STAB_PROFILE);
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
                if (trim($form->getInput('role_map_' . $role_id)) === '') {
                    continue;
                }

                $role_params = explode('::', $form->getInput('role_map_' . $role_id));
                $this->logger->dump($role_params, ilLogLevel::DEBUG);

                if (count($role_params) !== 2) {
                    if ($form->getItemByPostVar('role_map_' . $role_id)) {
                        $form->getItemByPostVar('role_map_' . $role_id)->setAlert($this->lng->txt('msg_wrong_format'));
                    }
                    $role_valid = false;
                    continue;
                }
                $role_settings[(int) $role_id]['update'] = !$form->getInput('role_map_update_' . $role_id);
                $role_settings[(int) $role_id]['value'] = (string) $form->getInput('role_map_' . $role_id);
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
}
