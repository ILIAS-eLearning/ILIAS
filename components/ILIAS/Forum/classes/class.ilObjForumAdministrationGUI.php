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

/**
 * @ilCtrl_Calls      ilObjForumAdministrationGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjForumAdministrationGUI: ilAdministrationGUI
 */
class ilObjForumAdministrationGUI extends ilObjectGUI
{
    private const PROP_SECTION_DEFAULTS = 'defaults';
    private const PROP_SECTION_FEATURES = 'features';
    private const PROP_SECTION_NOTIFICATIONS = 'notifications';
    private const PROP_SECTION_DRAFTS = 'drafts';

    private readonly \ILIAS\DI\RBACServices $rbac;
    private readonly ilCronManager $cronManager;
    private readonly UIServices $ui;

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
        $this->lng->loadLanguageModule('form');
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
        $this->checkPermission('write');

        $form = $this->settingsForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null || $this->request->getMethod() !== 'POST') {
            $this->editSettings($form);
            return;
        }

        $set_int = fn(string $field, string $section) => $this->settings->set(
            $field,
            (string) ((int) $data[$section][$field])
        );

        $data[self::PROP_SECTION_NOTIFICATIONS]['forum_notification'] = (
            $data[self::PROP_SECTION_NOTIFICATIONS]['forum_notification'] || $this->forumJobActive()
        );

        array_map($set_int, [
            'forum_default_view',
            'forum_enable_print',
            'enable_fora_statistics',
            'enable_anonymous_fora',
            'file_upload_allowed_fora',
            'forum_notification',
            'send_attachments_by_mail',
            'save_post_drafts',
        ], [
            self::PROP_SECTION_DEFAULTS,
            self::PROP_SECTION_FEATURES,
            self::PROP_SECTION_FEATURES,
            self::PROP_SECTION_FEATURES,
            self::PROP_SECTION_FEATURES,
            self::PROP_SECTION_NOTIFICATIONS,
            self::PROP_SECTION_NOTIFICATIONS,
            self::PROP_SECTION_DRAFTS
        ]);

        $drafts = $data[self::PROP_SECTION_DRAFTS]['autosave_drafts'] !== null;
        $this->settings->set('autosave_drafts', (string) ((int) $drafts));
        if ($drafts) {
            $this->settings->set(
                'autosave_drafts_ival',
                (string) ((int) $data[self::PROP_SECTION_DRAFTS]['autosave_drafts']['ival'])
            );
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->editSettings($form);
    }

    protected function settingsForm(): Form
    {
        $field = $this->ui->factory()->input()->field();

        $section = fn(string $label, array $inputs): \ILIAS\UI\Component\Input\Field\Section => $field->section(
            $inputs,
            $this->lng->txt($label)
        );
        $checkbox = fn(string $label): \ILIAS\UI\Component\Input\Field\Checkbox => $field->checkbox(
            $this->lng->txt($label),
            $this->lng->txt($label . '_desc')
        );
        $by_date_with_additional_info = fn(string $label): string => sprintf(
            '%s (%s)',
            $this->lng->txt('sort_by_date'),
            $this->lng->txt($label)
        );
        $to_string = static fn($value): string => (string) $value;
        $radio_with_options = static fn(
            \ILIAS\UI\Component\Input\Field\Radio $x,
            array $options
        ): \ILIAS\UI\Component\Input\Field\Radio => array_reduce(
            $options,
            static fn($field, array $option) => $field->withOption(...array_map($to_string, $option)),
            $x
        );
        $checkbox_with_func = function (string $name, ?string $label = null, $f = null) use (
            $checkbox
        ): \ILIAS\UI\Component\Input\Field\Checkbox {
            $f ??= static fn($x) => $x;
            return $f($checkbox($label ?? $name)->withValue((bool) $this->settings->get($name)));
        };
        $disable_if_no_permission = $this->checkPermissionBool('write') ? static fn(
            array $fields
        ): array => $fields : static fn(
            array $fields
        ): array => array_map(
            static fn($x) => $x->withDisabled(true),
            $fields
        );

        return $this->ui->factory()->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'saveSettings'),
            [
                self::PROP_SECTION_DEFAULTS => $section('frm_adm_sec_default_settings', $disable_if_no_permission([
                    'forum_default_view' => $radio_with_options($field->radio($this->lng->txt('frm_default_view')), [
                        [
                            ilForumProperties::VIEW_TREE,
                            $this->lng->txt('sort_by_posts'),
                            $this->lng->txt('sort_by_posts_desc')
                        ],
                        [
                            ilForumProperties::VIEW_DATE_ASC,
                            $by_date_with_additional_info('ascending_order'),
                            $this->lng->txt('sort_by_date_desc')
                        ],
                        [
                            ilForumProperties::VIEW_DATE_DESC,
                            $by_date_with_additional_info('descending_order'),
                            $this->lng->txt('sort_by_date_desc')
                        ],
                    ])->withValue($this->settings->get('forum_default_view', (string) ilForumProperties::VIEW_DATE_ASC))
                ])),
                self::PROP_SECTION_FEATURES => $section('frm_adm_sec_features', $disable_if_no_permission([
                    'forum_enable_print' => $checkbox_with_func('forum_enable_print', 'frm_enable_print_option'),
                    'enable_fora_statistics' => $checkbox_with_func('enable_fora_statistics'),
                    'enable_anonymous_fora' => $checkbox_with_func('enable_anonymous_fora'),
                    'file_upload_allowed_fora' => $radio_with_options(
                        $field->radio($this->lng->txt('file_upload_allowed_fora')),
                        [
                            [
                                ilForumProperties::FILE_UPLOAD_GLOBALLY_ALLOWED,
                                $this->lng->txt('file_upload_option_allow'),
                                $this->lng->txt('file_upload_option_allow_info')
                            ],
                            [
                                ilForumProperties::FILE_UPLOAD_INDIVIDUAL,
                                $this->lng->txt('file_upload_option_disallow'),
                                $this->lng->txt('file_upload_allowed_fora_desc')
                            ],
                        ]
                    )->withValue(
                        $this->settings->get(
                            'file_upload_allowed_fora',
                            (string) ilForumProperties::FILE_UPLOAD_GLOBALLY_ALLOWED
                        )
                    )
                ])),
                self::PROP_SECTION_NOTIFICATIONS => $section('frm_adm_sec_notifications', $disable_if_no_permission([
                    'forum_notification' => $checkbox_with_func(
                        'forum_notification',
                        'cron_forum_notification',
                        fn($field) => (
                            $field
                            ->withDisabled($this->forumJobActive())
                            ->withValue($field->getValue() || $this->forumJobActive())
                            ->withByLine($this->forumByLine($field))
                        )
                    ),
                    'send_attachments_by_mail' => $checkbox_with_func(
                        'send_attachments_by_mail',
                        'enable_send_attachments'
                    )
                ])),
                self::PROP_SECTION_DRAFTS => $section('frm_adm_sec_drafts', $disable_if_no_permission([
                    'save_post_drafts' => $checkbox_with_func('save_post_drafts', 'adm_save_drafts'),
                    'autosave_drafts' => $field->optionalGroup([
                        'ival' => $field
                            ->numeric($this->lng->txt('adm_autosave_ival'))
                            ->withRequired(true)
                            ->withAdditionalTransformation(
                                $this->refinery->in()->series([
                                    $this->refinery->int()->isGreaterThanOrEqual(30),
                                    $this->refinery->int()->isLessThanOrEqual(60 * 60)
                                ])
                            )
                    ], $this->lng->txt('adm_autosave_drafts'), $this->lng->txt('adm_autosave_drafts_desc'))->withValue(
                        $this->settings->get('autosave_drafts') ? [
                            'ival' => $this->settings->get(
                                'autosave_drafts_ival',
                                '30'
                            )
                        ] : null
                    )
                ])),
            ]
        );
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

        $url = $this->ctrl->getLinkTargetByClass(
            [ilAdministrationGUI::class, ilObjSystemFolderGUI::class],
            'jumpToCronJobs'
        );

        return $this->ui->factory()->messageBox()->info($this->lng->txt(key($data)) . ': ' . current($data))->withLinks(
            [
                $this->ui->factory()->link()->standard($this->lng->txt('adm_external_setting_edit'), $url)
            ]
        );
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
