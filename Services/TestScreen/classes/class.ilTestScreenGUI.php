<?php

declare(strict_types=1);

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Link;
use ILIAS\Data\Result;
use ILIAS\UI\Component\Launcher\Launcher;
use ILIAS\UI\Component\Launcher\Factory as LauncherFactory;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\Services as HTTPServices;

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
 * Class ilTestScreenGUI
 *
 * @author Matheus Zych <mzych@databay.de>
 */
class ilTestScreenGUI
{
    private readonly UIFactory $ui_factory;
    private readonly UIRenderer $ui_renderer;
    private readonly ilLanguage $lng;
    private readonly ilCtrl $ctrl;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilTestSequenceFactory $test_sequence_factory;
    private readonly HTTPServices $http;
    private readonly ilTestPassesSelector $test_passes_selector;
    private readonly ilTabsGUI $tabs;
    private readonly ilAccessHandler $access;
    private readonly int $ref_id;
    private readonly ilObjTestMainSettings $main_settings;
    private readonly ilTestSession $test_session;
    private readonly DataFactory $data_factory;

    public function __construct(
        private readonly ilObjTest $object,
        private readonly ilObjUser $user,
    ) {
        /** @var ILIAS\DI\Container $DIC **/
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->ref_id = $this->object->getRefId();
        $this->main_settings = $this->object->getMainSettings();
        $this->data_factory = new DataFactory();

        $db = $DIC->database();
        $this->test_session = (new ilTestSessionFactory($this->object, $db, $this->user))->getSession();
        $this->test_sequence_factory = new ilTestSequenceFactory($this->object, $db, $DIC->testQuestionPool()->questionInfo());

        $this->test_passes_selector = new ilTestPassesSelector($db, $this->object);
        $this->test_passes_selector->setActiveId($this->test_session->getActiveId());
        $this->test_passes_selector->setLastFinishedPass($this->test_session->getLastFinishedPass());
    }

    public function executeCommand(): void
    {
        if ($this->access->checkAccess('read', '', $this->ref_id)) {
            $this->{$this->ctrl->getCmd()}();
        } else {
            $this->tpl->setOnScreenMessage('failure', sprintf(
                $this->lng->txt('msg_no_perm_read_item'),
                $this->object->getTitle()
            ), true);
            $this->ctrl->setParameterByClass('ilrepositorygui', 'ref_id', ROOT_FOLDER_ID);
            $this->ctrl->redirectByClass('ilrepositorygui');
        }
    }

    public function testScreen(): void
    {
        $this->tabs->activateTab(ilTestTabsManager::TAB_ID_TEST);
        $this->tpl->setPermanentLink($this->object->getType(), $this->ref_id);

        $elements = [];

        $elements = $this->handleRenderIntroduction($elements);
        $elements = $this->handleRenderPreviousAccessCode($elements);
        $elements = $this->handleRenderSessionSettings($elements);

        $this->tpl->setContent($this->ui_renderer->render(!$this->object->getOfflineStatus() ? $this->handleRenderLauncher($elements) : $elements));
    }

    private function handleRenderIntroduction(array $elements): array
    {
        $introduction = $this->object->getIntroduction();

        if (
            $this->main_settings->getIntroductionSettings()->getIntroductionEnabled() &&
            !empty($introduction)
        ) {
            $elements[] = $this->ui_factory->panel()->standard(
                $this->lng->txt('tst_introduction'),
                $this->ui_factory->legacy($introduction),
            );
        }

        return $elements;
    }

    private function handleRenderPreviousAccessCode(array $elements): array
    {
        if ($this->user->isAnonymous()) {
            $elements[] = $this->ui_factory->panel()->standard(
                $this->lng->txt('tst_exam_previous_access_code'),
                $this->ui_factory->messageBox()->info($this->test_session->getAccessCodeFromSession() ?? $this->lng->txt('tst_previous_access_code_not_available'))
            );
        }

        return $elements;
    }

