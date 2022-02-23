<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/class.ilTestPlayerAbstractGUI.php';

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
    /**
     * @var ilTestQuestionRelatedObjectivesList
     */
    protected $questionRelatedObjectivesList;

    /**
     * Execute Command
     */
    public function executeCommand()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilPluginAdmin = $DIC['ilPluginAdmin'];
        $lng = $DIC['lng'];
        $ilTabs = $DIC['ilTabs'];

        $this->checkReadAccess();

        $ilTabs->clearTargets();
        
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        $this->ctrl->saveParameter($this, "sequence");
        $this->ctrl->saveParameter($this, "pmode");
        $this->ctrl->saveParameter($this, "active_id");

        $this->initAssessmentSettings();

        $DIC->globalScreen()->tool()->context()->current()->addAdditionalData(
            ilTestPlayerLayoutProvider::TEST_PLAYER_KIOSK_MODE_ENABLED,
            $this->object->getKioskMode()
        );

        $testSessionFactory = new ilTestSessionFactory($this->object);
        $this->testSession = $testSessionFactory->getSession($_GET['active_id']);
        
        $this->ensureExistingTestSession($this->testSession);
        $this->checkTestSessionUser($this->testSession);
        
        $this->initProcessLocker($this->testSession->getActiveId());
        
        $testSequenceFactory = new ilTestSequenceFactory($ilDB, $lng, $ilPluginAdmin, $this->object);
        $this->testSequence = $testSequenceFactory->getSequenceByTestSession($this->testSession);
        $this->testSequence->loadFromDb();
        $this->testSequence->loadQuestions();

        require_once 'Modules/Test/classes/class.ilTestQuestionRelatedObjectivesList.php';
        $this->questionRelatedObjectivesList = new ilTestQuestionRelatedObjectivesList();

        include_once 'Services/jQuery/classes/class.iljQueryUtil.php';
        iljQueryUtil::initjQuery();
        include_once "./Services/YUI/classes/class.ilYuiUtil.php";
        ilYuiUtil::initConnectionWithAnimation();
        
        $this->handlePasswordProtectionRedirect();
        
        $cmd = $this->getCommand($cmd);
        
        switch ($next_class) {
            case 'ilassquestionpagegui':

                $this->checkTestExecutable();
                
                $questionId = $this->testSequence->getQuestionForSequence($this->getCurrentSequenceElement());

                require_once "./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php";
                $page_gui = new ilAssQuestionPageGUI($questionId);
                $ret = $this->ctrl->forwardCommand($page_gui);
                break;

            case 'iltestsubmissionreviewgui':

                $this->checkTestExecutable();

                require_once './Modules/Test/classes/class.ilTestSubmissionReviewGUI.php';
                $gui = new ilTestSubmissionReviewGUI($this, $this->object, $this->testSession);
                $gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());
                $ret = $this->ctrl->forwardCommand($gui);
                break;

            case 'ilassquestionhintrequestgui':

                $this->checkTestExecutable();

                $questionGUI = $this->object->createQuestionGUI(
                    "",
                    $this->testSequence->getQuestionForSequence($this->getCurrentSequenceElement())
                );

                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintTracking.php';
                $questionHintTracking = new ilAssQuestionHintTracking(
                    $questionGUI->object->getId(),
                    $this->testSession->getActiveId(),
                    $this->testSession->getPass()
                );

                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';
                $gui = new ilAssQuestionHintRequestGUI($this, ilTestPlayerCommands::SHOW_QUESTION, $questionGUI, $questionHintTracking);

// fau: testNav - save the 'answer changed' status for viewing hint requests
                $this->setAnswerChangedParameter($this->getAnswerChangedParameter());
