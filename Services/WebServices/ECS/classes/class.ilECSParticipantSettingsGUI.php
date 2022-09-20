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

use ILIAS\UI\Factory as UiFactory;

/**
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*/
class ilECSParticipantSettingsGUI
{
    private int $server_id ;
    private int $mid;

    private ilECSParticipantSetting $participant;

    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs;
    protected UiFactory $ui_factory;
    protected ilToolbarGUI $toolbar;
    protected ilSetting $settings;

    public function __construct(int $a_server_id, int $a_mid)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->ui_factory = $DIC->ui()->factory();
        $this->toolbar = $DIC->toolbar();
        $this->settings = $DIC->settings();

        $this->server_id = $a_server_id;
        $this->mid = $a_mid;

        $this->lng->loadLanguageModule('ecs');

        $this->participant = new ilECSParticipantSetting($this->getServerId(), $this->getMid());
    }

    public function getServerId(): int
    {
        return $this->server_id;
    }

    public function getMid(): int
    {
        return $this->mid;
    }

    private function getParticipant(): ilECSParticipantSetting
    {
        return $this->participant;
    }


    /**
     * Execute command
     */
    public function executeCommand(): bool
    {
        $this->ctrl->saveParameter($this, 'server_id');
        $this->ctrl->saveParameter($this, 'mid');

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd('settings');

        $this->setTabs();
        $this->$cmd();

        return true;
    }

    /**
     * Abort editing
     */
    private function abort(): void
    {
        $this->ctrl->returnToParent($this);
    }


    /**
     * Settings
     */
    private function settings(ilPropertyFormGUI $form = null): void
    {
        $this->renderConsentToolbar();

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormSettings();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function renderConsentToolbar(): void
    {
        $consents = new ilECSParticipantConsents(
            $this->getServerId(),
            $this->getMid()
        );
        if (!$consents->hasConsents()) {
            return;
        }

        $confirm = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('ecs_consent_reset_confirm_title'),
            $this->lng->txt('ecs_consent_reset_confirm_title_info'),
            $this->ctrl->getLinkTarget($this, 'resetConsents')
        );
        $this->toolbar->addComponent($confirm);

        $confirmation_trigger = $this->ui_factory->button()->standard(
            $this->lng->txt('ecs_consent_reset_confirm_title'),
            ''
        )->withOnClick($confirm->getShowSignal());
        $this->toolbar->addComponent($confirmation_trigger);
    }

    protected function resetConsents()
    {
        $consents = new ilECSParticipantConsents($this->getServerId(), $this->getMid());
        $consents->delete();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('ecs_user_consents_deleted'), true);
        $this->ctrl->redirect($this, 'settings');
    }

    /**
     * Save settings
     */
    protected function saveSettings(): void
    {
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $this->getParticipant()->enableToken((bool) $form->getInput('token'));
            $this->getParticipant()->enableDeprecatedToken((bool) $form->getInput('dtoken'));
            $this->getParticipant()->enableExport((bool) $form->getInput('export'));
            $this->getParticipant()->setExportTypes($form->getInput('export_types'));
            $this->getParticipant()->enableImport((bool) $form->getInput('import'));
            $this->getParticipant()->setImportTypes($form->getInput('import_types'));
            $this->getParticipant()->enableIncomingLocalAccounts((bool) $form->getInput('incoming_local_accounts'));
            $this->getParticipant()->setIncomingAuthType((int) $form->getInput('incoming_auth_type'));
            $this->getParticipant()->setOutgoingAuthModes((array) $form->getInput('outgoing_auth_modes'));

            // placeholders
            $placeholders = [];
            foreach ($this->parseAvailableAuthModes() as $authmode_name => $authmode_text) {
                $placeholders[$authmode_name] = $form->getInput(
                    'username_placeholder_' . $authmode_name
                );
            }
            $this->getParticipant()->setOutgoingUsernamePlaceholders($placeholders);

            // additional validation
            $error_code = $this->getParticipant()->validate();
            switch ($error_code) {
                case ilECSParticipantSetting::ERR_MISSING_USERNAME_PLACEHOLDER:
                    $form->getItemByPostVar('outgoing_auth_modes')->setAlert(
                        $this->lng->txt('ecs_username_place_holder_err_mssing_placeholder')
                    );
                    break;
                default:
                    $this->getParticipant()->update();
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
                    $this->ctrl->redirect($this, 'settings');
            }
        }
        $form->setValuesByPost();
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $this->settings($form);
    }

    /**
     * Init settings form
     */
    protected function initFormSettings(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('ecs_part_settings') . ' ' . $this->getParticipant()->getTitle());


        $token = new ilCheckboxInputGUI($this->lng->txt('ecs_token_mechanism'), 'token');
        $token->setInfo($this->lng->txt('ecs_token_mechanism_info'));
        $token->setValue("1");
        $token->setChecked($this->getParticipant()->isTokenEnabled());
        $form->addItem($token);

        $dtoken = new ilCheckboxInputGUI($this->lng->txt('ecs_deprecated_token'), 'dtoken');
        $dtoken->setInfo($this->lng->txt('ecs_deprecated_token_info'));
        $dtoken->setValue("1");
        $dtoken->setChecked($this->getParticipant()->isDeprecatedTokenEnabled());
        $form->addItem($dtoken);

        // Export
        $export = new ilCheckboxInputGUI($this->lng->txt('ecs_tbl_export'), 'export');
        $export->setValue("1");
        $export->setChecked($this->getParticipant()->isExportEnabled());
        $form->addItem($export);

        $auth_types = new ilCheckboxInputGUI(
            $this->lng->txt('ecs_export_local_account'),
            'incoming_local_accounts'
        );
        $auth_types->setInfo($this->lng->txt('ecs_export_local_account_info'));
        $auth_types->setChecked($this->getParticipant()->areIncomingLocalAccountsSupported());
        $export->addSubItem($auth_types);

        // radio group with login page and (optional) shibboleth option
        $external_auth_type = new ilRadioGroupInputGUI(
            $this->lng->txt('ecs_export_auth_type'),
            'incoming_auth_type'
        );
        $external_auth_type->setInfo($this->lng->txt('ecs_export_auth_type_info'));
        $external_auth_type->setValue(
            (string) $this->getParticipant()->getIncomingAuthType()
        );
        $external_auth_type->addOption(
            new ilRadioOption(
                $this->lng->txt('ecs_export_auth_type_ilias'),
                (string) ilECSParticipantSetting::INCOMING_AUTH_TYPE_LOGIN_PAGE
            )
        );
        if ($this->isShibbolethActive()) {
            $external_auth_type->addOption(
                new ilRadioOption(
                    $this->lng->txt('ecs_export_auth_type_shib'),
                    (string) ilECSParticipantSetting::INCOMING_AUTH_TYPE_SHIBBOLETH
                )
            );
        }
        $external_auth_type->addOption(
            new ilRadioOption(
                $this->lng->txt('ecs_export_auth_type_none'),
                (string) ilECSParticipantSetting::INCOMING_AUTH_TYPE_INACTIVE
            )
        );
        $export->addSubItem($external_auth_type);

        // Export types
        $obj_types = new ilCheckboxGroupInputGUI($this->lng->txt('ecs_export_types'), 'export_types');
        $obj_types->setValue($this->getParticipant()->getExportTypes());


        foreach (ilECSUtils::getPossibleReleaseTypes(true) as $type => $trans) {
            $obj_types->addOption(new ilCheckboxOption($trans, $type));
        }
        $export->addSubItem($obj_types);


        // Import
        $import = new ilCheckboxInputGUI($this->lng->txt('ecs_tbl_import'), 'import');
        $import->setValue("1");
        $import->setChecked($this->getParticipant()->isImportEnabled());
        $form->addItem($import);

        // user credentials by auth mode
        $user_credentials = new ilCheckboxGroupInputGUI(
            $this->lng->txt('ecs_import_user_credentials_by_auth_mode'),
            'outgoing_auth_modes'
        );
        $user_credentials->setInfo($this->lng->txt('ecs_import_user_credentials_by_auth_mode_info'));
        $user_credentials->setValue($this->getParticipant()->getOutgoingAuthModes());
        $import->addSubItem($user_credentials);
        foreach ($this->parseAvailableAuthModes() as $option_name => $option_text) {
            $option = new ilCheckboxOption(
                $this->lng->txt('ecs_import_auth_mode') . ' ' . $option_text,
                $option_name
            );
            $user_credentials->addOption($option);
            $username_placeholder = new ilTextInputGUI(
                $this->lng->txt('ecs_outgoing_user_credentials'),
                'username_placeholder_' . $option_name
            );
            $username_placeholder->setRequired(false);
            $username_placeholder->setInfo($this->lng->txt('ecs_outgoing_user_credentials_info'));
            $username_placeholder->setValue(
                $this->getParticipant()->getOutgoingUsernamePlaceholderByAuthMode(
                    $option_name
                )
            );
            $option->addSubItem($username_placeholder);
        }
        $option = new ilCheckboxOption(
            $this->lng->txt('ecs_import_auth_type_default'),
            'default'
        );
        $user_credentials->addOption($option);

        // Import types
        $imp_types = new ilCheckboxGroupInputGUI($this->lng->txt('ecs_import_types'), 'import_types');
        $imp_types->setValue($this->getParticipant()->getImportTypes());


        foreach (ilECSUtils::getPossibleRemoteTypes(true) as $type => $trans) {
            $imp_types->addOption(new ilCheckboxOption($trans, $type));
        }
        $import->addSubItem($imp_types);

        $form->addCommandButton('saveSettings', $this->lng->txt('save'));
        $form->addCommandButton('abort', $this->lng->txt('cancel'));
        return $form;
    }

    protected function parseAvailableAuthModes($a_mode_incoming = true): array
    {
        $options = [];
        if ($this->isShibbolethActive()) {
            $options['shibboleth'] = $this->lng->txt('auth_shib');
        }
        foreach (ilLDAPServer::getServerIds() as $server_id) {
            $server = ilLDAPServer::getInstanceByServerId($server_id);
            $options['ldap_' . $server->getServerId()] = $server->getName();
        }
        return $options;
    }

    protected function isShibbolethActive(): bool
    {
        return (bool) $this->settings->get('shib_active', '0');
    }

    /**
     * Set tabs
     */
    private function setTabs(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getParentReturnByClass(self::class)
        );
    }
}
