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
 * Forum Administration Settings.
 * @author            Nadia Matuschek <nmatuschek@databay.de>
 * @ilCtrl_Calls      ilObjForumAdministrationGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjForumAdministrationGUI: ilAdministrationGUI
 * @ingroup           ModulesForum
 */
class ilObjForumAdministrationGUI extends ilObjectGUI
{
    private \ILIAS\DI\RBACServices $rbac;
    private ilCronManager $cronManager;

    public function __construct($a_data, int $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        /**
         * @var $DIC \ILIAS\DI\Container
         */
        global $DIC;

        $this->rbac = $DIC->rbac();
        $this->cronManager = $DIC->cron()->manager();

        $this->type = 'frma';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule('forum');
    }

    public function executeCommand(): void
    {
        if (!$this->rbac->system()->checkAccess('visible,read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                break;

            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = 'editSettings';
                }

                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs(): void
    {
        if ($this->rbac->system()->checkAccess('visible,read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'editSettings'),
                ['editSettings', 'view']
            );
        }

        if ($this->rbac->system()->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass(ilPermissionGUI::class, 'perm'),
                [],
                'ilpermissiongui'
            );
        }
    }

    public function editSettings(ilPropertyFormGUI $form = null): void
    {
        $this->tabs_gui->activateTab('settings');

        if ($form === null) {
            $form = $this->getSettingsForm();
            $this->populateForm($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    public function saveSettings(): void
    {
        $this->checkPermission("write");

        $form = $this->getSettingsForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editSettings($form);
            return;
        }

        $frma_set = new ilSetting('frma');
        $frma_set->set('forum_overview', (string) $form->getInput('forum_overview'));
        $this->settings->set('file_upload_allowed_fora', (string) ((int) $form->getInput('file_upload_allowed_fora')));
        $this->settings->set('send_attachments_by_mail', (string) ((int) $form->getInput('send_attachments_by_mail')));
        $this->settings->set('enable_fora_statistics', (string) ((int) $form->getInput('fora_statistics')));
        $this->settings->set('enable_anonymous_fora', (string) ((int) $form->getInput('anonymous_fora')));

        if (!$this->cronManager->isJobActive('frm_notification')) {
            $this->settings->set('forum_notification', (string) ((int) $form->getInput('forum_notification')));
        }

        $this->settings->set('save_post_drafts', (string) ((int) $form->getInput('save_post_drafts')));
        $this->settings->set('autosave_drafts', (string) ((int) $form->getInput('autosave_drafts')));
        $this->settings->set('autosave_drafts_ival', (string) ((int) $form->getInput('autosave_drafts_ival')));

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $form->setValuesByPost();
        $this->editSettings($form);
    }

    protected function populateForm(ilPropertyFormGUI $form): void
    {
        $frma_set = new ilSetting('frma');

        $form->setValuesByArray([
            'forum_overview' => (string) $frma_set->get('forum_overview'),
            'fora_statistics' => (bool) $this->settings->get('enable_fora_statistics'),
            'anonymous_fora' => (bool) $this->settings->get('enable_anonymous_fora'),
            'forum_notification' => (int) $this->settings->get('forum_notification', '0') === 1,
            'file_upload_allowed_fora' => (int) $this->settings->get(
                'file_upload_allowed_fora',
                (string) ilForumProperties::FILE_UPLOAD_GLOBALLY_ALLOWED
            ),
            'save_post_drafts' => (int) $this->settings->get('save_post_drafts', '0'),
            'autosave_drafts' => (int) $this->settings->get('autosave_drafts', '0'),
            'autosave_drafts_ival' => (int) $this->settings->get('autosave_drafts_ival', '30'),
            'send_attachments_by_mail' => (bool) $this->settings->get('send_attachments_by_mail')
        ]);
    }

    protected function getSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));
        $form->setTitle($this->lng->txt('settings'));

        $frm_radio = new ilRadioGroupInputGUI($this->lng->txt('frm_displayed_infos'), 'forum_overview');
        $frm_radio->addOption(new ilRadioOption(
            $this->lng->txt('frm_all_postings_stats') . ', ' . $this->lng->txt('unread') . ', ' . $this->lng->txt('new'),
            (string) ilForumProperties::FORUM_OVERVIEW_WITH_NEW_POSTS
        ));
        $frm_radio->addOption(new ilRadioOption(
            $this->lng->txt('frm_all_postings_stats') . ', ' . $this->lng->txt('unread'),
            (string) ilForumProperties::FORUM_OVERVIEW_NO_NEW_POSTS
        ));
        $frm_radio->setInfo($this->lng->txt('frm_disp_info_desc'));
        $form->addItem($frm_radio);

        $check = new ilCheckboxInputGUI($this->lng->txt('enable_fora_statistics'), 'fora_statistics');
        $check->setInfo($this->lng->txt('enable_fora_statistics_desc'));
        $form->addItem($check);

        $check = new ilCheckboxInputGUI($this->lng->txt('enable_anonymous_fora'), 'anonymous_fora');
        $check->setInfo($this->lng->txt('enable_anonymous_fora_desc'));
        $form->addItem($check);

        $file_upload = new ilRadioGroupInputGUI(
            $this->lng->txt('file_upload_allowed_fora'),
            'file_upload_allowed_fora'
        );
        $option_all_forums = new ilRadioOption(
            $this->lng->txt('file_upload_option_allow'),
            (string) ilForumProperties::FILE_UPLOAD_GLOBALLY_ALLOWED,
            $this->lng->txt('file_upload_option_allow_info')
        );
        $file_upload->addOption($option_all_forums);

        $option_per_forum = new ilRadioOption(
            $this->lng->txt('file_upload_option_disallow'),
            (string) ilForumProperties::FILE_UPLOAD_INDIVIDUAL,
            $this->lng->txt('file_upload_allowed_fora_desc')
        );
        $file_upload->addOption($option_per_forum);

        $form->addItem($file_upload);

        if ($this->cronManager->isJobActive('frm_notification')) {
            ilAdministrationSettingsFormHandler::addFieldsToForm(
                ilAdministrationSettingsFormHandler::FORM_FORUM,
                $form,
                $this
            );
        } else {
            $notifications = new ilCheckboxInputGUI($this->lng->txt('cron_forum_notification'), 'forum_notification');
            $notifications->setInfo($this->lng->txt('cron_forum_notification_desc'));
            $notifications->setValue('1');
            $form->addItem($notifications);
        }

        $check = new ilCheckboxInputGUI($this->lng->txt('enable_send_attachments'), 'send_attachments_by_mail');
        $check->setInfo($this->lng->txt('enable_send_attachments_desc'));
        $check->setValue('1');
        $form->addItem($check);

        $drafts = new ilCheckboxInputGUI($this->lng->txt('adm_save_drafts'), 'save_post_drafts');
        $drafts->setInfo($this->lng->txt('adm_save_drafts_desc'));
        $drafts->setValue('1');

        $autosave_drafts = new ilCheckboxInputGUI($this->lng->txt('adm_autosave_drafts'), 'autosave_drafts');
        $autosave_drafts->setInfo($this->lng->txt('adm_autosave_drafts_desc'));
        $autosave_drafts->setValue('1');

        $autosave_interval = new ilNumberInputGUI($this->lng->txt('adm_autosave_ival'), 'autosave_drafts_ival');
        $autosave_interval->allowDecimals(false);
        $autosave_interval->setMinValue(30);
        $autosave_interval->setMaxValue(60 * 60);
        $autosave_interval->setSize(10);
        $autosave_interval->setRequired(true);
        $autosave_interval->setSuffix($this->lng->txt('seconds'));
        $autosave_drafts->addSubItem($autosave_interval);
        $drafts->addSubItem($autosave_drafts);
        $form->addItem($drafts);

        if ($this->checkPermissionBool('write')) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
        }

        return $form;
    }

    public function addToExternalSettingsForm(int $a_form_id): array
    {
        if ($a_form_id === ilAdministrationSettingsFormHandler::FORM_PRIVACY) {
            $fields = [
                'enable_fora_statistics' => [
                    (bool) $this->settings->get('enable_fora_statistics', '0'),
                    ilAdministrationSettingsFormHandler::VALUE_BOOL
                ],
                'enable_anonymous_fora' => [
                    (bool) $this->settings->get('enable_anonymous_fora', '0'),
                    ilAdministrationSettingsFormHandler::VALUE_BOOL
                ]
            ];
            return [['editSettings', $fields]];
        }
        return [];
    }
}
