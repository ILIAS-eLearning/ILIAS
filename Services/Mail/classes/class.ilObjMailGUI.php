<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author       Stefan Meyer <meyer@leifos.com>
 * @author       Michael Jansen <mjansen@databay.de>
 * @ilCtrl_Calls ilObjMailGUI: ilPermissionGUI
 */
class ilObjMailGUI extends ilObjectGUI
{
    const SETTINGS_SUB_TAB_ID_GENERAL = 1;
    const SETTINGS_SUB_TAB_ID_EXTERNAL = 2;

    const PASSWORD_PLACE_HOLDER = '***********************';

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * ilObjMailGUI constructor.
     * @param      $a_data
     * @param int  $a_id
     * @param bool $a_call_by_reference
     */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->type = 'mail';
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->tabs          = $DIC->tabs();
        $this->rbacsystem    = $DIC->rbac()->system();
        $this->settings      = $DIC['ilSetting'];

        $this->lng->loadLanguageModule('mail');
    }

    /**
     * @inheritdoc
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd        = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilmailtemplategui':
                if (!$this->isViewAllowed()) {
                    $this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
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

        return true;
    }

    /**
     * @return bool
     */
    private function isEditingAllowed()
    {
        return $this->rbacsystem->checkAccess('write', $this->object->getRefId());
    }

    /**
     * @return bool
     */
    private function isViewAllowed()
    {
        return $this->rbacsystem->checkAccess('read', $this->object->getRefId());
    }

    /**
     * @return bool
     */
    private function isPermissionChangeAllowed()
    {
        return $this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId());
    }

    /**
     * @inheritdoc
     */
    public function getAdminTabs()
    {
        $this->getTabs();
    }

    /**
     * @inheritdoc
     */
    protected function getTabs()
    {
        if ($this->isViewAllowed()) {
            $this->tabs->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'view'),
                array(
                'view',
                'save',
                '',
                'showExternalSettingsForm',
                'saveExternalSettingsForm',
                'sendTestUserMail',
                'sendTestSystemMail'
            ),
                '',
                ''
            );
        }

        if ($this->isViewAllowed()) {
            $this->tabs->addTarget(
                'mail_templates',
                $this->ctrl->getLinkTargetByClass('ilmailtemplategui', 'showTemplates'),
                '',
                'ilmailtemplategui'
            );
        }

        if ($this->isPermissionChangeAllowed()) {
            $this->tabs->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), 'perm'),
                array('perm', 'info', 'owner'),
                'ilpermissiongui'
            );
        }
    }

    /**
     * @param int $activeSubTab
     */
    protected function buildSettingsSubTabs($activeSubTab)
    {
        if ($this->isViewAllowed()) {
            $this->tabs->addSubTab(
                self::SETTINGS_SUB_TAB_ID_GENERAL,
                $this->lng->txt('mail_settings_general_tab'),
                $this->ctrl->getLinkTarget($this, 'view')
            );

            if ($this->settings->get('mail_allow_external')) {
                $this->tabs->addSubTab(
                    self::SETTINGS_SUB_TAB_ID_EXTERNAL,
                    $this->lng->txt('mail_settings_external_tab'),
                    $this->ctrl->getLinkTarget($this, 'showExternalSettingsForm')
                );
            }

            $this->tabs->activateSubTab($activeSubTab);
        }
    }

    /**
     * @inheritdoc
     */
    public function viewObject()
    {
        $this->showGeneralSettingsForm();
    }

    /**
     * @param ilPropertyFormGUI|null $form
     */
    protected function showGeneralSettingsForm(ilPropertyFormGUI $form = null)
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

    /**
     * @return \ilPropertyFormGUI
     */
    protected function getGeneralSettingsForm()
    {
        $form = new ilPropertyFormGUI();

        $form->setFormAction($this->ctrl->getFormAction($this, 'save'));
        $form->setTitle($this->lng->txt('general_settings'));

        $cb = new ilCheckboxInputGUI($this->lng->txt('mail_allow_external'), 'mail_allow_external');
        $cb->setInfo($this->lng->txt('mail_allow_external_info'));
        $cb->setValue(1);
        $cb->setDisabled(!$this->isEditingAllowed());
        $form->addItem($cb);
        
        include_once 'Services/Mail/classes/Form/class.ilIncomingMailInputGUI.php';
        $incoming_mail_gui = new ilIncomingMailInputGUI($this->lng->txt('mail_incoming'), 'incoming_type');
        $incoming_mail_gui->setDisabled(!$this->isEditingAllowed());
        $this->ctrl->setParameterByClass('ilobjuserfoldergui', 'ref_id', USER_FOLDER_ID);
        $incoming_mail_gui->setInfo(sprintf(
            $this->lng->txt('mail_settings_incoming_type_see_also'),
            $this->ctrl->getLinkTargetByClass('ilobjuserfoldergui', 'settings')
        ));
        $this->ctrl->clearParametersByClass('ilobjuserfoldergui');
        $form->addItem($incoming_mail_gui);

        $show_mail_settings_gui = new ilCheckboxInputGUI($this->lng->txt('show_mail_settings'), 'show_mail_settings');
        $show_mail_settings_gui->setInfo($this->lng->txt('show_mail_settings_info'));
        $show_mail_settings_gui->setValue(1);
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

        $cron_mail    = new ilSelectInputGUI($this->lng->txt('cron_mail_notification'), 'mail_notification');
        $cron_options = array(
            0 => $this->lng->txt('cron_mail_notification_never'),
            1 => $this->lng->txt('cron_mail_notification_cron')
        );
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

    /**
     * @param ilPropertyFormGUI $form
     */
    protected function populateGeneralSettingsForm(ilPropertyFormGUI $form)
    {
        $form->setValuesByArray(array(
            'mail_allow_external'      => $this->settings->get('mail_allow_external'),
            'incoming_type'            => (int) $this->settings->get('mail_incoming_mail'),
            'mail_address_option'      => strlen($this->settings->get('mail_address_option')) ? $this->settings->get('mail_address_option') : ilMailOptions::FIRST_EMAIL,
            'mail_address_option_both' => strlen($this->settings->get('mail_address_option')) ? $this->settings->get('mail_address_option') : ilMailOptions::FIRST_EMAIL,
            'show_mail_settings' => $this->settings->get('show_mail_settings', 1),
            'mail_maxsize_attach'      => $this->settings->get('mail_maxsize_attach'),
            'mail_notification'        => $this->settings->get('mail_notification')
        ));
    }

    /**
     * @inheritdoc
     */
    public function saveObject()
    {
        if (!$this->isEditingAllowed()) {
            $this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
        }

        $form = $this->getGeneralSettingsForm();
        if ($form->checkInput()) {
            $incoming_type = (int) $form->getInput('incoming_type');

            $mail_address_option = ilMailOptions::FIRST_EMAIL;
            if ($incoming_type == ilMailOptions::INCOMING_EMAIL) {
                $mail_address_option = (int) $form->getInput('mail_address_option');
            } else {
                if ($incoming_type == ilMailOptions::INCOMING_BOTH) {
                    $mail_address_option = (int) $form->getInput('mail_address_option_both');
                }
            }

            $this->settings->set('mail_allow_external', (int) $form->getInput('mail_allow_external'));
            $this->settings->set('mail_incoming_mail', $incoming_type);
            $this->settings->set('show_mail_settings', (int) $form->getInput('show_mail_settings'));

            $this->settings->set('mail_address_option', $mail_address_option);
            $this->settings->set('mail_maxsize_attach', $form->getInput('mail_maxsize_attach'));
            $this->settings->set('mail_notification', (int) $form->getInput('mail_notification'));

            ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this);
        }

        $form->setValuesByPost();
        $this->showGeneralSettingsForm($form);
    }

    /**
     * @param ilPropertyFormGUI|null $form
     */
    protected function showExternalSettingsFormObject(ilPropertyFormGUI $form = null)
    {
        if (!$this->isViewAllowed()) {
            $this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
        }

        $this->buildSettingsSubTabs(self::SETTINGS_SUB_TAB_ID_EXTERNAL);

        if ($form === null) {
            $form = $this->getExternalSettingsForm();
            $this->populateExternalSettingsForm($form);
        }

        if (strlen($GLOBALS['DIC']->user()->getEmail()) > 0) {
            $btn = ilLinkButton::getInstance();
            $btn->setUrl($this->ctrl->getLinkTarget($this, 'sendTestUserMail'));
            $btn->setCaption('mail_external_send_test_usr');
            $GLOBALS['DIC']->toolbar()->addButtonInstance($btn);

            $btn = ilLinkButton::getInstance();
            $btn->setUrl($this->ctrl->getLinkTarget($this, 'sendTestSystemMail'));
            $btn->setCaption('mail_external_send_test_sys');
            $GLOBALS['DIC']->toolbar()->addButtonInstance($btn);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function sendTestUserMailObject()
    {
        $this->sendTestMail(true);
    }

    protected function sendTestSystemMailObject()
    {
        $this->sendTestMail();
    }

    /**
     * @param bool $is_manual_mail
     */
    protected function sendTestMail($is_manual_mail = false)
    {
        if (!$this->isViewAllowed()) {
            $this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
        }

        if (strlen($GLOBALS['DIC']->user()->getEmail()) == 0) {
            return $this->showExternalSettingsFormObject();
        }

        if ($is_manual_mail) {
            $mail = new ilMail($GLOBALS['DIC']->user()->getId());
            $type = array('normal');
        } else {
            $mail = new ilMail(ANONYMOUS_USER_ID);
            $type = array('system');
        }

        $mail->setSaveInSentbox(false);
        $mail->appendInstallationSignature(true);
        $mail->sendMail($GLOBALS['DIC']->user()->getEmail(), '', '', 'Test Subject', 'Test Body', array(), $type);

        ilUtil::sendSuccess($this->lng->txt('mail_external_test_sent'));
        $this->showExternalSettingsFormObject();
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getExternalSettingsForm()
    {
        $form = new ilPropertyFormGUI();

        $form->setFormAction($this->ctrl->getFormAction($this, 'saveExternalSettingsForm'));
        $form->setTitle($this->lng->txt('mail_settings_external_frm_head'));

        $smtp = new ilCheckboxInputGUI($this->lng->txt('mail_smtp_status'), 'mail_smtp_status');
        $smtp->setInfo($this->lng->txt('mail_smtp_status_info'));
        $smtp->setValue(1);
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

        $encryption        = new ilSelectInputGUI($this->lng->txt('mail_smtp_encryption'), 'mail_smtp_encryption');
        $encryptionOptions = array(
            ''    => $this->lng->txt('please_choose'),
            'tls' => $this->lng->txt('mail_smtp_encryption_tls'),
            'ssl' => $this->lng->txt('mail_smtp_encryption_ssl')
        );

        $encryption->setOptions($encryptionOptions);
        $encryption->setDisabled(!$this->isEditingAllowed());
        $smtp->addSubItem($encryption);

        $user = new ilTextInputGUI($this->lng->txt('mail_smtp_user'), 'mail_smtp_user');
        $user->setDisabled(!$this->isEditingAllowed());
        $smtp->addSubItem($user);

        $password = new ilPasswordInputGUI($this->lng->txt('mail_smtp_password'), 'mail_smtp_password');
        $password->setRetype(false);
        $password->setSkipSyntaxCheck(true);
        $password->setDisabled(!$this->isEditingAllowed());
        $smtp->addSubItem($password);

        $pre = new ilTextInputGUI($this->lng->txt('mail_subject_prefix'), 'mail_subject_prefix');
        $pre->setSize(12);
        $pre->setMaxLength(32);
        $pre->setInfo($this->lng->txt('mail_subject_prefix_info'));
        $pre->setDisabled(!$this->isEditingAllowed());
        $form->addItem($pre);

        $send_html = new ilCheckboxInputGUI($this->lng->txt('mail_send_html'), 'mail_send_html');
        $send_html->setInfo($this->lng->txt('mail_send_html_info'));
        $send_html->setValue(1);
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

        $user_from_name = new ilTextInputGUI($this->lng->txt('mail_system_usr_from_name'), 'mail_system_usr_from_name');
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

        $signature = new ilTextAreaInputGUI($this->lng->txt('mail_system_sys_signature'), 'mail_system_sys_signature');
        $signature->setRows(8);
        $signature->setDisabled(!$this->isEditingAllowed());
        $form->addItem($signature);

        $placeholders = new ilManualPlaceholderInputGUI('mail_system_sys_signature');
        foreach (array(
                     array('placeholder' => 'CLIENT_NAME', 'label' => $this->lng->txt('mail_nacc_client_name')),
                     array('placeholder' => 'CLIENT_DESC', 'label' => $this->lng->txt('mail_nacc_client_desc')),
                     array('placeholder' => 'CLIENT_URL', 'label' => $this->lng->txt('mail_nacc_ilias_url'))
                 ) as $key => $value) {
            $placeholders->addPlaceholder($value['placeholder'], $value['label']);
        }
        $placeholders->setDisabled(!$this->isEditingAllowed());
        $form->addItem($placeholders);

        if ($this->isEditingAllowed()) {
            $form->addCommandButton('saveExternalSettingsForm', $this->lng->txt('save'));
        }

        return $form;
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    protected function populateExternalSettingsForm(ilPropertyFormGUI $form)
    {
        $subjectPrefix = $this->settings->get('mail_subject_prefix');
        if (false === $subjectPrefix) {
            $subjectPrefix = ilMail::MAIL_SUBJECT_PREFIX;
        }

        $form->setValuesByArray(array(
            'mail_smtp_status'              => (bool) $this->settings->get('mail_smtp_status'),
            'mail_smtp_host'                => $this->settings->get('mail_smtp_host'),
            'mail_smtp_port'                => (int) $this->settings->get('mail_smtp_port'),
            'mail_smtp_user'                => $this->settings->get('mail_smtp_user'),
            'mail_smtp_password'            => strlen($this->settings->get('mail_smtp_password')) > 0 ? self::PASSWORD_PLACE_HOLDER : '',
            'mail_smtp_encryption'          => $this->settings->get('mail_smtp_encryption'),
            'mail_subject_prefix'           => $subjectPrefix,
            'mail_send_html'                => (int) $this->settings->get('mail_send_html'),
            'mail_system_usr_from_addr'     => $this->settings->get('mail_system_usr_from_addr'),
            'mail_system_usr_from_name'     => $this->settings->get('mail_system_usr_from_name'),
            'mail_system_usr_env_from_addr' => $this->settings->get('mail_system_usr_env_from_addr'),
            'mail_system_sys_from_addr'     => $this->settings->get('mail_system_sys_from_addr'),
            'mail_system_sys_from_name'     => $this->settings->get('mail_system_sys_from_name'),
            'mail_system_sys_reply_to_addr' => $this->settings->get('mail_system_sys_reply_to_addr'),
            'mail_system_sys_env_from_addr' => $this->settings->get('mail_system_sys_env_from_addr'),
            'mail_system_sys_signature'     => $this->settings->get('mail_system_sys_signature')
        ));
    }

    /**
     *
     */
    protected function saveExternalSettingsFormObject()
    {
        if (!$this->isEditingAllowed()) {
            $this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
        }

        $form        = $this->getExternalSettingsForm();
        $isFormValid = $form->checkInput();

        if (!$isFormValid) {
            $form->setValuesByPost();
            $this->showExternalSettingsFormObject($form);
            return;
        }

        $isSmtpEnabled = (bool) $form->getInput('mail_smtp_status');
        if ($isSmtpEnabled && $form->getInput('mail_smtp_user') && !$form->getInput('mail_smtp_password')) {
            $form->getItemByPostVar('mail_smtp_password')->setRequired(true);
            $form->getItemByPostVar('mail_smtp_password')->setAlert($this->lng->txt('mail_smtp_password_req'));
            $form->setValuesByPost();
            $this->showExternalSettingsFormObject($form);
            return;
        }

        $this->settings->set('mail_smtp_status', (int) $form->getInput('mail_smtp_status'));
        $this->settings->set('mail_smtp_host', $form->getInput('mail_smtp_host'));
        $this->settings->set('mail_smtp_port', (int) $form->getInput('mail_smtp_port'));
        $this->settings->set('mail_smtp_user', $form->getInput('mail_smtp_user'));
        if ($form->getInput('mail_smtp_password') != self::PASSWORD_PLACE_HOLDER) {
            $this->settings->set('mail_smtp_password', $form->getInput('mail_smtp_password'));
        }
        $this->settings->set('mail_smtp_encryption', $form->getInput('mail_smtp_encryption'));

        $this->settings->set('mail_send_html', $form->getInput('mail_send_html'));
        $this->settings->set('mail_subject_prefix', $form->getInput('mail_subject_prefix'));
        $this->settings->set('mail_system_usr_from_addr', $form->getInput('mail_system_usr_from_addr'));
        $this->settings->set('mail_system_usr_from_name', $form->getInput('mail_system_usr_from_name'));
        $this->settings->set('mail_system_usr_env_from_addr', $form->getInput('mail_system_usr_env_from_addr'));
        $this->settings->set('mail_system_sys_from_addr', $form->getInput('mail_system_sys_from_addr'));
        $this->settings->set('mail_system_sys_from_name', $form->getInput('mail_system_sys_from_name'));
        $this->settings->set('mail_system_sys_reply_to_addr', $form->getInput('mail_system_sys_reply_to_addr'));
        $this->settings->set('mail_system_sys_env_from_addr', $form->getInput('mail_system_sys_env_from_addr'));
        $this->settings->set('mail_system_sys_signature', $form->getInput('mail_system_sys_signature'));

        ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'showExternalSettingsForm');
    }

    /**
     * @param string $a_target
     */
    public static function _goto($a_target)
    {
        global $DIC;

        $mail = new ilMail($DIC->user()->getId());

        if ($DIC->rbac()->system()->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
            ilUtil::redirect('ilias.php?baseClass=ilMailGUI');
        } else {
            if ($DIC->access()->checkAccess('read', '', ROOT_FOLDER_ID)) {
                $_GET['cmd']       = 'frameset';
                $_GET['target']    = '';
                $_GET['ref_id']    = ROOT_FOLDER_ID;
                $_GET['baseClass'] = 'ilRepositoryGUI';
                ilUtil::sendFailure(
                    sprintf(
                        $DIC->language()->txt('msg_no_perm_read_item'),
                        ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                    ),
                    true
                );

                include 'ilias.php';
                exit();
            }
        }

        $DIC['ilErr']->raiseError($DIC->language()->txt('msg_no_perm_read'), $DIC['ilErr']->FATAL);
    }
}
