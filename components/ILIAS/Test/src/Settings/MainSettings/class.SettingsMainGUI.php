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

namespace ILIAS\Test\Settings\MainSettings;

use ILIAS\Test\Settings\TestSettingsGUI;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Logging\TestAdministrationInteractionTypes;
use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Test\Settings\MainSettings\MainSettingsRepository;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Checkbox;
use ILIAS\UI\Component\Input\Field\OptionalGroup;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation as TransformationInterface;
use ILIAS\Data\Factory as DataFactory;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Refinery\Constraint;

/**
 *
 * @ilCtrl_Calls ILIAS\Test\Settings\MainSettings\SettingsMainGUI: ilPropertyFormGUI
 * @ilCtrl_Calls ILIAS\Test\Settings\MainSettings\SettingsMainGUI: ilConfirmationGUI
 * @ilCtrl_Calls ILIAS\Test\Settings\MainSettings\SettingsMainGUI: ilTestSettingsChangeConfirmationGUI
 *
 */
class SettingsMainGUI extends TestSettingsGUI
{
    /**
     * command constants
     */
    public const CMD_SHOW_FORM = 'showForm';
    public const CMD_SAVE_FORM = 'saveForm';
    public const CMD_CONFIRMED_SAVE_FORM = 'confirmedSaveForm';
    public const CMD_SHOW_RESET_TPL_CONFIRM = 'showResetTemplateConfirmation';
    public const CMD_CONFIRMED_RESET_TPL = 'confirmedResetTemplate';

    private const GENERAL_SETTINGS_SECTION_LABEL = 'general_settings';
    private const AVAILABILITY_SETTINGS_SECTION_LABEL = 'availability settings';
    private const PRESENTATION_SETTINGS_SECTION_LABEL = 'presentation_settings';
    private const INTRODUCTION_SETTINGS_SECTION_LABEL = 'introduction_settings';
    private const ACCESS_SETTINGS_LABEL = 'access_settings';
    private const TEST_BEHAVIOUR_SETTINGS_LABEL = 'test_behaviour_settings';
    private const QUESTION_BEHAVIOUR_SETTINGS_LABEL = 'question_behaviour_settings';
    private const PARTICIPANTS_FUNCTIONALITY_SETTINGS_LABEL = 'participants_functionality_settings';
    private const FINISH_TEST_SETTINGS_LABEL = 'finish_test_settings';
    private const ECS_FUNCTIONALITY_SETTINGS_LABEL = 'ecs_settings';
    private const ADDITIONAL_FUNCTIONALITY_SETTINGS_LABEL = 'additional_functionality_settings';

    protected \ilObjectProperties $object_properties;
    protected MainSettings $main_settings;
    protected MainSettingsRepository $main_settings_repository;

    private \ilTestQuestionSetConfigFactory $testQuestionSetConfigFactory;

    public function __construct(
        protected readonly \ilGlobalTemplateInterface $tpl,
        protected readonly \ilTabsGUI $tabs,
        protected readonly \ilToolbarGUI $toolbar,
        protected readonly \ilCtrlInterface $ctrl,
        protected readonly \ilAccessHandler $access,
        protected readonly \ilLanguage $lng,
        protected readonly \ilTree $tree,
        protected readonly \ilDBInterface $db,
        protected readonly \ilObjectDataCache $object_data_cache,
        protected readonly \ilSetting $settings,
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly Refinery $refinery,
        protected readonly ServerRequestInterface $request,
        protected readonly \ilComponentRepository $component_repository,
        protected readonly \ilObjUser $active_user,
        protected readonly \ilObjTestGUI $test_gui,
        protected readonly TestLogger $logger,
        protected readonly GeneralQuestionPropertiesRepository $questionrepository
    ) {
        $this->object_properties = $this->test_gui->getTestObject()->getObjectProperties();
        $this->main_settings = $this->test_gui->getTestObject()->getMainSettings();
        $this->main_settings_repository = $this->test_gui->getTestObject()->getMainSettingsRepository();
        $this->testQuestionSetConfigFactory = new \ilTestQuestionSetConfigFactory(
            $this->tree,
            $this->db,
            $this->lng,
            $this->logger,
            $this->component_repository,
            $this->test_gui->getTestObject(),
            $this->questionrepository
        );

        $this->lng->loadLanguageModule('validation');

        parent::__construct($this->test_gui->getTestObject());
    }

