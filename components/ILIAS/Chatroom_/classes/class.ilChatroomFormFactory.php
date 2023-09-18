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

class ilChatroomFormFactory
{
    public const PROP_TITLE_AND_DESC = 'title_and_desc';
    public const PROP_ONLINE_STATUS = 'online_status';
    public const PROP_TILE_IMAGE = 'tile_image';
    public const PROP_DISPLAY_PAST_MSG = 'display_past_msgs';
    public const PROP_ENABLE_HISTORY = 'enable_history';
    public const PROP_ALLOW_ANONYMOUS = 'allow_anonymous';
    public const PROP_ALLOW_CUSTOM_NAMES = 'allow_custom_usernames';
    public const PROP_AUTOGEN_USERNAMES = 'autogen_usernames';

    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\Refinery\Factory $refinery;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->http = $DIC->http();
        $this->ui_factory = $DIC->ui()->factory();
        $this->refinery = $DIC->refinery();
    }

    /**
     * Applies given values to field in given form.
     */
    public static function applyValues(ilPropertyFormGUI $form, array $values): void
    {
        $form->setValuesByArray(
            array_map(
                static fn($value) => is_int($value) ? (string) $value : $value,
                $values
            )
        );
    }

    /**
     * Instantiates and returns ilPropertyFormGUI containing ilTextInputGUI
     * and ilTextAreaInputGUI
     * @deprecated replaced by default creation screens
     */
    public function getCreationForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setRequired(true);
        $form->addItem($title);

        $description = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
        $form->addItem($description);

        return $this->addDefaultBehaviour($form);
    }

    /**
     * Adds 'create-save' and 'cancel' button to given $form and returns it.
     */
    private function addDefaultBehaviour(ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        $form->addCommandButton('create-save', $this->lng->txt('create'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }

    private function mergeValuesTrafo(): \ILIAS\Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(static fn(array $values): array => array_merge(...$values));
    }

    private function saniziteArrayElementsTrafo(): \ILIAS\Refinery\Transformation
    {
        $sanitize = static function (array $data) use (&$sanitize): array {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $sanitize($value);
                } elseif (is_string($value)) {
                    $data[$key] = ilUtil::stripSlashes($value);
                }
            }

            return $data;
        };

        return $this->refinery->custom()->transformation($sanitize);
    }

    public function getSettingsForm(
        ilChatroomObjectGUI $gui,
        ilCtrlInterface $ctrl,
        ?array $values = null
    ): \ILIAS\UI\Component\Input\Container\Form\Form {
        $this->lng->loadLanguageModule('obj');
        $this->lng->loadLanguageModule('rep');

        $field_factory = $this->ui_factory->input()->field();

        $title_and_description = $gui->getObject()->getObjectProperties()->getPropertyTitleAndDescription();
        $general_settings_fields = [
            self::PROP_TITLE_AND_DESC => $title_and_description->toForm(
                $this->lng,
                $field_factory,
                $this->refinery
            )
        ];

        $online_status = $gui->getObject()->getObjectProperties()->getPropertyIsOnline();
        $availability_fields = [
            self::PROP_ONLINE_STATUS => $online_status->toForm(
                $this->lng,
                $field_factory,
                $this->refinery
            )->withByline($this->lng->txt('chtr_activation_online_info')),
        ];

        $tile_image = $gui->getObject()->getObjectProperties()->getPropertyTileImage();
        $presentation_fields = [
            self::PROP_TILE_IMAGE => $tile_image->toForm(
                $this->lng,
                $field_factory,
                $this->refinery
            ),
            self::PROP_DISPLAY_PAST_MSG => $field_factory->numeric(
                $this->lng->txt('display_past_msgs'),
                $this->lng->txt('hint_display_past_msgs')
            )->withRequired(
                true
            )->withAdditionalTransformation(
                $this->refinery->logical()->parallel([
                    $this->refinery->int()->isGreaterThanOrEqual(0),
                    $this->refinery->int()->isLessThanOrEqual(100)
                ])
            )->withValue(
                $values['display_past_msgs'] ?? 0
            ),
            self::PROP_ENABLE_HISTORY => $field_factory->checkbox(
                $this->lng->txt('chat_enable_history'),
                $this->lng->txt('chat_enable_history_info')
            )->withValue((bool) ($values['enable_history'] ?? false)),
        ];

        $function_fields = [
            self::PROP_ALLOW_ANONYMOUS => $field_factory->checkbox(
                $this->lng->txt('allow_anonymous'),
                $this->lng->txt('anonymous_hint')
            )->withValue((bool) ($values['allow_anonymous'] ?? false)),
            self::PROP_ALLOW_CUSTOM_NAMES => $field_factory->optionalGroup(
                [
                    self::PROP_AUTOGEN_USERNAMES => $field_factory->text(
                        $this->lng->txt('autogen_usernames'),
                        $this->lng->txt('autogen_usernames_info')
                    )->withRequired(true),
                ],
                $this->lng->txt('allow_custom_usernames')
            )->withValue(
                ($values['allow_custom_usernames'] ?? false) ? [self::PROP_AUTOGEN_USERNAMES => $values['autogen_usernames'] ?? ''] : null
            ),
        ];

        $sections = [
            $field_factory->section(
                $general_settings_fields,
                $this->lng->txt('settings_title'),
                ''
            ),
            $field_factory->section(
                $availability_fields,
                $this->lng->txt('rep_activation_availability'),
                ''
            ),
            $field_factory->section(
                $presentation_fields,
                $this->lng->txt('settings_presentation_header'),
                ''
            ),
            $field_factory->section(
                $function_fields,
                $this->lng->txt('chat_settings_functions_header'),
                ''
            ),
        ];

        return $this->ui_factory->input()
                                ->container()
                                ->form()
                                ->standard(
                                    $ctrl->getFormAction($gui, 'settings-saveGeneral'),
                                    $sections
                                )
                                ->withAdditionalTransformation($this->mergeValuesTrafo())
                                ->withAdditionalTransformation($this->saniziteArrayElementsTrafo());
    }

    public function getPeriodForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setPreventDoubleSubmission(false);

        $duration = new ilDateDurationInputGUI($this->lng->txt('period'), 'timeperiod');

        $duration->setStartText($this->lng->txt('duration_from'));
        $duration->setEndText($this->lng->txt('duration_to'));
        $duration->setShowTime(true);
        $duration->setRequired(true);
        $form->addItem($duration);

        return $form;
    }

    /**
     * Returns chatname selection form.
     * @param array<string, string> $name_options
     */
    public function getUserChatNameSelectionForm(array $name_options): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $radio = new ilRadioGroupInputGUI($this->lng->txt('select_custom_username'), 'custom_username_radio');

        foreach ($name_options as $key => $option) {
            $opt = new ilRadioOption($option, $key);
            $radio->addOption($opt);
        }

        $custom_opt = new ilRadioOption($this->lng->txt('custom_username'), 'custom_username');
        $radio->addOption($custom_opt);

        $txt = new ilTextInputGUI($this->lng->txt('preferred_chatname'), 'custom_username_text');
        $custom_opt->addSubItem($txt);
        $form->addItem($radio);

        if ($this->user->isAnonymous()) {
            $radio->setValue('anonymousName');
        } else {
            $radio->setValue('fullname');
        }

        return $form;
    }

    /**
     * Returns session form with period set by given $sessions.
     */
    public function getSessionForm(array $sessions): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setPreventDoubleSubmission(false);
        $list = new ilSelectInputGUI($this->lng->txt('session'), 'session');

        $options = [];

        foreach ($sessions as $session) {
            $start = new ilDateTime($session['connected'], IL_CAL_UNIX);
            $end = new ilDateTime($session['disconnected'], IL_CAL_UNIX);

            $options[$session['connected'] . ',' .
            $session['disconnected']] = ilDatePresentation::formatPeriod($start, $end);
        }

        $list->setOptions($options);
        $list->setRequired(true);

        $form->addItem($list);

        return $form;
    }

    public function getClientSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $enable_chat = new ilCheckboxInputGUI($this->lng->txt('chat_enabled'), 'chat_enabled');
        $form->addItem($enable_chat);

        $enable_osc = new ilCheckboxInputGUI($this->lng->txt('chatroom_enable_osc'), 'enable_osc');
        $enable_osc->setInfo($this->lng->txt('chatroom_enable_osc_info'));
        $enable_chat->addSubItem($enable_osc);

        $oscBrowserNotificationStatus = new ilCheckboxInputGUI(
            $this->lng->txt('osc_adm_browser_noti_label'),
            'enable_browser_notifications'
        );
        $oscBrowserNotificationStatus->setInfo($this->lng->txt('osc_adm_browser_noti_info'));
        $oscBrowserNotificationStatus->setValue('1');
        $enable_osc->addSubItem($oscBrowserNotificationStatus);

        $oscBrowserNotificationIdleTime = new ilNumberInputGUI(
            $this->lng->txt('osc_adm_conv_idle_state_threshold_label'),
            'conversation_idle_state_in_minutes'
        );
        $oscBrowserNotificationIdleTime->allowDecimals(false);
        $oscBrowserNotificationIdleTime->setSuffix($this->lng->txt('minutes'));
        $oscBrowserNotificationIdleTime->setMinValue(1);
        $oscBrowserNotificationIdleTime->setSize(5);
        $oscBrowserNotificationIdleTime->setInfo($this->lng->txt('osc_adm_conv_idle_state_threshold_info'));
        $enable_osc->addSubItem($oscBrowserNotificationIdleTime);

        $name = new ilTextInputGUI($this->lng->txt('chatroom_client_name'), 'client_name');
        $name->setInfo($this->lng->txt('chatroom_client_name_info'));
        $name->setRequired(true);
        $name->setMaxLength(100);
        $enable_chat->addSubItem($name);

        $auth = new ilChatroomAuthInputGUI(
            $this->lng->txt('chatroom_auth'),
            'auth',
            $this->http
        );
        $auth->setInfo($this->lng->txt('chat_auth_token_info'));
        $auth->setCtrlPath(
            [
                ilAdministrationGUI::class,
                ilObjChatroomGUI::class,
                ilPropertyFormGUI::class,
                ilFormPropertyDispatchGUI::class,
                ilChatroomAuthInputGUI::class,
            ]
        );
        $auth->setRequired(true);
        $enable_chat->addSubItem($auth);

        return $form;
    }
}
