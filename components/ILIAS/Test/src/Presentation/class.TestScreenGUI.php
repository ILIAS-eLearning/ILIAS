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

namespace ILIAS\Test\Presentation;

use ILIAS\Test\Access\ParticipantAccess;
use ILIAS\Test\Logging\TestParticipantInteractionTypes;
use ILIAS\Test\Settings\MainSettings\MainSettings;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Link;
use ILIAS\Data\Password;
use ILIAS\Data\Result;
use ILIAS\Test\Settings\MainSettings\SettingsMainGUI;
use ILIAS\UI\Component\Launcher\Launcher;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Style\Content\Service as ContentStyle;

/**
 * Class TestScreenGUI
 *
 * @author Matheus Zych <mzych@databay.de>
 */
class TestScreenGUI
{
    public const DEFAULT_CMD = 'testScreen';

    private readonly \ilTestPassesSelector $test_passes_selector;
    private readonly int $ref_id;
    private readonly MainSettings $main_settings;
    private readonly \ilTestSession $test_session;
    private readonly DataFactory $data_factory;
    private \ilTestPasswordChecker $password_checker;

    public function __construct(
        private readonly \ilObjTest $object,
        private readonly \ilObjUser $user,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly \ilLanguage $lng,
        private readonly Refinery $refinery,
        private readonly \ilCtrlInterface $ctrl,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly ContentStyle $content_style,
        private readonly HTTPServices $http,
        private readonly TabsManager $tabs_manager,
        private readonly \ilAccessHandler $access,
        private readonly \ilTestAccess $test_access,
        private readonly \ilDBInterface $database,
        private readonly \ilRbacSystem $rbac_system
    ) {
        $this->ref_id = $this->object->getRefId();
        $this->main_settings = $this->object->getMainSettings();
        $this->data_factory = new DataFactory();

        $this->test_session = (new \ilTestSessionFactory($this->object, $this->database, $this->user))->getSession();

        $this->test_passes_selector = new \ilTestPassesSelector($this->database, $this->object);
        $this->test_passes_selector->setActiveId($this->test_session->getActiveId());
        $this->test_passes_selector->setLastFinishedPass($this->test_session->getLastFinishedPass());
        $this->password_checker = new \ilTestPasswordChecker($this->rbac_system, $this->user, $this->object, $this->lng);
    }

    public function executeCommand(): void
    {
        if ($this->access->checkAccess('read', '', $this->ref_id)) {
            $this->{$this->ctrl->getCmd(self::DEFAULT_CMD)}();
            return;
        }

        $this->tabs_manager->activateTab(TabsManager::TAB_ID_TEST);

        if (!$this->object->getMainSettings()->getAdditionalSettings()->getHideInfoTab()) {
            $this->ctrl->redirectByClass([\ilRepositoryGUI::class, \ilObjTestGUI::class, \ilInfoScreenGUI::class]);
        }

        $this->tpl->setOnScreenMessage('failure', sprintf(
            $this->lng->txt('msg_no_perm_read_item'),
            $this->object->getTitle()
        ), true);
        $this->ctrl->setParameterByClass(\ilRepositoryGUI::class, 'ref_id', ROOT_FOLDER_ID);
        $this->ctrl->redirectByClass(\ilRepositoryGUI::class);
    }

    public function testScreen(): void
    {
        $this->tabs_manager->activateTab(TabsManager::TAB_ID_TEST);
        $this->tpl->setPermanentLink($this->object->getType(), $this->ref_id);

        $elements = [];

        if ($this->areSkillLevelThresholdsMissing()) {
            $elements = [$this->getSkillLevelThresholdsMissingInfo()];
        }
        $elements = $this->handleRenderMessageBox($elements);
        $elements = $this->handleRenderIntroduction($elements);

        if ($this->testCanBeStarted()) {
            $this->tpl->setContent(
                $this->ui_renderer->render(
                    $this->handleRenderLauncher(
                        $elements
                    )
                )
            );
            return;
        }

        $this->tpl->setContent(
            $this->ui_renderer->render(
                $this->addOfflineMessageBoxIfNecessary(
                    $elements
                )
            )
        );
    }

    private function addOfflineMessageBoxIfNecessary(array $elements): array
    {
        if (!$this->object->getOfflineStatus()) {
            return $elements;
        }

        $offline_message_box = $this->ui_factory->messageBox()->info($this->lng->txt('test_is_offline'));

        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $elements[] = $offline_message_box;
            return $elements;
        }