    private function handleRenderSessionSettings(array $elements): array
    {
        $elements[] = $this->ui_factory->panel()->standard($this->lng->txt('tst_session_settings'),[
            $this->ui_factory->item()->standard($this->lng->txt('tst_nr_of_tries'))->withDescription(
                $this->object->getNrOfTries() === 0
                    ? $this->lng->txt('unlimited')
                    : (string) $this->object->getNrOfTries()
            ),
            $this->ui_factory->item()->standard($this->lng->txt('tst_nr_of_tries_of_user'))->withDescription(
                ($this->test_session->getPass() == false)
                    ? $this->lng->txt('tst_no_tries')
                    : (string) $this->test_sequence_factory->getSequenceByTestSession($this->test_session)->getPass()
            )
        ]);

        return $elements;
    }

    private function handleRenderLauncher(array $elements): array
    {
        $launcher = $this->getLauncher();
        $request = $this->http->request();

        if (array_key_exists('launcher_id', $request->getQueryParams()) && $request->getQueryParams()['launcher_id'] === 'exam_modal') {
            $launcher = $launcher->withRequest($request);
        }

        $elements[] = $launcher;

        return $elements;
    }

    private function getLauncher(): Launcher
    {
        $launcher = $this->ui_factory->launcher();

        if ($this->isUserOutOfProcessingTime()) {
            return $launcher
                ->inline($this->data_factory->link('', $this->data_factory->uri($this->http->request()->getUri()->__toString())))
                ->withButtonLabel($this->lng->txt('tst_out_of_time_message'), false)
            ;
        }

        if ($this->hasAvailablePasses()) {
            if ($this->lastPassSuspended() && $this->insideProcessingTime()) {
                ilSession::set('tst_password_' . $this->object->getTestId(), $this->object->getPassword());

                $launcher =  $launcher
                    ->inline($this->getResumeLauncherLink())
                    ->withDescription($this->getResumeLauncherDescription())
                ;
            }

            if ($this->newPassCanBeStarted()) {
                if ($this->isModalLauncherNeeded()) {
                    $launcher = $launcher
                        ->inline($this->getModalLauncherLink())
                        ->withDescription($this->getModalLauncherDescription())
                        ->withInputs(
                            $this->ui_factory->input()->field()->group($this->getModalLauncherInputs()),
                            function (Result $result) {$this->evaluateLauncherModalForm($result);},
                            $this->getModalLauncherMessageBox()
                        )
                    ;
                } else {
                    $launcher = $launcher
                        ->inline($this->getStartLauncherLink())
                        ->withDescription($this->getStartLauncherDescription())
                    ;
                }
            }
        } else {
            $launcher = $launcher
                ->inline($this->data_factory->link($this->lng->txt('crs_loc_passes_reached'), $this->data_factory->uri($this->http->request()->getUri()->__toString())))
                ->withButtonLabel($this->lng->txt('tst_launcher_button_label_no_tries_left'), false)
            ;
        }

        if ($launcher instanceof LauncherFactory) {
            $launcher = $launcher->inline($this->data_factory->link('Test', $this->data_factory->uri($this->http->request()->getUri()->__toString())));
        }

        $launcher = $this->handleLauncherLocked($launcher);

        $launcher = $this->handleLauncherStatusIcon($launcher);

        $launcher = $this->handleLauncherStatusMessageBox($launcher);

        return $launcher;
    }

    private function getResumeLauncherLink(): Link
    {
        $url = $this->ctrl->getLinkTarget((new ilTestPlayerFactory($this->object))->getPlayerGUI(), ilTestPlayerCommands::RESUME_PLAYER);
        return $this->data_factory->link($this->lng->txt('tst_resume_test'), $this->data_factory->uri(ILIAS_HTTP_PATH . '/' . $url));
    }

