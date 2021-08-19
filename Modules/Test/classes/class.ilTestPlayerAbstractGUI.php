<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/Test/classes/class.ilTestPlayerCommands.php';
require_once './Modules/Test/classes/class.ilTestServiceGUI.php';
require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Services/UIComponent/Button/classes/class.ilSubmitButton.php';
require_once 'Modules/Test/classes/class.ilTestPlayerNavButton.php';

/**
 * Output class for assessment test execution
 *
 * The ilTestOutputGUI class creates the output for the ilObjTestGUI class when learners execute a test. This saves
 * some heap space because the ilObjTestGUI class will be much smaller then
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @inGroup		ModulesTest
 *
 */
abstract class ilTestPlayerAbstractGUI extends ilTestServiceGUI
{
    const PRESENTATION_MODE_VIEW = 'view';
    const PRESENTATION_MODE_EDIT = 'edit';

    const FIXED_SHUFFLER_SEED_MIN_LENGTH = 8;
    
    public $ref_id;
    public $saveResult;
    public $sequence;
    public $cmdCtrl;
    public $maxProcessingTimeReached;
    public $endingTimeReached;

    /**
     * @var ilTestPasswordChecker
     */
    protected $passwordChecker;

    /**
     * @var ilTestProcessLocker
     */
    protected $processLocker;
    
    /**
     * @var ilTestSession
     */
    protected $testSession;

    /**
     * @var ilSetting
     */
    protected $assSettings;

    /**
     * @var ilTestSequence|ilTestSequenceDynamicQuestionSet
     */
    protected $testSequence = null;

    /**
    * ilTestOutputGUI constructor
    *
    * @param ilObjTest $a_object
    */
    public function __construct($a_object)
    {
        parent::__construct($a_object);
        $this->ref_id = $_GET["ref_id"];
        
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        require_once 'Modules/Test/classes/class.ilTestPasswordChecker.php';
        $this->passwordChecker = new ilTestPasswordChecker($rbacsystem, $ilUser, $this->object, $lng);
        
        $this->processLocker = null;
        $this->testSession = null;
        $this->assSettings = null;
    }

