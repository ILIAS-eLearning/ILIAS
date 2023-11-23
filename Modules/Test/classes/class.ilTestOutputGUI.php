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

use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;

/**
 * Output class for assessment test execution
 *
 * The ilTestOutputGUI class creates the output for the ilObjTestGUI class when learners execute a test. This saves
 * some heap space because the ilObjTestGUI class will be much smaller then
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @inGroup		ModulesTest
 */
abstract class ilTestOutputGUI extends ilTestPlayerAbstractGUI
{
    protected ilTestQuestionRelatedObjectivesList $question_related_objectives_list;
    protected bool $save_result;

    /**
     * Execute Command
     */
    public function executeCommand()
    {
        $this->checkReadAccess();

        $this->tabs->clearTargets();

        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        $this->ctrl->saveParameter($this, "sequence");
        $this->ctrl->saveParameter($this, "pmode");
        $this->ctrl->saveParameter($this, "active_id");

        $this->initAssessmentSettings();

        $testSessionFactory = new ilTestSessionFactory($this->object, $this->db, $this->user);
        $this->test_session = $testSessionFactory->getSession($this->testrequest->int('active_id'));

        $this->ensureExistingTestSession($this->test_session);
        $this->checkTestSessionUser($this->test_session);

        $this->initProcessLocker($this->test_session->getActiveId());

        $test_sequence_factory = new ilTestSequenceFactory($this->object, $this->db, $this->questioninfo);
        $this->testSequence = $test_sequence_factory->getSequenceByTestSession($this->test_session);
        $this->testSequence->loadFromDb();
        $this->testSequence->loadQuestions();

        $this->question_related_objectives_list = new ilTestQuestionRelatedObjectivesList();

        iljQueryUtil::initjQuery();
        ilYuiUtil::initConnectionWithAnimation();

        $this->handlePasswordProtectionRedirect();


        $instance_name = $this->settings->get('short_inst_name') ?? '';
        if (trim($instance_name) === '') {
            $instance_name = 'ILIAS';
        }
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            ilTestPlayerLayoutProvider::TEST_PLAYER_SHORT_TITLE,
            $instance_name
        );
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            ilTestPlayerLayoutProvider::TEST_PLAYER_KIOSK_MODE_ENABLED,
            $this->object->getKioskMode()
        );
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            ilTestPlayerLayoutProvider::TEST_PLAYER_VIEW_TITLE,
            $this->object->getTitle()
        );
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            ilTestPlayerLayoutProvider::TEST_PLAYER_TITLE,
            $this->getTestPlayerTitle()
        );


        $cmd = $this->getCommand($cmd);

        switch ($next_class) {
            case 'ilassquestionpagegui':
                $this->checkTestExecutable();

                $questionId = $this->testSequence->getQuestionForSequence($this->getCurrentSequenceElement());

                $page_gui = new ilAssQuestionPageGUI($questionId);
                $ret = $this->ctrl->forwardCommand($page_gui);
                break;

            case 'iltestsubmissionreviewgui':
                $this->checkTestExecutable();

                $gui = new ilTestSubmissionReviewGUI($this, $this->object, $this->test_session);
                $gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());
                $ret = $this->ctrl->forwardCommand($gui);
                break;

            case 'ilassquestionhintrequestgui':
                $this->checkTestExecutable();

                $questionGUI = $this->object->createQuestionGUI(
                    "",
                    $this->testSequence->getQuestionForSequence($this->getCurrentSequenceElement())
                );

                $questionHintTracking = new ilAssQuestionHintTracking(
                    $questionGUI->object->getId(),
                    $this->test_session->getActiveId(),
                    $this->test_session->getPass()
                );

                $gui = new ilAssQuestionHintRequestGUI(
                    $this,
                    ilTestPlayerCommands::SHOW_QUESTION,
                    $questionGUI,
                    $questionHintTracking
                );

                // fau: testNav - save the 'answer changed' status for viewing hint requests
                $this->setAnswerChangedParameter($this->getAnswerChangedParameter());
                // fau.
                $ret = $this->ctrl->forwardCommand($gui);

                break;

            case 'iltestpasswordprotectiongui':
                $this->checkTestExecutable();

                $gui = new ilTestPasswordProtectionGUI(
                    $this->ctrl,
                    $this->tpl,
                    $this->lng,
                    $this,
                    $this->passwordChecker,
                    $this->testrequest
                );
                $ret = $this->ctrl->forwardCommand($gui);
                break;

            default:
                if (ilTestPlayerCommands::isTestExecutionCommand($cmd)) {
                    $this->checkTestExecutable();
                }

                if (strtolower($cmd) === 'showquestion') {
                    $testPassesSelector = new ilTestPassesSelector($this->db, $this->object);
                    $testPassesSelector->setActiveId($this->test_session->getActiveId());
                    $testPassesSelector->setLastFinishedPass($this->test_session->getLastFinishedPass());

                    if (!$testPassesSelector->openPassExists()) {
                        $this->tpl->setOnScreenMessage('info', $this->lng->txt('tst_pass_finished'), true);
                        $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
                    }
                }

                $cmd .= 'Cmd';
                $ret = $this->$cmd();
                break;
        }
        return $ret;
    }

    protected function startTestCmd()
    {
        ilSession::set('tst_pass_finish', 0);

        // ensure existing test session
        $this->test_session->setUserId($this->user->getId());
        $access_code = ilSession::get('tst_access_code');
        if ($access_code != null && isset($access_code[$this->object->getTestId()])) {
            $this->test_session->setAnonymousId($access_code[$this->object->getTestId()]);
        }
        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $this->test_session->setObjectiveOrientedContainerId($this->getObjectiveOrientedContainer()->getObjId());
        }
        $this->test_session->saveToDb();

        $active_id = $this->test_session->getActiveId();
        $this->ctrl->setParameter($this, "active_id", $active_id);

        $shuffle = $this->object->getShuffleQuestions();
        if ($this->object->isRandomTest()) {
            $this->generateRandomTestPassForActiveUser();

            $this->object->loadQuestions();
            $shuffle = false; // shuffle is already done during the creation of the random questions
        }

        $this->object->updateTestPassResults(
            $active_id,
            $this->test_session->getPass(),
            $this->object->areObligationsEnabled(),
            null,
            $this->object->getId()
        );

        // ensure existing test sequence
        if (!$this->testSequence->hasSequence()) {
            $this->testSequence->createNewSequence($this->object->getQuestionCount(), $shuffle);
            $this->testSequence->saveToDb();
        }

        $this->testSequence->loadFromDb();
        $this->testSequence->loadQuestions();

        if ($this->test_session->isObjectiveOriented()) {
            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($this->test_session);

            $objectivesAdapter->notifyTestStart($this->test_session, $this->object->getId());
            $objectivesAdapter->prepareTestPass($this->test_session, $this->testSequence);

            $objectivesAdapter->buildQuestionRelatedObjectiveList(
                $this->testSequence,
                $this->question_related_objectives_list
            );

            if ($this->testSequence->hasOptionalQuestions()) {
                $this->adoptUserSolutionsFromPreviousPass();

                $this->testSequence->reorderOptionalQuestionsToSequenceEnd();
                $this->testSequence->saveToDb();
            }
        }

        $active_time_id = $this->object->startWorkingTime(
            $this->test_session->getActiveId(),
            $this->test_session->getPass()
        );
        ilSession::set("active_time_id", $active_time_id);

        $this->updateLearningProgressOnTestStart();

        $sequence_element = $this->testSequence->getFirstSequence();

        $this->ctrl->setParameter($this, 'sequence', $sequence_element);
        $this->ctrl->setParameter($this, 'pmode', '');

        if ($this->object->getListOfQuestionsStart()) {
            $this->ctrl->redirect($this, ilTestPlayerCommands::QUESTION_SUMMARY);
        }

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function updateLearningProgressOnTestStart()
    {
        ilLPStatusWrapper::_updateStatus($this->object->getId(), $this->user->getId());
    }

    private function isValidSequenceElement($sequence_element): bool
    {
        if ($sequence_element === false) {
            return false;
        }

        if ($sequence_element < 1) {
            return false;
        }

        if (!$this->testSequence->getPositionOfSequence($sequence_element)) {
            return false;
        }

        return true;
    }

    protected function showQuestionCmd(): void
    {
        ilSession::set('tst_pass_finish', 0);

        ilSession::set(
            "active_time_id",
            $this->object->startWorkingTime(
                $this->test_session->getActiveId(),
                $this->test_session->getPass()
            )
        );

        $this->help->setScreenIdComponent("tst");
        $this->help->setScreenId("assessment");
        $this->help->setSubScreenId("question");

        $sequence_element = $this->getCurrentSequenceElement();

        if (!$this->isValidSequenceElement($sequence_element)) {
            $sequence_element = $this->testSequence->getFirstSequence();
        }

        $this->test_session->setLastSequence($sequence_element ?? 0);
        $this->test_session->saveToDb();

        $questionId = $this->testSequence->getQuestionForSequence($sequence_element ?? 0);

        if (!(int) $questionId && $this->test_session->isObjectiveOriented()) {
            $this->handleTearsAndAngerNoObjectiveOrientedQuestion();
        }

        if ($questionId !== null && !$this->testSequence->isQuestionPresented($questionId)) {
            $this->testSequence->setQuestionPresented($questionId);
            $this->testSequence->saveToDb();
        }

        $isQuestionWorkedThrough = $this->questioninfo->lookupResultRecordExist(
            $this->test_session->getActiveId(),
            $questionId,
            $this->test_session->getPass()
        );

        // fau: testNav - always use edit mode, except for fixed answer
        if ($this->isParticipantsAnswerFixed($questionId)) {
            $presentationMode = ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW;
            $instantResponse = true;
        } else {
            $presentationMode = ilTestPlayerAbstractGUI::PRESENTATION_MODE_EDIT;
            // #37025 don't show instant response if a request for it should fix the answer and answer is not yet fixed
            if ($this->object->isInstantFeedbackAnswerFixationEnabled()) {
                $instantResponse = false;
            } else {
                $instantResponse = $this->getInstantResponseParameter();
            }
        }
        // fau.

        $questionGui = $this->getQuestionGuiInstance($questionId);

        if (!($questionGui instanceof assQuestionGUI)) {
            $this->handleTearsAndAngerQuestionIsNull($questionId, $sequence_element);
        }

        $questionGui->setSequenceNumber($this->testSequence->getPositionOfSequence($sequence_element));
        $questionGui->setQuestionCount($this->testSequence->getUserQuestionCount());

        $headerBlockBuilder = new ilTestQuestionHeaderBlockBuilder($this->lng);
        $headerBlockBuilder->setHeaderMode($this->object->getTitleOutput());
        $headerBlockBuilder->setQuestionTitle($questionGui->object->getTitle());
        $headerBlockBuilder->setQuestionPoints($questionGui->object->getPoints());
        $headerBlockBuilder->setQuestionPosition($this->testSequence->getPositionOfSequence($sequence_element));
        $headerBlockBuilder->setQuestionCount($this->testSequence->getUserQuestionCount());
        $headerBlockBuilder->setQuestionPostponed($this->testSequence->isPostponedQuestion($questionId));
        $headerBlockBuilder->setQuestionObligatory(
            $this->object->areObligationsEnabled() && ilObjTest::isQuestionObligatory($questionGui->object->getId())
        );
        if ($this->test_session->isObjectiveOriented()) {
            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($this->test_session);
            $objectivesAdapter->buildQuestionRelatedObjectiveList($this->testSequence, $this->question_related_objectives_list);
            $this->question_related_objectives_list->loadObjectivesTitles();

            $objectivesString = $this->question_related_objectives_list->getQuestionRelatedObjectiveTitles($questionId);
            $headerBlockBuilder->setQuestionRelatedObjectives($objectivesString);
        }
        $questionGui->setQuestionHeaderBlockBuilder($headerBlockBuilder);

        $this->prepareTestPage($presentationMode, $sequence_element, $questionId);

        $navigationToolbarGUI = $this->getTestNavigationToolbarGUI();
        $navigationToolbarGUI->setFinishTestButtonEnabled(true);

        $isNextPrimary = $this->handlePrimaryButton($navigationToolbarGUI, $questionId);

        $this->ctrl->setParameter($this, 'sequence', $sequence_element);
        $this->ctrl->setParameter($this, 'pmode', $presentationMode);
        $formAction = $this->ctrl->getFormAction($this, ilTestPlayerCommands::SUBMIT_INTERMEDIATE_SOLUTION);

        switch ($presentationMode) {
            case ilTestPlayerAbstractGUI::PRESENTATION_MODE_EDIT:

                // fau: testNav - enable navigation toolbar in edit mode
                $navigationToolbarGUI->setDisabledStateEnabled(false);
                // fau.
                $this->showQuestionEditable($questionGui, $formAction, $isQuestionWorkedThrough, $instantResponse);

                break;

            case ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW:

                if ($this->testSequence->isQuestionOptional($questionGui->object->getId())) {
                    $this->populateQuestionOptionalMessage();
                }

                $this->showQuestionViewable($questionGui, $formAction, $isQuestionWorkedThrough, $instantResponse);

                break;

            default:
                throw new ilTestException('no presentation mode given');
        }

        $navigationToolbarGUI->build();
        $this->populateTestNavigationToolbar($navigationToolbarGUI);

        // fau: testNav - enable the question navigation in edit mode
        $this->populateQuestionNavigation($sequence_element, $isNextPrimary);
        // fau.

        if ($instantResponse) {
            // fau: testNav - always use authorized solution for instant feedback
            $this->populateInstantResponseBlocks(
                $questionGui,
                true
            );
            // fau.
        }

        // fau: testNav - add feedback modal
        if ($this->isForcedFeedbackNavUrlRegistered()) {
            $this->populateInstantResponseModal($questionGui, $this->getRegisteredForcedFeedbackNavUrl());
            $this->unregisterForcedFeedbackNavUrl();
        }
        // fau.
    }

    protected function editSolutionCmd()
    {
        $this->ctrl->setParameter($this, 'pmode', ilTestPlayerAbstractGUI::PRESENTATION_MODE_EDIT);
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function submitSolutionAndNextCmd()
    {
        if ($this->object->isForceInstantFeedbackEnabled()) {
            $this->submitSolutionCmd();
            return;
        }

        if ($this->saveQuestionSolution(true, false)) {
            $questionId = $this->testSequence->getQuestionForSequence(
                $this->getCurrentSequenceElement()
            );

            $this->removeIntermediateSolution();

            $nextSequenceElement = $this->testSequence->getNextSequence($this->getCurrentSequenceElement());

            if (!$this->isValidSequenceElement($nextSequenceElement)) {
                $nextSequenceElement = $this->testSequence->getFirstSequence();
            }

            $this->test_session->setLastSequence($nextSequenceElement ?? 0);
            $this->test_session->saveToDb();

            $this->ctrl->setParameter($this, 'sequence', $nextSequenceElement);
            $this->ctrl->setParameter($this, 'pmode', '');
        }

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function submitSolutionCmd()
    {
        if ($this->saveQuestionSolution(true, false)) {
            $questionId = $this->testSequence->getQuestionForSequence(
                $this->getCurrentSequenceElement()
            );

            $this->removeIntermediateSolution();

            if ($this->object->isForceInstantFeedbackEnabled()) {
                $this->ctrl->setParameter($this, 'instresp', 1);

                $this->testSequence->setQuestionChecked($questionId);
                $this->testSequence->saveToDb();
            }

            if ($this->getNextCommandParameter()) {
                if ($this->getNextSequenceParameter()) {
                    $this->ctrl->setParameter($this, 'sequence', $this->getNextSequenceParameter());
                    $this->ctrl->setParameter($this, 'pmode', '');
                }

                $this->ctrl->redirect($this, $this->getNextCommandParameter());
            }

            $this->ctrl->setParameter($this, 'pmode', ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW);
        } else {
            $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
        }

        // fau: testNav - remember to prevent the navigation confirmation
        $this->saveNavigationPreventConfirmation();
        // fau.

        // fau: testNav - handle navigation after saving
        if ($this->getNavigationUrlParameter()) {
            ilUtil::redirect($this->getNavigationUrlParameter());
        } else {
            $this->ctrl->saveParameter($this, 'sequence');
        }
        // fau.
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function discardSolutionCmd()
    {
        $currentSequenceElement = $this->getCurrentSequenceElement();

        $currentQuestionOBJ = $this->getQuestionInstance(
            $this->testSequence->getQuestionForSequence($currentSequenceElement)
        );

        $currentQuestionOBJ->resetUsersAnswer(
            $this->test_session->getActiveId(),
            $this->test_session->getPass()
        );

        $this->ctrl->saveParameter($this, 'sequence');

        $this->ctrl->setParameter($this, 'pmode', ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW);

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function skipQuestionCmd()
    {
        $curSequenceElement = $this->getCurrentSequenceElement();
        $nextSequenceElement = $this->testSequence->getNextSequence($curSequenceElement);

        if (!$this->isValidSequenceElement($nextSequenceElement)) {
            $nextSequenceElement = $this->testSequence->getFirstSequence();
        }

        if ($this->object->isPostponingEnabled()) {
            $this->testSequence->postponeSequence($curSequenceElement);
            $this->testSequence->saveToDb();
        }

        $this->ctrl->setParameter($this, 'sequence', $nextSequenceElement);
        $this->ctrl->setParameter($this, 'pmode', '');

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function handleQuestionPostponing($sequence_element)
    {
        $questionId = $this->testSequence->getQuestionForSequence($sequence_element);

        $isQuestionWorkedThrough = $this->questioninfo->lookupResultRecordExist(
            $this->test_session->getActiveId(),
            $questionId,
            $this->test_session->getPass()
        );

        if (!$isQuestionWorkedThrough) {
            $this->testSequence->postponeQuestion($questionId);
            $this->testSequence->saveToDb();
        }
    }

    protected function handleCheckTestPassValid(): void
    {
        $testObj = new ilObjTest($this->ref_id, true);

        $participants = $testObj->getActiveParticipantList();
        $participant = $participants->getParticipantByActiveId($this->testrequest->getActiveId());
        if (!$participant || !$participant->hasUnfinishedPasses()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("tst_current_run_no_longer_valid"), true);
        }
    }

    protected function nextQuestionCmd()
    {
        $this->handleCheckTestPassValid();
        $lastSequenceElement = $this->getCurrentSequenceElement();
        $nextSequenceElement = $this->testSequence->getNextSequence($lastSequenceElement);

        if ($this->object->isPostponingEnabled()) {
            $this->handleQuestionPostponing($lastSequenceElement);
        }

        if (!$this->isValidSequenceElement($nextSequenceElement)) {
            $nextSequenceElement = $this->testSequence->getFirstSequence();
        }

        $this->ctrl->setParameter($this, 'sequence', $nextSequenceElement);
        $this->ctrl->setParameter($this, 'pmode', '');

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function previousQuestionCmd()
    {
        $this->handleCheckTestPassValid();

        $sequence_element = $this->testSequence->getPreviousSequence(
            $this->getCurrentSequenceElement()
        );

        if (!$this->isValidSequenceElement($sequence_element)) {
            $sequence_element = $this->testSequence->getLastSequence();
        }

        $this->ctrl->setParameter($this, 'sequence', $sequence_element);
        $this->ctrl->setParameter($this, 'pmode', '');

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function isFirstQuestionInSequence($sequence_element): bool
    {
        return $sequence_element == $this->testSequence->getFirstSequence();
    }

    protected function isLastQuestionInSequence($sequence_element): bool
    {
        return $sequence_element == $this->testSequence->getLastSequence();
    }

    /**
     * Returns TRUE if the answers of the current user could be saved
     *
     * @return boolean TRUE if the answers could be saved, FALSE otherwise
     */
    protected function canSaveResult(): bool
    {
        return !$this->object->endingTimeReached() && !$this->isMaxProcessingTimeReached() && !$this->isNrOfTriesReached();
    }

    /**
     * @return integer
     */
    protected function getCurrentQuestionId(): int
    {
        return $this->testSequence->getQuestionForSequence($this->testrequest->int('sequence'));
    }

    /**
     * saves the user input of a question
     */
    public function saveQuestionSolution($authorized = true, $force = false): bool
    {
        $this->updateWorkingTime();
        $this->save_result = false;
        if (!$force) {
            $formtimestamp = $_POST["formtimestamp"] ?? '';
            if (strlen($formtimestamp) == 0) {
                $formtimestamp = $this->testrequest->raw("formtimestamp");
            }
            if (ilSession::get('formtimestamp') == null || $formtimestamp != ilSession::get("formtimestamp")) {
                ilSession::set("formtimestamp", $formtimestamp);
            } else {
                return false;
            }
        }

        /*
            #21097 - exceed maximum passes
            this is a battle of conditions; e.g. ilTestPlayerAbstractGUI::autosaveOnTimeLimitCmd forces saving of results.
            However, if an admin has finished the pass in the meantime, a new pass should not be created.
        */
        if ($force && $this->isNrOfTriesReached()) {
            $force = false;
        }

        // save question solution
        if ($this->canSaveResult() || $force) {
            // but only if the ending time is not reached
            $q_id = $this->testSequence->getQuestionForSequence($this->testrequest->int('sequence'));

            if ($this->isParticipantsAnswerFixed($q_id)) {
                // should only be reached by firebugging the disabled form in ui
                throw new ilTestException('not allowed request');
            }

            if (is_numeric($q_id) && (int) $q_id) {
                $questionOBJ = $this->getQuestionInstance($q_id);

                $active_id = (int) $this->test_session->getActiveId();
                $pass = ilObjTest::_getPass($active_id);
                $this->save_result = $questionOBJ->persistWorkingState(
                    $active_id,
                    $pass,
                    $this->object->areObligationsEnabled(),
                    $authorized
                );

                if ($authorized && $this->test_session->isObjectiveOriented()) {
                    $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($this->test_session);
                    $objectivesAdapter->updateQuestionResult($this->test_session, $questionOBJ);
                }

                if ($authorized && $this->object->isSkillServiceToBeConsidered()) {
                    $this->handleSkillTriggering($this->test_session);
                }
            }
        }

        if (!$this->save_result || ($questionOBJ instanceof ilAssQuestionPartiallySaveable && !$questionOBJ->validateSolutionSubmit())) {
            $this->ctrl->setParameter($this, "save_error", "1");
            ilSession::set("previouspost", $_POST);
        }

        return $this->save_result;
    }

    protected function showInstantResponseCmd()
    {
        $questionId = $this->testSequence->getQuestionForSequence(
            $this->getCurrentSequenceElement()
        );

        if (!$this->isParticipantsAnswerFixed($questionId)) {
            if ($this->saveQuestionSolution(true)) {
                $this->removeIntermediateSolution();
                $this->setAnswerChangedParameter(false);
            } else {
                $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
            }
            $this->testSequence->setQuestionChecked($questionId);
            $this->testSequence->saveToDb();
        } elseif ($this->object->isForceInstantFeedbackEnabled()) {
            $this->testSequence->setQuestionChecked($questionId);
            $this->testSequence->saveToDb();
        }

        $this->ctrl->setParameter($this, 'instresp', 1);

        // fau: testNav - handle navigation after feedback
        if ($this->getNavigationUrlParameter()) {
            $this->saveNavigationPreventConfirmation();
            $this->registerForcedFeedbackNavUrl($this->getNavigationUrlParameter());
        }
        // fau.
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function handleQuestionActionCmd()
    {
        $questionId = $this->testSequence->getQuestionForSequence(
            $this->getCurrentSequenceElement()
        );

        if (!$this->isParticipantsAnswerFixed($questionId)) {
            $this->updateWorkingTime();
            $this->saveQuestionSolution(false);
            // fau: testNav - add changed status of the question
            $this->setAnswerChangedParameter(true);
            // fau.
        }

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function performTearsAndAngerBrokenConfessionChecks(): bool
    {
        if ($this->test_session->getActiveId() > 0) {
            if ($this->testSequence->hasRandomQuestionsForPass($this->test_session->getActiveId(), $this->test_session->getPass()) > 0) {
                $this->logging_services->root()->write(
                    __METHOD__ . ' Random Questions allready exists for user ' .
                    $this->user->getId() . ' in test ' . $this->object->getTestId()
                );

                return true;
            }
        } else {
            $this->logging_services->root()->write(__METHOD__ . ' ' . sprintf(
                $this->lng->txt("error_random_question_generation"),
                $this->user->getId(),
                $this->object->getTestId()
            ));

            return true;
        };

        return false;
    }

    protected function generateRandomTestPassForActiveUser()
    {
        $questionSetConfig = new ilTestRandomQuestionSetConfig(
            $this->tree,
            $this->db,
            $this->lng,
            $this->logging_services->root(),
            $this->component_repository,
            $this->object,
            $this->questioninfo
        );
        $questionSetConfig->loadFromDb();

        $sourcePoolDefinitionFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory($this->db, $this->object);

        $sourcePoolDefinitionList = new ilTestRandomQuestionSetSourcePoolDefinitionList($this->db, $this->object, $sourcePoolDefinitionFactory);
        $sourcePoolDefinitionList->loadDefinitions();

        $this->processLocker->executeRandomPassBuildOperation(function () use ($questionSetConfig, $sourcePoolDefinitionList) {
            if (!$this->performTearsAndAngerBrokenConfessionChecks()) {
                $stagingPoolQuestionList = new ilTestRandomQuestionSetStagingPoolQuestionList($this->db, $this->component_repository);

                $questionSetBuilder = ilTestRandomQuestionSetBuilder::getInstance(
                    $this->db,
                    $this->lng,
                    $this->logging_services->root(),
                    $this->object,
                    $questionSetConfig,
                    $sourcePoolDefinitionList,
                    $stagingPoolQuestionList
                );

                $questionSetBuilder->performBuild($this->test_session);
            }
        }, $sourcePoolDefinitionList->hasTaxonomyFilters());
    }

    /**
     * Resume a test at the last position
     */
    protected function resumePlayerCmd()
    {
        $this->handleUserSettings();

        $active_id = $this->test_session->getActiveId();
        $this->ctrl->setParameter($this, "active_id", $active_id);

        $active_time_id = $this->object->startWorkingTime($active_id, $this->test_session->getPass());
        ilSession::set("active_time_id", $active_time_id);
        ilSession::set('tst_pass_finish', 0);

        if ($this->object->isRandomTest()) {
            if (!$this->testSequence->hasRandomQuestionsForPass($active_id, $this->test_session->getPass())) {
                // create a new set of random questions
                $this->generateRandomTestPassForActiveUser();
            }
        }

        $shuffle = $this->object->getShuffleQuestions();
        if ($this->object->isRandomTest()) {
            $shuffle = false;
        }

        $this->object->updateTestPassResults(
            $active_id,
            $this->test_session->getPass(),
            $this->object->areObligationsEnabled(),
            null,
            $this->object->getId()
        );

        // ensure existing test sequence
        if (!$this->testSequence->hasSequence()) {
            $this->testSequence->createNewSequence($this->object->getQuestionCount(), $shuffle);
            $this->testSequence->saveToDb();
        }

        if ($this->object->getListOfQuestionsStart()) {
            $this->ctrl->redirect($this, ilTestPlayerCommands::QUESTION_SUMMARY);
        }

        $this->ctrl->setParameter($this, 'sequence', $this->test_session->getLastSequence());
        $this->ctrl->setParameter($this, 'pmode', '');
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function isShowingPostponeStatusReguired($questionId): bool
    {
        return $this->testSequence->isPostponedQuestion($questionId);
    }

    protected function adoptUserSolutionsFromPreviousPass()
    {
        $assSettings = new ilSetting('assessment');

        $isAssessmentLogEnabled = ilObjAssessmentFolder::_enabledAssessmentLogging();

        $userSolutionAdopter = new ilAssQuestionUserSolutionAdopter($this->db, $assSettings, $isAssessmentLogEnabled);

        $userSolutionAdopter->setUserId($this->user->getId());
        $userSolutionAdopter->setActiveId($this->test_session->getActiveId());
        $userSolutionAdopter->setTargetPass($this->testSequence->getPass());
        $userSolutionAdopter->setQuestionIds($this->testSequence->getOptionalQuestions());

        $userSolutionAdopter->perform();
    }

    abstract protected function populateQuestionOptionalMessage();

    protected function isOptionalQuestionAnsweringConfirmationRequired($sequenceKey): bool
    {
        if ($this->testSequence->isAnsweringOptionalQuestionsConfirmed()) {
            return false;
        }

        $questionId = $this->testSequence->getQuestionForSequence($sequenceKey);

        if (!$this->testSequence->isQuestionOptional($questionId)) {
            return false;
        }

        return true;
    }

    protected function isQuestionSummaryFinishTestButtonRequired(): bool
    {
        return true;
    }

    protected function handleTearsAndAngerNoObjectiveOrientedQuestion()
    {
        $this->tpl->setOnScreenMessage('failure', sprintf($this->lng->txt('tst_objective_oriented_test_pass_without_questions'), $this->object->getTitle()), true);

        $this->backToInfoScreenCmd();
    }

    protected function handlePrimaryButton(ilTestNavigationToolbarGUI $navigationToolbarGUI, int $currentQuestionId): bool
    {
        $isNextPrimary = true;

        if ($this->object->isForceInstantFeedbackEnabled()) {
            $isNextPrimary = false;
        }

        $questionsMissingResult = $this->questioninfo->getQuestionsMissingResultRecord(
            $this->test_session->getActiveId(),
            $this->test_session->getPass(),
            $this->testSequence->getOrderedSequenceQuestions()
        );

        if ($questionsMissingResult === []) {
            $navigationToolbarGUI->setFinishTestButtonPrimary(true);
            return false;
        }

        if (count($questionsMissingResult) === 1
            && $currentQuestionId === current($questionsMissingResult)) {
            $navigationToolbarGUI->setFinishTestButtonPrimary(true);
            return false;
        }

        return $isNextPrimary;
    }

    protected function getTestPlayerTitle(): string
    {
        $test_title = $this->object->getShowKioskModeTitle() ? $this->object->getTitle() : '';
        $user_name = $this->object->getShowKioskModeParticipant() ? $this->user->getFullname() : '';
        $exam_id = '';
        if ($this->object->isShowExamIdInTestPassEnabled()) {
            $exam_id = $this->lng->txt("exam_id")
            . ' '
            . ilObjTest::buildExamId(
                $this->test_session->getActiveId(),
                $this->test_session->getPass(),
                $this->object->getId()
            );
        }

        $layout = $this->ui_factory->layout()->alignment()->vertical(
            $this->ui_factory->legacy($test_title),
            $this->ui_factory->layout()->alignment()->horizontal()->dynamicallyDistributed(
                $this->ui_factory->legacy($user_name),
                $this->ui_factory->legacy($exam_id)
            )
        );
        return $this->ui_renderer->render($layout);
    }
}