        $elements[] = $offline_message_box->withLinks([
            $this->ui_factory->link()->standard(
                $this->lng->txt('test_edit_settings'),
                $this->ctrl->getLinkTargetByClass(SettingsMainGUI::class)
            )
        ]);
        return $elements;
    }

    private function handleRenderMessageBox(array $elements): array
    {
        $message_box_message = '';
        $message_box_message_elements = [];

        $exam_conditions_enabled = $this->main_settings->getIntroductionSettings()->getExamConditionsCheckboxEnabled();
        $password_enabled = $this->main_settings->getAccessSettings()->getPasswordEnabled();
        $test_behaviour_settings = $this->main_settings->getTestBehaviourSettings();

        if ($exam_conditions_enabled && $password_enabled) {
            $message_box_message_elements[] = $this->lng->txt('tst_launcher_status_message_conditions_and_password');
        } elseif ($exam_conditions_enabled) {
            $message_box_message_elements[] = $this->lng->txt('tst_launcher_status_message_conditions');
        } elseif ($password_enabled) {
            $message_box_message_elements[] = $this->lng->txt('tst_launcher_status_message_password');
        }

        if ($test_behaviour_settings->getProcessingTimeEnabled() && !$this->isUserOutOfProcessingTime()) {
            $message_box_message_elements[] = sprintf(
                $this->lng->txt('tst_time_limit_message'),
                $test_behaviour_settings->getProcessingTimeAsMinutes()
            );
        }

        $nr_of_tries = $this->object->getNrOfTries();

        if ($nr_of_tries !== 0) {
            $message_box_message_elements[] = sprintf($this->lng->txt('tst_attempt_limit_message'), $nr_of_tries);
        }

        if ($this->object->isStartingTimeEnabled() && !$this->object->startingTimeReached()) {
            $message_box_message_elements[] = sprintf(
                $this->lng->txt('detail_starting_time_not_reached'),
                \ilDatePresentation::formatDate(new \ilDateTime($this->object->getStartingTime(), IL_CAL_UNIX))
            );
        }

        if ($this->object->isEndingTimeEnabled() && !$this->object->endingTimeReached()) {
            $message_box_message_elements[] = sprintf(
                $this->lng->txt('tst_exam_ending_time_message'),
                \ilDatePresentation::formatDate(new \ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX))
            );
        }

        foreach ($message_box_message_elements as $message_box_message_element) {
            $message_box_message .= ' ' . $message_box_message_element;
        }

        if (!empty($message_box_message)) {
            $elements[] = $this->ui_factory->messageBox()->info($message_box_message);
        }

        return $elements;
    }

    private function handleRenderIntroduction(array $elements): array
    {
        $introduction = $this->object->getIntroduction();

        if (
            $this->main_settings->getIntroductionSettings()->getIntroductionEnabled() &&
            !empty($introduction)
        ) {
            $this->content_style->gui()->addCss($this->tpl, $this->ref_id);
            $elements[] = $this->ui_factory->panel()->standard(
                $this->lng->txt('tst_introduction'),
                $this->ui_factory->legacy()->content($introduction),
            );
        }

        return $elements;
    }

    private function handleRenderLauncher(array $elements): array
    {
        $elements[] = $this->getLauncher();
        return $elements;
    }

    private function getLauncher(): Launcher
    {
        $launcher_factory = $this->ui_factory->launcher();

        if ($this->object->isStartingTimeEnabled() && !$this->object->startingTimeReached()) {
            return $launcher_factory
                ->inline($this->data_factory->link('', $this->data_factory->uri($this->http->request()->getUri()->__toString())))
                ->withButtonLabel(sprintf(
                    $this->lng->txt('detail_starting_time_not_reached'),
                    \ilDatePresentation::formatDate(new \ilDateTime($this->object->getStartingTime(), IL_CAL_UNIX))
                ), false)
            ;
        }

        if ($this->object->isEndingTimeEnabled() && $this->object->endingTimeReached()) {
            return $launcher_factory
                ->inline($this->data_factory->link('', $this->data_factory->uri($this->http->request()->getUri()->__toString())))
                ->withButtonLabel(sprintf(
                    $this->lng->txt('detail_ending_time_reached'),
                    \ilDatePresentation::formatDate(new \ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX))
                ), false)
            ;
        }

        if ($this->isUserOutOfProcessingTime()) {
            return $launcher_factory
                ->inline($this->data_factory->link('', $this->data_factory->uri($this->http->request()->getUri()->__toString())))
                ->withButtonLabel($this->lng->txt('tst_out_of_time_message'), false)
            ;
        }

        $participant_access = $this->test_access->isParticipantAllowed(
            $this->object->getId(),
            $this->user->getId()
        );

        if ($participant_access === ParticipantAccess::NOT_INVITED) {
            return $launcher_factory
                ->inline($this->data_factory->link('', $this->data_factory->uri($this->http->request()->getUri()->__toString())))
                ->withButtonLabel($this->lng->txt('tst_exam_not_assigned_participant_disclaimer'), false)
            ;
        }

        if ($participant_access !== ParticipantAccess::ALLOWED) {
            return $launcher_factory
                ->inline($this->data_factory->link('', $this->data_factory->uri($this->http->request()->getUri()->__toString())))
                ->withButtonLabel($participant_access->getAccessForbiddenMessage($this->lng), false)
            ;
        }

        if (!$this->hasAvailablePasses()) {
            return $launcher_factory
                ->inline($this->data_factory->link('', $this->data_factory->uri($this->http->request()->getUri()->__toString())))
                ->withButtonLabel($this->lng->txt('tst_launcher_button_label_passes_limit_reached'), false);
        }

        if ($this->blockUserAfterHavingPassed()) {
            return $launcher_factory
                ->inline($this->data_factory->link('', $this->data_factory->uri($this->http->request()->getUri()->__toString())))
                ->withButtonLabel($this->lng->txt('tst_already_passed_cannot_retake'), false)
            ;
        }

        $next_pass_allowed_timestamp = 0;
        if (!$this->object->isNextPassAllowed($this->test_passes_selector, $next_pass_allowed_timestamp)) {
            return $launcher_factory
                ->inline($this->data_factory->link('', $this->data_factory->uri($this->http->request()->getUri()->__toString())))
                ->withButtonLabel(
                    sprintf(
                        $this->lng->txt('wait_for_next_pass_hint_msg'),
                        \ilDatePresentation::formatDate(new \ilDateTime($next_pass_allowed_timestamp, IL_CAL_UNIX)),
                    ),
                    false
                )
            ;
        }

        if ($this->lastPassSuspended()) {
            return $launcher_factory->inline($this->getResumeLauncherLink());
        }

        if ($this->isModalLauncherNeeded()) {
            return $this->buildModalLauncher();
        }
        return $launcher_factory->inline($this->getStartLauncherLink());
    }

    private function getResumeLauncherLink(): Link
    {
        return $this->data_factory->link(
            $this->lng->txt('tst_resume_test'),
            $this->buildLauncherLinkUrl(\ilTestPlayerCommands::RESUME_PLAYER)
        );
    }

    private function buildModalLauncher(): Launcher
    {
        $launcher = $this->ui_factory->launcher()->inline($this->getModalLauncherLink())
            ->withInputs(
                $this->ui_factory->input()->field()->group($this->getModalLauncherInputs()),
                function (Result $result) {
                    $this->evaluateLauncherModalForm($result);
                },
                $this->getModalLauncherMessageBox()
            )->withModalSubmitLabel($this->lng->txt('continue'));

        $request = $this->http->request();
        $key = 'launcher_id';
        if (array_key_exists($key, $request->getQueryParams())
            && $request->getQueryParams()[$key] === 'exam_modal') {
            $launcher = $launcher->withRequest($request);
        }

        return $launcher;
    }

    private function getModalLauncherLink(): Link
    {
        return $this->data_factory->link(
            $this->lng->txt('tst_exam_start'),
            $this->data_factory->uri(
                $this->http->request()->getUri()->__toString()
            )->withParameter('launcher_id', 'exam_modal')
        );
    }

    private function getModalLauncherInputs(): array
    {
        if ($this->main_settings->getIntroductionSettings()->getExamConditionsCheckboxEnabled()) {
            $modal_inputs['exam_conditions'] = $this->ui_factory->input()->field()->checkbox(
                $this->lng->txt('tst_exam_conditions'),
                $this->lng->txt('tst_exam_conditions_label')
            )->withRequired(true)
            ->withAdditionalTransformation(
                $this->refinery->custom()->constraint(
                    static fn(bool $value): bool => $value,
                    $this->lng->txt('tst_exam_conditions_not_checked_message'),
                )
            );
        }

        if ($this->main_settings->getAccessSettings()->getPasswordEnabled()) {
            $modal_inputs['exam_password'] = $this->ui_factory->input()->field()->password(
                $this->lng->txt('tst_exam_password'),
                $this->lng->txt('tst_exam_password_label')
            )->withRevelation(true)
            ->withRequired(true)
            ->withAdditionalTransformation(
                $this->refinery->custom()->constraint(
                    fn(Password $value): bool => $value->toString() === $this->main_settings->getAccessSettings()->getPassword(),
                    $this->lng->txt('tst_exam_password_invalid_message'),
                )
            );
        }

        if ($this->user->isAnonymous()) {
            $access_code_input = $this->ui_factory->input()->field()->text(
                $this->lng->txt('tst_exam_access_code'),
                $this->lng->txt('tst_exam_access_code_label')
            )->withAdditionalTransformation(
                $this->refinery->custom()->constraint(
                    fn(string $value): bool => $value === '' || $this->test_session->isAccessCodeUsed($value),
                    $this->lng->txt('tst_exam_access_code_invalid_message'),
                )
            );

            $access_code_from_session = $this->test_session->getAccessCodeFromSession();
            if ($access_code_from_session) {
                $access_code_input = $access_code_input->withValue($access_code_from_session);
            }

            $modal_inputs['exam_access_code'] = $access_code_input;
        }

        if (
            $this->test_passes_selector->getLastFinishedPass() >= 0
            && $this->main_settings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed()
        ) {
            $modal_inputs['exam_use_previous_answers'] = $this->ui_factory->input()->field()->checkbox(
                $this->lng->txt('tst_exam_use_previous_answers'),
                $this->lng->txt('tst_exam_use_previous_answers_label')
            )->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    static fn(bool $value): string => $value ? '1' : '0'
                )
            );
        }

        return $modal_inputs ?? [];
    }

    private function getModalLauncherMessageBox(): ?MessageBox
    {
        $exam_conditions_enabled = $this->main_settings->getIntroductionSettings()->getExamConditionsCheckboxEnabled();
        $password_enabled = $this->main_settings->getAccessSettings()->getPasswordEnabled();

        if ($exam_conditions_enabled && $password_enabled) {
            $modal_message_box_message = $this->lng->txt('tst_exam_modal_message_conditions_and_password');
        } elseif ($exam_conditions_enabled) {
            $modal_message_box_message = $this->lng->txt('tst_exam_modal_message_conditions');
        } elseif ($password_enabled) {
            $modal_message_box_message = $this->lng->txt('tst_exam_modal_message_password');
        }

        return isset($modal_message_box_message) ? $this->ui_factory->messageBox()->info($modal_message_box_message) : null;
    }

    private function getStartLauncherLink(): Link
    {
        return $this->data_factory->link(
            $this->lng->txt('tst_exam_start'),
            $this->buildLauncherLinkUrl(\ilTestPlayerCommands::INIT_TEST)
        );
    }

    private function buildLauncherLinkUrl(
        string $cmd
    ): \ILIAS\Data\URI {
        return $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                [
                    \ilRepositoryGUI::class,
                    \ilObjTestGUI::class,
                    $this->object->isFixedTest()
                        ? \ilTestPlayerFixedQuestionSetGUI::class
                        : \ilTestPlayerRandomQuestionSetGUI::class
                ],
                $cmd
            )
        );
    }

    private function evaluateLauncherModalForm(Result $result): void
    {
        if ($result->isError()) {
            return;
        }

        $anonymous = $this->user->isAnonymous();
        if (array_key_exists('exam_access_code', $result->value())) {
            $value = $result->value()['exam_access_code'];
            if ($anonymous && !empty($value)) {
                $this->test_session->setAccessCodeToSession($value);
            } else {
                $this->test_session->unsetAccessCodeInSession();
            }
        }

        if (
            !$anonymous &&
            $this->main_settings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed()
        ) {
            $this->user->setPref(
                'tst_use_previous_answers',
                $result->value()['exam_use_previous_answers'] ?? '0'
            );
            $this->user->update();
        }

        $password = $result->value()['exam_password']->toString() ?? '';
        if ($password === $this->main_settings->getAccessSettings()->getPassword()) {
            \ilSession::set('tst_password_' . $this->object->getTestId(), $password);
        } else {
            \ilSession::set('tst_password_' . $this->object->getTestId(), '');
            $this->test_session->setPasswordChecked(false);
        }

        $this->ctrl->redirectByClass(
            (new \ilTestPlayerFactory($this->object))->getPlayerGUI()::class,
            \ilTestPlayerCommands::INIT_TEST
        );
    }

    private function testCanBeStarted(): bool
    {
        if ($this->object->getOfflineStatus()
            || !$this->object->isComplete($this->object->getQuestionSetConfig())) {
            return false;
        }

        return true;
    }

    private function isUserOutOfProcessingTime(): bool
    {
        $test_behaviour_settings = $this->object->getMainSettings()->getTestBehaviourSettings();
        if (!$test_behaviour_settings->getProcessingTimeEnabled()
            || $test_behaviour_settings->getResetProcessingTime()) {
            return false;
        }

        $active_id = $this->test_passes_selector->getActiveId();
        $last_started_pass = $this->test_session->getLastStartedPass();
        return $last_started_pass !== null
            && $this->object->isMaxProcessingTimeReached(
                $this->object->getStartingTimeOfUser($active_id, $last_started_pass),
                $active_id
            );
    }

    private function blockUserAfterHavingPassed(): bool
    {
        if ($this->main_settings->getTestBehaviourSettings()->getBlockAfterPassedEnabled()) {
            return $this->test_passes_selector->getLastFinishedPass() >= 0
                && $this->test_passes_selector->hasTestPassedOnce($this->test_session->getActiveId());
        }

        return false;
    }

    private function hasAvailablePasses(): bool
    {
        $nr_of_tries = $this->object->getNrOfTries();

        return $nr_of_tries === 0 || (count($this->test_passes_selector->getExistingPasses()) <= $nr_of_tries && count($this->test_passes_selector->getClosedPasses()) < $nr_of_tries);
    }

    private function lastPassSuspended(): bool
    {
        return (count($this->test_passes_selector->getExistingPasses()) - count($this->test_passes_selector->getClosedPasses())) === 1;
    }

    private function isModalLauncherNeeded(): bool
    {
        return (
            $this->main_settings->getIntroductionSettings()->getExamConditionsCheckboxEnabled()
            || $this->main_settings->getAccessSettings()->getPasswordEnabled()
            || $this->main_settings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed()
                && $this->test_passes_selector->getLastFinishedPass() >= 0
            || $this->user->isAnonymous()
        );
    }

    private function getSkillLevelThresholdsMissingInfo(): MessageBox
    {
        return $this->ui_factory->messageBox()->failure(
            $this->lng->txt('tst_skl_level_thresholds_missing')
        )->withLinks([
            $this->ui_factory->link()->standard(
                $this->lng->txt('tst_skl_level_thresholds_link'),
                $this->ctrl->getLinkTargetByClass(
                    [
                        \ilRepositoryGUI::class,
                        \ilObjTestGUI::class,
                        \ilTestSkillAdministrationGUI::class,
                        \ilTestSkillLevelThresholdsGUI::class
                    ],
                    \ilTestSkillLevelThresholdsGUI::CMD_SHOW_SKILL_THRESHOLDS
                )
            )
        ]);
    }

    private function areSkillLevelThresholdsMissing(): bool
    {
        if (!$this->object->isSkillServiceEnabled()) {
            return false;
        }

        $questionContainerId = $this->object->getId();

        $assignmentList = new \ilAssQuestionSkillAssignmentList($this->database);
        $assignmentList->setParentObjId($questionContainerId);
        $assignmentList->loadFromDb();

        foreach ($assignmentList->getUniqueAssignedSkills() as $data) {
            foreach ($data['skill']->getLevelData() as $level) {
                $threshold = new \ilTestSkillLevelThreshold($this->database);
                $threshold->setTestId($this->object->getTestId());
                $threshold->setSkillBaseId($data['skill_base_id']);
                $threshold->setSkillTrefId($data['skill_tref_id']);
                $threshold->setSkillLevelId($level['id']);

                if (!$threshold->dbRecordExists()) {
                    return true;
                }
            }
        }

        return false;
    }
}