    protected function checkReadAccess()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess("read", $this->object->getRefId())) {
            // only with read access it is possible to run the test
            $this->ilias->raiseError($this->lng->txt("cannot_execute_test"), $this->ilias->error_obj->MESSAGE);
        }
    }

    protected function checkTestExecutable()
    {
        $executable = $this->object->isExecutable($this->testSession, $this->testSession->getUserId());
        
        if (!$executable['executable']) {
            ilUtil::sendInfo($executable['errormessage'], true);
            $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
        }
    }
    
    protected function checkTestSessionUser(ilTestSession $testSession)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if ($testSession->getUserId() != $DIC->user()->getId()) {
            throw new ilTestException('active id given does not relate to current user!');
        }
    }
    
    protected function ensureExistingTestSession(ilTestSession $testSession)
    {
        if ($testSession->getActiveId()) {
            return;
        }

        global $DIC;
        $ilUser = $DIC['ilUser'];
        
        $testSession->setUserId($ilUser->getId());

        if ($testSession->isAnonymousUser()) {
            if (!$testSession->doesAccessCodeInSessionExists()) {
                return;
            }

            $testSession->setAnonymousId($testSession->getAccessCodeFromSession());
        }
        
        $testSession->saveToDb();
    }
    
    protected function initProcessLocker($activeId)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        require_once 'Modules/Test/classes/class.ilTestProcessLockerFactory.php';
        $processLockerFactory = new ilTestProcessLockerFactory($this->assSettings, $ilDB);

        $processLockerFactory->setActiveId($activeId);
        
        $this->processLocker = $processLockerFactory->getLocker();
    }

    /**
     * Save tags for tagging gui
     *
     * Needed this function here because the test info page
     * uses another class to send its form results
     */
    public function saveTagsCmd()
    {
        include_once("./Services/Tagging/classes/class.ilTaggingGUI.php");
        $tagging_gui = new ilTaggingGUI();
        $tagging_gui->setObject($this->object->getId(), $this->object->getType());
        $tagging_gui->saveInput();
        $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
    }

    /**
     * updates working time and stores state saveresult to see if question has to be stored or not
     */
    public function updateWorkingTime()
    {
        if ($_SESSION["active_time_id"]) {
            $this->object->updateWorkingTime($_SESSION["active_time_id"]);
        }
        
        $_SESSION["active_time_id"] = $this->object->startWorkingTime(
            $this->testSession->getActiveId(),
            $this->testSession->getPass()
        );
    }

    // fau: testNav - new function removeIntermediateSolution()
    /**
     * remove an auto-saved solution of the current question
     * @return mixed	number of rows or db error
     */
    public function removeIntermediateSolution()
    {
        $questionId = $this->getCurrentQuestionId();
        
        $this->getQuestionInstance($questionId)->removeIntermediateSolution(
            $this->testSession->getActiveId(),
            $this->testSession->getPass()
        );
    }
    // fau.

    /**
     * saves the user input of a question
     */
    abstract public function saveQuestionSolution($authorized = true, $force = false);

    abstract protected function canSaveResult();

    public function suspendTestCmd()
    {
        $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
    }

    /**
    * Checks wheather the maximum processing time is reached or not
    *
    * Checks wheather the maximum processing time is reached or not
    *
    * @return bool TRUE if the maximum processing time is reached, FALSE otherwise
    */
    public function isMaxProcessingTimeReached()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $active_id = $this->testSession->getActiveId();
        $starting_time = $this->object->getStartingTimeOfUser($active_id);
        if ($starting_time === false) {
            return false;
        } else {
            return $this->object->isMaxProcessingTimeReached($starting_time, $active_id);
        }
    }

    protected function determineInlineScoreDisplay()
    {
        $show_question_inline_score = false;
        if ($this->object->getAnswerFeedbackPoints()) {
            $show_question_inline_score = true;
            return $show_question_inline_score;
        }
        return $show_question_inline_score;
    }

    protected function populateTestNavigationToolbar(ilTestNavigationToolbarGUI $toolbarGUI)
    {
        $this->tpl->setCurrentBlock('test_nav_toolbar');
        $this->tpl->setVariable('TEST_NAV_TOOLBAR', $toolbarGUI->getHTML());
        $this->tpl->parseCurrentBlock();
    }

    protected function populateQuestionNavigation($sequenceElement, $disabled, $primaryNext)
    {
        if (!$this->isFirstQuestionInSequence($sequenceElement)) {
            $this->populatePreviousButtons($disabled);
        }

        if (!$this->isLastQuestionInSequence($sequenceElement)) {
            $this->populateNextButtons($disabled, $primaryNext);
        }
    }

    protected function populatePreviousButtons($disabled)
    {
        $this->populateUpperPreviousButtonBlock($disabled);
        $this->populateLowerPreviousButtonBlock($disabled);
    }
    
    protected function populateNextButtons($disabled, $primaryNext)
    {
        $this->populateUpperNextButtonBlock($disabled, $primaryNext);
        $this->populateLowerNextButtonBlock($disabled, $primaryNext);
    }

    protected function populateLowerNextButtonBlock($disabled, $primaryNext)
    {
        $button = $this->buildNextButtonInstance($disabled, $primaryNext);
        $button->setId('bottomnextbutton');

        $this->tpl->setCurrentBlock("next_bottom");
        $this->tpl->setVariable("BTN_NEXT", $button->render());
        $this->tpl->parseCurrentBlock();
    }

    protected function populateUpperNextButtonBlock($disabled, $primaryNext)
    {
        $button = $this->buildNextButtonInstance($disabled, $primaryNext);
        $button->setId('nextbutton');

        $this->tpl->setCurrentBlock("next");
        $this->tpl->setVariable("BTN_NEXT", $button->render());
        $this->tpl->parseCurrentBlock();
    }

    protected function populateLowerPreviousButtonBlock($disabled)
    {
        $button = $this->buildPreviousButtonInstance($disabled);
        $button->setId('bottomprevbutton');

        $this->tpl->setCurrentBlock("prev_bottom");
        $this->tpl->setVariable("BTN_PREV", $button->render());
        $this->tpl->parseCurrentBlock();
    }

    protected function populateUpperPreviousButtonBlock($disabled)
    {
        $button = $this->buildPreviousButtonInstance($disabled);
        $button->setId('prevbutton');

        $this->tpl->setCurrentBlock("prev");
        $this->tpl->setVariable("BTN_PREV", $button->render());
        $this->tpl->parseCurrentBlock();
    }
    
    /**
     * @param bool $disabled
     * @param bool $primaryNext
     * @return ilButtonBase|ilLinkButton|ilTestPlayerNavButton
     */
    private function buildNextButtonInstance($disabled, $primaryNext)
    {
        $button = ilTestPlayerNavButton::getInstance();
        // fau: testNav - set glyphicon and primary
        $button->setPrimary($primaryNext);
        $button->setRightGlyph('glyphicon glyphicon-arrow-right');
        // fau.
        $button->setNextCommand(ilTestPlayerCommands::NEXT_QUESTION);
        $button->setUrl($this->ctrl->getLinkTarget($this, ilTestPlayerCommands::NEXT_QUESTION));
        $button->setCaption('next_question');
        $button->addCSSClass('ilTstNavElem');
        //$button->setDisabled($disabled);
        return $button;
    }

    /**
     * @param $disabled
     * @return ilTestPlayerNavButton
     */
    private function buildPreviousButtonInstance($disabled)
    {
        $button = ilTestPlayerNavButton::getInstance();
        // fau: testNav - set glyphicon and primary
        $button->setLeftGlyph('glyphicon glyphicon-arrow-left');
        // fau.
        $button->setNextCommand(ilTestPlayerCommands::PREVIOUS_QUESTION);
        $button->setUrl($this->ctrl->getLinkTarget($this, ilTestPlayerCommands::PREVIOUS_QUESTION));
        $button->setCaption('previous_question');
        $button->addCSSClass('ilTstNavElem');
        //$button->setDisabled($disabled);
        return $button;
    }

    protected function populateSpecificFeedbackBlock(assQuestionGUI $question_gui)
    {
        $solutionValues = $question_gui->object->getSolutionValues(
            $this->testSession->getActiveId(),
            null
        );
        
        $feedback = $question_gui->getSpecificFeedbackOutput(
            $question_gui->object->fetchIndexedValuesFromValuePairs($solutionValues)
        );
        
        $this->tpl->setCurrentBlock("specific_feedback");
        $this->tpl->setVariable("SPECIFIC_FEEDBACK", $feedback);
        $this->tpl->parseCurrentBlock();
    }

    protected function populateGenericFeedbackBlock(assQuestionGUI $question_gui, $solutionCorrect)
    {
        $feedback = $question_gui->getGenericFeedbackOutput($this->testSession->getActiveId(), null);
        
        if (strlen($feedback)) {
            $cssClass = (
                $solutionCorrect ?
                ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT : ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_WRONG
            );
            
            $this->tpl->setCurrentBlock("answer_feedback");
            $this->tpl->setVariable("ANSWER_FEEDBACK", $feedback);
            $this->tpl->setVariable("ILC_FB_CSS_CLASS", $cssClass);
            $this->tpl->parseCurrentBlock();
        }
    }

    protected function populateScoreBlock($reachedPoints, $maxPoints)
    {
        $scoreInformation = sprintf(
            $this->lng->txt("you_received_a_of_b_points"),
            $reachedPoints,
            $maxPoints
        );
        
        $this->tpl->setCurrentBlock("received_points_information");
        $this->tpl->setVariable("RECEIVED_POINTS_INFORMATION", $scoreInformation);
        $this->tpl->parseCurrentBlock();
    }

    protected function populateSolutionBlock($solutionoutput)
    {
        if (strlen($solutionoutput)) {
            $this->tpl->setCurrentBlock("solution_output");
            $this->tpl->setVariable("CORRECT_SOLUTION", $this->lng->txt("tst_best_solution_is"));
            $this->tpl->setVariable("QUESTION_FEEDBACK", $solutionoutput);
            $this->tpl->parseCurrentBlock();
        }
    }
    
    protected function populateSyntaxStyleBlock()
    {
        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $this->tpl->parseCurrentBlock();
    }

    protected function populateContentStyleBlock()
    {
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(0)
        );
        $this->tpl->parseCurrentBlock();
    }

    /**
     * Sets a session variable with the test access code for an anonymous test user
     *
     * Sets a session variable with the test access code for an anonymous test user
     */
    public function setAnonymousIdCmd()
    {
        if ($this->testSession->isAnonymousUser()) {
            $this->testSession->setAccessCodeToSession($_POST['anonymous_id']);
        }
        
        $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
    }

    /**
     * Start a test for the first time
     *
     * Start a test for the first time. This method contains a lock
     * to prevent multiple submissions by the start test button
     */
    protected function startPlayerCmd()
    {
        $testStartLock = $this->getLockParameter();
        $isFirstTestStartRequest = false;

        $this->processLocker->executeTestStartLockOperation(function () use ($testStartLock, &$isFirstTestStartRequest) {
            if ($this->testSession->lookupTestStartLock() != $testStartLock) {
                $this->testSession->persistTestStartLock($testStartLock);
                $isFirstTestStartRequest = true;
            }
        });

        if ($isFirstTestStartRequest) {
            $this->handleUserSettings();
            $this->ctrl->redirect($this, ilTestPlayerCommands::INIT_TEST);
        }
        
        $this->ctrl->setParameterByClass('ilObjTestGUI', 'lock', $testStartLock);
        $this->ctrl->redirectByClass("ilobjtestgui", "redirectToInfoScreen");
    }

    public function getLockParameter()
    {
        if (isset($_POST['lock']) && strlen($_POST['lock'])) {
            return $_POST['lock'];
        } elseif (isset($_GET['lock']) && strlen($_GET['lock'])) {
            return $_GET['lock'];
        }

        return null;
    }

    /**
     * Resume a test at the last position
     */
    abstract protected function resumePlayerCmd();

    /**
     * Start a test for the first time after a redirect
     */
    protected function initTestCmd()
    {
        if ($this->object->checkMaximumAllowedUsers() == false) {
            return $this->showMaximumAllowedUsersReachedMessage();
        }

        if ($this->testSession->isAnonymousUser() && !$this->testSession->getActiveId()) {
            $accessCode = $this->testSession->createNewAccessCode();
            
            $this->testSession->setAccessCodeToSession($accessCode);
            $this->testSession->setAnonymousId($accessCode);
            $this->testSession->saveToDb();
            
            $this->ctrl->redirect($this, ilTestPlayerCommands::DISPLAY_ACCESS_CODE);
        }

        $this->testSession->unsetAccessCodeInSession();
        $this->ctrl->redirect($this, ilTestPlayerCommands::START_TEST);
    }
    
    public function displayAccessCodeCmd()
    {
        $this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_anonymous_code_presentation.html", "Modules/Test");
        $this->tpl->setCurrentBlock("adm_content");
        $this->tpl->setVariable("TEXT_ANONYMOUS_CODE_CREATED", $this->lng->txt("tst_access_code_created"));
        $this->tpl->setVariable("TEXT_ANONYMOUS_CODE", $this->testSession->getAccessCodeFromSession());
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable("CMD_CONFIRM", ilTestPlayerCommands::ACCESS_CODE_CONFIRMED);
        $this->tpl->setVariable("TXT_CONFIRM", $this->lng->txt("continue_work"));
        $this->tpl->parseCurrentBlock();
    }
    
    public function accessCodeConfirmedCmd()
    {
        $this->ctrl->redirect($this, ilTestPlayerCommands::START_TEST);
    }

    /**
     * Handles some form parameters on starting and resuming a test
     */
    public function handleUserSettings()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        if ($_POST["chb_javascript"]) {
            $ilUser->writePref("tst_javascript", 1);
        } else {
            $ilUser->writePref("tst_javascript", 0);
        }
        
        // hide previous results
        if ($this->object->getNrOfTries() != 1) {
            if ($this->object->getUsePreviousAnswers() == 1) {
                if ($_POST["chb_use_previous_answers"]) {
                    $ilUser->writePref("tst_use_previous_answers", 1);
                } else {
                    $ilUser->writePref("tst_use_previous_answers", 0);
                }
            }
        }
    }

    /**
     * Redirect the user after an automatic save when the time limit is reached
     * @throws ilTestException
     */
    public function redirectAfterAutosaveCmd()
    {
        $active_id = $this->testSession->getActiveId();
        $actualpass = ilObjTest::_getPass($active_id);
        
        $this->performTestPassFinishedTasks($actualpass);

        $this->testSession->setLastFinishedPass($this->testSession->getPass());
        $this->testSession->increaseTestPass();

        $url = $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::AFTER_TEST_PASS_FINISHED, '', false, false);

        $this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_redirect_autosave.html", "Modules/Test");
        $this->tpl->setVariable("TEXT_REDIRECT", $this->lng->txt("redirectAfterSave"));
        $this->tpl->setVariable("URL", $url);
    }

    public function redirectAfterDashboardCmd()
    {
        $active_id = $this->testSession->getActiveId();
        $actualpass = ilObjTest::_getPass($active_id);

        $this->performTestPassFinishedTasks($actualpass);

        $this->testSession->setLastFinishedPass($this->testSession->getPass());
        $this->testSession->increaseTestPass();

        $url = $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::AFTER_TEST_PASS_FINISHED, '', false, false);

        $this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_redirect_autosave.html", "Modules/Test");
        $this->tpl->setVariable("TEXT_REDIRECT", $this->lng->txt("redirectAfterSave"));
        $this->tpl->setVariable("URL", $url);
    }
    
    abstract protected function getCurrentQuestionId();

    /**
     * Automatically save a user answer while working on the test
     * (called repeatedly by asynchronous posts in configured autosave interval)
     */
    public function autosaveCmd()
    {
        $result = "";
        if (is_array($_POST) && count($_POST) > 0) {
            if (!$this->canSaveResult() || $this->isParticipantsAnswerFixed($this->getCurrentQuestionId())) {
                $result = '-IGNORE-';
            } else {
                // answer is changed from authorized solution, so save the change as intermediate solution
                if ($this->getAnswerChangedParameter()) {
                    $res = $this->saveQuestionSolution(false, true);
                }
                // answer is not changed from authorized solution, so delete an intermediate solution
                else {
                    $db_res = $this->removeIntermediateSolution();
                    $res = is_int($db_res);
                }
                if ($res) {
                    $result = $this->lng->txt("autosave_success");
                } else {
                    $result = $this->lng->txt("autosave_failed");
                }
            }
        }
        echo $result;
        exit;
    }

    /**
     * Automatically save a user answer when the limited duration of a test run is reached
     * (called by synchronous form submit when the remaining time count down reaches zero)
     */
    public function autosaveOnTimeLimitCmd()
    {
        if (!$this->isParticipantsAnswerFixed($this->getCurrentQuestionId())) {
            // time limit saves the user solution as authorized
            $this->saveQuestionSolution(true, true);
        }
        $this->ctrl->redirect($this, ilTestPlayerCommands::REDIRECT_ON_TIME_LIMIT);
    }


    // fau: testNav - new function detectChangesCmd()
    /**
     * Detect changes sent in the background to the server
     * This is called by ajax from ilTestPlayerQuestionEditControl.js
     * It is needed by Java and Flash question and eventually plgin question vtypes
     */
    protected function detectChangesCmd()
    {
        $questionId = $this->getCurrentQuestionId();
        $state = $this->getQuestionInstance($questionId)->lookupForExistingSolutions(
            $this->testSession->getActiveId(),
            $this->testSession->getPass()
        );
        $result = array();
        $result['isAnswered'] = $state['authorized'];
        $result['isAnswerChanged'] = $state['intermediate'];

        echo json_encode($result);
        exit;
    }
    // fau.

    protected function submitIntermediateSolutionCmd()
    {
        $this->saveQuestionSolution(false, true);
        // fau: testNav - set the 'answer changed' parameter when an intermediate solution is submitted
        $this->setAnswerChangedParameter(true);
        // fau.
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }
    
    /**
     * Toggle side list
     */
    public function toggleSideListCmd()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $show_side_list = $ilUser->getPref('side_list_of_questions');
        $ilUser->writePref('side_list_of_questions', !$show_side_list);
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }
    
    protected function markQuestionAndSaveIntermediateCmd()
    {
        // fau: testNav - handle intermediate submit when marking the question
        $this->handleIntermediateSubmit();
        // fau.
        $this->markQuestionCmd();
    }
    
    /**
     * Set a question solved
     */
    protected function markQuestionCmd()
    {
        $questionId = $this->testSequence->getQuestionForSequence(
            $this->getCurrentSequenceElement()
        );
        
        $this->object->setQuestionSetSolved(1, $questionId, $this->testSession->getUserId());
        
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function unmarkQuestionAndSaveIntermediateCmd()
    {
        // fau: testNav - handle intermediate submit when unmarking the question
        $this->handleIntermediateSubmit();
        // fau.
        $this->unmarkQuestionCmd();
    }

    /**
     * Set a question unsolved
     */
    protected function unmarkQuestionCmd()
    {
        $questionId = $this->testSequence->getQuestionForSequence(
            $this->getCurrentSequenceElement()
        );

        $this->object->setQuestionSetSolved(0, $questionId, $this->testSession->getUserId());

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    /**
     * The final submission of a test was confirmed
     */
    protected function confirmFinishCmd()
    {
        $this->finishTestCmd(false);
    }
    
    /**
     * Confirmation of the tests final submission
     */
    protected function confirmFinishTestCmd()
    {
        /**
         * @var $ilUser ilObjUser
         */
        global $DIC;
        $ilUser = $DIC['ilUser'];

        require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'confirmFinish'));
        $confirmation->setHeaderText($this->lng->txt("tst_finish_confirmation_question"));
        $confirmation->setConfirm($this->lng->txt("tst_finish_confirm_button"), 'confirmFinish');
        $confirmation->setCancel($this->lng->txt("tst_finish_confirm_cancel_button"), ilTestPlayerCommands::BACK_FROM_FINISHING);

        $this->populateHelperGuiContent($confirmation);
    }

    public function finishTestCmd($requires_confirmation = true)
    {
        unset($_SESSION["tst_next"]);

        $active_id = $this->testSession->getActiveId();
        $actualpass = ilObjTest::_getPass($active_id);

        $allObligationsAnswered = ilObjTest::allObligationsAnswered($this->testSession->getTestId(), $active_id, $actualpass);

        /*
         * The following "endgames" are possible prior to actually finishing the test:
         * - Obligations (Ability to finish the test.)
         *      If not all obligatory questions are answered, the user is presented a list
         *      showing the deficits.
         * - Examview (Will to finish the test.)
         *      With the examview, the participant can review all answers given in ILIAS or a PDF prior to
         *      commencing to the finished test.
         * - Last pass allowed (Reassuring the will to finish the test.)
         *      If passes are limited, on the last pass, an additional confirmation is to be displayed.
         */

        
        if ($this->object->areObligationsEnabled() && !$allObligationsAnswered) {
            if ($this->object->getListOfQuestions()) {
                $this->ctrl->redirect($this, ilTestPlayerCommands::QUESTION_SUMMARY_INC_OBLIGATIONS);
            } else {
                $this->ctrl->redirect($this, ilTestPlayerCommands::QUESTION_SUMMARY_OBLIGATIONS_ONLY);
            }

            return;
        }

        // Examview enabled & !reviewed & requires_confirmation? test_submission_overview (review gui)
        if ($this->object->getEnableExamview() && !isset($_GET['reviewed']) && $requires_confirmation) {
            $this->ctrl->redirectByClass('ilTestSubmissionReviewGUI', "show");
            return;
        }

        // Last try in limited tries & !confirmed
        if (($requires_confirmation) && ($actualpass == $this->object->getNrOfTries() - 1)) {
            // show confirmation page
            return $this->confirmFinishTestCmd();
        }

        // Last try in limited tries & confirmed?
        if (($actualpass == $this->object->getNrOfTries() - 1) && (!$requires_confirmation)) {
            // @todo: php7 ask mister test
            #$ilAuth->setIdle(ilSession::getIdleValue(), false);
            #$ilAuth->setExpire(0);
            switch ($this->object->getMailNotification()) {
                case 1:
                    $this->object->sendSimpleNotification($active_id);
                    break;
                case 2:
                    $this->object->sendAdvancedNotification($active_id);
                    break;
            }
        }

        // Non-last try finish
        if (!$_SESSION['tst_pass_finish']) {
            if (!$_SESSION['tst_pass_finish']) {
                $_SESSION['tst_pass_finish'] = 1;
            }
            if ($this->object->getMailNotificationType() == 1) {
                switch ($this->object->getMailNotification()) {
                    case 1:
                        $this->object->sendSimpleNotification($active_id);
                        break;
                    case 2:
                        $this->object->sendAdvancedNotification($active_id);
                        break;
                }
            }
        }
        
        // no redirect request loops after test pass finished tasks has been performed
        
        $this->performTestPassFinishedTasks($actualpass);

        $this->ctrl->redirect($this, ilTestPlayerCommands::AFTER_TEST_PASS_FINISHED);
    }

    protected function performTestPassFinishedTasks($finishedPass)
    {
        require_once 'Modules/Test/classes/class.ilTestPassFinishTasks.php';

        $finishTasks = new ilTestPassFinishTasks($this->testSession->getActiveId(), $this->object->getId());
        $finishTasks->performFinishTasks($this->processLocker);
    }

    protected function afterTestPassFinishedCmd()
    {
        $activeId = $this->testSession->getActiveId();
        $lastFinishedPass = $this->testSession->getLastFinishedPass();

        // handle test signature
        if ($this->isTestSignRedirectRequired($activeId, $lastFinishedPass)) {
            $this->ctrl->redirectByClass('ilTestSignatureGUI', 'invokeSignaturePlugin');
        }

        // show final statement
        if (!$_GET['skipfinalstatement']) {
            if ($this->object->getShowFinalStatement()) {
                $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_FINAL_STATMENT);
            }
        }

        // redirect after test
        $redirection_mode = $this->object->getRedirectionMode();
        $redirection_url = $this->object->getRedirectionUrl();
        if ($redirection_url && $redirection_mode) {
            if ($redirection_mode == REDIRECT_KIOSK) {
                if ($this->object->getKioskMode()) {
                    ilUtil::redirect($redirection_url);
                }
            } else {
                ilUtil::redirect($redirection_url);
            }
        }

        // default redirect (pass overview when enabled, otherwise infoscreen)
        $this->redirectBackCmd();
    }

    protected function isTestSignRedirectRequired($activeId, $lastFinishedPass)
    {
        if (!$this->object->getSignSubmission()) {
            return false;
        }

        if (!is_null(ilSession::get("signed_{$activeId}_{$lastFinishedPass}"))) {
            return false;
        }

        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $activePlugins = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, 'Test', 'tsig');

        if (!count($activePlugins)) {
            return false;
        }
        
        return true;
    }

    /**
     * @param $active
     *
     * @return void
     */
    protected function archiveParticipantSubmission($active, $pass)
    {
        global $DIC;
        $ilObjDataCache = $DIC['ilObjDataCache'];

        require_once 'Modules/Test/classes/class.ilTestResultHeaderLabelBuilder.php';
        $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $ilObjDataCache);

        $objectivesList = null;

        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $testSequence = $this->testSequenceFactory->getSequenceByActiveIdAndPass($this->testSession->getActiveId(), $this->testSession->getPass());
            $testSequence->loadFromDb();
            $testSequence->loadQuestions();

            require_once 'Modules/Course/classes/Objectives/class.ilLOTestQuestionAdapter.php';
            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($this->testSession);

            $objectivesList = $this->buildQuestionRelatedObjectivesList($objectivesAdapter, $testSequence);
            $objectivesList->loadObjectivesTitles();

            $testResultHeaderLabelBuilder->setObjectiveOrientedContainerId($this->testSession->getObjectiveOrientedContainerId());
            $testResultHeaderLabelBuilder->setUserId($this->testSession->getUserId());
            $testResultHeaderLabelBuilder->setTestObjId($this->object->getId());
            $testResultHeaderLabelBuilder->setTestRefId($this->object->getRefId());
            $testResultHeaderLabelBuilder->initObjectiveOrientedMode();
        }

        $results = $this->object->getTestResult(
            $active,
            $pass,
            false,
            !$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()
        );

        require_once 'class.ilTestEvaluationGUI.php';
        $testevaluationgui = new ilTestEvaluationGUI($this->object);
        $results_output = $testevaluationgui->getPassListOfAnswers(
            $results,
            $active,
            $pass,
            false,
            false,
            false,
            false,
            false,
            $objectivesList,
            $testResultHeaderLabelBuilder
        );

        require_once './Modules/Test/classes/class.ilTestArchiver.php';
        global $DIC;
        $ilSetting = $DIC['ilSetting'];
        $inst_id = $ilSetting->get('inst_id', null);
        $archiver = new ilTestArchiver($this->object->getId());

        $path = ilUtil::getWebspaceDir() . '/assessment/' . $this->object->getId() . '/exam_pdf';
        if (!is_dir($path)) {
            ilUtil::makeDirParents($path);
        }
        $filename = realpath($path) . '/exam_N' . $inst_id . '-' . $this->object->getId()
                    . '-' . $active . '-' . $pass . '.pdf';

        ilTestPDFGenerator::generatePDF($results_output, ilTestPDFGenerator::PDF_OUTPUT_FILE, $filename);
        //$template->setVariable("PDF_FILE_LOCATION", $filename);
        // Participant submission
        $archiver->handInParticipantSubmission($active, $pass, $filename, $results_output);
        //$archiver->handInParticipantMisc( $active, $pass, 'signature_gedoens.sig', $filename );
        //$archiver->handInParticipantQuestionMaterial( $active, $pass, 123, 'file_upload.pdf', $filename );

        global $DIC;
        $ilias = $DIC['ilias'];
        $questions = $this->object->getQuestions();
        foreach ($questions as $question_id) {
            $question_object = $this->object->getQuestionDataset($question_id);
            if ($question_object->type_tag == 'assFileUpload') {
                // Pfad: /data/default/assessment/tst_2/14/21/files/file_14_4_1370417414.png
                // /data/ - klar
                // /assessment/ - Konvention
                // /tst_2/ = /tst_<test_id> (ilObjTest)
                // /14/ = /<active_fi>/
                // /21/ = /<question_id>/ (question_object)
                // /files/ - Konvention
                // file_14_4_1370417414.png = file_<active_fi>_<pass>_<some timestamp>.<ext>

                $candidate_path =
                    $ilias->ini_ilias->readVariable('server', 'absolute_path') . ilTestArchiver::DIR_SEP
                        . $ilias->ini_ilias->readVariable('clients', 'path') . ilTestArchiver::DIR_SEP
                        . $ilias->client_id . ilTestArchiver::DIR_SEP
                        . 'assessment' . ilTestArchiver::DIR_SEP
                        . 'tst_' . $this->object->test_id . ilTestArchiver::DIR_SEP
                        . $active . ilTestArchiver::DIR_SEP
                        . $question_id . ilTestArchiver::DIR_SEP
                        . 'files' . ilTestArchiver::DIR_SEP;
                $handle = opendir($candidate_path);
                while ($handle !== false && ($file = readdir($handle)) !== false) {
                    if ($file != null) {
                        $filename_start = 'file_' . $active . '_' . $pass . '_';

                        if (strpos($file, $filename_start) === 0) {
                            $archiver->handInParticipantQuestionMaterial($active, $pass, $question_id, $file, $file);
                        }
                    }
                }
            }
        }
        $passdata = $this->object->getTestResult(
            $active,
            $pass,
            false,
            !$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()
        );
        $overview = $testevaluationgui->getPassListOfAnswers(
            $passdata,
            $active,
            $pass,
            true,
            false,
            false,
            true,
            false,
            $objectivesList,
            $testResultHeaderLabelBuilder
        );
        $filename = realpath(ilUtil::getWebspaceDir()) . '/assessment/scores-' . $this->object->getId() . '-' . $active . '-' . $pass . '.pdf';
        ilTestPDFGenerator::generatePDF($overview, ilTestPDFGenerator::PDF_OUTPUT_FILE, $filename);
        $archiver->handInTestResult($active, $pass, $filename);
        unlink($filename);
        
        return;
    }
    
    public function redirectBackCmd()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
        $testPassesSelector = new ilTestPassesSelector($DIC['ilDB'], $this->object);
        $testPassesSelector->setActiveId($this->testSession->getActiveId());
        $testPassesSelector->setLastFinishedPass($this->testSession->getLastFinishedPass());

        if (count($testPassesSelector->getReportablePasses())) {
            if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
                $this->ctrl->redirectByClass(array('ilTestResultsGUI', 'ilTestEvalObjectiveOrientedGUI'));
            }
            
            $this->ctrl->redirectByClass(array('ilTestResultsGUI', 'ilMyTestResultsGUI', 'ilTestEvaluationGUI'));
        }

        $this->backToInfoScreenCmd();
    }
    
    protected function backToInfoScreenCmd()
    {
        $this->ctrl->redirectByClass('ilObjTestGUI', 'redirectToInfoScreen');
    }
    
    /*
    * Presents the final statement of a test
    */
    public function showFinalStatementCmd()
    {
        $template = new ilTemplate("tpl.il_as_tst_final_statement.html", true, true, "Modules/Test");
        $this->ctrl->setParameter($this, "skipfinalstatement", 1);
        $template->setVariable("FORMACTION", $this->ctrl->getFormAction($this, ilTestPlayerCommands::AFTER_TEST_PASS_FINISHED));
        $template->setVariable("FINALSTATEMENT", $this->object->prepareTextareaOutput($this->object->getFinalStatement(), true));
        $template->setVariable("BUTTON_CONTINUE", $this->lng->txt("btn_next"));
        $this->tpl->setVariable($this->getContentBlockName(), $template->get());
    }
    
    public function getKioskHead()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        
        $template = new ilTemplate('tpl.il_as_tst_kiosk_head.html', true, true, 'Modules/Test');
        if ($this->object->getShowKioskModeTitle()) {
            $template->setCurrentBlock("kiosk_show_title");
            $template->setVariable("TEST_TITLE", $this->object->getTitle());
            $template->parseCurrentBlock();
        }
        if ($this->object->getShowKioskModeParticipant()) {
            $template->setCurrentBlock("kiosk_show_participant");
            $template->setVariable("PARTICIPANT_NAME_TXT", $this->lng->txt("login_as"));
            $template->setVariable("PARTICIPANT_NAME", $ilUser->getFullname());
            $template->setVariable("PARTICIPANT_LOGIN", $ilUser->getLogin());
            $template->setVariable("PARTICIPANT_MATRICULATION", $ilUser->getMatriculation());
            $template->setVariable("PARTICIPANT_EMAIL", $ilUser->getEmail());
            $template->parseCurrentBlock();
        }
        if ($this->object->isShowExamIdInTestPassEnabled()) {
            $exam_id = ilObjTest::buildExamId(
                $this->testSession->getActiveId(),
                $this->testSession->getPass(),
                $this->object->getId()
            );
            
            $template->setCurrentBlock("kiosk_show_exam_id");
            $template->setVariable("EXAM_ID_TXT", $this->lng->txt("exam_id"));
            $template->setVariable("EXAM_ID", $exam_id);
            $template->parseCurrentBlock();
        }
        return $template->get();
    }

    /**
     * @return string $formAction
     */
    protected function prepareTestPage($presentationMode, $sequenceElement, $questionId)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilNavigationHistory = $DIC['ilNavigationHistory'];

        $ilNavigationHistory->addItem(
            $this->testSession->getRefId(),
            $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::RESUME_PLAYER),
            'tst'
        );

        $this->initTestPageTemplate();
        $this->populateContentStyleBlock();
        $this->populateSyntaxStyleBlock();

        if ($this->isMaxProcessingTimeReached()) {
            $this->maxProcessingTimeReached();
            return;
        }
        
        if ($this->object->endingTimeReached()) {
            $this->endingTimeReached();
            return;
        }

        if ($this->isOptionalQuestionAnsweringConfirmationRequired($sequenceElement)) {
            $this->ctrl->setParameter($this, "sequence", $sequenceElement);
            $this->showAnswerOptionalQuestionsConfirmation();
            return;
        }
            
        if ($this->object->getKioskMode()) {
            $this->populateKioskHead();
        }
        
        $this->tpl->setVariable("TEST_ID", $this->object->getTestId());
        $this->tpl->setVariable("LOGIN", $ilUser->getLogin());
        $this->tpl->setVariable("SEQ_ID", $sequenceElement);
        $this->tpl->setVariable("QUEST_ID", $questionId);
                
        if ($this->object->getEnableProcessingTime()) {
            $this->outProcessingTime($this->testSession->getActiveId());
        }
        
        $this->tpl->setVariable("PAGETITLE", "- " . $this->object->getTitle());
        
        if ($this->object->isShowExamIdInTestPassEnabled() && !$this->object->getKioskMode()) {
            $this->tpl->setCurrentBlock('exam_id_footer');
            $this->tpl->setVariable('EXAM_ID_VAL', ilObjTest::lookupExamId(
                $this->testSession->getActiveId(),
                $this->testSession->getPass(),
                $this->object->getId()
            ));
            $this->tpl->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->object->getListOfQuestions()) {
            $this->showSideList($presentationMode, $sequenceElement);
        }
    }
    
    abstract protected function isOptionalQuestionAnsweringConfirmationRequired($sequenceElement);
    
    abstract protected function isShowingPostponeStatusReguired($questionId);

    protected function showQuestionViewable(assQuestionGUI $questionGui, $formAction, $isQuestionWorkedThrough, $instantResponse)
    {
        $questionNavigationGUI = $this->buildReadOnlyStateQuestionNavigationGUI($questionGui->object->getId());
        $questionNavigationGUI->setQuestionWorkedThrough($isQuestionWorkedThrough);
        $questionGui->setNavigationGUI($questionNavigationGUI);
        
        // fau: testNav - set answere status in question header
        $questionGui->getQuestionHeaderBlockBuilder()->setQuestionAnswered($isQuestionWorkedThrough);
        // fau.

        $answerFeedbackEnabled = (
            $instantResponse && $this->object->getSpecificAnswerFeedback()
        );

        $solutionoutput = $questionGui->getSolutionOutput(
            $this->testSession->getActiveId(), 	#active_id
            $this->testSession->getPass(),		#pass
            false, 								#graphical_output
            false,								#result_output
            true, 								#show_question_only
            $answerFeedbackEnabled,				#show_feedback
            false, 								#show_correct_solution
            false, 								#show_manual_scoring
            true								#show_question_text
        );

        $pageoutput = $questionGui->outQuestionPage(
            "",
            $this->isShowingPostponeStatusReguired($questionGui->object->getId()),
            $this->testSession->getActiveId(),
            $solutionoutput
        );
        
        $this->tpl->setCurrentBlock('readonly_css_class');
        $this->tpl->touchBlock('readonly_css_class');
        $this->tpl->parseCurrentBlock();

        $this->tpl->setVariable('QUESTION_OUTPUT', $pageoutput);

        $this->tpl->setVariable("FORMACTION", $formAction);
        $this->tpl->setVariable("ENCTYPE", 'enctype="' . $questionGui->getFormEncodingType() . '"');
        $this->tpl->setVariable("FORM_TIMESTAMP", time());
    }

    protected function showQuestionEditable(assQuestionGUI $questionGui, $formAction, $isQuestionWorkedThrough, $instantResponse)
    {
        $questionNavigationGUI = $this->buildEditableStateQuestionNavigationGUI(
            $questionGui->object->getId(),
            $this->populateCharSelectorIfRequired()
        );
        if ($isQuestionWorkedThrough) {
            $questionNavigationGUI->setDiscardSolutionButtonEnabled(true);
            // fau: testNav - set answere status in question header
            $questionGui->getQuestionHeaderBlockBuilder()->setQuestionAnswered(true);
        // fau.
        } elseif ($this->object->isPostponingEnabled()) {
            $questionNavigationGUI->setSkipQuestionLinkTarget(
                $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::SKIP_QUESTION)
            );
        }
        $questionGui->setNavigationGUI($questionNavigationGUI);

        $isPostponed = $this->isShowingPostponeStatusReguired($questionGui->object->getId());
        
        $answerFeedbackEnabled = (
            $instantResponse && $this->object->getSpecificAnswerFeedback()
        );

        if (isset($_GET['save_error']) && $_GET['save_error'] == 1 && isset($_SESSION['previouspost'])) {
            $userPostSolution = $_SESSION['previouspost'];
            unset($_SESSION['previouspost']);
        } else {
            $userPostSolution = false;
        }

        // fau: testNav - add special checkbox for mc question
        // moved to another patch block
        // fau.
        
        // hey: prevPassSolutions - determine solution pass index and configure gui accordingly
        $qstConfig = $questionGui->object->getTestPresentationConfig();
        
        if ($questionGui instanceof assMultipleChoiceGUI) {
            $qstConfig->setWorkedThrough($isQuestionWorkedThrough);
            $qstConfig->setIsUnchangedAnswerPossible($this->object->getMCScoring());
        }

        if ($qstConfig->isPreviousPassSolutionReuseAllowed()) {
            $passIndex = $this->determineSolutionPassIndex($questionGui); // last pass having solution stored
            if ($passIndex < $this->testSession->getPass()) { // it's the previous pass if current pass is higher
                $qstConfig->setSolutionInitiallyPrefilled(true);
            }
        } else {
            $passIndex = $this->testSession->getPass();
        }
        // hey.
        
        // Answer specific feedback is rendered into the display of the test question with in the concrete question types outQuestionForTest-method.
        // Notation of the params prior to getting rid of this crap in favor of a class
        $questionGui->outQuestionForTest(
            $formAction, 							#form_action
            $this->testSession->getActiveId(),		#active_id
            // hey: prevPassSolutions - prepared pass index having no, current or previous solution
            $passIndex, 							#pass
            // hey.
            $isPostponed, 							#is_postponed
            $userPostSolution, 						#user_post_solution
            $answerFeedbackEnabled					#answer_feedback == inline_specific_feedback
        );
        // The display of specific inline feedback and specific feedback in an own block is to honor questions, which
        // have the possibility to embed the specific feedback into their output while maintaining compatibility to
        // questions, which do not have such facilities. E.g. there can be no "specific inline feedback" for essay
        // questions, while the multiple-choice questions do well.


        $this->populateModals();

        // fau: testNav - pouplate the new question edit control instead of the deprecated intermediate solution saver
        $this->populateQuestionEditControl($questionGui);
        // fau.
    }
    
    // hey: prevPassSolutions - determine solution pass index
    protected function determineSolutionPassIndex(assQuestionGUI $questionGui)
    {
        require_once './Modules/Test/classes/class.ilObjTest.php';
        
        if (ilObjTest::_getUsePreviousAnswers($this->testSession->getActiveId(), true)) {
            $currentSolutionAvailable = $questionGui->object->authorizedOrIntermediateSolutionExists(
                $this->testSession->getActiveId(),
                $this->testSession->getPass()
            );
            
            if (!$currentSolutionAvailable) {
                $previousPass = $questionGui->object->getSolutionMaxPass(
                    $this->testSession->getActiveId()
                );
                
                $previousSolutionAvailable = $questionGui->object->authorizedSolutionExists(
                    $this->testSession->getActiveId(),
                    $previousPass
                );
                
                if ($previousSolutionAvailable) {
                    return $previousPass;
                }
            }
        }
        
        return $this->testSession->getPass();
    }
    // hey.

    abstract protected function showQuestionCmd();

    abstract protected function editSolutionCmd();

    abstract protected function submitSolutionCmd();

    // fau: testNav - new function to revert probably auto-saved changes and show the last submitted question state
    protected function revertChangesCmd()
    {
        $this->removeIntermediateSolution();
        $this->setAnswerChangedParameter(false);
        $this->ctrl->saveParameter($this, 'sequence');
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }
    // fau.

    abstract protected function discardSolutionCmd();
    
    abstract protected function skipQuestionCmd();

    abstract protected function startTestCmd();
    /**
    * check access restrictions like client ip, partipating user etc.
    *
    * check access restrictions like client ip, partipating user etc.
    *
    * @access public
    */
    public function checkOnlineTestAccess()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        
        // check if user is invited to participate
        $user = $this->object->getInvitedUsers($ilUser->getId());
        if (!is_array($user) || count($user) != 1) {
            ilUtil::sendInfo($this->lng->txt("user_not_invited"), true);
            $this->ctrl->redirectByClass("ilobjtestgui", "backToRepository");
        }
            
        $user = array_pop($user);
        // check if client ip is set and if current remote addr is equal to stored client-ip
        if (strcmp($user["clientip"], "") != 0 && strcmp($user["clientip"], $_SERVER["REMOTE_ADDR"]) != 0) {
            ilUtil::sendInfo($this->lng->txt("user_wrong_clientip"), true);
            $this->ctrl->redirectByClass("ilobjtestgui", "backToRepository");
        }
    }

    
    /**
     * test accessible returns true if the user can perform the test
     */
    public function isTestAccessible()
    {
        return 	!$this->isNrOfTriesReached()
                and !$this->isMaxProcessingTimeReached()
                and $this->object->startingTimeReached()
                and !$this->object->endingTimeReached();
    }

    /**
     * nr of tries exceeded
     */
    public function isNrOfTriesReached()
    {
        return $this->object->hasNrOfTriesRestriction() && $this->object->isNrOfTriesReached($this->testSession->getPass());
    }
    
    /**
     * handle endingTimeReached
     * @private
     */
    
    public function endingTimeReached()
    {
        ilUtil::sendInfo(sprintf($this->lng->txt("detail_ending_time_reached"), ilDatePresentation::formatDate(new ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX))));
        $this->testSession->increasePass();
        $this->testSession->setLastSequence(0);
        $this->testSession->saveToDb();

        $this->redirectBackCmd();
    }
    
    /**
    * Outputs a message when the maximum processing time is reached
    *
    * Outputs a message when the maximum processing time is reached
    *
    * @access public
    */
    public function maxProcessingTimeReached()
    {
        $this->suspendTestCmd();
    }

    /**
    * confirm submit results
    * if confirm then results are submitted and the screen will be redirected to the startpage of the test
    * @access public
    */
    public function confirmSubmitAnswers()
    {
        $this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_submit_answers_confirm.html", "Modules/Test");
        $this->tpl->setCurrentBlock("adm_content");
        if ($this->object->isTestFinished($this->testSession->getActiveId())) {
            $this->tpl->setCurrentBlock("not_submit_allowed");
            $this->tpl->setVariable("TEXT_ALREADY_SUBMITTED", $this->lng->txt("tst_already_submitted"));
            $this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_show_answer_sheet"));
        } else {
            $this->tpl->setCurrentBlock("submit_allowed");
            $this->tpl->setVariable("TEXT_CONFIRM_SUBMIT_RESULTS", $this->lng->txt("tst_confirm_submit_answers"));
            $this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_submit_results"));
        }
        $this->tpl->setVariable("BTN_BACK", $this->lng->txt("back"));
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "finalSubmission"));
        $this->tpl->parseCurrentBlock();
    }
    
    public function outProcessingTime($active_id)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $starting_time = $this->object->getStartingTimeOfUser($active_id);
        $processing_time = $this->object->getProcessingTimeInSeconds($active_id);
        $processing_time_minutes = floor($processing_time / 60);
        $processing_time_seconds = $processing_time - $processing_time_minutes * 60;
        $str_processing_time = "";
        if ($processing_time_minutes > 0) {
            $str_processing_time = $processing_time_minutes . " " . ($processing_time_minutes == 1 ? $this->lng->txt("minute") : $this->lng->txt("minutes"));
        }
        if ($processing_time_seconds > 0) {
            if (strlen($str_processing_time) > 0) {
                $str_processing_time .= " " . $this->lng->txt("and") . " ";
            }
            $str_processing_time .= $processing_time_seconds . " " . ($processing_time_seconds == 1 ? $this->lng->txt("second") : $this->lng->txt("seconds"));
        }
        $time_left = $starting_time + $processing_time - time();
        $time_left_minutes = floor($time_left / 60);
        $time_left_seconds = $time_left - $time_left_minutes * 60;
        $str_time_left = "";
        if ($time_left_minutes > 0) {
            $str_time_left = $time_left_minutes . " " . ($time_left_minutes == 1 ? $this->lng->txt("minute") : $this->lng->txt("minutes"));
        }
        if ($time_left < 300) {
            if ($time_left_seconds > 0) {
                if (strlen($str_time_left) > 0) {
                    $str_time_left .= " " . $this->lng->txt("and") . " ";
                }
                $str_time_left .= $time_left_seconds . " " . ($time_left_seconds == 1 ? $this->lng->txt("second") : $this->lng->txt("seconds"));
            }
        }
        $date = getdate($starting_time);
        $formattedStartingTime = ilDatePresentation::formatDate(new ilDateTime($date, IL_CAL_FKT_GETDATE));
        $datenow = getdate();
        $this->tpl->setCurrentBlock("enableprocessingtime");
        $this->tpl->setVariable(
            "USER_WORKING_TIME",
            sprintf(
                $this->lng->txt("tst_time_already_spent"),
                $formattedStartingTime,
                $str_processing_time
            )
        );
        $this->tpl->setVariable("USER_REMAINING_TIME", sprintf($this->lng->txt("tst_time_already_spent_left"), $str_time_left));
        $this->tpl->parseCurrentBlock();

        // jQuery is required by tpl.workingtime.js
        require_once "./Services/jQuery/classes/class.iljQueryUtil.php";
        iljQueryUtil::initjQuery();
        $template = new ilTemplate("tpl.workingtime.js", true, true, 'Modules/Test');
        $template->setVariable("STRING_MINUTE", $this->lng->txt("minute"));
        $template->setVariable("STRING_MINUTES", $this->lng->txt("minutes"));
        $template->setVariable("STRING_SECOND", $this->lng->txt("second"));
        $template->setVariable("STRING_SECONDS", $this->lng->txt("seconds"));
        $template->setVariable("STRING_TIMELEFT", $this->lng->txt("tst_time_already_spent_left"));
        $template->setVariable("AND", strtolower($this->lng->txt("and")));
        $template->setVariable("YEAR", $date["year"]);
        $template->setVariable("MONTH", $date["mon"] - 1);
        $template->setVariable("DAY", $date["mday"]);
        $template->setVariable("HOUR", $date["hours"]);
        $template->setVariable("MINUTE", $date["minutes"]);
        $template->setVariable("SECOND", $date["seconds"]);
        if ($this->object->isEndingTimeEnabled()) {
            $date_time = new ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX);
            preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $date_time->get(IL_CAL_TIMESTAMP), $matches);
            $template->setVariable("ENDYEAR", $matches[1]);
            $template->setVariable("ENDMONTH", $matches[2] - 1);
            $template->setVariable("ENDDAY", $matches[3]);
            $template->setVariable("ENDHOUR", $matches[4]);
            $template->setVariable("ENDMINUTE", $matches[5]);
            $template->setVariable("ENDSECOND", $matches[6]);
        }
        $template->setVariable("YEARNOW", $datenow["year"]);
        $template->setVariable("MONTHNOW", $datenow["mon"] - 1);
        $template->setVariable("DAYNOW", $datenow["mday"]);
        $template->setVariable("HOURNOW", $datenow["hours"]);
        $template->setVariable("MINUTENOW", $datenow["minutes"]);
        $template->setVariable("SECONDNOW", $datenow["seconds"]);
        $template->setVariable("PTIME_M", $processing_time_minutes);
        $template->setVariable("PTIME_S", $processing_time_seconds);
        if($this->ctrl->getCmd() == 'outQuestionSummary') {
            $template->setVariable("REDIRECT_URL", $this->ctrl->getFormAction($this, 'redirectAfterDashboardCmd'));
        } else {
            $template->setVariable("REDIRECT_URL", "");
        }
        $this->tpl->addOnLoadCode($template->get());
    }

    protected function showSideList($presentationMode, $currentSequenceElement)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $sideListActive = $ilUser->getPref('side_list_of_questions');

        if ($sideListActive) {
            $this->tpl->addCss(
                ilUtil::getStyleSheetLocation("output", "ta_split.css", "Modules/Test"),
                "screen"
            );

            $questionSummaryData = $this->service->getQuestionSummaryData($this->testSequence, false);

            require_once 'Modules/Test/classes/class.ilTestQuestionSideListGUI.php';
            $questionSideListGUI = new ilTestQuestionSideListGUI($this->ctrl, $this->lng);
            $questionSideListGUI->setTargetGUI($this);
            $questionSideListGUI->setQuestionSummaryData($questionSummaryData);
            $questionSideListGUI->setCurrentSequenceElement($currentSequenceElement);
            // fau: testNav - set side list presentation mode to "view" to allow navigation when question is in edit mode
            $questionSideListGUI->setCurrentPresentationMode(ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW);
            $questionSideListGUI->setDisabled(false);
            //			$questionSideListGUI->setCurrentPresentationMode($presentationMode);
            //			$questionSideListGUI->setDisabled($presentationMode == self::PRESENTATION_MODE_EDIT);
            // fau.
            $this->tpl->setVariable('LIST_OF_QUESTIONS', $questionSideListGUI->getHTML());
        }
    }

    abstract protected function isQuestionSummaryFinishTestButtonRequired();
    
    /**
     * Output of a summary of all test questions for test participants
     */
    public function outQuestionSummaryCmd($fullpage = true, $contextFinishTest = false, $obligationsInfo = false, $obligationsFilter = false)
    {
        if ($fullpage) {
            $this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_question_summary.html", "Modules/Test");
        }

        $obligationsFulfilled = \ilObjTest::allObligationsAnswered(
            $this->object->getId(),
            $this->testSession->getActiveId(),
            $this->testSession->getPass()
        );

        if ($obligationsInfo && $this->object->areObligationsEnabled() && !$obligationsFulfilled) {
            ilUtil::sendFailure($this->lng->txt('not_all_obligations_answered'));
        }
        
        if ($this->object->getKioskMode() && $fullpage) {
            $head = $this->getKioskHead();
            if (strlen($head)) {
                $this->tpl->setCurrentBlock("kiosk_options");
                $this->tpl->setVariable("KIOSK_HEAD", $head);
                $this->tpl->parseCurrentBlock();
            }
        }


        $active_id = $this->testSession->getActiveId();
        $questionSummaryData = $this->service->getQuestionSummaryData($this->testSequence, $obligationsFilter);
        
        $this->ctrl->setParameter($this, "sequence", $_GET["sequence"]);
        
        if ($fullpage) {
            include_once "./Modules/Test/classes/tables/class.ilListOfQuestionsTableGUI.php";
            $table_gui = new ilListOfQuestionsTableGUI($this, 'showQuestion');
            
            $table_gui->setShowPointsEnabled(!$this->object->getTitleOutput());
            $table_gui->setShowMarkerEnabled($this->object->getShowMarker());
            $table_gui->setObligationsNotAnswered(!$obligationsFulfilled);
            $table_gui->setShowObligationsEnabled($this->object->areObligationsEnabled());
            $table_gui->setObligationsFilterEnabled($obligationsFilter);
            $table_gui->setFinishTestButtonEnabled($this->isQuestionSummaryFinishTestButtonRequired());

            $table_gui->init();
                
            $table_gui->setData($questionSummaryData);

            $this->tpl->setVariable('TABLE_LIST_OF_QUESTIONS', $table_gui->getHTML());
            
            if ($this->object->getEnableProcessingTime()) {
                $this->outProcessingTime($active_id);
            }

            if ($this->object->isShowExamIdInTestPassEnabled()) {
                $this->tpl->setCurrentBlock('exam_id_footer');
                $this->tpl->setVariable('EXAM_ID_VAL', ilObjTest::lookupExamId(
                    $this->testSession->getActiveId(),
                    $this->testSession->getPass(),
                    $this->object->getId()
                ));
                $this->tpl->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
                $this->tpl->parseCurrentBlock();
            }
        }
    }
    
    public function outQuestionSummaryWithObligationsInfoCmd()
    {
        return $this->outQuestionSummaryCmd(true, true, true, false);
    }
    
    public function outObligationsOnlySummaryCmd()
    {
        return $this->outQuestionSummaryCmd(true, true, true, true);
    }
    
    public function showMaximumAllowedUsersReachedMessage()
    {
        $this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_max_allowed_users_reached.html", "Modules/Test");
        $this->tpl->setCurrentBlock("adm_content");
        $this->tpl->setVariable("MAX_ALLOWED_USERS_MESSAGE", sprintf($this->lng->txt("tst_max_allowed_users_message"), $this->object->getAllowedUsersTimeGap()));
        $this->tpl->setVariable("MAX_ALLOWED_USERS_HEADING", sprintf($this->lng->txt("tst_max_allowed_users_heading"), $this->object->getAllowedUsersTimeGap()));
        $this->tpl->setVariable("CMD_BACK_TO_INFOSCREEN", ilTestPlayerCommands::BACK_TO_INFO_SCREEN);
        $this->tpl->setVariable("TXT_BACK_TO_INFOSCREEN", $this->lng->txt("tst_results_back_introduction"));
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $this->tpl->parseCurrentBlock();
    }
    
    public function backFromFinishingCmd()
    {
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }
    
    /**
    * Creates an output of the solution of an answer compared to the correct solution
    *
    * @access public
    */
    public function outCorrectSolution()
    {
        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_correct_solution.html", "Modules/Test");

        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $this->tpl->parseCurrentBlock();

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
        }

        $this->tpl->setCurrentBlock("adm_content");
        $solution = $this->getCorrectSolutionOutput($_GET["evaluation"], $_GET["active_id"], $_GET["pass"]);
        $this->tpl->setVariable("OUTPUT_SOLUTION", $solution);
        $this->tpl->setVariable("TEXT_BACK", $this->lng->txt("back"));
        $this->ctrl->saveParameter($this, "pass");
        $this->ctrl->saveParameter($this, "active_id");
        $this->tpl->setVariable("URL_BACK", $this->ctrl->getLinkTarget($this, "outUserResultsOverview"));
        $this->tpl->parseCurrentBlock();
    }

    /**
    * Creates an output of the list of answers for a test participant during the test
    * (only the actual pass will be shown)
    *
    * @param integer $active_id Active id of the participant
    * @param integer $pass Test pass of the participant
    * @param boolean $testnavigation Deceides wheather to show a navigation for tests or not
    * @access public
    */
    public function showListOfAnswers($active_id, $pass = null, $top_data = "", $bottom_data = "")
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_finish_list_of_answers.html", "Modules/Test");

        $result_array = &$this->object->getTestResult(
            $active_id,
            $pass,
            false,
            !$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()
        );

        $counter = 1;
        // output of questions with solutions
        foreach ($result_array as $question_data) {
            $question = $question_data["qid"];
            if (is_numeric($question)) {
                $this->tpl->setCurrentBlock("printview_question");
                $question_gui = $this->object->createQuestionGUI("", $question);
                $template = new ilTemplate("tpl.il_as_qpl_question_printview.html", true, true, "Modules/TestQuestionPool");
                $template->setVariable("COUNTER_QUESTION", $counter . ". ");
                $template->setVariable("QUESTION_TITLE", $question_gui->object->getTitle());
                
                $show_question_only = ($this->object->getShowSolutionAnswersOnly()) ? true : false;
                $result_output = $question_gui->getSolutionOutput($active_id, $pass, false, false, $show_question_only, $this->object->getShowSolutionFeedback());
                $template->setVariable("SOLUTION_OUTPUT", $result_output);
                $this->tpl->setVariable("QUESTION_OUTPUT", $template->get());
                $this->tpl->parseCurrentBlock();
                $counter++;
            }
        }

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
        }
        if (strlen($top_data)) {
            $this->tpl->setCurrentBlock("top_data");
            $this->tpl->setVariable("TOP_DATA", $top_data);
            $this->tpl->parseCurrentBlock();
        }
        
        if (strlen($bottom_data)) {
            $this->tpl->setCurrentBlock("bottom_data");
            $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
            $this->tpl->setVariable("BOTTOM_DATA", $bottom_data);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setCurrentBlock("adm_content");
        $this->tpl->setVariable("TXT_ANSWER_SHEET", $this->lng->txt("tst_list_of_answers"));
        $user_data = $this->getAdditionalUsrDataHtmlAndPopulateWindowTitle($this->testSession, $active_id, true);
        $signature = $this->getResultsSignature();
        $this->tpl->setVariable("USER_DETAILS", $user_data);
        $this->tpl->setVariable("SIGNATURE", $signature);
        $this->tpl->setVariable("TITLE", $this->object->getTitle());
        $this->tpl->setVariable("TXT_TEST_PROLOG", $this->lng->txt("tst_your_answers"));
        $invited_user = &$this->object->getInvitedUsers($ilUser->getId());
        $pagetitle = $this->object->getTitle() . " - " . $this->lng->txt("clientip") .
            ": " . $invited_user[$ilUser->getId()]["clientip"] . " - " .
            $this->lng->txt("matriculation") . ": " .
            $invited_user[$ilUser->getId()]["matriculation"];
        $this->tpl->setVariable("PAGETITLE", $pagetitle);
        $this->tpl->parseCurrentBlock();
    }
    
    /**
     * Returns the name of the current content block (depends on the kiosk mode setting)
     *
     * @return string The name of the content block
     */
    public function getContentBlockName()
    {
        return "ADM_CONTENT";

        if ($this->object->getKioskMode()) {
            $this->tpl->setBodyClass("kiosk");
            $this->tpl->hideFooter();
            return "CONTENT";
        } else {
            return "ADM_CONTENT";
        }
    }

    public function outUserResultsOverviewCmd()
    {
        $this->ctrl->redirectByClass(
            array('ilRepositoryGUI', 'ilObjTestGUI', 'ilTestEvaluationGUI'),
            "outUserResultsOverview"
        );
    }

    /**
     * Go to requested hint list
     */
    protected function showRequestedHintListCmd()
    {
        // fau: testNav - handle intermediate submit for viewing requested hints
        $this->handleIntermediateSubmit();
        // fau.

        $this->ctrl->setParameter($this, 'pmode', self::PRESENTATION_MODE_EDIT);
        
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';
        $this->ctrl->redirectByClass('ilAssQuestionHintRequestGUI', ilAssQuestionHintRequestGUI::CMD_SHOW_LIST);
    }
    
    /**
     * Go to hint request confirmation
     */
    protected function confirmHintRequestCmd()
    {
        // fau: testNav - handle intermediate submit for confirming hint requests
        $this->handleIntermediateSubmit();
        // fau.

        $this->ctrl->setParameter($this, 'pmode', self::PRESENTATION_MODE_EDIT);

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';
        $this->ctrl->redirectByClass('ilAssQuestionHintRequestGUI', ilAssQuestionHintRequestGUI::CMD_CONFIRM_REQUEST);
    }
    
    abstract protected function isFirstQuestionInSequence($sequenceElement);
    
    abstract protected function isLastQuestionInSequence($sequenceElement);
    
    
    abstract protected function handleQuestionActionCmd();
    
    abstract protected function showInstantResponseCmd();
    
    abstract protected function nextQuestionCmd();
    
    abstract protected function previousQuestionCmd();
    
    protected function prepareSummaryPage()
    {
        $this->tpl->addBlockFile(
            $this->getContentBlockName(),
            'adm_content',
            'tpl.il_as_tst_question_summary.html',
            'Modules/Test'
        );

        if ($this->object->getKioskMode()) {
            $this->populateKioskHead();
        }
    }
    
    protected function initTestPageTemplate()
    {
        $this->tpl->addBlockFile(
            $this->getContentBlockName(),
            'adm_content',
            'tpl.il_as_tst_output.html',
            'Modules/Test'
        );
    }
    
    protected function populateKioskHead()
    {
        ilUtil::sendInfo(); // ???
        
        $head = $this->getKioskHead();
        
        if (strlen($head)) {
            $this->tpl->setCurrentBlock("kiosk_options");
            $this->tpl->setVariable("KIOSK_HEAD", $head);
            $this->tpl->parseCurrentBlock();
        }
    }

    protected function handlePasswordProtectionRedirect()
    {
        if ($this->ctrl->getNextClass() == 'iltestpasswordprotectiongui') {
            return;
        }
        
        if (!$this->passwordChecker->isPasswordProtectionPageRedirectRequired()) {
            return;
        }
        
        $this->ctrl->setParameter($this, 'lock', $this->getLockParameter());
        
        $nextCommand = $this->ctrl->getCmdClass() . '::' . $this->ctrl->getCmd();
        $this->ctrl->setParameterByClass('ilTestPasswordProtectionGUI', 'nextCommand', $nextCommand);
        $this->ctrl->redirectByClass('ilTestPasswordProtectionGUI', 'showPasswordForm');
    }

    protected function isParticipantsAnswerFixed($questionId)
    {
        if ($this->object->isInstantFeedbackAnswerFixationEnabled() && $this->testSequence->isQuestionChecked($questionId)) {
            return true;
        }

        if ($this->object->isFollowupQuestionAnswerFixationEnabled() && $this->testSequence->isNextQuestionPresented($questionId)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    protected function getIntroductionPageButtonLabel()
    {
        return $this->lng->txt("save_introduction");
    }

    protected function initAssessmentSettings()
    {
        $this->assSettings = new ilSetting('assessment');
    }

    /**
     * @param ilTestSession $testSession
     */
    protected function handleSkillTriggering(ilTestSession $testSession)
    {
        $questionList = $this->buildTestPassQuestionList();
        $questionList->load();

        $testResults = $this->object->getTestResult($testSession->getActiveId(), $testSession->getPass(), true);
        
        require_once 'Modules/Test/classes/class.ilTestSkillEvaluation.php';
        $skillEvaluation = new ilTestSkillEvaluation($this->db, $this->object->getTestId(), $this->object->getRefId());

        $skillEvaluation->setUserId($testSession->getUserId());
        $skillEvaluation->setActiveId($testSession->getActiveId());
        $skillEvaluation->setPass($testSession->getPass());
        
        $skillEvaluation->setNumRequiredBookingsForSkillTriggering($this->assSettings->get(
            'ass_skl_trig_num_answ_barrier',
            ilObjAssessmentFolder::DEFAULT_SKL_TRIG_NUM_ANSWERS_BARRIER
        ));


        $skillEvaluation->init($questionList);
        $skillEvaluation->evaluate($testResults);
        
        $skillEvaluation->handleSkillTriggering();
    }
    
    abstract protected function buildTestPassQuestionList();
    
    protected function showAnswerOptionalQuestionsConfirmation()
    {
        require_once 'Modules/Test/classes/confirmations/class.ilTestAnswerOptionalQuestionsConfirmationGUI.php';
        $confirmation = new ilTestAnswerOptionalQuestionsConfirmationGUI($this->lng);

        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setCancelCmd('cancelAnswerOptionalQuestions');
        $confirmation->setConfirmCmd('confirmAnswerOptionalQuestions');

        $confirmation->build($this->object->isFixedTest());
        
        $this->populateHelperGuiContent($confirmation);
    }
    
    protected function confirmAnswerOptionalQuestionsCmd()
    {
        $this->testSequence->setAnsweringOptionalQuestionsConfirmed(true);
        $this->testSequence->saveToDb();
        
        $this->ctrl->setParameter($this, 'activecommand', 'gotoquestion');
        $this->ctrl->redirect($this, 'redirectQuestion');
    }

    protected function cancelAnswerOptionalQuestionsCmd()
    {
        if ($this->object->getListOfQuestions()) {
            $this->ctrl->setParameter($this, 'activecommand', 'summary');
        } else {
            $this->ctrl->setParameter($this, 'activecommand', 'previous');
        }

        $this->ctrl->redirect($this, 'redirectQuestion');
    }

    /**
     * @param $helperGui
     */
    protected function populateHelperGuiContent($helperGui)
    {
        if ($this->object->getKioskMode()) {
            //$this->tpl->setBodyClass("kiosk");
            $this->tpl->hideFooter();
            $this->tpl->addBlockfile('CONTENT', 'adm_content', "tpl.il_as_tst_kiosk_mode_content.html", "Modules/Test");
            $this->tpl->setContent($this->ctrl->getHTML($helperGui));
        } else {
            $this->tpl->setVariable($this->getContentBlockName(), $this->ctrl->getHTML($helperGui));
        }
    }
    
    /**
     * @return bool $charSelectorAvailable
     */
    protected function populateCharSelectorIfRequired()
    {
        global $DIC;
        $ilSetting = $DIC['ilSetting'];
        
        if ($ilSetting->get('char_selector_availability') > 0) {
            require_once 'Services/UIComponent/CharSelector/classes/class.ilCharSelectorGUI.php';
            $char_selector = ilCharSelectorGUI::_getCurrentGUI($this->object);
            if ($char_selector->getConfig()->getAvailability() == ilCharSelectorConfig::ENABLED) {
                $char_selector->addToPage();
                $this->tpl->setCurrentBlock('char_selector');
                $this->tpl->setVariable("CHAR_SELECTOR_TEMPLATE", $char_selector->getSelectorHtml());
                $this->tpl->parseCurrentBlock();
                
                return true;
            }
        }
        
        return false;
    }
    
    protected function getTestNavigationToolbarGUI()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        
        require_once 'Modules/Test/classes/class.ilTestNavigationToolbarGUI.php';
        $navigationToolbarGUI = new ilTestNavigationToolbarGUI($this->ctrl, $this->lng, $this);
        
        $navigationToolbarGUI->setSuspendTestButtonEnabled($this->object->getShowCancel());
        $navigationToolbarGUI->setQuestionTreeButtonEnabled($this->object->getListOfQuestions());
        $navigationToolbarGUI->setQuestionTreeVisible($ilUser->getPref('side_list_of_questions'));
        $navigationToolbarGUI->setQuestionListButtonEnabled($this->object->getListOfQuestions());
        $navigationToolbarGUI->setFinishTestCommand($this->getFinishTestCommand());
        
        return $navigationToolbarGUI;
    }

    protected function buildReadOnlyStateQuestionNavigationGUI($questionId)
    {
        require_once 'Modules/Test/classes/class.ilTestQuestionNavigationGUI.php';
        $navigationGUI = new ilTestQuestionNavigationGUI($this->lng);
        
        if (!$this->isParticipantsAnswerFixed($questionId)) {
            $navigationGUI->setEditSolutionCommand(ilTestPlayerCommands::EDIT_SOLUTION);
        }

        if ($this->object->getShowMarker()) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $solved_array = ilObjTest::_getSolvedQuestions($this->testSession->getActiveId(), $questionId);
            $solved = 0;

            if (count($solved_array) > 0) {
                $solved = array_pop($solved_array);
                $solved = $solved["solved"];
            }
            // fau: testNav - change question mark command to link target
            if ($solved == 1) {
                $navigationGUI->setQuestionMarkLinkTarget($this->ctrl->getLinkTarget($this, ilTestPlayerCommands::UNMARK_QUESTION));
                $navigationGUI->setQuestionMarked(true);
            } else {
                $navigationGUI->setQuestionMarkLinkTarget($this->ctrl->getLinkTarget($this, ilTestPlayerCommands::MARK_QUESTION));
                $navigationGUI->setQuestionMarked(false);
            }
        }
        // fau.

        return $navigationGUI;
    }
    
    protected function buildEditableStateQuestionNavigationGUI($questionId, $charSelectorAvailable)
    {
        require_once 'Modules/Test/classes/class.ilTestQuestionNavigationGUI.php';
        $navigationGUI = new ilTestQuestionNavigationGUI($this->lng);
        
        if ($this->object->isForceInstantFeedbackEnabled()) {
            $navigationGUI->setSubmitSolutionCommand(ilTestPlayerCommands::SUBMIT_SOLUTION);
        } else {
            // fau: testNav - use simple "submitSolution" button instead of "submitSolutionAndNext"
            $navigationGUI->setSubmitSolutionCommand(ilTestPlayerCommands::SUBMIT_SOLUTION);
            // fau.
        }
        
        // fau: testNav - add a 'revert changes' link for editable question
        $navigationGUI->setRevertChangesLinkTarget($this->ctrl->getLinkTarget($this, ilTestPlayerCommands::REVERT_CHANGES));
        // fau.


        // feedback
        switch (1) {
            case $this->object->getSpecificAnswerFeedback():
            case $this->object->getGenericAnswerFeedback():
            case $this->object->getAnswerFeedbackPoints():
            case $this->object->getInstantFeedbackSolution():

                $navigationGUI->setAnswerFreezingEnabled($this->object->isInstantFeedbackAnswerFixationEnabled());
                
                if ($this->object->isForceInstantFeedbackEnabled()) {
                    $navigationGUI->setForceInstantResponseEnabled(true);
                    $navigationGUI->setInstantFeedbackCommand(ilTestPlayerCommands::SUBMIT_SOLUTION);
                } else {
                    $navigationGUI->setInstantFeedbackCommand(ilTestPlayerCommands::SHOW_INSTANT_RESPONSE);
                }
        }

        // hints
        if ($this->object->isOfferingQuestionHintsEnabled()) {
            $activeId = $this->testSession->getActiveId();
            $pass = $this->testSession->getPass();

            require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintTracking.php';
            $questionHintTracking = new ilAssQuestionHintTracking($questionId, $activeId, $pass);

            if ($questionHintTracking->requestsPossible()) {
                $navigationGUI->setRequestHintCommand(ilTestPlayerCommands::CONFIRM_HINT_REQUEST);
            }

            if ($questionHintTracking->requestsExist()) {
                $navigationGUI->setShowHintsCommand(ilTestPlayerCommands::SHOW_REQUESTED_HINTS_LIST);
            }
        }

        $navigationGUI->setCharSelectorEnabled($charSelectorAvailable);

        if ($this->object->getShowMarker()) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $solved_array = ilObjTest::_getSolvedQuestions($this->testSession->getActiveId(), $questionId);
            $solved = 0;

            if (count($solved_array) > 0) {
                $solved = array_pop($solved_array);
                $solved = $solved["solved"];
            }

            // fau: testNav - change question mark command to link target
            if ($solved == 1) {
                $navigationGUI->setQuestionMarkLinkTarget($this->ctrl->getLinkTarget($this, ilTestPlayerCommands::UNMARK_QUESTION_SAVE));
                $navigationGUI->setQuestionMarked(true);
            } else {
                $navigationGUI->setQuestionMarkLinkTarget($this->ctrl->getLinkTarget($this, ilTestPlayerCommands::MARK_QUESTION_SAVE));
                $navigationGUI->setQuestionMarked(false);
            }
        }
        // fau.
        return $navigationGUI;
    }

    /**
     * @return string
     */
    protected function getFinishTestCommand()
    {
        if (!$this->object->getListOfQuestionsEnd()) {
            return 'finishTest';
        }
        
        if ($this->object->areObligationsEnabled()) {
            $allObligationsAnswered = ilObjTest::allObligationsAnswered(
                $this->testSession->getTestId(),
                $this->testSession->getActiveId(),
                $this->testSession->getPass()
            );
            
            if (!$allObligationsAnswered) {
                return 'outQuestionSummaryWithObligationsInfo';
            }
        }

        return 'outQuestionSummary';
    }

    // fau: testNav - populateIntermediateSolutionSaver is obsolete and can be deletd.
    //	/**
    //	 * @param assQuestionGUI $questionGui
    //	 */
    //	protected function populateIntermediateSolutionSaver(assQuestionGUI $questionGui)
    //	{
    //		$this->tpl->addJavaScript(ilUtil::getJSLocation("autosave.js", "Modules/Test"));