    private function getResumeLauncherDescription(): string
    {
        $test_behaviour_settings = $this->main_settings->getTestBehaviourSettings();

        $launcher_description = '';
        $launcher_description_elements = [];

        if ($test_behaviour_settings->getProcessingTimeEnabled()) {
            $launcher_description_elements[] = sprintf($this->lng->txt('tst_time_limit_message'), $test_behaviour_settings->getProcessingTimeAsMinutes());
        }

        if ($this->object->isEndingTimeEnabled()) {
            if ($this->object->endingTimeReached()) {
                $launcher_description_elements[] = sprintf(
                    $this->lng->txt('detail_ending_time_reached'),
                    ilDatePresentation::formatDate(new ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX))
                );
            } else {
                $launcher_description_elements[] = $this->lng->txt('tst_disclaimer');
                $launcher_description_elements[] = sprintf(
                    $this->lng->txt('tst_exam_ending_time_message'),
                    ilDatePresentation::formatDate(new ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX))
                );
            }
        }

        foreach ($launcher_description_elements as $launcher_description_element) {
            $launcher_description .=  '<p>' . $launcher_description_element . '</p>';
        }

        return $launcher_description;
    }

    private function getModalLauncherLink(): Link
    {
        $uri = $this->data_factory->uri($this->http->request()->getUri()->__toString())->withParameter('launcher_id', 'exam_modal');
        return $this->data_factory->link($this->lng->txt('tst_exam_start'), $uri);
    }

    private function getModalLauncherDescription(): string
    {
        $test_behaviour_settings = $this->main_settings->getTestBehaviourSettings();

        $launcher_description = '';
        $launcher_description_elements = [];

        if ($test_behaviour_settings->getProcessingTimeEnabled()) {
            $launcher_description_elements[] = sprintf($this->lng->txt('tst_time_limit_message'), $test_behaviour_settings->getProcessingTimeAsMinutes());
        }

        if ($this->object->isEndingTimeEnabled() && !$this->object->endingTimeReached()) {
            $launcher_description_elements[] = $this->lng->txt('tst_disclaimer');
        }

        if ($this->object->isStartingTimeEnabled() && !$this->object->startingTimeReached()) {
            $launcher_description_elements[] = sprintf(
                $this->lng->txt('detail_starting_time_not_reached'),
                ilDatePresentation::formatDate(new ilDateTime($this->object->getStartingTime(), IL_CAL_UNIX))
            );
        }

        if ($this->object->isEndingTimeEnabled()) {
            if ($this->object->endingTimeReached()) {
                $launcher_description_elements[] = sprintf(
                    $this->lng->txt('detail_ending_time_reached'),
                    ilDatePresentation::formatDate(new ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX))
                );
            } else {
                $launcher_description_elements[] = sprintf(
                    $this->lng->txt('tst_exam_ending_time_message'),
                    ilDatePresentation::formatDate(new ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX))
                );
            }
        }

        foreach ($launcher_description_elements as $launcher_description_element) {
            $launcher_description .=  '<p>' . $launcher_description_element . '</p>';
        }

        return $launcher_description;
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

    private function getModalLauncherInputs(): array
    {
        if ($this->main_settings->getIntroductionSettings()->getExamConditionsCheckboxEnabled()) {
            $modal_inputs[] = $this->ui_factory->input()->field()->checkbox(
                $this->lng->txt('tst_exam_conditions'),
                $this->lng->txt('tst_exam_conditions_label')
            )->withDedicatedName('exam_conditions')->withRequired(true);
        }

        if ($this->main_settings->getAccessSettings()->getPasswordEnabled()) {
            $modal_inputs[] = $this->ui_factory->input()->field()->text(
                $this->lng->txt('tst_exam_password'),
                $this->lng->txt('tst_exam_password_label')
            )->withDedicatedName('exam_password')->withRequired(true);
        }

        if ($this->user->isAnonymous()) {
            $access_code_input = $this->ui_factory->input()->field()->text(
                $this->lng->txt('tst_exam_access_code'),
                $this->lng->txt('tst_exam_access_code_label')
            )->withDedicatedName('exam_access_code');

            $access_code_from_session = $this->test_session->getAccessCodeFromSession();
            if ($access_code_from_session) {
                $access_code_input = $access_code_input->withValue($access_code_from_session);
            }

            $modal_inputs[] = $access_code_input;
        }

        if ($this->main_settings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed()) {
            $modal_inputs[] = $this->ui_factory->input()->field()->checkbox(
                $this->lng->txt('tst_exam_use_previous_answers'),
                $this->lng->txt('tst_exam_use_previous_answers_label')
            )->withDedicatedName('exam_use_previous_answers');
        }

        return $modal_inputs ?? [];
    }

    private function getStartLauncherLink(): Link
    {
        $url = $this->ctrl->getLinkTarget((new ilTestPlayerFactory($this->object))->getPlayerGUI(), ilTestPlayerCommands::START_TEST);
        return $this->data_factory->link($this->lng->txt('tst_exam_start'), $this->data_factory->uri(ILIAS_HTTP_PATH . '/' . $url));
    }

    private function getStartLauncherDescription(): string
    {
        $launcher_description = '';
        $launcher_description_elements = [];
        $test_behaviour_settings = $this->main_settings->getTestBehaviourSettings();

        if ($this->object->isEndingTimeEnabled() && !$this->object->endingTimeReached()) {
            $launcher_description_elements[] = $this->lng->txt('tst_disclaimer');
        }

        if ($test_behaviour_settings->getProcessingTimeEnabled()) {
            $launcher_description_elements[] = sprintf($this->lng->txt('tst_time_limit_message'), $test_behaviour_settings->getProcessingTimeAsMinutes());
        }

        $nr_of_tries = $this->object->getNrOfTries();

        if ($nr_of_tries !== 0) {
            $launcher_description_elements[] = sprintf($this->lng->txt('tst_attempt_limit_message'), $nr_of_tries);
        }

        if ($this->object->isStartingTimeEnabled() && !$this->object->startingTimeReached()) {
            $launcher_description_elements[] = sprintf(
                $this->lng->txt('detail_starting_time_not_reached'),
                ilDatePresentation::formatDate(new ilDateTime($this->object->getStartingTime(), IL_CAL_UNIX))
            );
        }

        if ($this->object->isEndingTimeEnabled()) {
            if ($this->object->endingTimeReached()) {
                $launcher_description_elements[] = sprintf(
                    $this->lng->txt('detail_ending_time_reached'),
                    ilDatePresentation::formatDate(new ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX))
                );
            } else {
                $launcher_description_elements[] = sprintf(
                    $this->lng->txt('tst_exam_ending_time_message'),
                    ilDatePresentation::formatDate(new ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX))
                );
            }
        }

        foreach ($launcher_description_elements as $launcher_description_element) {
            $launcher_description .=  '<p>' . $launcher_description_element . '</p>';
        }

        return $launcher_description;
    }

    private function evaluateLauncherModalForm(Result $result): void
    {
        if ($result->isOK()) {
            $conditions_met = true;
            $access_settings_password = $this->main_settings->getAccessSettings()->getPassword();
            $anonymous = $this->user->isAnonymous();
            foreach ($result->value() as $key => $value) {
                if (!$conditions_met) {
                    break;
                }

                switch ($key) {
                    case 'exam_conditions':
                        $exam_conditions_value = (bool) $value;
                        $conditions_met = $exam_conditions_value;
                        if (!$exam_conditions_value) {
                            $this->tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE, $this->lng->txt('tst_exam_conditions_not_checked_message'), true);
                        }
                        break;
                    case 'exam_password':
                        $password = $value;
                        $exam_password_valid = ($password === $access_settings_password);
                        $conditions_met = $exam_password_valid;
                        if (!$exam_password_valid) {
                            $this->tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE, $this->lng->txt('tst_exam_password_invalid_message'), true);
                        }
                        break;
                    case 'exam_access_code':
                        if ($anonymous && !empty($value)) {
                            $this->test_session->setAccessCodeToSession($value);
                        } else {
                            $this->test_session->unsetAccessCodeInSession();
                        }
                        break;
                    case 'exam_use_previous_answers':
                        $exam_use_previous_answers_value = (string) (int) $value;
                        break;
                }
            }

            if (empty($result->value())) {
                $this->tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE, $this->lng->txt('tst_exam_required_fields_not_filled_message'), true);
            } elseif ($conditions_met) {
                if (
                    !$anonymous &&
                    isset($exam_use_previous_answers_value) &&
                    $this->main_settings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed()
                ) {
                    $this->user->setPref('tst_use_previous_answers', $exam_use_previous_answers_value);
                }

                if (isset($password) && $password === $access_settings_password) {
                    ilSession::set('tst_password_' . $this->object->getTestId(), $password);
                } else {
                    ilSession::set('tst_password_' . $this->object->getTestId(), '');
                    $this->test_session->setPasswordChecked(false);
                }

                $this->ctrl->redirectByClass((new ilTestPlayerFactory($this->object))->getPlayerGUI()::class, ilTestPlayerCommands::INIT_TEST);
            }
        }
    }

    private function handleLauncherLocked(Launcher $launcher): Launcher
    {
        if ($this->object->isStartingTimeEnabled() && !$this->object->startingTimeReached()) {
            $launcher = $launcher->withButtonLabel($this->lng->txt('tst_launcher_button_label_cannot_start_yet'), false);
        }

        if ($this->object->isEndingTimeEnabled() && $this->object->endingTimeReached()) {
            $launcher = $launcher->withButtonLabel(sprintf(
                $this->lng->txt('detail_ending_time_reached'),
                ilDatePresentation::formatDate(new ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX))
            ), false);
        }

        return $launcher;
    }

    private function handleLauncherStatusIcon(Launcher $launcher): Launcher
    {
        if ($this->isLauncherStatusIconNeeded()) {
            return $launcher->withStatusIcon($this->ui_factory->symbol()->icon()->standard('ps', 'authentification needed', 'large'));
        }

        return $launcher;
    }

    private function isLauncherStatusIconNeeded(): bool
    {
        return ($this->main_settings->getIntroductionSettings()->getExamConditionsCheckboxEnabled() || $this->main_settings->getAccessSettings()->getPasswordEnabled())
            && (!$this->object->isEndingTimeEnabled() || !$this->object->endingTimeReached());
    }

    private function handleLauncherStatusMessageBox(Launcher $launcher): Launcher
    {
        if ($this->object->isEndingTimeEnabled() && $this->object->endingTimeReached()) {
            return $launcher;
        }

        $exam_conditions_enabled = $this->main_settings->getIntroductionSettings()->getExamConditionsCheckboxEnabled();
        $password_enabled = $this->main_settings->getAccessSettings()->getPasswordEnabled();

        $test_behaviour_settings = $this->main_settings->getTestBehaviourSettings();
        $processing_time_enabled = $test_behaviour_settings->getProcessingTimeEnabled();
        $processing_time_as_minutes = $test_behaviour_settings->getProcessingTimeAsMinutes();

        $launcher_status_message_box_message = '';
        $launcher_status_message_box_message_elements = [];

        if ($exam_conditions_enabled && $password_enabled) {
            $launcher_status_message_box_message_elements[] = $this->lng->txt('tst_launcher_status_message_conditions_and_password');
        } else if ($exam_conditions_enabled) {
            $launcher_status_message_box_message_elements[] = $this->lng->txt('tst_launcher_status_message_conditions');
        } else if ($password_enabled) {
            $launcher_status_message_box_message_elements[] = $this->lng->txt('tst_launcher_status_message_password');
        }

        if ($processing_time_enabled) {
            $launcher_status_message_box_message_elements[] = sprintf($this->lng->txt('tst_time_limit_message'), $processing_time_as_minutes);
        }

        $nr_of_tries = $this->object->getNrOfTries();

        if ($nr_of_tries !== 0) {
            $launcher_status_message_box_message_elements[] = sprintf($this->lng->txt('tst_attempt_limit_message'), $nr_of_tries);
        }

        foreach ($launcher_status_message_box_message_elements as $launcher_status_message_box_message_element) {
            $launcher_status_message_box_message .=  ' ' . $launcher_status_message_box_message_element;
        }

        if (!empty($launcher_status_message_box_message)) {
            $launcher = $launcher->withStatusMessageBox($this->ui_factory->messageBox()->info($launcher_status_message_box_message));
        }

        return $launcher;
    }

    private function isUserOutOfProcessingTime(): bool
    {
        $active_id = $this->test_passes_selector->getActiveId();
        $last_started_pass = $this->test_session->getLastStartedPass();
        return $last_started_pass !== null && $this->object->isMaxProcessingTimeReached($this->object->getStartingTimeOfUser($active_id, $last_started_pass), $active_id);
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

    private function newPassCanBeStarted(): bool
    {
        $nr_of_tries = $this->object->getNrOfTries();

        return $nr_of_tries === 0 || count($this->test_passes_selector->getExistingPasses()) < $nr_of_tries;
    }

    private function isModalLauncherNeeded(): bool
    {
        return (
            $this->main_settings->getIntroductionSettings()->getExamConditionsCheckboxEnabled()
            || $this->main_settings->getAccessSettings()->getPasswordEnabled()
            || $this->main_settings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed()
            || ($this->main_settings->getGeneralSettings()->getAnonymity() && $this->user->isAnonymous())
        );
    }

    private function insideProcessingTime(): bool
    {
        return !(
            ($this->object->isStartingTimeEnabled() && !$this->object->startingTimeReached())
            || ($this->object->isEndingTimeEnabled() && $this->object->endingTimeReached())
        );
    }
}