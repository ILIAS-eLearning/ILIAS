<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Forum Administration Settings.
 * @author            Nadia Matuschek <nmatuschek@databay.de>
 * @version           $Id:$
 * @ilCtrl_Calls      ilObjForumAdministrationGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjForumAdministrationGUI: ilAdministrationGUI
 * @ingroup           ModulesForum
 */
class ilObjForumAdministrationGUI extends ilObjectGUI
{
    private $rbac;

    /**
     * Contructor
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;
        $this->rbac = $DIC->rbac();

        $this->type = 'frma';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule('forum');
    }

    /**
     * @return bool
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd        = $this->ctrl->getCmd();

        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = 'editSettings';
                }

                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     *
     */
    public function getAdminTabs()
    {
        if ($this->rbac->system()->checkAccess('visible,read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'editSettings'),
                array('editSettings', 'view')
            );
        }

        if ($this->rbac->system()->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'),
                array(),
                'ilpermissiongui'
            );
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function editSettings(ilPropertyFormGUI $form = null)
    {
        $this->tabs_gui->activateTab('settings');

        if (!$form) {
            $form = $this->getSettingsForm();
            $this->populateForm($form);
        }

        $this->tpl->setContent($form->getHtml());
    }

    /**
     * Save settings
     */
    public function saveSettings()
    {
        $this->checkPermission("write");

        $form = $this->getSettingsForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editSettings($form);
            return;
        }

        $frma_set = new ilSetting('frma');
        $frma_set->set('forum_overview', $form->getInput('forum_overview'));
        $this->settings->set('file_upload_allowed_fora', (int) $form->getInput('file_upload_allowed_fora'));
        $this->settings->set('send_attachments_by_mail', (int) $form->getInput('send_attachments_by_mail'));
        $this->settings->set('enable_fora_statistics', (int) $form->getInput('fora_statistics'));
        $this->settings->set('enable_anonymous_fora', (int) $form->getInput('anonymous_fora'));

        if (!ilCronManager::isJobActive('frm_notification')) {
            $this->settings->set('forum_notification', (int) $form->getInput('forum_notification'));
        }

        ilCaptchaUtil::setActiveForForum((bool) $form->getInput('activate_captcha_anonym'));

        $this->settings->set('save_post_drafts', (int) $form->getInput('save_post_drafts'));
        $this->settings->set('autosave_drafts', (int) $form->getInput('autosave_drafts'));
        $this->settings->set('autosave_drafts_ival', (int) $form->getInput('autosave_drafts_ival'));
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $form->setValuesByPost();
        $this->editSettings($form);
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    protected function populateForm(ilPropertyFormGUI $form)
    {
        $frma_set = new ilSetting('frma');

        $form->setValuesByArray(array(
            'forum_overview'           => (bool) $frma_set->get('forum_overview', false),
            'fora_statistics'          => (bool) $this->settings->get('enable_fora_statistics', false),
            'anonymous_fora'           => (bool) $this->settings->get('enable_anonymous_fora', false),
            'forum_notification'       => (int) $this->settings->get('forum_notification') === 1 ? true : false,
            'activate_captcha_anonym'  => ilCaptchaUtil::isActiveForForum(),
            'file_upload_allowed_fora' => (int) $this->settings->get('file_upload_allowed_fora', ilForumProperties::FILE_UPLOAD_GLOBALLY_ALLOWED),
            'save_post_drafts'         => (int) $this->settings->get('save_post_drafts', 0),
            'autosave_drafts'          => (int) $this->settings->get('autosave_drafts', 0),
            'autosave_drafts_ival'     => (int) $this->settings->get('autosave_drafts_ival', 30),
            'send_attachments_by_mail' => (bool) $this->settings->get('send_attachments_by_mail', false)
        ));
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getSettingsForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));
        $form->setTitle($this->lng->txt('settings'));

        $frm_radio = new ilRadioGroupInputGUI($this->lng->txt('frm_displayed_infos'), 'forum_overview');
        $frm_radio->addOption(new ilRadioOption($this->lng->txt('new') . ', ' . $this->lng->txt('is_read') . ', ' . $this->lng->txt('unread'), '0'));
        $frm_radio->addOption(new ilRadioOption($this->lng->txt('is_read') . ', ' . $this->lng->txt('unread'), '1'));
        $frm_radio->setInfo($this->lng->txt('frm_disp_info_desc'));
        $form->addItem($frm_radio);