//
    //		$this->tpl->setVariable("AUTOSAVE_URL", $this->ctrl->getFormAction(
    //			$this, ilTestPlayerCommands::AUTO_SAVE, "", true
    //		));
//
    //		if( $questionGui->isAutosaveable() && $this->object->getAutosave() )
    //		{
    //			$formAction = $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::AUTO_SAVE, '', false, false);
//
    //			$this->tpl->touchBlock('autosave');
    //			$this->tpl->setVariable("AUTOSAVEFORMACTION", $formAction);
    //			$this->tpl->setVariable("AUTOSAVEINTERVAL", $this->object->getAutosaveIval());
    //		}
    //	}
    // fau.

    // fau: testNav - new function populateInstantResponseModal()
    protected function populateInstantResponseModal(assQuestionGUI $questionGui, $navUrl)
    {
        $questionGui->setNavigationGUI(null);
        $questionGui->getQuestionHeaderBlockBuilder()->setQuestionAnswered(true);

        $answerFeedbackEnabled = $this->object->getSpecificAnswerFeedback();

        $solutionoutput = $questionGui->getSolutionOutput(
            $this->testSession->getActiveId(), 	#active_id
            $this->testSession->getPass(),		#pass
            false, 								#graphical_output
            false,								#result_output
            true, 								#show_question_only
            $answerFeedbackEnabled,				#show_feedback
            false, 								#show_correct_solution
            false, 								#show_manual_scoring
            true								#show_question_text
        );

        $pageoutput = $questionGui->outQuestionPage(
            "",
            $this->isShowingPostponeStatusReguired($questionGui->object->getId()),
            $this->testSession->getActiveId(),
            $solutionoutput
        );

        $tpl = new ilTemplate('tpl.tst_player_response_modal.html', true, true, 'Modules/Test');

        // populate the instant response blocks in the
        $saved_tpl = $this->tpl;
        $this->tpl = $tpl;
        $this->populateInstantResponseBlocks($questionGui, true);
        $this->tpl = $saved_tpl;

        $tpl->setVariable('QUESTION_OUTPUT', $pageoutput);

        $button = ilLinkButton::getInstance();
        $button->setId('tst_confirm_feedback');
        $button->setUrl($navUrl);
        $button->setCaption('proceed');
        $button->setPrimary(true);
        $tpl->setVariable('BUTTON', $button->render());


        require_once('Services/UIComponent/Modal/classes/class.ilModalGUI.php');
        $modal = ilModalGUI::getInstance();
        $modal->setType(ilModalGUI::TYPE_LARGE);
        $modal->setId('tst_question_feedback_modal');
        $modal->setHeading($this->lng->txt('tst_instant_feedback'));
        $modal->setBody($tpl->get());

        $this->tpl->addOnLoadCode("$('#tst_question_feedback_modal').modal('show');");
        $this->tpl->setVariable('INSTANT_RESPONSE_MODAL', $modal->getHTML());
    }
    // fau;


    /**
     * @param assQuestionGUI $questionGui
     */
    protected function populateInstantResponseBlocks(assQuestionGUI $questionGui, $authorizedSolution)
    {
        $this->populateFeedbackBlockHeader(
            !$this->object->getSpecificAnswerFeedback() || !$questionGui->hasInlineFeedback()
        );
        
        // This controls if the solution should be shown.
        // It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Solutions"
        if ($this->object->getInstantFeedbackSolution()) {
            $show_question_inline_score = $this->determineInlineScoreDisplay();

            // Notation of the params prior to getting rid of this crap in favor of a class
            $solutionoutput = $questionGui->getSolutionOutput(
                $this->testSession->getActiveId(),    #active_id
                null,                                                #pass
                false,                                                #graphical_output
                $show_question_inline_score,                        #result_output
                true,                                                #show_question_only
                false,                                                #show_feedback
                true,                                                #show_correct_solution
                false,                                                #show_manual_scoring
                false                                                #show_question_text
            );
            $solutionoutput = str_replace('<h1 class="ilc_page_title_PageTitle"></h1>', '', $solutionoutput);
            $this->populateSolutionBlock($solutionoutput);
        }
        
        $reachedPoints = $questionGui->object->getAdjustedReachedPoints(
            $this->testSession->getActiveId(),
            null,
            $authorizedSolution
        );
        
        $maxPoints = $questionGui->object->getMaximumPoints();
        
        $solutionCorrect = ($reachedPoints == $maxPoints);
        
        // This controls if the score should be shown.
        // It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Results (Only Points)"
        if ($this->object->getAnswerFeedbackPoints()) {
            $this->populateScoreBlock($reachedPoints, $maxPoints);
        }

        // This controls if the generic feedback should be shown.
        // It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Solutions"
        if ($this->object->getGenericAnswerFeedback()) {
            $this->populateGenericFeedbackBlock($questionGui, $solutionCorrect);
        }

        // This controls if the specific feedback should be shown.
        // It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Answer-Specific Feedback"
        if ($this->object->getSpecificAnswerFeedback()) {
            $this->populateSpecificFeedbackBlock($questionGui);
        }
    }
    
    protected function populateFeedbackBlockHeader($withFocusAnchor)
    {
        if ($withFocusAnchor) {
            $this->tpl->setCurrentBlock('inst_resp_id');
            $this->tpl->setVariable('INSTANT_RESPONSE_FOCUS_ID', 'focus');
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setCurrentBlock('instant_response_header');
        $this->tpl->setVariable('INSTANT_RESPONSE_HEADER', $this->lng->txt('tst_feedback'));
        $this->tpl->parseCurrentBlock();
    }
    
    protected function getCurrentSequenceElement()
    {
        if ($this->getSequenceElementParameter()) {
            return $this->getSequenceElementParameter();
        }

        return $this->testSession->getLastSequence();
    }
    
    protected function resetSequenceElementParameter()
    {
        unset($_GET['sequence']);
        $this->ctrl->setParameter($this, 'sequence', null);
    }

    protected function getSequenceElementParameter()
    {
        if (isset($_GET['sequence'])) {
            return $_GET['sequence'];
        }

        return null;
    }

    protected function getPresentationModeParameter()
    {
        if (isset($_GET['pmode'])) {
            return $_GET['pmode'];
        }

        return null;
    }

    protected function getInstantResponseParameter()
    {
        if (isset($_GET['instresp'])) {
            return $_GET['instresp'];
        }

        return null;
    }
    
    protected function getNextCommandParameter()
    {
        if (isset($_POST['nextcmd']) && strlen($_POST['nextcmd'])) {
            return $_POST['nextcmd'];
        }

        return null;
    }

    protected function getNextSequenceParameter()
    {
        if (isset($_POST['nextseq']) && is_numeric($_POST['nextseq'])) {
            return (int) $_POST['nextseq'];
        }

        return null;
    }

    // fau: testNav - get the navigation url set by a submit from ilTestPlayerNavigationControl.js
    protected function getNavigationUrlParameter()
    {
        if (isset($_POST['test_player_navigation_url'])) {
            return $_POST['test_player_navigation_url'];
        }
        return null;
    }
    // fau.

    // fau: testNav - get set and check the 'answer_changed' url parameter
    /**
     * Get the 'answer changed' status from the current request
     * It may be set by ilTestPlayerNavigationControl.js or by a previousRequest
     * @return bool
     */
    protected function getAnswerChangedParameter()
    {
        return !empty($_GET['test_answer_changed']);
    }

    /**
     * Set the 'answer changed' url parameter for generated links
     * @param bool $changed
     */
    protected function setAnswerChangedParameter($changed = true)
    {
        $this->ctrl->setParameter($this, 'test_answer_changed', $changed ? '1' : '0');
    }


    /**
     * Check the 'answer changed' parameter when a question form is intermediately submitted
     * - save or delete the intermediate solution
     * - save the parameter for the next request
     */
    protected function handleIntermediateSubmit()
    {
        if ($this->getAnswerChangedParameter()) {
            $this->saveQuestionSolution(false);
        } else {
            $this->removeIntermediateSolution();
        }
        $this->setAnswerChangedParameter($this->getAnswerChangedParameter());
    }
    // fau.

    // fau: testNav - save the switch to prevent the navigation confirmation
    /**
     * Save the save the switch to prevent the navigation confirmation
     */
    protected function saveNavigationPreventConfirmation()
    {
        if (!empty($_POST['save_on_navigation_prevent_confirmation'])) {
            $_SESSION['save_on_navigation_prevent_confirmation'] = true;
        }
        
        if (!empty($_POST[self::FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM])) {
            $_SESSION[self::FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM] = true;
        }
    }
    // fau.

    /**
     * @var array[assQuestion]
     */
    private $cachedQuestionGuis = array();

    /**
     * @param $questionId
     * @param $sequenceElement
     * @return object
     */
    protected function getQuestionGuiInstance($questionId, $fromCache = true)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        
        if (!$fromCache || !isset($this->cachedQuestionGuis[$questionId])) {
            $questionGui = $this->object->createQuestionGUI("", $questionId);
            $questionGui->setTargetGui($this);
            $questionGui->setPresentationContext(assQuestionGUI::PRESENTATION_CONTEXT_TEST);
            $questionGui->object->setObligationsToBeConsidered($this->object->areObligationsEnabled());
            $questionGui->populateJavascriptFilesRequiredForWorkForm($tpl);
            $questionGui->object->setOutputType(OUTPUT_JAVASCRIPT);
            $questionGui->object->setShuffler($this->buildQuestionAnswerShuffler($questionId));
            
            // hey: prevPassSolutions - determine solution pass index and configure gui accordingly
            $this->initTestQuestionConfig($questionGui->object);
            // hey.
            
            $this->cachedQuestionGuis[$questionId] = $questionGui;
        }
        
        return $this->cachedQuestionGuis[$questionId];
    }

    /**
     * @var array[assQuestion]
     */
    private $cachedQuestionObjects = array();
    
    /**
     * @param $questionId
     * @return assQuestion
     */
    protected function getQuestionInstance($questionId, $fromCache = true)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        
        if (!$fromCache || !isset($this->cachedQuestionObjects[$questionId])) {
            $questionOBJ = assQuestion::_instantiateQuestion($questionId);

            $assSettings = new ilSetting('assessment');
            require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLockerFactory.php';
            $processLockerFactory = new ilAssQuestionProcessLockerFactory($assSettings, $ilDB);
            $processLockerFactory->setQuestionId($questionOBJ->getId());
            $processLockerFactory->setUserId($ilUser->getId());
            include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
            $processLockerFactory->setAssessmentLogEnabled(ilObjAssessmentFolder::_enabledAssessmentLogging());
            $questionOBJ->setProcessLocker($processLockerFactory->getLocker());

            $questionOBJ->setObligationsToBeConsidered($this->object->areObligationsEnabled());
            $questionOBJ->setOutputType(OUTPUT_JAVASCRIPT);

            // hey: prevPassSolutions - determine solution pass index and configure gui accordingly
            $this->initTestQuestionConfig($questionOBJ);
            // hey.
            
            $this->cachedQuestionObjects[$questionId] = $questionOBJ;
        }
        
        return $this->cachedQuestionObjects[$questionId];
    }

    // hey: prevPassSolutions - determine solution pass index and configure gui accordingly
    protected function initTestQuestionConfig(assQuestion $questionOBJ)
    {
        $questionOBJ->getTestPresentationConfig()->setPreviousPassSolutionReuseAllowed(
            $this->object->isPreviousSolutionReuseEnabled($this->testSession->getActiveId())
        );
    }
    // hey.
    
    /**
     * @param $questionId
     * @return ilArrayElementShuffler
     */
    protected function buildQuestionAnswerShuffler($questionId)
    {
        require_once 'Services/Randomization/classes/class.ilArrayElementShuffler.php';
        $shuffler = new ilArrayElementShuffler();
        
        $fixedSeed = $this->buildFixedShufflerSeed($questionId);
        $shuffler->setSeed($fixedSeed);
        
        return $shuffler;
    }

    /**
     * @param $sequence
     * @param $questionId
     * @param $ilLog
     */
    protected function handleTearsAndAngerQuestionIsNull($questionId, $sequenceElement)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];

        $ilLog->write(
            "INV SEQ:"
            . "active={$this->testSession->getActiveId()} "
            . "qId=$questionId seq=$sequenceElement "
            . serialize($this->testSequence)
        );

        $ilLog->logStack('INV SEQ');

        $this->ctrl->setParameter($this, 'sequence', $this->testSequence->getFirstSequence());
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    /**
     * @param $contentHTML
     */
    protected function populateMessageContent($contentHTML)
    {
        if ($this->object->getKioskMode()) {
            $this->tpl->addBlockfile($this->getContentBlockName(), 'content', "tpl.il_as_tst_kiosk_mode_content.html", "Modules/Test");
            $this->tpl->setContent($contentHTML);
        } else {
            $this->tpl->setVariable($this->getContentBlockName(), $contentHTML);
        }
    }
    
    protected function populateModals()
    {
        require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';
        require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
        require_once 'Services/UIComponent/Modal/classes/class.ilModalGUI.php';

        $this->populateDiscardSolutionModal();
        // fau: testNav - populateNavWhenChangedModal instead of populateNavWhileEditModal
        $this->populateNavWhenChangedModal();
        // fau.

        if ($this->object->isFollowupQuestionAnswerFixationEnabled()) {
            $this->populateNextLocksChangedModal();
            
            $this->populateNextLocksUnchangedModal();
        }
    }
    
    protected function populateDiscardSolutionModal()
    {
        $tpl = new ilTemplate('tpl.tst_player_confirmation_modal.html', true, true, 'Modules/Test');
        
        $tpl->setVariable('CONFIRMATION_TEXT', $this->lng->txt('discard_answer_confirmation'));

        $button = ilSubmitButton::getInstance();
        $button->setCommand(ilTestPlayerCommands::DISCARD_SOLUTION);
        $button->setCaption('discard_answer');
        $tpl->setCurrentBlock('buttons');
        $tpl->setVariable('BUTTON', $button->render());
        $tpl->parseCurrentBlock();

        $button = ilLinkButton::getInstance();
        $button->setId('tst_cancel_discard_button');
        $button->setCaption('cancel');
        $button->setPrimary(true);
        $tpl->setCurrentBlock('buttons');
        $tpl->setVariable('BUTTON', $button->render());
        $tpl->parseCurrentBlock();
        
        $modal = ilModalGUI::getInstance();
        $modal->setId('tst_discard_solution_modal');
        $modal->setHeading($this->lng->txt('discard_answer'));
        $modal->setBody($tpl->get());
        
        $this->tpl->setCurrentBlock('discard_solution_modal');
        $this->tpl->setVariable('DISCARD_SOLUTION_MODAL', $modal->getHTML());
        $this->tpl->parseCurrentBlock();
        
        // fau: testNav - the discard solution modal is now handled by ilTestPlayerNavigationControl.js
//		$this->tpl->addJavaScript('Modules/Test/js/ilTestPlayerDiscardSolutionModal.js', true);
// fau.
    }

    // fau: testNav - populateNavWhileEditModal is obsolete and can be deleted.
    //	protected function populateNavWhileEditModal()
    //	{
    //		require_once 'Services/Form/classes/class.ilFormPropertyGUI.php';
    //		require_once 'Services/Form/classes/class.ilHiddenInputGUI.php';
