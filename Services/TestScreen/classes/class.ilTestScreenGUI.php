<?php

declare(strict_types=1);

use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\UI\Component\Launcher\Launcher;
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
    private readonly ilTestSessionFactory $test_session_factory;
    private readonly ilTestSequenceFactory $test_sequence_factory;
    private readonly HTTPServices $http;
    private readonly ilTestPassesSelector $test_passes_selector;
    private readonly ilTabsGUI $tabs;
    private readonly ilAccessHandler $access;
    private readonly int $ref_id;

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

        $db = $DIC->database();
        $this->test_session_factory = new ilTestSessionFactory($this->object, $db, $this->user);
        $this->test_sequence_factory = new ilTestSequenceFactory($this->object, $db);

        $test_session = $this->test_session_factory->getSession();

        $this->test_passes_selector = new ilTestPassesSelector($db, $this->object);
        $this->test_passes_selector->setActiveId($test_session->getActiveId());
        $this->test_passes_selector->setLastFinishedPass($test_session->getLastFinishedPass());
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

        $elements = $this->handleTestScreenRenderIntroduction($elements);
        $elements = $this->handleTestScreenRenderSessionSettings($elements);

        switch ($this->evaluateTestScreenSwitchValue()) {
            case 'showModal':
                $elements = $this->handleTestScreenRenderModal($elements);
                break;
            case 'showContinueButton':
                $elements = $this->handleTestScreenRenderResumeButton($elements);
                break;
            case 'showStartButton':
                $elements = $this->handleTestScreenRenderStartButton($elements);
                break;
            case 'showOutOfTimeMessage':
                $elements = $this->handleTestScreenRenderOutOfTimeMessage($elements);
                break;

        }

        $this->tpl->setContent($this->ui_renderer->render($elements));
    }

    private function handleTestScreenRenderIntroduction(array $elements): array
    {
        if ($this->object->getMainSettings()?->getIntroductionSettings()->getIntroductionEnabled() && !empty($this->object->getIntroduction())) {
            $elements[] = $this->ui_factory->panel()->standard(
                $this->lng->txt('tst_introduction'),
                $this->ui_factory->messageBox()->info($this->object->getIntroduction())
            );
        }

        return $elements;
    }

    private function handleTestScreenRenderSessionSettings(array $elements): array
    {
        $testSession = $this->test_session_factory->getSession();

        $elements[] = $this->ui_factory->panel()->standard($this->lng->txt('tst_session_settings'),[
            $this->ui_factory->item()->standard($this->lng->txt('tst_nr_of_tries'))->withDescription(
                $this->object->getNrOfTries() === 0
                    ? $this->lng->txt('unlimited')
                    : (string) $this->object->getNrOfTries()
            ),
            $this->ui_factory->item()->standard($this->lng->txt('tst_nr_of_tries_of_user'))->withDescription(
                ($testSession->getPass() == false)
                    ? $this->lng->txt('tst_no_tries')
                    : (string) $this->test_sequence_factory->getSequenceByTestSession($testSession)->getPass()
            )
        ]);

        return $elements;
    }

    private function handleTestScreenRenderResumeButton(array $elements): array
    {
        ilSession::set('tst_password_' . $this->object->getTestId(), $this->object->getPassword());

        $elements[] = $this->ui_factory->button()->primary(
            $this->lng->txt('tst_resume_test'),
            $this->ctrl->getLinkTarget((new ilTestPlayerFactory($this->object))->getPlayerGUI(), 'resumePlayer')
        );

        return $elements;
    }

    private function handleTestScreenRenderStartButton(array $elements): array
    {
        $elements[] = $this->ui_factory->button()->primary(
            $this->lng->txt('tst_exam_start'),
            $this->ctrl->getLinkTarget((new ilTestPlayerFactory($this->object))->getPlayerGUI(), 'startTest')
        );

        return $elements;
    }

    private function handleTestScreenRenderModal(array $elements): array
    {
        $modal = $this->getTestScreenModal();
        $request = $this->http->request();

        if (array_key_exists('launcher_id', $request->getQueryParams()) && $request->getQueryParams()['launcher_id'] === 'exam_modal') {
            $modal = $modal->withRequest($request);
        }

        $elements[] = $modal;

        return $elements;
    }

    private function getTestScreenModal(): Launcher
    {
        $main_settings = $this->object->getMainSettings();
        $anonymous = $this->user->isAnonymous();
        $data_factory = new Factory();
        $url = $data_factory->uri($this->http->request()->getUri()->__toString());
        $modal_inputs = [];

        $exam_conditions_enabled = $main_settings->getIntroductionSettings()->getExamConditionsCheckboxEnabled();

        if ($exam_conditions_enabled) {
            $modal_inputs[] = $this->ui_factory->input()->field()->checkbox(
                $this->lng->txt('tst_exam_conditions'),
                $this->lng->txt('tst_exam_conditions_label')
            )->withDedicatedName('exam_conditions')->withRequired(true);
        }

        $password_enabled = $main_settings->getAccessSettings()->getPasswordEnabled();

        if ($password_enabled) {
            $modal_inputs[] = $this->ui_factory->input()->field()->text(
                $this->lng->txt('tst_exam_password'),
                $this->lng->txt('tst_exam_password_label')
            )->withDedicatedName('exam_password')->withRequired(true);
        }

        if ($anonymous) {
            $modal_inputs[] = $this->ui_factory->input()->field()->text(
                $this->lng->txt('tst_exam_access_code'),
                $this->lng->txt('tst_exam_access_code_label')
            )->withDedicatedName('exam_access_code');
        }

        if ($main_settings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed()) {
            $modal_inputs[] = $this->ui_factory->input()->field()->checkbox(
                $this->lng->txt('tst_exam_use_previous_answers'),
                $this->lng->txt('tst_exam_use_previous_answers_label')
            )->withDedicatedName('exam_use_previous_answers');
        }

        $test_behaviour_settings = $main_settings->getTestBehaviourSettings();
        $processing_time_enabled = $test_behaviour_settings->getProcessingTimeEnabled();
        $processing_time_as_minutes = $test_behaviour_settings->getProcessingTimeAsMinutes();

        $launcher = $this->ui_factory->launcher()
            ->inline($data_factory->link($this->lng->txt('tst_exam_start'), $url->withParameter('launcher_id', 'exam_modal')))
            ->withInputs(
                $this->ui_factory->input()->field()->group($modal_inputs),
                function (Result $result) {$this->evaluateTestScreenModalForm($result);},
                $this->ui_factory->messageBox()->info($this->lng->txt('tst_exam_conditions_modal_desc'))
            )
            ->withDescription(
                '</p>' . 'Please make sure that you have the time to complete the test and that you will be undisturbed. There is no way for you to pause or re-take this test.' . '</p>' .
                ($processing_time_enabled ? '<p>' . sprintf('You will have <b>%s minutes</b> to answer all questions.', $processing_time_as_minutes) . '</p>' : '')
            )
        ;

        if ($exam_conditions_enabled || $password_enabled) {
            $launcher->withStatusIcon($this->ui_factory->symbol()->icon()->standard('ps', 'authentification needed', 'large'));
        }

        if ($exam_conditions_enabled || $password_enabled || $processing_time_enabled || $this->object->getNrOfTries() !== 0) {
            $launcher
                ->withStatusMessageBox($this->ui_factory->messageBox()->info(
                    (($exam_conditions_enabled || $password_enabled) ? 'You will be asked for the password and/or your approval of the exam conditions when you start the test.' : '') . ' ' .
                    ($processing_time_enabled ? sprintf('Your Time-Limit is %s minutes.', $processing_time_as_minutes) : '') . ' ' .
                    (($this->object->getNrOfTries() !== 0) ? sprintf('Your limit of test attempts is %s.', $this->object->getNrOfTries()) : '')
                ));
        }

        return $launcher;
    }

    private function handleTestScreenRenderOutOfTimeMessage(array $elements): array
    {
        $elements[] = $this->ui_factory->messageBox()->failure($this->lng->txt('tst_out_of_time_message'));

        return $elements;
    }

    private function evaluateTestScreenModalForm(Result $result): void
    {
        $main_settings = $this->object->getMainSettings();
        $anonymous = $this->user->isAnonymous();

        if ($result->isOK()) {
            $conditions_met = true;
            $access_settings_password = $main_settings->getAccessSettings()->getPassword();
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
                        if (!empty($value) && $anonymous) {
                            ilSession::set('tst_access_code', $value);
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
                    $main_settings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed() &&
                    isset($exam_use_previous_answers_value)
                ) {
                    $this->user->setPref('tst_use_previous_answers', $exam_use_previous_answers_value);
                }
                if (isset($password) && $password === $access_settings_password) {
                    ilSession::set('tst_password_' . $this->object->getTestId(), $password);
                }
                $this->ctrl->redirectByClass((new ilTestPlayerFactory($this->object))->getPlayerGUI()::class, 'startTest');
            }
        }
    }

    private function evaluateTestScreenSwitchValue(): string
    {
        if ($this->object->isMaxProcessingTimeReached($this->object->getStartingTime(), $this->test_passes_selector->getActiveId())) {
            return 'showOutOfTimeMessage';
        }

        $existing_passes = $this->test_passes_selector->getExistingPasses();
        $nr_of_tries = $this->object->getNrOfTries();

        $main_settings = $this->object->getMainSettings();
        $exam_conditions_enabled = $main_settings->getIntroductionSettings()->getExamConditionsCheckboxEnabled();
        $password_enabled = $main_settings->getAccessSettings()->getPasswordEnabled();
        $access_code_enabled = $main_settings->getGeneralSettings()->getAnonymity();
        $allow_previous_answers_enabled = $main_settings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed();

        if ($nr_of_tries === 0 || count($existing_passes) <= $nr_of_tries) {
            if ((count($existing_passes) - count($this->test_passes_selector->getClosedPasses())) === 1) {
                return 'showContinueButton';
            }
            if ($nr_of_tries === 0 || count($existing_passes) < $nr_of_tries) {
                return ($exam_conditions_enabled || $password_enabled || $access_code_enabled || $allow_previous_answers_enabled) ? 'showModal' : 'showStartButton';
            }
        }

        return '';
    }
}