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

use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Component;
use ILIAS\Data\Result\Ok;

/**
 * Forum Administration Settings.
 * @author            Nadia Matuschek <nmatuschek@databay.de>
 * @ilCtrl_Calls      ilObjForumAdministrationGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjForumAdministrationGUI: ilAdministrationGUI
 * @ingroup components\ILIASForum
 */
class ilObjForumAdministrationGUI extends ilObjectGUI
{
    private \ILIAS\DI\RBACServices $rbac;
    private ilCronManager $cronManager;
    private UIServices $ui;

    public function __construct($a_data, int $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        /**
         * @var $DIC \ILIAS\DI\Container
         */
        global $DIC;

        $this->rbac = $DIC->rbac();
        $this->cronManager = $DIC->cron()->manager();
        $this->ui = $DIC->ui();

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

    public function editSettings(Form $form = null): void
    {
        $this->tabs_gui->activateTab('settings');

        $this->tpl->setContent($this->render([
            $this->cronMessage(),
            $form ?? $this->settingsForm()
        ]));
    }

    private function render($x): string
    {
        return $this->ui->renderer()->render($x);
    }

    public function saveSettings(): void
    {
        $this->checkPermission("write");

        $form = $this->settingsForm()->withRequest($this->request);
        $data = $form->getData();
        $drafts_key = 'autosave_drafts';
        if ($data === null || $this->request->getMethod() !== 'POST' || !$this->draftsValid($data[$drafts_key])) {
            $this->editSettings($form);
            return;
        }

        $set_int = fn($key) => $this->settings->set($key, (string) ((int) $data[$key]));

        $data['forum_notification'] = $data['forum_notification'] || $this->forumJobActive();

        array_map($set_int, [
            'file_upload_allowed_fora',
            'send_attachments_by_mail',
            'enable_fora_statistics',
            'enable_anonymous_fora',
            'save_post_drafts',
            'forum_default_view',
            'forum_notification',
            'forum_enable_print',
        ]);

        $drafts = null !== $data[$drafts_key];
        $this->settings->set('autosave_drafts', (string) ((int) $drafts));
        if ($drafts) {
            $this->settings->set('autosave_drafts_ival', (string) ((int) $data['autosave_drafts']['ival']));
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->editSettings($form);
    }

    protected function settingsForm(): Form
    {
        $field = $this->ui->factory()->input()->field();

        $items = [];

        $checkbox = fn($label) => $field->checkbox($this->lng->txt($label), $this->lng->txt($label . '_desc'));
        $nice = fn($label) => sprintf('%s (%s)', $this->lng->txt('sort_by_date'), $this->lng->txt($label));
        $to_string = static fn($x) => (string) $x;
        $with_options = static fn($x, $options) => array_reduce(
            $options,
            static fn($x, $option) => $x->withOption(...array_map($to_string, $option)),
            $x
        );
        $add_checkbox = function ($name, $label = null, $f = null) use (&$items, $checkbox) {
            $f = $f ?? static fn($x) => $x;
            $items[$name] = $f($checkbox($label ?? $name)->withValue((bool) $this->settings->get($name)));
        };

        $items['forum_default_view'] = $with_options($field->radio($this->lng->txt('frm_default_view')), [
            [ilForumProperties::VIEW_TREE, $this->lng->txt('sort_by_posts'), $this->lng->txt('sort_by_posts_desc')],
            [ilForumProperties::VIEW_DATE_ASC, $nice('ascending_order'), $this->lng->txt('sort_by_date_desc')],
            [ilForumProperties::VIEW_DATE_DESC, $nice('descending_order'), $this->lng->txt('sort_by_date_desc')],
        ])->withValue($this->settings->get('forum_default_view', (string) ilForumProperties::VIEW_DATE_ASC));

        $add_checkbox('forum_enable_print', 'frm_enable_print_option');
        $add_checkbox('enable_fora_statistics');
        $add_checkbox('enable_anonymous_fora');

        $items['file_upload_allowed_fora'] = $with_options($field->radio($this->lng->txt('file_upload_allowed_fora')), [
            [ilForumProperties::FILE_UPLOAD_GLOBALLY_ALLOWED, $this->lng->txt('file_upload_option_allow'), $this->lng->txt('file_upload_option_allow_info')],
            [ilForumProperties::FILE_UPLOAD_INDIVIDUAL, $this->lng->txt('file_upload_option_disallow'), $this->lng->txt('file_upload_allowed_fora_desc')],
        ])->withValue($this->settings->get(
            'file_upload_allowed_fora',
            (string) ilForumProperties::FILE_UPLOAD_GLOBALLY_ALLOWED
        ));

        $add_checkbox('forum_notification', 'cron_forum_notification', fn($x) => (
            $x->withDisabled($this->forumJobActive())
              ->withValue($x->getValue() || $this->forumJobActive())
              ->withByLine($this->forumByLine($x))
        ));

        $add_checkbox('send_attachments_by_mail', 'enable_send_attachments');
        $add_checkbox('save_post_drafts', 'adm_save_drafts');

        $items['autosave_drafts'] = $field->optionalGroup([
            'ival' => $field->numeric($this->lng->txt('adm_autosave_ival'))->withRequired(true),
        ], $this->lng->txt('adm_autosave_drafts'), $this->lng->txt('adm_autosave_drafts_desc'))->withValue(
            $this->settings->get('autosave_drafts') ? ['ival' => $this->settings->get('autosave_drafts_ival', '30')] : null
        );

        if (!$this->checkPermissionBool('write')) {
            $items = array_map(static fn($x) => $x->withDisabled(true), $items);
        }

        return $this->ui->factory()->input()->container()->form()->standard($this->ctrl->getFormAction($this, 'saveSettings'), $items);
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

    private function cronMessage(): Component
    {
        $gui = new ilCronManagerGUI();
        $data = $gui->addToExternalSettingsForm(ilAdministrationSettingsFormHandler::FORM_FORUM);
        $data = $data['cron_jobs'][1];

        $url = $this->ctrl->getLinkTargetByClass([ilAdministrationGUI::class, ilObjSystemFolderGUI::class], 'jumpToCronJobs');

        return $this->ui->factory()->messageBox()->info($this->lng->txt(key($data)) . ': ' . current($data))->withButtons([
            $this->ui->factory()->link()->standard($this->lng->txt('adm_external_setting_edit'), $url)
        ]);
    }

    /**
     * At the time of this writing there is no equivalent to: ilNumberInputGUI::setM*Value. So this must be done manually.
     * Additionally there is also no way to set custom / additional error messages to form inputs AFTER Form::withRequest is called,
     * so the error message cannot be displayed with the corresponding input field.
     *
     * @param array{ival: int}|null
     */
    private function draftsValid(?array $drafts): bool
    {
        if ($drafts === null) {
            return true;
        }

        $this->lng->loadLanguageModule('form');

        return $this->refinery->in()->series([
            $this->refinery->int()->isGreaterThanOrEqual(30)->withProblemBuilder(fn() => $this->lng->txt('form_msg_value_too_low')),
            $this->refinery->int()->isLessThanOrEqual(60 * 60)->withProblemBuilder(fn() => $this->lng->txt('form_msg_value_too_high')),
            $this->refinery->always(true),
        ])->applyTo(new Ok($drafts['ival']))->except(function ($error) {
            $this->tpl->setOnScreenMessage('failure', sprintf('%s: %s', $this->lng->txt('adm_autosave_drafts'), $error->getMessage()));
            return new Ok(false);
        })->value();
    }

    private function forumJobActive(): bool
    {
        return $this->cronManager->isJobActive('frm_notification');
    }

    private function forumByLine(Component $component): string
    {
        return $this->forumJobActive() ?
            sprintf('%s<br/>%s', $component->getByLine(), $this->lng->txt('cron_forum_notification_disabled')) :
            $component->getByLine();
    }
}