//
    //		$tpl = new ilTemplate('tpl.tst_player_confirmation_modal.html', true, true, 'Modules/Test');
//
    //		$tpl->setVariable('CONFIRMATION_TEXT', $this->lng->txt('tst_nav_while_edit_modal_text'));
//
    //		$button = ilSubmitButton::getInstance();
    //		$button->setCommand(ilTestPlayerCommands::SUBMIT_SOLUTION);
    //		$button->setCaption('tst_nav_while_edit_modal_save_btn');
    //		$button->setPrimary(true);
    //		$tpl->setCurrentBlock('buttons');
    //		$tpl->setVariable('BUTTON', $button->render());
    //		$tpl->parseCurrentBlock();
//
    //		foreach(array('nextcmd', 'nextseq') as $hiddenPostVar)
    //		{
    //			$nextCmdInp = new ilHiddenInputGUI($hiddenPostVar);
    //			$nextCmdInp->setValue('');
    //			$tpl->setCurrentBlock('hidden_inputs');
    //			$tpl->setVariable('HIDDEN_INPUT', $nextCmdInp->getToolbarHTML());
    //			$tpl->parseCurrentBlock();
    //		}
//
    //		$button = ilLinkButton::getInstance();
    //		$this->ctrl->setParameter($this, 'pmode', self::PRESENTATION_MODE_VIEW);
    //		$button->setId('nextCmdLink');
    //		$button->setUrl('#');
    //		$this->ctrl->setParameter($this, 'pmode', $this->getPresentationModeParameter());
    //		$button->setCaption('tst_nav_while_edit_modal_nosave_btn');
    //		$tpl->setCurrentBlock('buttons');
    //		$tpl->setVariable('BUTTON', $button->render());
    //		$tpl->parseCurrentBlock();
