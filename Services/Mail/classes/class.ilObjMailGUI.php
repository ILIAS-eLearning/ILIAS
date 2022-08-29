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

/**
 * @author       Stefan Meyer <meyer@leifos.com>
 * @author       Michael Jansen <mjansen@databay.de>
 * @ilCtrl_Calls ilObjMailGUI: ilPermissionGUI
 */
class ilObjMailGUI extends ilObjectGUI
{
    public const SETTINGS_SUB_TAB_ID_GENERAL = 'settings_general';
    public const SETTINGS_SUB_TAB_ID_EXTERNAL = 'settings_external';
    public const PASSWORD_PLACE_HOLDER = '***********************';
    protected ilTabsGUI $tabs;

    public function __construct($a_data, int $a_id, bool $a_call_by_reference)
    {
        global $DIC;
        $this->type = 'mail';
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->tabs = $DIC->tabs();

        $this->lng->loadLanguageModule('mail');
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch (strtolower($next_class)) {
            case strtolower(ilPermissionGUI::class):
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case strtolower(ilMailTemplateGUI::class):
                if (!$this->isViewAllowed()) {
                    $this->ilias->raiseError(
                        $this->lng->txt('msg_no_perm_write'),
                        $this->ilias->error_obj->WARNING
                    );
                }

                $this->ctrl->forwardCommand(new ilMailTemplateGUI($this->object));
                break;

            default:
                if (!$cmd) {
                    $cmd = 'view';
                }
                $cmd .= 'Object';
                $this->$cmd();
                break;
        }
    }

    private function isEditingAllowed(): bool
    {
        return $this->rbac_system->checkAccess('write', $this->object->getRefId());
    }

    private function isViewAllowed(): bool
    {
        return $this->rbac_system->checkAccess('read', $this->object->getRefId());
    }

    private function isPermissionChangeAllowed(): bool
    {
        return $this->rbac_system->checkAccess('edit_permission', $this->object->getRefId());
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    protected function getTabs(): void
    {
        if ($this->isViewAllowed()) {
            $this->tabs->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'view'),
                [
                    'view',
                    'save',
                    '',
                    'showExternalSettingsForm',
                    'saveExternalSettingsForm',
                    'sendTestUserMail',
                    'sendTestSystemMail',
                ]
            );
        }

        if ($this->isViewAllowed()) {
            $this->tabs->addTarget(
                'mail_templates',
                $this->ctrl->getLinkTargetByClass(ilMailTemplateGUI::class, 'showTemplates'),
                '',
                ilMailTemplateGUI::class
            );
        }