        $check = new ilCheckboxInputGui($this->lng->txt('enable_fora_statistics'), 'fora_statistics');
        $check->setInfo($this->lng->txt('enable_fora_statistics_desc'));
        $form->addItem($check);

        $check = new ilCheckboxInputGui($this->lng->txt('enable_anonymous_fora'), 'anonymous_fora');
        $check->setInfo($this->lng->txt('enable_anonymous_fora_desc'));
        $form->addItem($check);

        $file_upload = new ilRadioGroupInputGUI($this->lng->txt('file_upload_allowed_fora'), 'file_upload_allowed_fora');
        $file_upload->addOption(new ilRadioOption($this->lng->txt('file_upload_option_allow'), ilForumProperties::FILE_UPLOAD_GLOBALLY_ALLOWED));
        $file_upload->addOption(new ilRadioOption($this->lng->txt('file_upload_option_disallow'), ilForumProperties::FILE_UPLOAD_INDIVIDUAL));
        $file_upload->setInfo($this->lng->txt('file_upload_allowed_fora_desc'));
        $form->addItem($file_upload);

        if (ilCronManager::isJobActive('frm_notification')) {
            ilAdministrationSettingsFormHandler::addFieldsToForm(
                ilAdministrationSettingsFormHandler::FORM_FORUM,
                $form,
                $this
            );
        } else {
            $notifications = new ilCheckboxInputGui($this->lng->txt('cron_forum_notification'), 'forum_notification');
            $notifications->setInfo($this->lng->txt('cron_forum_notification_desc'));
            $notifications->setValue(1);
            $form->addItem($notifications);
        }

        $check = new ilCheckboxInputGui($this->lng->txt('enable_send_attachments'), 'send_attachments_by_mail');
        $check->setInfo($this->lng->txt('enable_send_attachments_desc'));
        $check->setValue(1);
        $form->addItem($check);

        $cap = new ilCheckboxInputGUI($this->lng->txt('adm_captcha_anonymous_short'), 'activate_captcha_anonym');
        $cap->setInfo($this->lng->txt('adm_captcha_anonymous_frm'));
        $cap->setValue(1);
        if (!ilCaptchaUtil::checkFreetype()) {
            $cap->setAlert(ilCaptchaUtil::getPreconditionsMessage());
        }
        $form->addItem($cap);

        $drafts = new ilCheckboxInputGUI($this->lng->txt('adm_save_drafts'), 'save_post_drafts');
        $drafts->setInfo($this->lng->txt('adm_save_drafts_desc'));
        $drafts->setValue(1);

        $autosave_drafts = new ilCheckboxInputGUI($this->lng->txt('adm_autosave_drafts'), 'autosave_drafts');
        $autosave_drafts->setInfo($this->lng->txt('adm_autosave_drafts_desc'));
        $autosave_drafts->setValue(1);

        $autosave_interval = new ilNumberInputGUI($this->lng->txt('adm_autosave_ival'), 'autosave_drafts_ival');
        $autosave_interval->allowDecimals(false);
        $autosave_interval->setMinValue(30);
        $autosave_interval->setMaxValue(60 * 60);
        $autosave_interval->setSize(10);
        $autosave_interval->setSuffix($this->lng->txt('seconds'));
        $autosave_drafts->addSubItem($autosave_interval);
        $drafts->addSubItem($autosave_drafts);
        $form->addItem($drafts);

        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
        }

        return $form;
    }

    /**
     * @param string $a_form_id
     * @return array
     */
    public function addToExternalSettingsForm($a_form_id)
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_PRIVACY:

                $fields = array(
                    'enable_fora_statistics' => array($this->settings->get('enable_fora_statistics', false), ilAdministrationSettingsFormHandler::VALUE_BOOL),
                    'enable_anonymous_fora'  => array($this->settings->get('enable_anonymous_fora', false), ilAdministrationSettingsFormHandler::VALUE_BOOL)
                );

                return array(array("editSettings", $fields));

            case ilAdministrationSettingsFormHandler::FORM_ACCESSIBILITY:
                $fields = array(
                    'adm_captcha_anonymous_short' => array(ilCaptchaUtil::isActiveForForum(), ilAdministrationSettingsFormHandler::VALUE_BOOL)
                );

                return array('obj_frma' => array('editSettings', $fields));
        }
    }
}