//
    //		$button = ilLinkButton::getInstance();
    //		$button->setId('tst_cancel_nav_while_edit_button');
    //		$button->setCaption('tst_nav_while_edit_modal_cancel_btn');
    //		$tpl->setCurrentBlock('buttons');
    //		$tpl->setVariable('BUTTON', $button->render());
    //		$tpl->parseCurrentBlock();
//
    //		$modal = ilModalGUI::getInstance();
    //		$modal->setId('tst_nav_while_edit_modal');
    //		$modal->setHeading($this->lng->txt('tst_nav_while_edit_modal_header'));
    //		$modal->setBody($tpl->get());
//
    //		$this->tpl->setCurrentBlock('nav_while_edit_modal');
    //		$this->tpl->setVariable('NAV_WHILE_EDIT_MODAL', $modal->getHTML());
    //		$this->tpl->parseCurrentBlock();
//
    //		$this->tpl->addJavaScript('Modules/Test/js/ilTestPlayerNavWhileEditModal.js', true);
    //	}
    // fau.

    // fau: testNav - new function populateNavWhenChangedModal
    protected function populateNavWhenChangedModal()
    {
        return; // usibility fix: get rid of popup
        
        if (!empty($_SESSION['save_on_navigation_prevent_confirmation'])) {
            return;
        }

        $tpl = new ilTemplate('tpl.tst_player_confirmation_modal.html', true, true, 'Modules/Test');

        if ($this->object->isInstantFeedbackAnswerFixationEnabled() && $this->object->isForceInstantFeedbackEnabled()) {
            $text = $this->lng->txt('save_on_navigation_locked_confirmation');
        } else {
            $text = $this->lng->txt('save_on_navigation_confirmation');
        }
        if ($this->object->isForceInstantFeedbackEnabled()) {
            $text .= " " . $this->lng->txt('save_on_navigation_forced_feedback_hint');
        }
        $tpl->setVariable('CONFIRMATION_TEXT', $text);


        $button = ilLinkButton::getInstance();
        $button->setId('tst_save_on_navigation_button');
        $button->setUrl('#');
        $button->setCaption('tst_save_and_proceed');
        $button->setPrimary(true);
        $tpl->setCurrentBlock('buttons');
        $tpl->setVariable('BUTTON', $button->render());
        $tpl->parseCurrentBlock();

        $button = ilLinkButton::getInstance();
        $button->setId('tst_cancel_on_navigation_button');
        $button->setUrl('#');
        $button->setCaption('cancel');
        $button->setPrimary(false);
        $tpl->setCurrentBlock('buttons');
        $tpl->setVariable('BUTTON', $button->render());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock('checkbox');
        $tpl->setVariable('CONFIRMATION_CHECKBOX_NAME', 'save_on_navigation_prevent_confirmation');
        $tpl->setVariable('CONFIRMATION_CHECKBOX_LABEL', $this->lng->txt('tst_dont_show_msg_again_in_current_session'));
        $tpl->parseCurrentBlock();

        $modal = ilModalGUI::getInstance();
        $modal->setId('tst_save_on_navigation_modal');
        $modal->setHeading($this->lng->txt('save_on_navigation'));
        $modal->setBody($tpl->get());

        $this->tpl->setCurrentBlock('nav_while_edit_modal');
        $this->tpl->setVariable('NAV_WHILE_EDIT_MODAL', $modal->getHTML());
        $this->tpl->parseCurrentBlock();
    }
    // fau.
        
    protected function populateNextLocksUnchangedModal()
    {
        require_once 'Modules/Test/classes/class.ilTestPlayerConfirmationModal.php';
        $modal = new ilTestPlayerConfirmationModal();
        $modal->setModalId('tst_next_locks_unchanged_modal');

        $modal->setHeaderText($this->lng->txt('tst_nav_next_locks_empty_answer_header'));
        $modal->setConfirmationText($this->lng->txt('tst_nav_next_locks_empty_answer_confirm'));
        
        $button = $modal->buildModalButtonInstance('tst_nav_next_empty_answer_button');
        $button->setCaption('tst_proceed');
        $button->setPrimary(false);
        $modal->addButton($button);
        
        $button = $modal->buildModalButtonInstance('tst_cancel_next_empty_answer_button');
        $button->setCaption('cancel');
        $button->setPrimary(true);
        $modal->addButton($button);
        
        $this->tpl->setCurrentBlock('next_locks_unchanged_modal');
        $this->tpl->setVariable('NEXT_LOCKS_UNCHANGED_MODAL', $modal->getHTML());
        $this->tpl->parseCurrentBlock();
    }
    
    protected function populateNextLocksChangedModal()
    {
        if ($this->isFollowUpQuestionLocksConfirmationPrevented()) {
            return;
        }
        
        require_once 'Modules/Test/classes/class.ilTestPlayerConfirmationModal.php';
        $modal = new ilTestPlayerConfirmationModal();
        $modal->setModalId('tst_next_locks_changed_modal');
        
        $modal->setHeaderText($this->lng->txt('tst_nav_next_locks_current_answer_header'));
        $modal->setConfirmationText($this->lng->txt('tst_nav_next_locks_current_answer_confirm'));
        
        $modal->setConfirmationCheckboxName(self::FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM);
        $modal->setConfirmationCheckboxLabel($this->lng->txt('tst_dont_show_msg_again_in_current_session'));
        
        $button = $modal->buildModalButtonInstance('tst_nav_next_changed_answer_button');
        $button->setCaption('tst_save_and_proceed');
        $button->setPrimary(true);
        $modal->addButton($button);
        
        $button = $modal->buildModalButtonInstance('tst_cancel_next_changed_answer_button');
        $button->setCaption('cancel');
        $button->setPrimary(false);
        $modal->addButton($button);
        
        $this->tpl->setCurrentBlock('next_locks_changed_modal');
        $this->tpl->setVariable('NEXT_LOCKS_CHANGED_MODAL', $modal->getHTML());
        $this->tpl->parseCurrentBlock();
    }
    
    const FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM = 'followup_qst_locks_prevent_confirmation';
    
    protected function setFollowUpQuestionLocksConfirmationPrevented()
    {
        $_SESSION[self::FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM] = true;
    }
    
    protected function isFollowUpQuestionLocksConfirmationPrevented()
    {
        if (!isset($_SESSION[self::FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM])) {
            return false;
        }
        
        return $_SESSION[self::FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM];
    }
        
    // fau: testNav - new function populateQuestionEditControl
    /**
     * Populate the navigation and saving control for editable questions
     *
     * @param assQuestionGUI 	$questionGUI
     */
    protected function populateQuestionEditControl($questionGUI)
    {
        // configuration for ilTestPlayerQuestionEditControl.js
        $config = array();

        // set the initial state of the question
        $state = $questionGUI->object->lookupForExistingSolutions($this->testSession->getActiveId(), $this->testSession->getPass());
        $config['isAnswered'] = $state['authorized'];
        $config['isAnswerChanged'] = $state['intermediate'] || $this->getAnswerChangedParameter();

        // set  url to which the for should be submitted when the working time is over
        // don't use asynch url because the form is submitted directly
        // but use simple '&' because url is copied by javascript into the form action
        $config['saveOnTimeReachedUrl'] = str_replace('&amp;', '&', $this->ctrl->getFormAction($this, ilTestPlayerCommands::AUTO_SAVE_ON_TIME_LIMIT));

        // enable the auto saving function
        // the autosave url is asynch because it will be used by an ajax request
        if ($questionGUI->isAutosaveable() && $this->object->getAutosave()) {
            $config['autosaveUrl'] = $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::AUTO_SAVE, '', true);
            $config['autosaveInterval'] = $this->object->getAutosaveIval();
        } else {
            $config['autosaveUrl'] = '';
            $config['autosaveInterval'] = 0;
        }

        /** @var  ilTestQuestionConfig $questionConfig */
        // hey: prevPassSolutions - refactored method identifiers
        $questionConfig = $questionGUI->object->getTestPresentationConfig();
        // hey.

        // Normal questions: changes are done in form fields an can be detected there
        $config['withFormChangeDetection'] = $questionConfig->isFormChangeDetectionEnabled();

        // Flash and Java questions: changes are directly sent to ilias and have to be polled from there
        $config['withBackgroundChangeDetection'] = $questionConfig->isBackgroundChangeDetectionEnabled();
        $config['backgroundDetectorUrl'] = $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::DETECT_CHANGES, '', true);

        // Forced feedback will change the navigation saving command
        $config['forcedInstantFeedback'] = $this->object->isForceInstantFeedbackEnabled();
        $config['nextQuestionLocks'] = $this->object->isFollowupQuestionAnswerFixationEnabled();
        
        $this->tpl->addJavascript('./Modules/Test/js/ilTestPlayerQuestionEditControl.js');
        $this->tpl->addOnLoadCode('il.TestPlayerQuestionEditControl.init(' . json_encode($config) . ')');
    }
    // fau.

    protected function getQuestionsDefaultPresentationMode($isQuestionWorkedThrough)
    {
        // fau: testNav - always set default presentation mode to "edit"
        return self::PRESENTATION_MODE_EDIT;
        // fau.
    }
    
    /**
     * @param $questionId
     * @return string
     */
    protected function buildFixedShufflerSeed($questionId)
    {
        $fixedSeed = $questionId . $this->testSession->getActiveId() . $this->testSession->getPass();
        
        if (strlen($fixedSeed < ilTestPlayerAbstractGUI::FIXED_SHUFFLER_SEED_MIN_LENGTH)) {
            $fixedSeed *= (
                10 * (ilTestPlayerAbstractGUI::FIXED_SHUFFLER_SEED_MIN_LENGTH - strlen($fixedSeed))
            );
        }
        
        return $fixedSeed;
    }
    
    protected function registerForcedFeedbackNavUrl($forcedFeedbackNavUrl)
    {
        if (!isset($_SESSION['forced_feedback_navigation_url'])) {
            $_SESSION['forced_feedback_navigation_url'] = array();
        }
        
        $_SESSION['forced_feedback_navigation_url'][$this->testSession->getActiveId()] = $forcedFeedbackNavUrl;
    }
    
    protected function getRegisteredForcedFeedbackNavUrl()
    {
        if (!isset($_SESSION['forced_feedback_navigation_url'])) {
            return null;
        }
        
        if (!isset($_SESSION['forced_feedback_navigation_url'][$this->testSession->getActiveId()])) {
            return null;
        }
        
        return $_SESSION['forced_feedback_navigation_url'][$this->testSession->getActiveId()];
    }
    
    protected function isForcedFeedbackNavUrlRegistered()
    {
        return !empty($this->getRegisteredForcedFeedbackNavUrl());
    }
    
    protected function unregisterForcedFeedbackNavUrl()
    {
        if (isset($_SESSION['forced_feedback_navigation_url'][$this->testSession->getActiveId()])) {
            unset($_SESSION['forced_feedback_navigation_url'][$this->testSession->getActiveId()]);
        }
    }
}
