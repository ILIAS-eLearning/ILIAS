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
    private readonly ILIAS\DI\Container $dic;
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
        $this->dic = $DIC;
        $this->ui_factory = $this->dic->ui()->factory();
        $this->ui_renderer = $this->dic->ui()->renderer();
        $this->lng = $this->dic->language();
        $this->ctrl = $this->dic->ctrl();
        $this->tpl = $this->dic->ui()->mainTemplate();
        $this->http = $this->dic->http();
        $this->tabs = $this->dic->tabs();
        $this->access = $this->dic->access();
        $this->ref_id = $this->object->getRefId();

        $db = $this->dic->database();
        $this->test_session_factory = new ilTestSessionFactory($this->object, $db, $this->user);
        $this->test_sequence_factory = new ilTestSequenceFactory($this->object, $db);

        $testSession = $this->test_session_factory->getSession();

        $this->test_passes_selector = new ilTestPassesSelector($db, $this->object);
        $this->test_passes_selector->setActiveId($testSession->getActiveId());
        $this->test_passes_selector->setLastFinishedPass($testSession->getLastFinishedPass());
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
        $mainSettings = $this->object->getMainSettings();
        $anonymous = $this->user->isAnonymous();
        $data_factory = new Factory();
        $url = $data_factory->uri($this->http->request()->getUri()->__toString());
        $modalInputs = [];

        $examConditionsEnabled = $mainSettings->getIntroductionSettings()->getExamConditionsCheckboxEnabled();

        if ($examConditionsEnabled) {
            $modalInputs[] = $this->ui_factory->input()->field()->checkbox(
                $this->lng->txt('tst_exam_conditions'),
                $this->lng->txt('tst_exam_conditions_label')
            )->withDedicatedName('exam_conditions')->withRequired(true);
        }

        $passwordEnabled = $mainSettings->getAccessSettings()->getPasswordEnabled();

        if ($passwordEnabled) {
            $modalInputs[] = $this->ui_factory->input()->field()->text(
                $this->lng->txt('tst_exam_password'),
                $this->lng->txt('tst_exam_password_label')
            )->withDedicatedName('exam_password')->withRequired(true);
        }

        if ($anonymous) {
            $modalInputs[] = $this->ui_factory->input()->field()->text(
                $this->lng->txt('tst_exam_access_code'),
                $this->lng->txt('tst_exam_access_code_label')
            )->withDedicatedName('exam_access_code');
        }

        if ($mainSettings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed()) {
            $modalInputs[] = $this->ui_factory->input()->field()->checkbox(
                $this->lng->txt('tst_exam_use_previous_answers'),
                $this->lng->txt('tst_exam_use_previous_answers_label')
            )->withDedicatedName('exam_use_previous_answers');
        }

        $testBehaviourSettings = $mainSettings->getTestBehaviourSettings();

        return $this->ui_factory->launcher()
            ->inline($data_factory->link($this->lng->txt('tst_exam_start'), $url->withParameter('launcher_id', 'exam_modal')))
            ->withStatusIcon($this->ui_factory->symbol()->icon()->standard('ps', 'authentification needed', 'large'))
            ->withStatusMessageBox($this->ui_factory->messageBox()->info(
                (($examConditionsEnabled || $passwordEnabled) ? 'You will be asked for the password and/or your approval of the exam conditions when you start the test.' : '') . ' ' .
                ($testBehaviourSettings->getProcessingTimeEnabled() ? sprintf('Your Time-Limit is %s minutes.', $testBehaviourSettings->getProcessingTimeAsMinutes()) : '') . ' ' .
                (($this->object->getNrOfTries() !== 0) ? sprintf('Your limit of test attempts is %s.', $this->object->getNrOfTries()) : '')
            ))
            ->withDescription(
                ($testBehaviourSettings->getProcessingTimeEnabled() ? '<p>' . sprintf('You will have <b>%s minutes</b> to answer all questions.', $testBehaviourSettings->getProcessingTimeAsMinutes()) . '</p>' : '') .
                '</p>' . 'Please make sure that you have the time to complete the test and that you will be undisturbed. There is no way for you to pause or re-take this test.' . '</p>'
            )
            ->withInputs(
                $this->ui_factory->input()->field()->group($modalInputs),
                function (Result $result) {$this->evaluateTestScreenModalForm($result);},
                $this->ui_factory->messageBox()->info($this->lng->txt('tst_exam_conditions_modal_desc'))
            );
    }

    private function handleTestScreenRenderOutOfTimeMessage(array $elements): array
    {
        $elements[] = $this->ui_factory->messageBox()->failure($this->lng->txt('tst_out_of_time_message'));

        return $elements;
    }

    private function evaluateTestScreenModalForm(Result $result): void
    {
        $mainSettings = $this->object->getMainSettings();
        $anonymous = $this->user->isAnonymous();

        if ($result->isOK()) {
            $mainTemplate = $this->dic->ui()->mainTemplate();

            $conditionsMet = true;
            foreach ($result->value() as $key => $value) {
                if (!$conditionsMet) {
                    break;
                }

                switch ($key) {
                    case 'exam_conditions':
                        $examConditionsValue = (bool) $value;
                        $conditionsMet = $examConditionsValue;
                        if (!$examConditionsValue) {
                            $mainTemplate->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE, $this->lng->txt('tst_exam_conditions_not_checked_message'), true);
                        }
                        break;
                    case 'exam_password':
                        $password = $value;
                        $examPasswordValid = ($password === $mainSettings->getAccessSettings()->getPassword());
                        $conditionsMet = $examPasswordValid;
                        if (!$examPasswordValid) {
                            $mainTemplate->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE, $this->lng->txt('tst_exam_password_invalid_message'), true);
                        }
                        break;
                    case 'exam_access_code':
                        if (!empty($value) && $anonymous) {
                            ilSession::set('tst_access_code', $value);
                        }
                        break;
                    case 'exam_use_previous_answers':
                        $examUsePreviousAnswersValue = (string) (int) $value;
                        break;
                }
            }

            if (empty($result->value())) {
                $mainTemplate->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE, $this->lng->txt('tst_exam_required_fields_not_filled_message'), true);
            } elseif ($conditionsMet) {
                if (
                    !$anonymous &&
                    $mainSettings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed() &&
                    isset($examUsePreviousAnswersValue)
                ) {
                    $this->user->setPref('tst_use_previous_answers', $examUsePreviousAnswersValue);
                }
                if (isset($password) && $password === $mainSettings->getAccessSettings()->getPassword()) {
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

        $existingPasses = $this->test_passes_selector->getExistingPasses();
        $closedPasses = $this->test_passes_selector->getClosedPasses();
        $nrOfTries = $this->object->getNrOfTries();

        $mainSettings = $this->object->getMainSettings();
        $examConditionsEnabled = $mainSettings->getIntroductionSettings()->getExamConditionsCheckboxEnabled();
        $passwordEnabled = $mainSettings->getAccessSettings()->getPasswordEnabled();
        $accessCodeEnabled = $mainSettings->getGeneralSettings()->getAnonymity();
        $allowPreviousAnswersEnabled = $mainSettings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed();

        if ($nrOfTries === 0 || count($existingPasses) <= $nrOfTries) {
            if ((count($existingPasses) - count($closedPasses)) === 1) {
                return 'showContinueButton';
            }
            if (count($existingPasses) < $nrOfTries) {
                return ($examConditionsEnabled || $passwordEnabled || $accessCodeEnabled || $allowPreviousAnswersEnabled) ? 'showModal' : 'showStartButton';
            }
        }

        return '';
    }
}