    /**
     * Command Execution
     */
    public function executeCommand()
    {
        if (!$this->access->checkAccess('write', '', $this->test_gui->getRefId())) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cannot_edit_test'), true);
            $this->ctrl->redirect($this->test_gui, 'infoScreen');
        }

        $cmd = $this->ctrl->getCmd(self::CMD_SHOW_FORM);
        $this->$cmd();

        $this->object_data_cache->deleteCachedEntry($this->test_object->getId());
        $this->test_gui->prepareOutput();
        $this->tabs->activateTab(\ilTestTabsManager::TAB_ID_SETTINGS);
        $this->tabs->activateSubTab(\ilTestTabsManager::SUBTAB_ID_GENERAL_SETTINGS);
    }

    private function showOldIntroduction(): void
    {
        $this->toolbar->addComponent(
            $this->ui_factory->link()->standard(
                $this->lng->txt('back'),
                $this->ctrl->getLinkTargetByClass(self::class, 'showForm')
            )
        );

        $this->tpl->setContent(
            $this->main_settings->getIntroductionSettings()->getIntroductionText()
        );
    }

    private function showOldConcludingRemarks(): void
    {
        $this->toolbar->addComponent(
            $this->ui_factory->link()->standard(
                $this->lng->txt('back'),
                $this->ctrl->getLinkTargetByClass(self::class, 'showForm')
            )
        );

        $this->tpl->setContent(
            $this->main_settings->getFinishingSettings()->getConcludingRemarksText()
        );
    }

    private function showForm(StandardForm $form = null, InterruptiveModal $modal = null): void
    {
        if ($form === null) {
            $form = $this->buildForm();
        }

        if ($this->main_settings->getIntroductionSettings()->getIntroductionText() !== '') {
            $this->toolbar->addComponent(
                $this->ui_factory->link()->standard(
                    $this->lng->txt('show_old_introduction'),
                    $this->ctrl->getLinkTargetByClass(self::class, 'showOldIntroduction')
                )
            );
        }

        if ($this->main_settings->getFinishingSettings()->getConcludingRemarksText() !== '') {
            $this->toolbar->addComponent(
                $this->ui_factory->link()->standard(
                    $this->lng->txt('show_old_concluding_remarks'),
                    $this->ctrl->getLinkTargetByClass(self::class, 'showOldConcludingRemarks')
                )
            );
        }

        $rendered_modal = '';
        if ($modal !== null) {
            $rendered_modal = $this->ui_renderer->render($modal);
        }

        $this->tpl->setContent($this->ui_renderer->render($form) . $rendered_modal);
    }

    private function confirmedSaveForm(): void
    {
        $form = $this->buildForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->showForm($form);
            return;
        }

        $data[self::AVAILABILITY_SETTINGS_SECTION_LABEL]['is_online'] =
            $this->test_object->getObjectProperties()->getPropertyIsOnline()->withOffline();
        $this->testQuestionSetConfigFactory->getQuestionSetConfig()->removeQuestionSetRelatedData();

        $this->finalizeSave($data);
    }


    private function saveForm(): void
    {
        $form = $this->buildForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->showForm($form);
            return;
        }

        $current_question_set_type = $this->main_settings->getGeneralSettings()->getQuestionSetType();
        $current_question_config = $this->testQuestionSetConfigFactory->getQuestionSetConfig();
        $new_question_set_type = $data[self::GENERAL_SETTINGS_SECTION_LABEL]['question_set_type'];

        $question_set_modal_required = $new_question_set_type !== $current_question_set_type
                && $current_question_config->doesQuestionSetRelatedDataExist();
        $anonymity_modal_required = $this->anonymityChanged(
            $data[self::GENERAL_SETTINGS_SECTION_LABEL]['anonymity']
        );

        if ($question_set_modal_required || $anonymity_modal_required) {
            $modal = $this->populateConfirmationModal(
                $question_set_modal_required ? $current_question_set_type : null,
                $question_set_modal_required ? $new_question_set_type : null,
                $anonymity_modal_required
            );
            $this->showForm($form, $modal);
            return;
        }

        $this->finalizeSave($data);
    }

    private function finalizeSave(array $data): void
    {
        $this->performSaveForm($data);
        $additional_information = $this->getObjectDataArrayForLog()
            + $this->main_settings->getArrayForLog($this->logger->getAdditionalInformationGenerator());
        if ($this->logger->isLoggingEnabled()) {
            $this->logger->logTestAdministrationInteraction(
                $this->logger->getInteractionFactory()->buildTestAdministrationInteraction(
                    $this->test_object->getRefId(),
                    $this->active_user->getId(),
                    TestAdministrationInteractionTypes::MAIN_SETTINGS_MODIFIED,
                    $additional_information
                )
            );
        }

        if ($this->anonymityChanged($data[self::GENERAL_SETTINGS_SECTION_LABEL]['anonymity'])) {
            $this->logger->deleteParticipantInteractionsForTest($this->test_object->getRefId());
        }

        $this->removeAllParticipantsIfRequired();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->showForm();
    }

    private function anonymityChanged(bool $anonymity_from_data): bool
    {
        return $this->main_settings->getGeneralSettings()->getAnonymity() === false
            && $anonymity_from_data === true
            && $this->logger->testHasParticipantInteractions($this->test_object->getRefId());
    }

    private function getObjectDataArrayForLog(): array
    {
        $title_and_description = $this->object_properties->getPropertyTitleAndDescription();
        $log_array = [
            AdditionalInformationGenerator::KEY_TEST_TITLE => $title_and_description->getTitle(),
            AdditionalInformationGenerator::KEY_TEST_DESCRIPTION => $title_and_description->getDescription(),
            AdditionalInformationGenerator::KEY_TEST_ONLINE => $this->logger
                ->getAdditionalInformationGenerator()->getTrueFalseTagForBool(
                    $this->object_properties->getPropertyIsOnline()->getIsOnline()
                )
        ];

        if ($this->test_object->isActivationLimited()) {
            $log_array[AdditionalInformationGenerator::KEY_TEST_VISIBILITY_PERIOD] = $this->logger
                ->getAdditionalInformationGenerator()->getEnabledDisabledTagForBool(false);
            return $log_array;
        }

        $none_tag = $this->logger->getAdditionalInformationGenerator()->getNoneTag();
        $from = $this->test_object->getActivationStartingTime() ?? $none_tag;
        $until = $this->test_object->getActivationEndingTime() ?? $none_tag;

        $log_array[AdditionalInformationGenerator::KEY_TEST_VISIBILITY_PERIOD] = $from . ' - ' . $until;
        $log_array[AdditionalInformationGenerator::KEY_TEST_VISIBLE_OUTSIDE_PERIOD] = $this->logger
            ->getAdditionalInformationGenerator()->getEnabledDisabledTagForBool(
                $this->test_object->getActivationVisibility()
            );
        return $log_array;
    }

    private function buildForm(): StandardForm
    {
        $lng = $this->lng;
        $input_factory = $this->ui_factory->input();
        $refinery = $this->refinery;

        $data_factory = new DataFactory();
        $user_format = $this->active_user->getDateFormat();
        if ($this->active_user->getTimeFormat() == \ilCalendarSettings::TIME_FORMAT_24) {
            $user_format = $data_factory->dateFormat()->withTime24($user_format);
        } else {
            $user_format = $data_factory->dateFormat()->withTime12($user_format);
        }

        $environment['participant_data_exists'] = $this->test_object->participantDataExist();
        $environment['user_date_format'] = $user_format;
        $environment['user_time_zone'] = $this->active_user->getTimeZone();

        $main_inputs = [
            self::GENERAL_SETTINGS_SECTION_LABEL => $this->getGeneralSettingsSection($environment),
            self::AVAILABILITY_SETTINGS_SECTION_LABEL => $this->getAvailabilitySettingsSection(),
            self::PRESENTATION_SETTINGS_SECTION_LABEL => $this->getPresentationSettingsSection(),
            self::INTRODUCTION_SETTINGS_SECTION_LABEL => $this->main_settings->getIntroductionSettings()
                ->toForm($lng, $input_factory->field(), $refinery),
            self::ACCESS_SETTINGS_LABEL => $this->main_settings->getAccessSettings()
                ->toForm($lng, $input_factory->field(), $refinery, $environment),
            self::TEST_BEHAVIOUR_SETTINGS_LABEL => $this->main_settings->getTestBehaviourSettings()
                ->toForm($lng, $input_factory->field(), $refinery, $environment),
            self::QUESTION_BEHAVIOUR_SETTINGS_LABEL => $this->main_settings->getQuestionBehaviourSettings()
                ->toForm($lng, $input_factory->field(), $refinery, $environment),
            self::PARTICIPANTS_FUNCTIONALITY_SETTINGS_LABEL => $this->main_settings->getParticipantFunctionalitySettings()
                ->toForm($lng, $input_factory->field(), $refinery, $environment),
            self::FINISH_TEST_SETTINGS_LABEL => $this->main_settings->getFinishingSettings()
                ->toForm($lng, $input_factory->field(), $refinery)
        ];

        $inputs = array_merge($main_inputs, $this->getAdditionalFunctionalitySettingsSections($environment));

        return $input_factory->container()->form()->standard(
            $this->ctrl->getFormActionByClass(self::class, self::CMD_SAVE_FORM),
            $inputs
        )->withAdditionalTransformation($this->getFormConstraints());
    }

    private function getFormConstraints(): Constraint
    {
        return $this->refinery->custom()->constraint(
            function (array $vs): bool {
                if ($vs[self::PARTICIPANTS_FUNCTIONALITY_SETTINGS_LABEL]['postponed_questions_behaviour'] === true
                    && $vs[self::QUESTION_BEHAVIOUR_SETTINGS_LABEL]['lock_answers']['lock_answer_on_next_question']) {
                    return false;
                }
                return true;
            },
            $this->lng->txt('tst_settings_conflict_postpone_and_lock')
        );
    }

    private function populateConfirmationModal(
        ?string $current_question_set_type,
        ?string $new_question_set_type,
        bool $anonymity_modal_required
    ): InterruptiveModal {
        $message = '';

        if ($current_question_set_type !== null) {
            $message .= sprintf(
                $this->lng->txt('tst_change_quest_set_type_from_old_to_new_with_conflict'),
                $this->test_object->getQuestionSetTypeTranslation($this->lng, $current_question_set_type),
                $this->test_object->getQuestionSetTypeTranslation($this->lng, $new_question_set_type)
            );
        }

        if ($current_question_set_type === \ilObjTest::QUESTION_SET_TYPE_FIXED
            && $this->test_object->hasQuestionsWithoutQuestionpool()) {
            $message .= '<br /><br />' . $this->lng->txt('tst_nonpool_questions_get_lost_warning');
        }

        if ($anonymity_modal_required) {
            if ($message !== '') {
                $message .= '<br /><br />';
            }
            $message .= $this->lng->txt('log_participant_data_delete_warning');
        }

        $this->tpl->addJavaScript('assets/js/settings_confirmation.js');
        $on_load_code = static function (string $id): string {
            return 'il.test.confirmSettings.init(' . $id . ')';
        };

        $modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $message,
            $this->ctrl->getFormActionByClass(self::class, self::CMD_CONFIRMED_SAVE_FORM)
        )->withActionButtonLabel($this->lng->txt('confirm'))
            ->withAdditionalOnLoadCode($on_load_code);

        return $modal->withOnLoad($modal->getShowSignal());
    }

    private function performSaveForm(array $data)
    {
        $old_online_status = $this->test_object->getObjectProperties()->getPropertyIsOnline()->getIsOnline();
        $this->test_object->getObjectProperties()->storePropertyTitleAndDescription(
            $data[self::GENERAL_SETTINGS_SECTION_LABEL]['title_and_description']
        );
        $general_settings = $this->getGeneralSettingsForStorage($data[self::GENERAL_SETTINGS_SECTION_LABEL]);

        $this->saveAvailabilitySettingsSection($data[self::AVAILABILITY_SETTINGS_SECTION_LABEL]);
        $this->savePresentationSettingsSection($data[self::PRESENTATION_SETTINGS_SECTION_LABEL]);

        $introduction_settings = $this->getIntroductionSettingsForStorage(
            $data[self::INTRODUCTION_SETTINGS_SECTION_LABEL]
        );
        $access_settings = $this->getAccessSettingsForStorage(
            $data[self::ACCESS_SETTINGS_LABEL]
        );
        $test_behaviour_settings = $this->getTestBehaviourSettingsForStorage(
            $data[self::TEST_BEHAVIOUR_SETTINGS_LABEL]
        );
        $question_behaviour_settings = $this->getQuestionBehaviourSettingsForStorage(
            $data[self::QUESTION_BEHAVIOUR_SETTINGS_LABEL]
        );
        $participant_functionality_settings = $this->getParticipantsFunctionalitySettingsForStorage(
            $data[self::PARTICIPANTS_FUNCTIONALITY_SETTINGS_LABEL]
        );

        $finishing_settings = $this->getFinishingSettingsForStorage($data[self::FINISH_TEST_SETTINGS_LABEL]);

        if (array_key_exists(self::ADDITIONAL_FUNCTIONALITY_SETTINGS_LABEL, $data)
            || array_key_exists(self::ECS_FUNCTIONALITY_SETTINGS_LABEL, $data)) {
            $this->saveAdditionalFunctionalitySettingsSection($data);
        }

        $additional_settings = $this->getAdditionalFunctionalitySettingsForStorage(
            $data[self::ADDITIONAL_FUNCTIONALITY_SETTINGS_LABEL] ?? []
        );

        $settings = new MainSettings(
            $this->test_object->getTestId(),
            $this->test_object->getId(),
            $general_settings,
            $introduction_settings,
            $access_settings,
            $test_behaviour_settings,
            $question_behaviour_settings,
            $participant_functionality_settings,
            $finishing_settings,
            $additional_settings
        );
        $this->main_settings_repository->store($settings);
        $this->main_settings = $this->main_settings_repository->getFor($this->test_object->getTestId());
        $this->test_object->read();
        $this->test_object->addToNewsOnOnline(
            $old_online_status,
            $this->test_object->getObjectProperties()->getPropertyIsOnline()->getIsOnline()
        );
    }

    private function removeAllParticipantsIfRequired(): void
    {
        if (!$this->test_object->participantDataExist() && !$this->test_object->getFixedParticipants()) {
            foreach (array_keys($this->test_object->getInvitedUsers()) as $usr_id) {
                $this->test_object->disinviteUser($usr_id);
            }
        }
    }

    private function getGeneralSettingsSection(array $environment): Section
    {
        $field_factory = $this->ui_factory->input()->field();

        $inputs['title_and_description'] = $this->test_object->getObjectProperties()->getPropertyTitleAndDescription()
            ->toForm($this->lng, $field_factory, $this->refinery);
        $inputs += $this->main_settings->getGeneralSettings()
            ->toForm($this->lng, $field_factory, $this->refinery, $environment);

        return $field_factory->section(
            $inputs,
            $this->lng->txt('tst_general_properties')
        );
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    private function getGeneralSettingsForStorage(array $section): SettingsGeneral
    {
        if ($this->test_object->participantDataExist()) {
            return $this->main_settings->getGeneralSettings();
        }

        return $this->main_settings->getGeneralSettings()
            ->withQuestionSetType($section['question_set_type'])
            ->withAnonymity($section['anonymity']);
    }

    private function getAvailabilitySettingsSection(): Section
    {
        $input_factory = $this->ui_factory->input();

        $inputs['is_online'] = $this->getIsOnlineSettingInput();
        $inputs['timebased_availability'] = $this->getTimebasedAvailabilityInputs();

        return $input_factory->field()->section(
            $inputs,
            $this->lng->txt('rep_activation_availability')
        );
    }

    private function getIsOnlineSettingInput(): Checkbox
    {
        $field_factory = $this->ui_factory->input()->field();

        $question_set_config_complete = $this->test_object->isComplete(
            $this->testQuestionSetConfigFactory->getQuestionSetConfig()
        );

        $is_online = $this->test_object->getObjectProperties()->getPropertyIsOnline()
            ->toForm($this->lng, $field_factory, $this->refinery);

        if (sizeof(\ilObject::_getAllReferences($this->test_object->getId())) > 1) {
            $is_online = $is_online->withByline(
                $is_online->getByline() . ' ' . $this->lng->txt('rep_activation_online_object_info')
            );
        }

        if (!$question_set_config_complete) {
            $is_online = $is_online
                ->withByline($this->lng->txt('cannot_switch_to_online_no_questions_andor_no_mark_steps'))
                ->withDisabled(true);
        }

        return $is_online;
    }

    private function getTimebasedAvailabilityInputs(): OptionalGroup
    {
        $field_factory = $this->ui_factory->input()->field();

        $trafo = $this->getTransformationForActivationLimitedOptionalGroup();
        $value = $this->getValueForActivationLimitedOptionalGroup();

        $data_factory = new DataFactory();
        $user_format = $this->active_user->getDateFormat();
        $format = $data_factory->dateFormat()->withTime24($user_format);

        $inputs['time_span'] = $field_factory->duration($this->lng->txt('rep_time_period'))
            ->withTimezone($this->active_user->getTimeZone())
            ->withFormat($format)
            ->withUseTime(true)
            ->withRequired(true);
        $inputs['activation_visibility'] = $field_factory->checkbox(
            $this->lng->txt('rep_activation_limited_visibility'),
            $this->lng->txt('tst_activation_limited_visibility_info')
        );

        return $field_factory->optionalGroup(
            $inputs,
            $this->lng->txt('rep_time_based_availability')
        )->withAdditionalTransformation($trafo)
            ->withValue($value);
    }

    private function getTransformationForActivationLimitedOptionalGroup(): TransformationInterface
    {
        return $this->refinery->custom()->transformation(
            static function (?array $vs): array {
                if ($vs === null) {
                    return [
                        'is_activation_limited' => false,
                        'activation_starting_time' => null,
                        'activation_ending_time' => null,
                        'activation_visibility' => false
                    ];
                }

                $start = $vs['time_span']['start']->getTimestamp();
                $end = $vs['time_span']['end']->getTimestamp();

                return [
                    'is_activation_limited' => true,
                    'activation_starting_time' => $start,
                    'activation_ending_time' => $end,
                    'activation_visibility' => $vs['activation_visibility']
                ];
            }
        );
    }

    private function getValueForActivationLimitedOptionalGroup(): ?array
    {
        $value = null;
        if ($this->test_object->isActivationLimited()) {
            $value = [
                'time_span' => [
                    \DateTimeImmutable::createFromFormat(
                        'U',
                        (string) $this->test_object->getActivationStartingTime()
                    )->setTimezone(new DateTimeZone($this->active_user->getTimeZone())),
                    \DateTimeImmutable::createFromFormat(
                        'U',
                        (string) $this->test_object->getActivationEndingTime()
                    )->setTimezone(new DateTimeZone($this->active_user->getTimeZone())),
                ],
                'activation_visibility' => $this->test_object->getActivationVisibility()
            ];
        }
        return $value;
    }

    private function saveAvailabilitySettingsSection(array $section): void
    {
        $timebased_availability = $section['timebased_availability'];
        if ($this->test_object->participantDataExist()) {
            $timebased_availability['is_activation_limited'] = $this->test_object->isActivationLimited();
            $timebased_availability['activation_starting_time'] = $this->test_object->getActivationStartingTime();
        }

        $this->test_object->storeActivationSettings($timebased_availability);
        $this->test_object->getObjectProperties()->storePropertyIsOnline($section['is_online']);
    }

    protected function getPresentationSettingsSection(): Section
    {
        $input_factory = $this->ui_factory->input();

        $custom_icon_input = $this->test_object->getObjectProperties()->getPropertyIcon()
            ->toForm($this->lng, $input_factory->field(), $this->refinery);

        if ($custom_icon_input !== null) {
            $inputs['custom_icon'] = $custom_icon_input;
        }
        $inputs['tile_image'] = $this->test_object->getObjectProperties()->getPropertyTileImage()
            ->toForm($this->lng, $input_factory->field(), $this->refinery);

        return $input_factory->field()->section(
            $inputs,
            $this->lng->txt('tst_presentation_settings_section')
        );
    }

    protected function savePresentationSettingsSection(array $section): void
    {
        if (array_key_exists('custom_icon', $section)) {
            $this->test_object->getObjectProperties()->storePropertyIcon($section['custom_icon']);
        }
        $this->test_object->getObjectProperties()->storePropertyTileImage($section['tile_image']);
    }

    private function getIntroductionSettingsForStorage(array $section): SettingsIntroduction
    {
        return $this->main_settings->getIntroductionSettings()
            ->withIntroductionEnabled($section['introduction_enabled'])
            ->withExamConditionsCheckboxEnabled($section['conditions_checkbox_enabled']);
    }

    private function getAccessSettingsForStorage(array $section): SettingsAccess
    {
        $access_settings = $this->main_settings->getAccessSettings()
            ->withStartTimeEnabled($section['access_window']['start_time_enabled'])
            ->withStartTime($section['access_window']['start_time'])
            ->withEndTimeEnabled($section['access_window']['end_time_enabled'])
            ->withEndTime($section['access_window']['end_time'])
            ->withPasswordEnabled($section['test_password']['password_enabled'])
            ->withPassword($section['test_password']['password_value'])
            ->withIpRangeFrom($section['ip_range']['ip_range_from'])
            ->withIpRangeTo($section['ip_range']['ip_range_to'])
            ->withFixedParticipants($section['fixed_participants_enabled']);

        if ($this->test_object->participantDataExist()) {
            return $access_settings;
        }

        return $access_settings->withStartTimeEnabled($section['access_window']['start_time_enabled'])
            ->withStartTime($section['access_window']['start_time']);
    }

    private function getTestBehaviourSettingsForStorage(array $section): SettingsTestBehaviour
    {
        $test_behaviour_settings = $this->main_settings->getTestBehaviourSettings()
            ->withKioskMode($section['kiosk_mode'])
            ->withExamIdInTestPassEnabled($section['show_exam_id']);

        if ($this->test_object->participantDataExist()) {
            return $test_behaviour_settings;
        }

        return $test_behaviour_settings
            ->withNumberOfTries($section['limit_attempts']['number_of_available_attempts'])
            ->withBlockAfterPassedEnabled($section['limit_attempts']['block_after_passed'])
            ->withPassWaiting($section['force_waiting_between_attempts'])
            ->withProcessingTimeEnabled($section['time_limit_for_completion']['processing_time_limit'])
            ->withProcessingTime($section['time_limit_for_completion']['time_limit_for_completion_value'])
            ->withResetProcessingTime($section['time_limit_for_completion']['reset_time_limit_for_completion_by_attempt']);
    }

    private function getQuestionBehaviourSettingsForStorage(array $section): SettingsQuestionBehaviour
    {
        $question_behaviour_settings = $this->main_settings->getQuestionBehaviourSettings()
            ->withQuestionTitleOutputMode($section['title_output'])
            ->withAutosaveEnabled($section['autosave']['autosave_enabled'])
            ->withAutosaveInterval($section['autosave']['autosave_interval'])
            ->withShuffleQuestions($section['shuffle_questions']);

        if ($this->test_object->participantDataExist()) {
            return $question_behaviour_settings;
        }

        return $question_behaviour_settings
            ->withQuestionHintsEnabled($section['offer_hints'])
            ->withInstantFeedbackPointsEnabled($section['instant_feedback']['enabled_feedback_types']['instant_feedback_points'])
            ->withInstantFeedbackGenericEnabled($section['instant_feedback']['enabled_feedback_types']['instant_feedback_generic'])
            ->withInstantFeedbackSpecificEnabled($section['instant_feedback']['enabled_feedback_types']['instant_feedback_specific'])
            ->withInstantFeedbackSolutionEnabled($section['instant_feedback']['enabled_feedback_types']['instant_feedback_solution'])
            ->withForceInstantFeedbackOnNextQuestion($section['instant_feedback']['feedback_on_next_question'])
            ->withLockAnswerOnInstantFeedbackEnabled($section['lock_answers']['lock_answer_on_instant_feedback'])
            ->withLockAnswerOnNextQuestionEnabled($section['lock_answers']['lock_answer_on_next_question'])
            ->withCompulsoryQuestionsEnabled($section['enable_compulsory_questions']);
    }


    private function getParticipantsFunctionalitySettingsForStorage(array $section): SettingsParticipantFunctionality
    {
        return $this->main_settings->getParticipantFunctionalitySettings()
            ->withUsePreviousAnswerAllowed($section['use_previous_answers'])
            ->withSuspendTestAllowed($section['allow_suspend_test'])
            ->withPostponedQuestionsMoveToEnd($section['postponed_questions_behaviour'])
            ->withUsrPassOverviewMode($section['usr_pass_overview'])
            ->withQuestionMarkingEnabled($section['enable_question_marking'])
            ->withQuestionListEnabled($section['enable_question_list']);
    }

    private function getFinishingSettingsForStorage(array $section): SettingsFinishing
    {
        $redirect_after_finish = $section['redirect_after_finish'];
        $finish_notification = $section['finish_notification'];
        return $this->main_settings->getFinishingSettings()
            ->withShowAnswerOverview($section['show_answer_overview'])
            ->withConcludingRemarksEnabled($section['show_concluding_remarks'])
            ->withRedirectionMode($redirect_after_finish['redirect_mode'])
            ->withRedirectionUrl($redirect_after_finish['redirect_url'])
            ->withMailNotificationContentType($finish_notification['notification_content_type'])
            ->withAlwaysSendMailNotification($finish_notification['always_notify']);
    }

    protected function getAdditionalFunctionalitySettingsSections(array $environment): array
    {
        $sections = [];

        $ecs = new \ilECSTestSettings($this->test_object);
        $ecs_section = $ecs->getSettingsSection(
            $this->ui_factory->input()->field(),
            $this->refinery
        );

        if ($ecs_section !== null) {
            $sections[self::ECS_FUNCTIONALITY_SETTINGS_LABEL] = $ecs_section;
        }

        $inputs['organisational_units_activation'] = $this->getOrganisationalUnitsActivationInput();

        $inputs += $this->main_settings->getAdditionalSettings()->toForm(
            $this->lng,
            $this->ui_factory->input()->field(),
            $this->refinery,
            $environment
        );

        $inputs = array_filter($inputs, fn($v) => $v !== null);

        if (count($inputs) > 0) {
            $sections[self::ADDITIONAL_FUNCTIONALITY_SETTINGS_LABEL] = $this->ui_factory->input()->field()
                ->section($inputs, $this->lng->txt('obj_features'));
        }

        return $sections;
    }

    protected function getOrganisationalUnitsActivationInput(): ?Checkbox
    {
        $position_settings = \ilOrgUnitGlobalSettings::getInstance()->getObjectPositionSettingsByType(
            $this->test_object->getType()
        );
        if (!$position_settings->isActive()) {
            return null;
        }

        $enable_organisational_units_access = $this->ui_factory->input()->field()
            ->checkbox(
                $this->lng->txt('obj_orgunit_positions'),
                $this->lng->txt('obj_orgunit_positions_info')
            )->withValue(
                (new \ilOrgUnitObjectPositionSetting($this->test_object->getId()))->isActive()
            );
        if (!$position_settings->isChangeableForObject()) {
            return $enable_organisational_units_access->withDisabled(true);
        }
        return $enable_organisational_units_access;
    }

    protected function saveAdditionalFunctionalitySettingsSection(array $sections): void
    {
        if (array_key_exists(self::ECS_FUNCTIONALITY_SETTINGS_LABEL, $sections)) {
            $ecs = new \ilECSTestSettings($this->test_object);
            $ecs->saveSettingsSection($sections[self::ECS_FUNCTIONALITY_SETTINGS_LABEL]);
        }

        if (!array_key_exists(self::ADDITIONAL_FUNCTIONALITY_SETTINGS_LABEL, $sections)) {
            return;
        }

        $additional_settings_section = $sections[self::ADDITIONAL_FUNCTIONALITY_SETTINGS_LABEL];
        if (array_key_exists('organisational_units_activation', $additional_settings_section)) {
            $this->saveOrganisationalUnitsActivation($additional_settings_section['organisational_units_activation']);
        }
    }

    protected function saveOrganisationalUnitsActivation(bool $activation): void
    {
        $position_settings = \ilOrgUnitGlobalSettings::getInstance()->getObjectPositionSettingsByType(
            $this->test_object->getType()
        );

        if ($position_settings->isActive() && $position_settings->isChangeableForObject()) {
            $orgu_object_settings = new \ilOrgUnitObjectPositionSetting($this->test_object->getId());
            $orgu_object_settings->setActive(
                $activation
            );
            $orgu_object_settings->update();
        }
    }

    protected function getAdditionalFunctionalitySettingsForStorage(array $section): SettingsAdditional
    {
        $additional_settings = $this->main_settings->getAdditionalSettings()->withHideInfoTab($section['hide_info_tab']);

        if ($this->test_object->participantDataExist()) {
            return $additional_settings;
        }

        if (!(new \ilSkillManagementSettings())->isActivated()) {
            return $additional_settings->withSkillsServiceEnabled(false);
        }

        return $additional_settings->withSkillsServiceEnabled($section['skills_service_activation']);
    }
}