// fau.
                $ret = $this->ctrl->forwardCommand($gui);

                break;

            case 'iltestsignaturegui':

                $this->checkTestExecutable();

                require_once './Modules/Test/classes/class.ilTestSignatureGUI.php';
                $gui = new ilTestSignatureGUI($this);
                $ret = $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestpasswordprotectiongui':

                $this->checkTestExecutable();

                require_once 'Modules/Test/classes/class.ilTestPasswordProtectionGUI.php';
                $gui = new ilTestPasswordProtectionGUI($this->ctrl, $this->tpl, $this->lng, $this, $this->passwordChecker);
                $ret = $this->ctrl->forwardCommand($gui);
                break;

            default:

                if (ilTestPlayerCommands::isTestExecutionCommand($cmd)) {
                    $this->checkTestExecutable();
                }

                $cmd .= 'Cmd';
                $ret = &$this->$cmd();
                break;
        }
        return $ret;
    }

    protected function startTestCmd()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $_SESSION['tst_pass_finish'] = 0;

        // ensure existing test session
        $this->testSession->setUserId($ilUser->getId());
        $this->testSession->setAnonymousId($_SESSION["tst_access_code"][$this->object->getTestId()]);
        $this->testSession->setObjectiveOrientedContainerId($this->getObjectiveOrientedContainer()->getObjId());
        $this->testSession->saveToDb();

        $active_id = $this->testSession->getActiveId();
        $this->ctrl->setParameter($this, "active_id", $active_id);

        $shuffle = $this->object->getShuffleQuestions();
        if ($this->object->isRandomTest()) {
            $this->generateRandomTestPassForActiveUser();

            $this->object->loadQuestions();
            $shuffle = false; // shuffle is already done during the creation of the random questions
        }

        assQuestion::_updateTestPassResults(
            $active_id,
            $this->testSession->getPass(),
            $this->object->areObligationsEnabled(),
            null,
            $this->object->id
        );

        // ensure existing test sequence
        if (!$this->testSequence->hasSequence()) {
            $this->testSequence->createNewSequence($this->object->getQuestionCount(), $shuffle);
            $this->testSequence->saveToDb();
        }

        $this->testSequence->loadFromDb();
        $this->testSequence->loadQuestions();

        if ($this->testSession->isObjectiveOriented()) {
            require_once 'Modules/Course/classes/Objectives/class.ilLOTestQuestionAdapter.php';
            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($this->testSession);

            $objectivesAdapter->notifyTestStart($this->testSession, $this->object->getId());
            $objectivesAdapter->prepareTestPass($this->testSession, $this->testSequence);

            $objectivesAdapter->buildQuestionRelatedObjectiveList(
                $this->testSequence,
                $this->questionRelatedObjectivesList
            );
            
            if ($this->testSequence->hasOptionalQuestions()) {
                $this->adoptUserSolutionsFromPreviousPass();

                $this->testSequence->reorderOptionalQuestionsToSequenceEnd();
                $this->testSequence->saveToDb();
            }
        }

        $active_time_id = $this->object->startWorkingTime(
            $this->testSession->getActiveId(),
            $this->testSession->getPass()
        );
        $_SESSION["active_time_id"] = $active_time_id;

        $this->updateLearningProgressOnTestStart();

        $sequenceElement = $this->testSequence->getFirstSequence();

        $this->ctrl->setParameter($this, 'sequence', $sequenceElement);
        $this->ctrl->setParameter($this, 'pmode', '');

        if ($this->object->getListOfQuestionsStart()) {
            $this->ctrl->redirect($this, ilTestPlayerCommands::QUESTION_SUMMARY);
        }

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }
    
    protected function updateLearningProgressOnTestStart()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        require_once('./Modules/Test/classes/class.ilObjTestAccess.php');
        require_once('./Services/Tracking/classes/class.ilLPStatusWrapper.php');
        ilLPStatusWrapper::_updateStatus($this->object->getId(), $ilUser->getId());
    }
    
    private function isValidSequenceElement($sequenceElement)
    {
        if ($sequenceElement === false) {
            return false;
        }
        
        if ($sequenceElement < 1) {
            return false;
        }
        
        if (!$this->testSequence->getPositionOfSequence($sequenceElement)) {
            return false;
        }
        
        return true;
    }
    
    protected function showQuestionCmd()
    {
        $_SESSION['tst_pass_finish'] = 0;

        $_SESSION["active_time_id"] = $this->object->startWorkingTime(
            $this->testSession->getActiveId(),
            $this->testSession->getPass()
        );

        $sequenceElement = $this->getCurrentSequenceElement();

        if (!$this->isValidSequenceElement($sequenceElement)) {
            $sequenceElement = $this->testSequence->getFirstSequence();
        }

        $this->testSession->setLastSequence($sequenceElement);
        $this->testSession->saveToDb();


        $questionId = $this->testSequence->getQuestionForSequence($sequenceElement);

        if (!(int) $questionId && $this->testSession->isObjectiveOriented()) {
            $this->handleTearsAndAngerNoObjectiveOrientedQuestion();
        }
        
        if (!$this->testSequence->isQuestionPresented($questionId)) {
            $this->testSequence->setQuestionPresented($questionId);
            $this->testSequence->saveToDb();
        }

        $isQuestionWorkedThrough = assQuestion::_isWorkedThrough(
            $this->testSession->getActiveId(),
            $questionId,
            $this->testSession->getPass()
        );
        
        // fau: testNav - always use edit mode, except for fixed answer
        if ($this->isParticipantsAnswerFixed($questionId)) {
            $presentationMode = ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW;
            $instantResponse = true;
        } else {
            $presentationMode = ilTestPlayerAbstractGUI::PRESENTATION_MODE_EDIT;
            $instantResponse = $this->getInstantResponseParameter();
        }
        // fau.

        $questionGui = $this->getQuestionGuiInstance($questionId);

        if (!($questionGui instanceof assQuestionGUI)) {
            $this->handleTearsAndAngerQuestionIsNull($questionId, $sequenceElement);
        }
        
        $questionGui->setSequenceNumber($this->testSequence->getPositionOfSequence($sequenceElement));
        $questionGui->setQuestionCount($this->testSequence->getUserQuestionCount());

        require_once 'Modules/Test/classes/class.ilTestQuestionHeaderBlockBuilder.php';
        $headerBlockBuilder = new ilTestQuestionHeaderBlockBuilder($this->lng);
        $headerBlockBuilder->setHeaderMode($this->object->getTitleOutput());
        $headerBlockBuilder->setQuestionTitle($questionGui->object->getTitle());
        $headerBlockBuilder->setQuestionPoints($questionGui->object->getPoints());
        $headerBlockBuilder->setQuestionPosition($this->testSequence->getPositionOfSequence($sequenceElement));
        $headerBlockBuilder->setQuestionCount($this->testSequence->getUserQuestionCount());
        $headerBlockBuilder->setQuestionPostponed($this->testSequence->isPostponedQuestion($questionId));
        $headerBlockBuilder->setQuestionObligatory(
            $this->object->areObligationsEnabled() && ilObjTest::isQuestionObligatory($questionGui->object->getId())
        );
        if ($this->testSession->isObjectiveOriented()) {
            require_once 'Modules/Course/classes/Objectives/class.ilLOTestQuestionAdapter.php';
            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($this->testSession);
            $objectivesAdapter->buildQuestionRelatedObjectiveList($this->testSequence, $this->questionRelatedObjectivesList);
            $this->questionRelatedObjectivesList->loadObjectivesTitles();

            $objectivesString = $this->questionRelatedObjectivesList->getQuestionRelatedObjectiveTitles($questionId);
            $headerBlockBuilder->setQuestionRelatedObjectives($objectivesString);
        }
        $questionGui->setQuestionHeaderBlockBuilder($headerBlockBuilder);

        $this->prepareTestPage($presentationMode, $sequenceElement, $questionId);

        $navigationToolbarGUI = $this->getTestNavigationToolbarGUI();
        $navigationToolbarGUI->setFinishTestButtonEnabled(true);
        
        $isNextPrimary = $this->handlePrimaryButton($navigationToolbarGUI, $questionId);
        
        $this->ctrl->setParameter($this, 'sequence', $sequenceElement);
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
                
                require_once 'Modules/Test/exceptions/class.ilTestException.php';
                throw new ilTestException('no presentation mode given');
        }

        $navigationToolbarGUI->build();
        $this->populateTestNavigationToolbar($navigationToolbarGUI);

        // fau: testNav - enable the question navigation in edit mode
        $this->populateQuestionNavigation($sequenceElement, false, $isNextPrimary);
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
            return $this->submitSolutionCmd();
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

            $this->testSession->setLastSequence($nextSequenceElement);
            $this->testSession->saveToDb();

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
            $this->testSession->getActiveId(),
            $this->testSession->getPass()
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
    
    protected function handleQuestionPostponing($sequenceElement)
    {
        $questionId = $this->testSequence->getQuestionForSequence($sequenceElement);
    
        $isQuestionWorkedThrough = assQuestion::_isWorkedThrough(
            $this->testSession->getActiveId(),
            $questionId,
            $this->testSession->getPass()
        );
        
        if (!$isQuestionWorkedThrough) {
            $this->testSequence->postponeQuestion($questionId);
            $this->testSequence->saveToDb();
        }
    }

    protected function nextQuestionCmd()
    {
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
        $sequenceElement = $this->testSequence->getPreviousSequence(
            $this->getCurrentSequenceElement()
        );

        if (!$this->isValidSequenceElement($sequenceElement)) {
            $sequenceElement = $this->testSequence->getLastSequence();
        }

        $this->ctrl->setParameter($this, 'sequence', $sequenceElement);
        $this->ctrl->setParameter($this, 'pmode', '');

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function isFirstQuestionInSequence($sequenceElement)
    {
        return $sequenceElement == $this->testSequence->getFirstSequence();
    }

    protected function isLastQuestionInSequence($sequenceElement)
    {
        return $sequenceElement == $this->testSequence->getLastSequence();
    }

    /**
     * Returns TRUE if the answers of the current user could be saved
     *
     * @return boolean TRUE if the answers could be saved, FALSE otherwise
     */
    protected function canSaveResult()
    {
        return !$this->object->endingTimeReached() && !$this->isMaxProcessingTimeReached() && !$this->isNrOfTriesReached();
    }
    
    /**
     * @return integer
     */
    protected function getCurrentQuestionId()
    {
        return $this->testSequence->getQuestionForSequence($_GET["sequence"]);
    }
    
    /**
     * saves the user input of a question
     */
    public function saveQuestionSolution($authorized = true, $force = false)
    {
        $this->updateWorkingTime();
        $this->saveResult = false;
        if (!$force) {
            $formtimestamp = $_POST["formtimestamp"];
            if (strlen($formtimestamp) == 0) {
                $formtimestamp = $_GET["formtimestamp"];
            }
            if ($formtimestamp != $_SESSION["formtimestamp"]) {
                $_SESSION["formtimestamp"] = $formtimestamp;
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
            $q_id = $this->testSequence->getQuestionForSequence($_GET["sequence"]);
            
            if ($this->isParticipantsAnswerFixed($q_id)) {
                // should only be reached by firebugging the disabled form in ui
                throw new ilTestException('not allowed request');
            }
            
            if (is_numeric($q_id) && (int) $q_id) {
                $questionOBJ = $this->getQuestionInstance($q_id);
                $pass = null;
                $active_id = $this->testSession->getActiveId();
                if ($this->object->isRandomTest()) {
                    $pass = ilObjTest::_getPass($active_id);
                }
                $this->saveResult = $questionOBJ->persistWorkingState(
                    $active_id,
                    $pass,
                    $this->object->areObligationsEnabled(),
                    $authorized
                );

                if ($authorized && $this->testSession->isObjectiveOriented()) {
                    require_once 'Modules/Course/classes/Objectives/class.ilLOTestQuestionAdapter.php';
                    $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($this->testSession);
                    $objectivesAdapter->updateQuestionResult($this->testSession, $questionOBJ);
                }

                if ($authorized && $this->object->isSkillServiceToBeConsidered()) {
                    $this->handleSkillTriggering($this->testSession);
                }
            }
        }

        if ($this->saveResult == false || (!$questionOBJ->validateSolutionSubmit() && $questionOBJ->savePartial())) {
            $this->ctrl->setParameter($this, "save_error", "1");
            $_SESSION["previouspost"] = $_POST;
        }

        return $this->saveResult;
    }

    protected function showInstantResponseCmd()
    {
        $questionId = $this->testSequence->getQuestionForSequence(
            $this->getCurrentSequenceElement()
        );
        
        if ($this->getAnswerChangedParameter() && !$this->isParticipantsAnswerFixed($questionId)) {
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

    protected function performTearsAndAngerBrokenConfessionChecks()
    {
        if ($this->testSession->getActiveId() > 0) {
            if ($this->testSequence->hasRandomQuestionsForPass($this->testSession->getActiveId(), $this->testSession->getPass()) > 0) {
                // Something went wrong. Maybe the user pressed the start button twice
                // Questions already exist so there is no need to create new questions

                global $DIC;
                $ilLog = $DIC['ilLog'];
                $ilUser = $DIC['ilUser'];

                $ilLog->write(
                    __METHOD__ . ' Random Questions allready exists for user ' .
                    $ilUser->getId() . ' in test ' . $this->object->getTestId()
                );

                return true;
            }
        } else {
            // This may not happen! If it happens, raise a fatal error...

            global $DIC;
            $ilLog = $DIC['ilLog'];
            $ilUser = $DIC['ilUser'];

            $ilLog->write(__METHOD__ . ' ' . sprintf(
                $this->lng->txt("error_random_question_generation"),
                $ilUser->getId(),
                $this->object->getTestId()
            ));
            
            return true;
        };

        return false;
    }

    protected function generateRandomTestPassForActiveUser()
    {
        global $DIC;
        $tree = $DIC['tree'];
        $ilDB = $DIC['ilDB'];
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetConfig.php';
        $questionSetConfig = new ilTestRandomQuestionSetConfig($tree, $ilDB, $ilPluginAdmin, $this->object);
        $questionSetConfig->loadFromDb();

        require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
        $sourcePoolDefinitionFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory($ilDB, $this->object);

        require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
        $sourcePoolDefinitionList = new ilTestRandomQuestionSetSourcePoolDefinitionList($ilDB, $this->object, $sourcePoolDefinitionFactory);
        $sourcePoolDefinitionList->loadDefinitions();

        $this->processLocker->executeRandomPassBuildOperation(function () use ($ilDB, $ilPluginAdmin, $questionSetConfig, $sourcePoolDefinitionList) {
            if (!$this->performTearsAndAngerBrokenConfessionChecks()) {
                require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolQuestionList.php';
                $stagingPoolQuestionList = new ilTestRandomQuestionSetStagingPoolQuestionList($ilDB, $ilPluginAdmin);

                require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetBuilder.php';
                $questionSetBuilder = ilTestRandomQuestionSetBuilder::getInstance($ilDB, $this->object, $questionSetConfig, $sourcePoolDefinitionList, $stagingPoolQuestionList);

                $questionSetBuilder->performBuild($this->testSession);
            }
        }, $sourcePoolDefinitionList->hasTaxonomyFilters());
    }

    /**
     * Resume a test at the last position
     */
    protected function resumePlayerCmd()
    {
        $this->handleUserSettings();

        $active_id = $this->testSession->getActiveId();
        $this->ctrl->setParameter($this, "active_id", $active_id);

        $active_time_id = $this->object->startWorkingTime($active_id, $this->testSession->getPass());
        $_SESSION["active_time_id"] = $active_time_id;
        $_SESSION['tst_pass_finish'] = 0;

        if ($this->object->isRandomTest()) {
            if (!$this->testSequence->hasRandomQuestionsForPass($active_id, $this->testSession->getPass())) {
                // create a new set of random questions
                $this->generateRandomTestPassForActiveUser();
            }
        }

        $shuffle = $this->object->getShuffleQuestions();
        if ($this->object->isRandomTest()) {
            $shuffle = false;
        }

        assQuestion::_updateTestPassResults(
            $active_id,
            $this->testSession->getPass(),
            $this->object->areObligationsEnabled(),
            null,
            $this->object->id
        );

        // ensure existing test sequence
        if (!$this->testSequence->hasSequence()) {
            $this->testSequence->createNewSequence($this->object->getQuestionCount(), $shuffle);
            $this->testSequence->saveToDb();
        }

        if ($this->object->getListOfQuestionsStart()) {
            $this->ctrl->redirect($this, ilTestPlayerCommands::QUESTION_SUMMARY);
        }
        
        $this->ctrl->setParameter($this, 'sequence', $this->testSession->getLastSequence());
        $this->ctrl->setParameter($this, 'pmode', '');
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function isShowingPostponeStatusReguired($questionId)
    {
        return $this->testSequence->isPostponedQuestion($questionId);
    }

    protected function adoptUserSolutionsFromPreviousPass()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        
        $assSettings = new ilSetting('assessment');

        include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
        $isAssessmentLogEnabled = ilObjAssessmentFolder::_enabledAssessmentLogging();

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionUserSolutionAdopter.php';
        $userSolutionAdopter = new ilAssQuestionUserSolutionAdopter($ilDB, $assSettings, $isAssessmentLogEnabled);

        $userSolutionAdopter->setUserId($ilUser->getId());
        $userSolutionAdopter->setActiveId($this->testSession->getActiveId());
        $userSolutionAdopter->setTargetPass($this->testSequence->getPass());
        $userSolutionAdopter->setQuestionIds($this->testSequence->getOptionalQuestions());

        $userSolutionAdopter->perform();
    }
    
    abstract protected function populateQuestionOptionalMessage();

    protected function isOptionalQuestionAnsweringConfirmationRequired($sequenceKey)
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
    
    protected function isQuestionSummaryFinishTestButtonRequired()
    {
        return true;
    }

    protected function handleTearsAndAngerNoObjectiveOrientedQuestion()
    {
        ilUtil::sendFailure(sprintf($this->lng->txt('tst_objective_oriented_test_pass_without_questions'), $this->object->getTitle()), true);

        $this->backToInfoScreenCmd();
    }
    
    /**
     * @param ilTestNavigationToolbarGUI $navigationToolbarGUI
     */
    protected function handlePrimaryButton(ilTestNavigationToolbarGUI $navigationToolbarGUI, $currentQuestionId)
    {
        $isNextPrimary = true;
        
        if ($this->object->isForceInstantFeedbackEnabled()) {
            $isNextPrimary = false;
        }
        
        $questionsMissingResult = assQuestion::getQuestionsMissingResultRecord(
            $this->testSession->getActiveId(),
            $this->testSession->getPass(),
            $this->testSequence->getOrderedSequenceQuestions()
        );

        if (!count($questionsMissingResult)) {
            $navigationToolbarGUI->setFinishTestButtonPrimary(true);
            $isNextPrimary = false;
        } elseif (count($questionsMissingResult) == 1) {
            $lastOpenQuestion = current($questionsMissingResult);
            
            if ($currentQuestionId == $lastOpenQuestion) {
                $navigationToolbarGUI->setFinishTestButtonPrimary(true);
                $isNextPrimary = false;
            }
        }
        
        return $isNextPrimary;
    }
}