        if ($this->isPermissionChangeAllowed()) {
            $this->tabs->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass([$this::class, ilPermissionGUI::class], 'perm'),
                ['perm', 'info', 'owner'],
                ilPermissionGUI::class
            );
        }
    }

    protected function buildSettingsSubTabs(string $activeSubTab): void
    {
        if ($this->isViewAllowed()) {
            $this->tabs->addSubTab(
                self::SETTINGS_SUB_TAB_ID_GENERAL,
                $this->lng->txt('mail_settings_general_tab'),
                $this->ctrl->getLinkTarget($this, 'view')
            );

            if ($this->settings->get('mail_allow_external', '0')) {
                $this->tabs->addSubTab(
                    self::SETTINGS_SUB_TAB_ID_EXTERNAL,
                    $this->lng->txt('mail_settings_external_tab'),
                    $this->ctrl->getLinkTarget($this, 'showExternalSettingsForm')
                );
            }

            $this->tabs->activateSubTab($activeSubTab);
        }
    }

    public function viewObject(): void
    {
        $this->showGeneralSettingsForm();
    }

    protected function showGeneralSettingsForm(ilPropertyFormGUI $form = null): void
    {
        if (!$this->isViewAllowed()) {
            $this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
        }

        $this->buildSettingsSubTabs(self::SETTINGS_SUB_TAB_ID_GENERAL);

        if ($form === null) {
            $form = $this->getGeneralSettingsForm();
            $this->populateGeneralSettingsForm($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function getGeneralSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $form->setFormAction($this->ctrl->getFormAction($this, 'save'));
        $form->setTitle($this->lng->txt('general_settings'));

        $cb = new ilCheckboxInputGUI($this->lng->txt('mail_allow_external'), 'mail_allow_external');
        $cb->setInfo($this->lng->txt('mail_allow_external_info'));
        $cb->setValue('1');
        $cb->setDisabled(!$this->isEditingAllowed());
        $form->addItem($cb);

        $incoming_mail_gui = new ilIncomingMailInputGUI(
            $this->lng->txt('mail_incoming'),
            'incoming_type'
        );
        $incoming_mail_gui->setDisabled(!$this->isEditingAllowed());
        $this->ctrl->setParameterByClass(ilObjUserFolderGUI::class, 'ref_id', USER_FOLDER_ID);
        $incoming_mail_gui->setInfo(sprintf(
            $this->lng->txt('mail_settings_incoming_type_see_also'),
            $this->ctrl->getLinkTargetByClass(ilObjUserFolderGUI::class, 'settings')
        ));
        $this->ctrl->clearParametersByClass(ilObjUserFolderGUI::class);
        $form->addItem($incoming_mail_gui);

        $show_mail_settings_gui = new ilCheckboxInputGUI(
            $this->lng->txt('show_mail_settings'),
            'show_mail_settings'
        );
        $show_mail_settings_gui->setInfo($this->lng->txt('show_mail_settings_info'));
        $show_mail_settings_gui->setValue('1');
        $form->addItem($show_mail_settings_gui);

        $ti = new ilNumberInputGUI($this->lng->txt('mail_maxsize_attach'), 'mail_maxsize_attach');
        $ti->setSuffix($this->lng->txt('kb'));
        $ti->setInfo($this->lng->txt('mail_max_size_attachments_total'));
        $ti->setMaxLength(10);
        $ti->setSize(10);
        $ti->setDisabled(!$this->isEditingAllowed());
        $form->addItem($ti);

        $mn = new ilFormSectionHeaderGUI();
        $mn->setTitle($this->lng->txt('mail_member_notification'));
        $form->addItem($mn);

        $cron_mail = new ilSelectInputGUI(
            $this->lng->txt('cron_mail_notification'),
            'mail_notification'
        );
        $cron_options = [
            0 => $this->lng->txt('cron_mail_notification_never'),
            1 => $this->lng->txt('cron_mail_notification_cron'),
        ];
        $cron_mail->setOptions($cron_options);
        $cron_mail->setInfo(sprintf(
            $this->lng->txt('cron_mail_notification_desc'),
            $this->lng->txt('mail_allow_external')
        ));
        $cron_mail->setDisabled(!$this->isEditingAllowed());
        $form->addItem($cron_mail);

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            ilAdministrationSettingsFormHandler::FORM_MAIL,
            $form,
            $this
        );

        if ($this->isEditingAllowed()) {
            $form->addCommandButton('save', $this->lng->txt('save'));
        }

        return $form;
    }

    protected function populateGeneralSettingsForm(ilPropertyFormGUI $form): void
    {
        $form->setValuesByArray([
            'mail_allow_external' => (bool) $this->settings->get('mail_allow_external', '0'),
            'incoming_type' => (string) $this->settings->get('mail_incoming_mail', '0'),
            'mail_address_option' => $this->settings->get('mail_address_option', '') !== '' ?
                $this->settings->get('mail_address_option') :
                (string) ilMailOptions::FIRST_EMAIL,
            'mail_address_option_both' => $this->settings->get('mail_address_option', '') !== '' ?
                $this->settings->get('mail_address_option') :
                (string) ilMailOptions::FIRST_EMAIL,
            'show_mail_settings' => (bool) $this->settings->get('show_mail_settings', '1'),
            'mail_maxsize_attach' => $this->settings->get('mail_maxsize_attach', ''),
            'mail_notification' => $this->settings->get('mail_notification', ''),
        ]);
    }

    public function saveObject(): void
    {
        if (!$this->isEditingAllowed()) {
            $this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
        }

        $form = $this->getGeneralSettingsForm();
        if ($form->checkInput()) {
            $incoming_type = (int) $form->getInput('incoming_type');

            $mail_address_option = ilMailOptions::FIRST_EMAIL;
            if ($incoming_type === ilMailOptions::INCOMING_EMAIL) {
                $mail_address_option = (int) $form->getInput('mail_address_option');
            } elseif ($incoming_type === ilMailOptions::INCOMING_BOTH) {
                $mail_address_option = (int) $form->getInput('mail_address_option_both');
            }

            $this->settings->set('mail_allow_external', (string) ((int) $form->getInput('mail_allow_external')));
            $this->settings->set('mail_incoming_mail', (string) $incoming_type);
            $this->settings->set('show_mail_settings', (string) ((int) $form->getInput('show_mail_settings')));
            $this->settings->set('mail_address_option', (string) $mail_address_option);
            $this->settings->set('mail_maxsize_attach', (string) $form->getInput('mail_maxsize_attach'));
            $this->settings->set('mail_notification', (string) ((int) $form->getInput('mail_notification')));

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this);
        }

        $form->setValuesByPost();
        $this->showGeneralSettingsForm($form);
    }

    protected function showExternalSettingsFormObject(ilPropertyFormGUI $form = null): void
    {
        if (!$this->isViewAllowed()) {
            $this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
        }

        $this->buildSettingsSubTabs(self::SETTINGS_SUB_TAB_ID_EXTERNAL);

        if ($form === null) {
            $form = $this->getExternalSettingsForm();
            $this->populateExternalSettingsForm($form);
        }

        if ($this->user->getEmail() !== '') {
            $btn = ilLinkButton::getInstance();
            $btn->setUrl($this->ctrl->getLinkTarget($this, 'sendTestUserMail'));
            $btn->setCaption('mail_external_send_test_usr');
            $this->toolbar->addButtonInstance($btn);

            $btn = ilLinkButton::getInstance();
            $btn->setUrl($this->ctrl->getLinkTarget($this, 'sendTestSystemMail'));
            $btn->setCaption('mail_external_send_test_sys');
            $this->toolbar->addButtonInstance($btn);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function sendTestUserMailObject(): void
    {
        $this->sendTestMail(true);
    }

    protected function sendTestSystemMailObject(): void
    {
        $this->sendTestMail();
    }

    protected function sendTestMail(bool $isManualMail = false): void
    {
        if (!$this->isViewAllowed()) {
            $this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
        }

        if ($this->user->getEmail() === '') {
            $this->showExternalSettingsFormObject();
            return;
        }

        if ($isManualMail) {
            $mail = new ilMail($this->user->getId());
        } else {
            $mail = new ilMail(ANONYMOUS_USER_ID);
        }

        $mail->setSaveInSentbox(false);
        $mail->appendInstallationSignature(true);

        $lngVariablePrefix = 'sys';
        if ($isManualMail) {
            $lngVariablePrefix = 'usr';
        }

        $mail->enqueue(
            $this->user->getEmail(),
            '',
            '',
            $this->lng->txt('mail_email_' . $lngVariablePrefix . '_subject'),
            $this->lng->txt('mail_email_' . $lngVariablePrefix . '_body'),
            []
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_external_test_sent'));
        $this->showExternalSettingsFormObject();
    }

    protected function getExternalSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $form->setFormAction($this->ctrl->getFormAction($this, 'saveExternalSettingsForm'));
        $form->setTitle($this->lng->txt('mail_settings_external_frm_head'));

        $smtp = new ilCheckboxInputGUI($this->lng->txt('mail_smtp_status'), 'mail_smtp_status');
        $smtp->setInfo($this->lng->txt('mail_smtp_status_info'));
        $smtp->setValue('1');
        $smtp->setDisabled(!$this->isEditingAllowed());
        $form->addItem($smtp);

        $host = new ilTextInputGUI($this->lng->txt('mail_smtp_host'), 'mail_smtp_host');
        $host->setInfo($this->lng->txt('mail_smtp_host_info'));
        $host->setRequired(true);
        $host->setDisabled(!$this->isEditingAllowed());
        $smtp->addSubItem($host);

        $port = new ilNumberInputGUI($this->lng->txt('mail_smtp_port'), 'mail_smtp_port');
        $port->setInfo($this->lng->txt('mail_smtp_port_info'));
        $port->allowDecimals(false);
        $port->setMinValue(0);
        $port->setMinValue(0);
        $port->setRequired(true);
        $port->setDisabled(!$this->isEditingAllowed());
        $smtp->addSubItem($port);

        $encryption = new ilSelectInputGUI(
            $this->lng->txt('mail_smtp_encryption'),
            'mail_smtp_encryption'
        );
        $encryptionOptions = [
            '' => $this->lng->txt('please_choose'),
            'tls' => $this->lng->txt('mail_smtp_encryption_tls'),
            'ssl' => $this->lng->txt('mail_smtp_encryption_ssl'),
        ];

        $encryption->setOptions($encryptionOptions);
        $encryption->setDisabled(!$this->isEditingAllowed());
        $smtp->addSubItem($encryption);

        $user = new ilTextInputGUI($this->lng->txt('mail_smtp_user'), 'mail_smtp_user');
        $user->setDisabled(!$this->isEditingAllowed());
        $user->setDisableHtmlAutoComplete(true);
        $smtp->addSubItem($user);

        $password = new ilPasswordInputGUI(
            $this->lng->txt('mail_smtp_password'),
            'mail_smtp_password'
        );
        $password->setRetype(false);
        $password->setSkipSyntaxCheck(true);
        $password->setDisabled(!$this->isEditingAllowed());
        $password->setDisableHtmlAutoComplete(true);
        $smtp->addSubItem($password);

        $pre = new ilTextInputGUI($this->lng->txt('mail_subject_prefix'), 'mail_subject_prefix');
        $pre->setSize(12);
        $pre->setMaxLength(32);
        $pre->setInfo($this->lng->txt('mail_subject_prefix_info'));
        $pre->setDisabled(!$this->isEditingAllowed());
        $form->addItem($pre);

        $send_html = new ilCheckboxInputGUI($this->lng->txt('mail_send_html'), 'mail_send_html');
        $send_html->setInfo($this->lng->txt('mail_send_html_info'));
        $send_html->setValue('1');
        $send_html->setDisabled(!$this->isEditingAllowed());
        $form->addItem($send_html);

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt('mail_settings_user_frm_head'));
        $form->addItem($sh);

        $user_from_address = new ilEMailInputGUI(
            $this->lng->txt('mail_system_usr_from_addr'),
            'mail_system_usr_from_addr'
        );
        $user_from_address->setInfo($this->lng->txt('mail_system_usr_from_addr_info'));
        $user_from_address->setRequired(true);
        $user_from_address->setDisabled(!$this->isEditingAllowed());
        $form->addItem($user_from_address);

        $useGlobalReplyToAddress = new ilCheckboxInputGUI(
            $this->lng->txt('mail_use_global_reply_to_addr'),
            'use_global_reply_to_addr'
        );
        $useGlobalReplyToAddress->setInfo($this->lng->txt('mail_use_global_reply_to_addr_info'));
        $useGlobalReplyToAddress->setValue('1');
        $useGlobalReplyToAddress->setDisabled(!$this->isEditingAllowed());
        $form->addItem($useGlobalReplyToAddress);
        $globalReplyTo = new ilEMailInputGUI(
            $this->lng->txt('mail_global_reply_to_addr'),
            'global_reply_to_addr'
        );
        $globalReplyTo->setInfo($this->lng->txt('mail_global_reply_to_addr_info'));
        $globalReplyTo->setRequired(true);
        $globalReplyTo->setDisabled(!$this->isEditingAllowed());
        $useGlobalReplyToAddress->addSubItem($globalReplyTo);

        $user_from_name = new ilTextInputGUI(
            $this->lng->txt('mail_system_usr_from_name'),
            'mail_system_usr_from_name'
        );
        $user_from_name->setInfo($this->lng->txt('mail_system_usr_from_name_info'));
        $user_from_name->setRequired(true);
        $user_from_name->setDisabled(!$this->isEditingAllowed());
        $form->addItem($user_from_name);

        $user_envelope_from_addr = new ilEMailInputGUI(
            $this->lng->txt('mail_system_usr_env_from_addr'),
            'mail_system_usr_env_from_addr'
        );
        $user_envelope_from_addr->setInfo($this->lng->txt('mail_system_usr_env_from_addr_info'));
        $user_envelope_from_addr->setDisabled(!$this->isEditingAllowed());
        $form->addItem($user_envelope_from_addr);

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt('mail_settings_system_frm_head'));
        $form->addItem($sh);

        $system_from_addr = new ilEMailInputGUI(
            $this->lng->txt('mail_system_sys_from_addr'),
            'mail_system_sys_from_addr'
        );
        $system_from_addr->setInfo($this->lng->txt('mail_system_sys_from_addr_info'));
        $system_from_addr->setRequired(true);
        $system_from_addr->setDisabled(!$this->isEditingAllowed());
        $form->addItem($system_from_addr);

        $system_from_name = new ilTextInputGUI(
            $this->lng->txt('mail_system_sys_from_name'),
            'mail_system_sys_from_name'
        );
        $system_from_name->setRequired(true);
        $system_from_name->setDisabled(!$this->isEditingAllowed());
        $form->addItem($system_from_name);

        $system_reply_to_addr = new ilEMailInputGUI(
            $this->lng->txt('mail_system_sys_reply_to_addr'),
            'mail_system_sys_reply_to_addr'
        );
        $system_reply_to_addr->setRequired(true);
        $system_reply_to_addr->setDisabled(!$this->isEditingAllowed());
        $form->addItem($system_reply_to_addr);

        $system_return_path = new ilEMailInputGUI(
            $this->lng->txt('mail_system_sys_env_from_addr'),
            'mail_system_sys_env_from_addr'
        );
        $system_return_path->setInfo($this->lng->txt('mail_system_sys_env_from_addr_info'));
        $system_return_path->setDisabled(!$this->isEditingAllowed());
        $form->addItem($system_return_path);

        $signature = new ilTextAreaInputGUI(
            $this->lng->txt('mail_system_sys_signature'),
            'mail_system_sys_signature'
        );
        $signature->setRows(8);
        $signature->setDisabled(!$this->isEditingAllowed());
        $form->addItem($signature);

        $placeholders = new ilManualPlaceholderInputGUI('mail_system_sys_signature');
        $placeholder_list = [
            ['placeholder' => 'INSTALLATION_NAME', 'label' => $this->lng->txt('mail_nacc_installation_name')],
            ['placeholder' => 'INSTALLATION_DESC', 'label' => $this->lng->txt('mail_nacc_installation_desc')],
            ['placeholder' => 'ILIAS_URL', 'label' => $this->lng->txt('mail_nacc_ilias_url')],
        ];
        foreach ($placeholder_list as $value) {
            $placeholders->addPlaceholder($value['placeholder'], $value['label']);
        }
        $placeholders->setDisabled(!$this->isEditingAllowed());
        $form->addItem($placeholders);

        if ($this->isEditingAllowed()) {
            $form->addCommandButton('saveExternalSettingsForm', $this->lng->txt('save'));
        }

        return $form;
    }

    protected function populateExternalSettingsForm(ilPropertyFormGUI $form): void
    {
        $subjectPrefix = $this->settings->get('mail_subject_prefix');
        if (null === $subjectPrefix) {
            $subjectPrefix = ilMimeMail::MAIL_SUBJECT_PREFIX;
        }

        $form->setValuesByArray([
            'mail_smtp_status' => (bool) $this->settings->get('mail_smtp_status', '0'),
            'mail_smtp_host' => $this->settings->get('mail_smtp_host', ''),
            'mail_smtp_port' => $this->settings->get('mail_smtp_port', ''),
            'mail_smtp_user' => $this->settings->get('mail_smtp_user', ''),
            'mail_smtp_password' => $this->settings->get('mail_smtp_password') !== '' ?
                self::PASSWORD_PLACE_HOLDER :
                '',
            'mail_smtp_encryption' => $this->settings->get('mail_smtp_encryption', ''),
            'mail_subject_prefix' => $subjectPrefix,
            'mail_send_html' => (bool) $this->settings->get('mail_send_html', '0'),
            'mail_system_usr_from_addr' => $this->settings->get('mail_system_usr_from_addr', ''),
            'mail_system_usr_from_name' => $this->settings->get('mail_system_usr_from_name', ''),
            'mail_system_usr_env_from_addr' => $this->settings->get('mail_system_usr_env_from_addr', ''),
            'mail_system_sys_from_addr' => $this->settings->get('mail_system_sys_from_addr', ''),
            'mail_system_sys_from_name' => $this->settings->get('mail_system_sys_from_name', ''),
            'mail_system_sys_reply_to_addr' => $this->settings->get('mail_system_sys_reply_to_addr', ''),
            'mail_system_sys_env_from_addr' => $this->settings->get('mail_system_sys_env_from_addr', ''),
            'mail_system_sys_signature' => $this->settings->get('mail_system_sys_signature', ''),
            'use_global_reply_to_addr' => (bool) $this->settings->get('use_global_reply_to_addr', '0'),
            'global_reply_to_addr' => $this->settings->get('global_reply_to_addr', ''),
        ]);
    }

    protected function saveExternalSettingsFormObject(): void
    {
        if (!$this->isEditingAllowed()) {
            $this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
        }

        $form = $this->getExternalSettingsForm();
        $isFormValid = $form->checkInput();

        if (!$isFormValid) {
            $form->setValuesByPost();
            $this->showExternalSettingsFormObject($form);
            return;
        }

        $isSmtpEnabled = (bool) $form->getInput('mail_smtp_status');
        if ($isSmtpEnabled && $form->getInput('mail_smtp_user') &&
            !$form->getInput('mail_smtp_password')
        ) {
            $form->getItemByPostVar('mail_smtp_password')->setRequired(true);
            $form->getItemByPostVar('mail_smtp_password')
                 ->setAlert($this->lng->txt('mail_smtp_password_req'));
            $form->setValuesByPost();
            $this->showExternalSettingsFormObject($form);
            return;
        }

        $this->settings->set('mail_smtp_status', (string) ((int) $form->getInput('mail_smtp_status')));
        $this->settings->set('mail_smtp_host', (string) $form->getInput('mail_smtp_host'));
        $this->settings->set('mail_smtp_port', (string) ((int) $form->getInput('mail_smtp_port')));
        $this->settings->set('mail_smtp_user', (string) $form->getInput('mail_smtp_user'));
        if ($form->getInput('mail_smtp_password') !== self::PASSWORD_PLACE_HOLDER) {
            $this->settings->set('mail_smtp_password', (string) $form->getInput('mail_smtp_password'));
        }
        $this->settings->set('mail_smtp_encryption', (string) $form->getInput('mail_smtp_encryption'));
        $this->settings->set('mail_send_html', (string) $form->getInput('mail_send_html'));
        $this->settings->set('mail_subject_prefix', (string) $form->getInput('mail_subject_prefix'));
        $this->settings->set('mail_system_usr_from_addr', (string) $form->getInput('mail_system_usr_from_addr'));
        $this->settings->set('mail_system_usr_from_name', (string) $form->getInput('mail_system_usr_from_name'));
        $this->settings->set(
            'mail_system_usr_env_from_addr',
            (string) $form->getInput('mail_system_usr_env_from_addr')
        );
        $this->settings->set(
            'mail_system_sys_from_addr',
            (string) $form->getInput('mail_system_sys_from_addr')
        );
        $this->settings->set('mail_system_sys_from_name', (string) $form->getInput('mail_system_sys_from_name'));
        $this->settings->set(
            'mail_system_sys_reply_to_addr',
            (string) $form->getInput('mail_system_sys_reply_to_addr')
        );
        $this->settings->set(
            'mail_system_sys_env_from_addr',
            (string) $form->getInput('mail_system_sys_env_from_addr')
        );
        $this->settings->set('use_global_reply_to_addr', (string) ((int) $form->getInput('use_global_reply_to_addr')));
        $this->settings->set('global_reply_to_addr', (string) $form->getInput('global_reply_to_addr'));
        $this->settings->set('mail_system_sys_signature', (string) $form->getInput('mail_system_sys_signature'));

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'showExternalSettingsForm');
    }

    public static function _goto(string $target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $mail = new ilMail($DIC->user()->getId());

        if ($DIC->rbac()->system()->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
            $DIC->ctrl()->redirectToURL('ilias.php?baseClass=ilMailGUI');
        } elseif ($DIC->access()->checkAccess('read', '', ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $DIC->language()->txt('msg_no_perm_read_item'),
                ilObject::_lookupTitle(ilObject::_lookupObjId((int) $target))
            ), true);

            $DIC->ctrl()->setTargetScript('ilias.php');
            $DIC->ctrl()->setParameterByClass(ilRepositoryGUI::class, 'ref_id', ROOT_FOLDER_ID);
            $DIC->ctrl()->redirectByClass(ilRepositoryGUI::class);
        }

        $DIC['ilErr']->raiseError($DIC->language()->txt('msg_no_perm_read'), $DIC['ilErr']->FATAL);
    }
}
