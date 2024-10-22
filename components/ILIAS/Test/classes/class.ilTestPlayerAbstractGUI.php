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

use ILIAS\Test\Access\ParticipantAccess;
use ILIAS\Test\Logging\TestParticipantInteractionTypes;
use ILIAS\Test\Presentation\TestScreenGUI;
use ILIAS\Test\Results\StatusOfAttempt;
use ILIAS\TestQuestionPool\Questions\QuestionAutosaveable;
use ILIAS\TestQuestionPool\Questions\QuestionPartiallySaveable;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 */
abstract class ilTestPlayerAbstractGUI extends ilTestServiceGUI
{
    public const PRESENTATION_MODE_VIEW = 'view';
    public const PRESENTATION_MODE_EDIT = 'edit';

    protected const FINISH_TEST_CMD = 'finishTest';

    public const FIXED_SHUFFLER_SEED_MIN_LENGTH = 8;

    public bool $max_processing_time_reached;
    public bool $ending_time_reached;
    public int $ref_id;

    /**
     * @var array<assQuestionGUI>
     */
    private array $cached_question_guis = [];

    /**
     * @var array<assQuestion>
     */
    private array $cached_question_objects = [];

    protected ilTestPasswordChecker $password_checker;
    protected ilTestProcessLocker $process_locker;
    protected ?ilTestSession $test_session = null;
    protected ?ilSetting $ass_settings = null;
    protected ?ilTestSequence $test_sequence = null;
    protected ilTestQuestionRelatedObjectivesList $question_related_objectives_list;

    protected ?InterruptiveModal $finish_test_modal = null;

    public function __construct(ilObjTest $object)
    {
        parent::__construct($object);
        $this->ref_id = $this->testrequest->getRefId();
        $this->password_checker = new ilTestPasswordChecker($this->rbac_system, $this->user, $this->object, $this->lng);
    }

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

        $test_sequence_factory = new ilTestSequenceFactory($this->object, $this->db, $this->questionrepository);
        $this->test_sequence = $test_sequence_factory->getSequenceByTestSession($this->test_session);
        $this->test_sequence->loadFromDb();
        $this->test_sequence->loadQuestions();

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

        switch ($next_class) {
            case 'ilassquestionpagegui':
                $this->checkTestExecutable();

                $question_id = $this->test_sequence->getQuestionForSequence($this->getCurrentSequenceElement());

                $page_gui = new ilAssQuestionPageGUI($question_id);
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

                $question_gui = $this->object->createQuestionGUI(
                    "",
                    $this->test_sequence->getQuestionForSequence($this->getCurrentSequenceElement())
                );

                $questionHintTracking = new ilAssQuestionHintTracking(
                    $question_gui->getObject()->getId(),
                    $this->test_session->getActiveId(),
                    $this->test_session->getPass()
                );

                $gui = new ilAssQuestionHintRequestGUI(
                    $this,
                    ilTestPlayerCommands::SHOW_QUESTION,
                    $question_gui,
                    $questionHintTracking,
                    $this->ctrl,
                    $this->lng,
                    $this->tpl,
                    $this->tabs,
                    $this->global_screen
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
                    $this->password_checker,
                    $this->testrequest,
                    $this->global_screen
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
                        $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
                    }
                }

                $cmd .= 'Cmd';
                $ret = $this->$cmd();
                break;
        }
        return $ret;
    }

    abstract protected function buildTestPassQuestionList();
    abstract protected function populateQuestionOptionalMessage();

    protected function checkReadAccess()
    {
        if (!$this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt('cannot_execute_test'), $this->ilias->error_obj->MESSAGE);
        }

        $participant_access = (new ilTestAccess($this->object->getRefId()))->isParticipantAllowed(
            $this->object->getId(),
            $this->user->getId()
        );
        if ($participant_access !== ParticipantAccess::ALLOWED) {
            $this->ilias->raiseError($this->lng->txt('user_wrong_clientip'), $this->ilias->error_obj->MESSAGE);
        }
    }

    protected function checkTestExecutable()
    {
        $executable = $this->object->isExecutable($this->test_session, $this->test_session->getUserId());

        if (!$executable['executable']) {
            $this->tpl->setOnScreenMessage('info', $executable['errormessage'], true);
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
        }
    }

    protected function checkTestSessionUser(ilTestSession $test_session): void
    {
        if ($test_session->getUserId() != $this->user->getId()) {
            throw new ilTestException('active id given does not relate to current user!');
        }
    }

    protected function ensureExistingTestSession(ilTestSession $test_session): void
    {
        if ($test_session->getActiveId()) {
            return;
        }

        $test_session->setUserId($this->user->getId());

        if ($test_session->isAnonymousUser()) {
            if (!$test_session->doesAccessCodeInSessionExists()) {
                return;
            }

            $test_session->setAnonymousId($test_session->getAccessCodeFromSession());
        }

        $test_session->saveToDb();
    }

    protected function initProcessLocker($activeId)
    {
        $ilDB = $this->db;
        $process_lockerFactory = new ilTestProcessLockerFactory($this->ass_settings, $ilDB);
        $this->process_locker = $process_lockerFactory->withContextId((int) $activeId)->getLocker();
    }

    /**
     * Save tags for tagging gui
     *
     * Needed this function here because the test info page
     * uses another class to send its form results
     */
    public function saveTagsCmd()
    {
        $tagging_gui = new ilTaggingGUI();
        $tagging_gui->setObject($this->object->getId(), $this->object->getType());
        $tagging_gui->saveInput();
        $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
    }

    /**
     * updates working time and stores state saveresult to see if question has to be stored or not
     */
    public function updateWorkingTime()
    {
        if (ilSession::get("active_time_id") != null) {
            $this->object->updateWorkingTime(ilSession::get("active_time_id"));
        }

        ilSession::set(
            "active_time_id",
            $this->object->startWorkingTime(
                $this->test_session->getActiveId(),
                $this->test_session->getPass()
            )
        );
    }

    public function removeIntermediateSolution(): void
    {
        $question_id = $this->getCurrentQuestionId();

        $this->getQuestionInstance($question_id)->removeIntermediateSolution(
            $this->test_session->getActiveId(),
            $this->test_session->getPass()
        );
    }

    public function saveQuestionSolution(
        bool $authorized = true,
        bool $force = false
    ): bool {
        $this->updateWorkingTime();

        $formtimestamp = $this->testrequest->int('formtimestamp');
        if (!$force
            && ilSession::get('formtimestamp') !== null
            && $formtimestamp === ilSession::get('formtimestamp')) {
            return false;
        }

        ilSession::set('formtimestamp', $formtimestamp);

        /*
            #21097 - exceed maximum passes
            this is a battle of conditions; e.g. ilTestPlayerAbstractGUI::autosaveOnTimeLimitCmd forces saving of results.
            However, if an admin has finished the pass in the meantime, a new pass should not be created.
        */
        if ($force && $this->isNrOfTriesReached()) {
            $force = false;
        }

        $question_obj = $this->buildQuestionObject();

        if ($question_obj === null) {
            return false;
        }

        if ($this->canSaveResult() || $force) {
            $saved = $this->save($question_obj, $authorized);
        }

        if (!$saved
            || ($question_obj instanceof QuestionPartiallySaveable
                && !$question_obj->validateSolutionSubmit())) {
            $this->ctrl->setParameter($this, 'save_error', '1');
            ilSession::set('previouspost', $_POST);
        }

        return $saved;
    }

    private function buildQuestionObject(): ?assQuestion
    {
        $q_id = $this->test_sequence->getQuestionForSequence($this->testrequest->int('sequence'));

        if ($this->isParticipantsAnswerFixed($q_id)) {
            // should only be reached by firebugging the disabled form in ui
            throw new ilTestException('not allowed request');
        }

        if ($q_id === null) {
            return null;
        }

        return $this->getQuestionInstance($q_id);
    }

    private function save(assQuestion $question_obj, bool $authorized): bool
    {
        $active_id = $this->test_session->getActiveId();
        $pass = ilObjTest::_getPass($active_id);
        if (!$question_obj->persistWorkingState(
            $active_id,
            $pass,
            $authorized
        )) {
            return false;
        }

        if ($authorized && $this->test_session->isObjectiveOriented()) {
            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($this->test_session);
            $objectivesAdapter->updateQuestionResult($this->test_session, $question_obj);
        }

        if ($authorized && $this->object->isSkillServiceToBeConsidered()) {
            $this->handleSkillTriggering($this->test_session);
        }

        if ($this->logger->isLoggingEnabled()
            && !$this->getObject()->getAnonymity()
            && ($interaction = $question_obj->answerToParticipantInteraction(
                $this->logger->getAdditionalInformationGenerator(),
                $this->getObject()->getRefId(),
                $active_id,
                $pass,
                $this->logger->isIPLoggingEnabled() ? $_SERVER['REMOTE_ADDR'] : '',
                TestParticipantInteractionTypes::ANSWER_SUBMITTED
            )) !== null) {
            $this->logger->logParticipantInteraction($interaction);
        }
        return true;
    }

    protected function canSaveResult(): bool
    {
        return !$this->object->endingTimeReached() && !$this->isMaxProcessingTimeReached() && !$this->isNrOfTriesReached();
    }

    public function suspendTestCmd()
    {
        $this->ctrl->redirectByClass(TestScreenGUI::class, TestScreenGUI::DEFAULT_CMD);
    }

    public function isMaxProcessingTimeReached(): bool
    {
        $active_id = $this->test_session->getActiveId();
        $starting_time = $this->object->getStartingTimeOfUser($active_id);
        if ($starting_time === false) {
            return false;
        } else {
            return $this->object->isMaxProcessingTimeReached($starting_time, $active_id);
        }
    }

    protected function determineInlineScoreDisplay(): bool
    {
        $show_question_inline_score = false;
        if ($this->object->getAnswerFeedbackPoints()) {
            $show_question_inline_score = true;
            return $show_question_inline_score;
        }
        return $show_question_inline_score;
    }

    protected function populateTestNavigationToolbar(ilTestNavigationToolbarGUI $toolbar_gui): void
    {
        $this->tpl->setCurrentBlock('test_nav_toolbar');
        $this->tpl->setVariable('TEST_NAV_TOOLBAR', $toolbar_gui->getHTML());
        $this->tpl->parseCurrentBlock();

        if ($this->finish_test_modal === null) {
            return;
        }

        $this->tpl->setCurrentBlock('finish_test_modal');
        $this->tpl->setVariable(
            'FINISH_TEST_MODAL',
            $this->ui_renderer->render(
                $this->finish_test_modal->withOnLoad($this->finish_test_modal->getShowSignal())
            )
        );
        $this->tpl->parseCurrentBlock();
    }

    protected function populateQuestionNavigation($sequence_element, $primary_next): void
    {
        if (!$this->isFirstQuestionInSequence($sequence_element)) {
            $this->populatePreviousButtons();
        }

        if (!$this->isLastQuestionInSequence($sequence_element)) {
            $this->populateNextButtons($primary_next);
        }
    }

    protected function populatePreviousButtons(): void
    {
        $this->populateUpperPreviousButtonBlock();
        $this->populateLowerPreviousButtonBlock();
    }

    protected function populateNextButtons($primary_next): void
    {
        $this->populateUpperNextButtonBlock($primary_next);
        $this->populateLowerNextButtonBlock($primary_next);
    }

    protected function populateLowerNextButtonBlock($primary_next): void
    {
        $button = $this->buildNextButtonInstance($primary_next);

        $this->tpl->setCurrentBlock("next_bottom");
        $this->tpl->setVariable("BTN_NEXT_BOTTOM", $this->ui_renderer->render($button));
        $this->tpl->parseCurrentBlock();
    }

    protected function populateUpperNextButtonBlock($primaryNext)
    {
        $button = $this->buildNextButtonInstance($primaryNext);

        $this->tpl->setCurrentBlock("next");
        $this->tpl->setVariable("BTN_NEXT", $this->ui_renderer->render($button));
        $this->tpl->parseCurrentBlock();
    }

    protected function populateLowerPreviousButtonBlock()
    {
        $button = $this->buildPreviousButtonInstance();

        $this->tpl->setCurrentBlock("prev_bottom");
        $this->tpl->setVariable("BTN_PREV_BOTTOM", $this->ui_renderer->render($button));
        $this->tpl->parseCurrentBlock();
    }

    protected function populateUpperPreviousButtonBlock()
    {
        $button = $this->buildPreviousButtonInstance();

        $this->tpl->setCurrentBlock("prev");
        $this->tpl->setVariable("BTN_PREV", $this->ui_renderer->render($button));
        $this->tpl->parseCurrentBlock();
    }

    /**
     * @param bool $primaryNext
     * @return \ILIAS\UI\Component\Button\Primary
     */
    private function buildNextButtonInstance($primaryNext)
    {
        $target = $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::NEXT_QUESTION);
        if ($primaryNext) {
            $button = $this->ui_factory->button()->primary(
                $this->lng->txt('next_question') . '<span class="glyphicon glyphicon-arrow-right"></span> ',
                ''
            )->withOnLoadCode($this->getOnLoadCodeForNavigationButtons($target, ilTestPlayerCommands::NEXT_QUESTION));
        } else {
            $button = $this->ui_factory->button()->standard(
                $this->lng->txt('next_question') . '<span class="glyphicon glyphicon-arrow-right"></span> ',
                ''
            )->withOnLoadCode($this->getOnLoadCodeForNavigationButtons($target, ilTestPlayerCommands::NEXT_QUESTION));
        }
        return $button;
    }

    /**
     * @param $disabled
     * @return \ILIAS\UI\Component\Button\Primary
     */
    private function buildPreviousButtonInstance()
    {
        $target = $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::PREVIOUS_QUESTION);
        $button = $this->ui_factory->button()->standard(
            '<span class="glyphicon glyphicon-arrow-left"></span> ' . $this->lng->txt('previous_question'),
            ''
        )->withOnLoadCode($this->getOnLoadCodeForNavigationButtons($target, ilTestPlayerCommands::PREVIOUS_QUESTION));
        return $button;
    }

    private function getOnLoadCodeForNavigationButtons(string $target, string $cmd): Closure
    {
        return static function (string $id) use ($target, $cmd): string {
            return "document.getElementById('{$id}').addEventListener('click', "
                . "(e) => {il.TestPlayerQuestionEditControl.checkNavigation('{$target}', '{$cmd}', e);}"
                . ");";
        };
    }

    /**
     * @return bool     true, if there is some feedback populated
     */
    protected function populateSpecificFeedbackBlock(assQuestionGUI $question_gui): bool
    {
        $solutionValues = $question_gui->getObject()->getSolutionValues(
            $this->test_session->getActiveId(),
            null
        );

        $feedback = $question_gui->getSpecificFeedbackOutput(
            $question_gui->getObject()->fetchIndexedValuesFromValuePairs($solutionValues)
        );

        if (!empty($feedback)) {
            $this->tpl->setCurrentBlock("specific_feedback");
            $this->tpl->setVariable("SPECIFIC_FEEDBACK", $feedback);
            $this->tpl->parseCurrentBlock();
            return true;
        }
        return false;
    }

    /**
     * @return bool     true, if there is some feedback populated
     */
    protected function populateGenericFeedbackBlock(assQuestionGUI $question_gui, $solutionCorrect): bool
    {
        // fix #031263: add pass
        $feedback = $question_gui->getGenericFeedbackOutput($this->test_session->getActiveId(), $this->test_session->getPass());

        if (strlen($feedback)) {
            $cssClass = (
                $solutionCorrect ?
                ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT : ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_WRONG
            );

            $this->tpl->setCurrentBlock("answer_feedback");
            $this->tpl->setVariable("ANSWER_FEEDBACK", $feedback);
            $this->tpl->setVariable("ILC_FB_CSS_CLASS", $cssClass);
            $this->tpl->parseCurrentBlock();
            return true;
        }
        return false;
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
        if ($this->test_session->isAnonymousUser()) {
            $this->test_session->setAccessCodeToSession($_POST['anonymous_id']);
        }

        $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
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

        $this->process_locker->executeTestStartLockOperation(function () use ($testStartLock, &$isFirstTestStartRequest) {
            if ($this->test_session->lookupTestStartLock() !== $testStartLock) {
                $this->test_session->persistTestStartLock($testStartLock);
                $isFirstTestStartRequest = true;
            }
        });

        if ($isFirstTestStartRequest) {
            $this->handleUserSettings();
            $this->ctrl->redirect($this, ilTestPlayerCommands::INIT_TEST);
        }

        $this->ctrl->setParameterByClass('ilObjTestGUI', 'lock', $testStartLock);
        $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
    }

    public function getLockParameter()
    {
        if ($this->testrequest->isset('lock') && strlen($this->testrequest->raw('lock'))) {
            return $this->testrequest->raw('lock');
        }

        return null;
    }

    protected function resumePlayerCmd()
    {
        $this->handleUserSettings();

        $active_id = $this->test_session->getActiveId();
        $this->ctrl->setParameter($this, "active_id", $active_id);

        $active_time_id = $this->object->startWorkingTime($active_id, $this->test_session->getPass());
        ilSession::set("active_time_id", $active_time_id);
        ilSession::set('tst_pass_finish', 0);

        if ($this->object->isRandomTest()) {
            if (!$this->test_sequence->hasRandomQuestionsForPass($active_id, $this->test_session->getPass())) {
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
            null,
            $this->object->getId()
        );

        // ensure existing test sequence
        if (!$this->test_sequence->hasSequence()) {
            $this->test_sequence->createNewSequence($this->object->getQuestionCount(), $shuffle);
            $this->test_sequence->saveToDb();
        }

        if ($this->object->getListOfQuestionsStart()) {
            $this->ctrl->redirect($this, ilTestPlayerCommands::QUESTION_SUMMARY);
        }

        $this->ctrl->setParameter($this, 'sequence', $this->test_session->getLastSequence());
        $this->ctrl->setParameter($this, 'pmode', '');
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    /**
     * Start a test for the first time after a redirect
     */
    protected function initTestCmd()
    {
        if ($this->test_session->isAnonymousUser()
            && !$this->test_session->doesAccessCodeInSessionExists()) {
            $access_code = $this->test_session->createNewAccessCode();

            $this->test_session->setAccessCodeToSession($access_code);
            $this->test_session->setAnonymousId($access_code);
            $this->test_session->saveToDb();

            $this->ctrl->redirect($this, ilTestPlayerCommands::DISPLAY_ACCESS_CODE);
        }

        if (!$this->test_session->isAnonymousUser()) {
            $this->test_session->unsetAccessCodeInSession();
        }
        $this->ctrl->redirect($this, ilTestPlayerCommands::START_TEST);
    }

    public function displayAccessCodeCmd()
    {
        $this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_anonymous_code_presentation.html", "components/ILIAS/Test");
        $this->tpl->setCurrentBlock("adm_content");
        $this->tpl->setVariable("TEXT_ANONYMOUS_CODE_CREATED", $this->lng->txt("tst_access_code_created"));
        $this->tpl->setVariable("TEXT_ANONYMOUS_CODE", $this->test_session->getAccessCodeFromSession());
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
        if ($this->object->getNrOfTries() != 1
            && $this->object->getUsePreviousAnswers() == 1
        ) {
            $chb_use_previous_answers = 0;
            if ($this->post_wrapper->has('chb_use_previous_answers')) {
                $chb_use_previous_answers = $this->post_wrapper->retrieve(
                    'chb_use_previous_answers',
                    $this->refinery->kindlyTo()->int()
                );
            }
            $this->user->writePref("tst_use_previous_answers", (string) $chb_use_previous_answers);
        }
    }

    /**
     * Redirect the user after an automatic save when the time limit is reached
     * @throws ilTestException
     */
    public function redirectAfterAutosaveCmd(): void
    {
        $this->performTestPassFinishedTasks(StatusOfAttempt::FINISHED_BY_DURATION);

        $this->redirectAfterFinish();
    }

    public function redirectAfterDashboardCmd(): void
    {
        $this->performTestPassFinishedTasks(StatusOfAttempt::FINISHED_BY_PARTICIPANT);

        $this->redirectAfterFinish();
    }

    protected function redirectAfterFinish(): void
    {
        $url = $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::AFTER_TEST_PASS_FINISHED, '', false, false);

        $this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_redirect_autosave.html", "components/ILIAS/Test");
        $this->tpl->setVariable("TEXT_REDIRECT", $this->lng->txt("redirectAfterSave"));
        $this->tpl->setVariable("URL", $url);
    }

    protected function getCurrentQuestionId(): int
    {
        return $this->test_sequence->getQuestionForSequence($this->testrequest->int('sequence'));
    }

    /**
     * Automatically save a user answer while working on the test
     * (called repeatedly by asynchronous posts in configured autosave interval)
     */
    public function autosaveCmd(): void
    {
        if (!is_countable($_POST) || count($_POST) === 0) {
            echo '';
            exit;
        }

        if (!$this->canSaveResult() || $this->isParticipantsAnswerFixed($this->getCurrentQuestionId())) {
            echo '-IGNORE-';
            exit;
        }

        $authorize = !$this->getAnswerChangedParameter();
        $res = $this->saveQuestionSolution($authorize, true);

        if ($res) {
            echo $this->lng->txt("autosave_success");
            exit;
        }

        echo $this->lng->txt("autosave_failed");
        exit;
    }

    /**
     * Automatically save a user answer when the limited duration of a test run is reached
     * (called by synchronous form submit when the remaining time count down reaches zero)
     */
    public function autosaveOnTimeLimitCmd()
    {
        if (!$this->isParticipantsAnswerFixed($this->getCurrentQuestionId())) {
            $this->saveQuestionSolution(false, true);
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
        $question_id = $this->getCurrentQuestionId();
        $state = $this->getQuestionInstance($question_id)->lookupForExistingSolutions(
            $this->test_session->getActiveId(),
            $this->test_session->getPass()
        );
        $result = [];
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

    protected function markQuestionAndSaveIntermediateCmd(): void
    {
        $this->handleIntermediateSubmit();
        $this->markQuestionCmd();
    }

    /**
     * Set a question solved
     */
    protected function markQuestionCmd(): void
    {
        $question_id = $this->test_sequence->getQuestionForSequence(
            $this->getCurrentSequenceElement()
        );

        $this->object->setQuestionSetSolved(1, $question_id, $this->test_session->getUserId());

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
        $question_id = $this->test_sequence->getQuestionForSequence(
            $this->getCurrentSequenceElement()
        );

        $this->object->setQuestionSetSolved(0, $question_id, $this->test_session->getUserId());

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    public function finishTestCmd()
    {
        $this->handleCheckTestPassValid();
        ilSession::clear("tst_next");

        if ($this->testrequest->strVal('finalization_confirmed') !== 'confirmed') {
            $this->finish_test_modal = $this->buildFinishTestModal();
            $this->showQuestionCmd();
            return;
        }

        // Non-last try finish
        if (ilSession::get('tst_pass_finish') === null) {
            ilSession::set('tst_pass_finish', 1);
        }

        $this->sendNewPassFinishedNotificationEmailIfActivated(
            $this->test_session->getActiveId(),
            $this->test_session->getPass()
        );

        $this->performTestPassFinishedTasks(StatusOfAttempt::FINISHED_BY_PARTICIPANT);

        if ($this->logger->isLoggingEnabled()
            && !$this->getObject()->getAnonymity()
            && ($interaction = $this->logger->getInteractionFactory()->buildParticipantInteraction(
                $this->ref_id,
                null,
                $this->user->getId(),
                $this->logger->isIPLoggingEnabled() ? $_SERVER['REMOTE_ADDR'] : '',
                TestParticipantInteractionTypes::TEST_RUN_FINISHED,
                []
            )) !== null) {
            $this->logger->logParticipantInteraction($interaction);
        }

        $this->ctrl->redirect($this, ilTestPlayerCommands::AFTER_TEST_PASS_FINISHED);
    }

    protected function performTestPassFinishedTasks(StatusOfAttempt $status_of_attempt): void
    {
        $finishTasks = new ilTestPassFinishTasks(
            $this->test_session,
            $this->object->getId(),
            $this->test_pass_result_repository
        );
        $finishTasks->performFinishTasks($this->process_locker, $status_of_attempt);
    }

    protected function sendNewPassFinishedNotificationEmailIfActivated(int $active_id, int $pass)
    {
        $notification_type = $this->object->getMainSettings()->getFinishingSettings()->getMailNotificationContentType();

        if ($notification_type === 0
            || !$this->object->getMainSettings()->getFinishingSettings()->getAlwaysSendMailNotification()
                && $pass !== $this->object->getNrOfTries() - 1) {
            return;
        }

        switch ($this->object->getMainSettings()->getFinishingSettings()->getMailNotificationContentType()) {
            case 1:
                $this->object->sendSimpleNotification($active_id);
                break;
            case 2:
                $this->object->sendAdvancedNotification($active_id);
                break;
        }
    }

    protected function afterTestPassFinishedCmd()
    {
        // show final statement
        if (!$this->testrequest->isset('skipfinalstatement')) {
            if ($this->object->getMainSettings()->getFinishingSettings()->getConcludingRemarksEnabled()) {
                $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_FINAL_STATMENT);
            }
        }

        // redirect after test
        $redirection_mode = $this->object->getRedirectionMode();
        $redirection_url = $this->object->getRedirectionUrl();
        if ($redirection_url !== '' && $redirection_mode !== '0') {
            if ($this->object->isRedirectModeKiosk()) {
                if ($this->object->getKioskMode()) {
                    ilUtil::redirect($redirection_url);
                }
            } else {
                ilUtil::redirect($redirection_url);
            }
        }

        // default redirect (pass overview when enabled, otherwise testscreen)
        $this->redirectBackCmd();
    }

    public function buildFinishTestModal(): InterruptiveModal
    {
        $class = get_class($this);
        $this->ctrl->setParameterByClass($class, 'finalization_confirmed', 'confirmed');
        $next_url = $this->ctrl->getLinkTargetByClass($class, ilTestPlayerCommands::FINISH_TEST);
        $this->ctrl->clearParameterByClass($class, 'finalization_confirmed');

        $message = $this->lng->txt('tst_finish_confirmation_question');
        if (($this->object->getNrOfTries() - 1) === $this->test_session->getPass()) {
            $message = $this->lng->txt('tst_finish_confirmation_question_no_attempts_left');
        }

        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('finish_test'),
            $message,
            $next_url
        )->withActionButtonLabel($this->lng->txt('tst_finish_confirm_button'));
    }

    public function redirectBackCmd(): void
    {
        $testPassesSelector = new ilTestPassesSelector($this->db, $this->object);
        $testPassesSelector->setActiveId($this->test_session->getActiveId());
        $testPassesSelector->setLastFinishedPass($this->test_session->getLastFinishedPass());

        if (count($testPassesSelector->getReportablePasses())) {
            if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
                $this->ctrl->redirectByClass(['ilTestResultsGUI', 'ilTestEvalObjectiveOrientedGUI']);
            }

            $this->ctrl->redirectByClass([ilTestResultsGUI::class, ilMyTestResultsGUI::class, ilTestEvaluationGUI::class]);
        }

        $this->ctrl->redirectByClass(TestScreenGUI::class, TestScreenGUI::DEFAULT_CMD);
    }

    /*
    * Presents the final statement of a test
    */
    public function showFinalStatementCmd()
    {
        $this->global_screen->tool()->context()->current()->getAdditionalData()->replace(
            ilTestPlayerLayoutProvider::TEST_PLAYER_VIEW_TITLE,
            $this->object->getTitle() . ' - ' . $this->lng->txt('final_statement')
        );

        $template = new ilTemplate('tpl.il_as_tst_final_statement.html', true, true, 'components/ILIAS/Test');
        $this->ctrl->setParameter($this, 'skipfinalstatement', 1);
        $template->setVariable('FORMACTION', $this->ctrl->getFormAction($this, ilTestPlayerCommands::AFTER_TEST_PASS_FINISHED));
        $template->setVariable('FINALSTATEMENT', $this->object->prepareTextareaOutput($this->object->getFinalStatement(), true));
        $template->setVariable('BUTTON_CONTINUE', $this->lng->txt('btn_next'));
        $this->tpl->setVariable($this->getContentBlockName(), $template->get());
    }

    protected function prepareTestPage($presentationMode, $sequenceElement, $question_id)
    {
        $this->navigation_history->addItem(
            $this->test_session->getRefId(),
            $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::RESUME_PLAYER),
            'tst'
        );

        $this->initTestPageTemplate();
        $this->populateContentStyleBlock();
        $this->populateSyntaxStyleBlock();

        if ($this->isMaxProcessingTimeReached()) {
            $this->max_processing_time_reached();
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

        $this->tpl->setVariable("TEST_ID", (string) $this->object->getTestId());
        $this->tpl->setVariable("LOGIN", $this->user->getLogin());

        $this->tpl->setVariable("SEQ_ID", $sequenceElement);
        $this->tpl->setVariable("QUEST_ID", $question_id);

        if ($this->object->getEnableProcessingTime()) {
            $this->outProcessingTime($this->test_session->getActiveId());
        }

        $this->tpl->setVariable("PAGETITLE", "- " . $this->object->getTitle());

        if ($this->object->isShowExamIdInTestPassEnabled() && !$this->object->getKioskMode()) {
            $this->tpl->setCurrentBlock('exam_id_footer');
            $this->tpl->setVariable('EXAM_ID_VAL', ilObjTest::lookupExamId(
                $this->test_session->getActiveId(),
                $this->test_session->getPass(),
                $this->object->getId()
            ));
            $this->tpl->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->object->getListOfQuestions()) {
            $this->showSideList($sequenceElement);
        }
    }

    protected function isOptionalQuestionAnsweringConfirmationRequired(int $sequence_key): bool
    {
        if ($this->test_sequence->isAnsweringOptionalQuestionsConfirmed()) {
            return false;
        }

        $question_id = $this->test_sequence->getQuestionForSequence($sequence_key);

        if (!$this->test_sequence->isQuestionOptional($question_id)) {
            return false;
        }

        return true;
    }

    protected function isShowingPostponeStatusReguired(int $question_id): bool
    {
        return $this->test_sequence->isPostponedQuestion($question_id);
    }

    protected function showQuestionViewable(assQuestionGUI $question_gui, $formAction, $isQuestionWorkedThrough, $instantResponse)
    {
        $questionNavigationGUI = $this->buildReadOnlyStateQuestionNavigationGUI($question_gui->getObject()->getId());
        $questionNavigationGUI->setQuestionWorkedThrough($isQuestionWorkedThrough);
        $question_gui->setNavigationGUI($questionNavigationGUI);

        // fau: testNav - set answere status in question header
        $question_gui->getQuestionHeaderBlockBuilder()->setQuestionAnswered($isQuestionWorkedThrough);
        // fau.

        $answerFeedbackEnabled = (
            $instantResponse && $this->object->getSpecificAnswerFeedback()
        );

        $solutionoutput = $question_gui->getSolutionOutput(
            $this->test_session->getActiveId(), 	#active_id
            $this->test_session->getPass(),		#pass
            false, 								#graphical_output
            false,								#result_output
            true, 								#show_question_only
            $answerFeedbackEnabled,				#show_feedback
            false, 								#show_correct_solution
            false, 								#show_manual_scoring
            true								#show_question_text
        );

        $pageoutput = $question_gui->outQuestionPage(
            "",
            $this->isShowingPostponeStatusReguired($question_gui->getObject()->getId()),
            $this->test_session->getActiveId(),
            $solutionoutput
        );

        $this->tpl->setVariable(
            'LOCKSTATE_INFOBOX',
            $this->ui_renderer->render(
                $this->ui_factory->messageBox()->info($this->lng->txt("tst_player_answer_saved_and_locked"))
            )
        );
        $this->tpl->parseCurrentBlock();

        $this->tpl->setVariable('QUESTION_OUTPUT', $pageoutput);

        $this->tpl->setVariable("FORMACTION", $formAction);
        $this->tpl->setVariable("ENCTYPE", 'enctype="' . $question_gui->getFormEncodingType() . '"');
        $this->tpl->setVariable("FORM_TIMESTAMP", time());
        $this->populateQuestionEditControl($question_gui);
    }

    protected function showQuestionEditable(assQuestionGUI $question_gui, $formAction, $isQuestionWorkedThrough, $instantResponse)
    {
        $questionNavigationGUI = $this->buildEditableStateQuestionNavigationGUI($question_gui->getObject()->getId());
        if ($isQuestionWorkedThrough) {
            $questionNavigationGUI->setDiscardSolutionButtonEnabled(true);
            // fau: testNav - set answere status in question header
            $question_gui->getQuestionHeaderBlockBuilder()->setQuestionAnswered(true);
            // fau.
        } elseif ($this->object->isPostponingEnabled()) {
            $questionNavigationGUI->setSkipQuestionLinkTarget(
                $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::SKIP_QUESTION)
            );
        }
        $question_gui->setNavigationGUI($questionNavigationGUI);

        $isPostponed = $this->isShowingPostponeStatusReguired($question_gui->getObject()->getId());

        $answerFeedbackEnabled = (
            $instantResponse && $this->object->getSpecificAnswerFeedback()
        );

        if ($this->testrequest->isset('save_error') && $this->testrequest->raw('save_error') == 1 && ilSession::get('previouspost') != null) {
            $userPostSolution = ilSession::get('previouspost');
            ilSession::clear('previouspost');
        } else {
            $userPostSolution = false;
        }

        // fau: testNav - add special checkbox for mc question
        // moved to another patch block
        // fau.

        // hey: prevPassSolutions - determine solution pass index and configure gui accordingly
        $qstConfig = $question_gui->getObject()->getTestPresentationConfig();

        if ($question_gui instanceof assMultipleChoiceGUI) {
            $qstConfig->setWorkedThrough($isQuestionWorkedThrough);
        }

        if ($qstConfig->isPreviousPassSolutionReuseAllowed()) {
            $passIndex = $this->determineSolutionPassIndex($question_gui); // last pass having solution stored
            if ($passIndex < $this->test_session->getPass()) { // it's the previous pass if current pass is higher
                $qstConfig->setSolutionInitiallyPrefilled(true);
            }
        } else {
            $passIndex = $this->test_session->getPass();
        }
        // hey.

        // Answer specific feedback is rendered into the display of the test question with in the concrete question types outQuestionForTest-method.
        // Notation of the params prior to getting rid of this crap in favor of a class
        $question_gui->outQuestionForTest(
            $formAction,
            $this->test_session->getActiveId(),
            $passIndex,
            $isPostponed,
            $userPostSolution,
            $answerFeedbackEnabled
        );

        $this->populateModals();

        // fau: testNav - pouplate the new question edit control instead of the deprecated intermediate solution saver
        $this->populateQuestionEditControl($question_gui);
        // fau.
    }

    // hey: prevPassSolutions - determine solution pass index
    protected function determineSolutionPassIndex(assQuestionGUI $question_gui): int
    {
        if ($this->object->isPreviousSolutionReuseEnabled($this->test_session->getActiveId())) {
            $currentSolutionAvailable = $question_gui->getObject()->authorizedOrIntermediateSolutionExists(
                $this->test_session->getActiveId(),
                $this->test_session->getPass()
            );

            if (!$currentSolutionAvailable) {
                $previousPass = $question_gui->getObject()->getSolutionMaxPass(
                    $this->test_session->getActiveId()
                );

                $previousSolutionAvailable = $question_gui->getObject()->authorizedSolutionExists(
                    $this->test_session->getActiveId(),
                    $previousPass
                );

                if ($previousSolutionAvailable) {
                    return $previousPass;
                }

            }
        }

        return $this->test_session->getPass();
    }
    // hey.

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
            $sequence_element = $this->test_sequence->getFirstSequence();
        }

        $this->test_session->setLastSequence($sequence_element ?? 0);
        $this->test_session->saveToDb();

        $question_id = $this->test_sequence->getQuestionForSequence($sequence_element ?? 0);

        if (!(int) $question_id && $this->test_session->isObjectiveOriented()) {
            $this->handleTearsAndAngerNoObjectiveOrientedQuestion();
        }

        if ($question_id !== null && !$this->test_sequence->isQuestionPresented($question_id)) {
            $this->test_sequence->setQuestionPresented($question_id);
            $this->test_sequence->saveToDb();
        }

        $isQuestionWorkedThrough = $this->questionrepository->lookupResultRecordExist(
            $this->test_session->getActiveId(),
            $question_id,
            $this->test_session->getPass()
        );

        // fau: testNav - always use edit mode, except for fixed answer
        if ($this->isParticipantsAnswerFixed($question_id)) {
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

        $question_gui = $this->getQuestionGuiInstance($question_id);

        if (!($question_gui instanceof assQuestionGUI)) {
            $this->handleTearsAndAngerQuestionIsNull($question_id, $sequence_element);
        }

        $question_gui->setSequenceNumber($this->test_sequence->getPositionOfSequence($sequence_element));
        $question_gui->setQuestionCount($this->test_sequence->getUserQuestionCount());

        $headerBlockBuilder = new ilTestQuestionHeaderBlockBuilder($this->lng);
        $headerBlockBuilder->setHeaderMode($this->object->getTitleOutput());
        $headerBlockBuilder->setQuestionTitle($question_gui->getObject()->getTitle());
        $headerBlockBuilder->setQuestionPoints($question_gui->getObject()->getPoints());
        $headerBlockBuilder->setQuestionPosition($this->test_sequence->getPositionOfSequence($sequence_element));
        $headerBlockBuilder->setQuestionCount($this->test_sequence->getUserQuestionCount());
        $headerBlockBuilder->setQuestionPostponed($this->test_sequence->isPostponedQuestion($question_id));
        if ($this->test_session->isObjectiveOriented()) {
            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($this->test_session);
            $objectivesAdapter->buildQuestionRelatedObjectiveList($this->test_sequence, $this->question_related_objectives_list);
            $this->question_related_objectives_list->loadObjectivesTitles();

            $objectivesString = $this->question_related_objectives_list->getQuestionRelatedObjectiveTitles($question_id);
            $headerBlockBuilder->setQuestionRelatedObjectives($objectivesString);
        }
        $question_gui->setQuestionHeaderBlockBuilder($headerBlockBuilder);

        $this->prepareTestPage($presentationMode, $sequence_element, $question_id);

        $navigationToolbarGUI = $this->getTestNavigationToolbarGUI();
        $navigationToolbarGUI->setFinishTestButtonEnabled(true);

        $isNextPrimary = $this->handlePrimaryButton($navigationToolbarGUI, $question_id);

        $this->ctrl->setParameter($this, 'sequence', $sequence_element);
        $this->ctrl->setParameter($this, 'pmode', $presentationMode);
        $formAction = $this->ctrl->getFormAction($this, ilTestPlayerCommands::SUBMIT_INTERMEDIATE_SOLUTION);

        switch ($presentationMode) {
            case ilTestPlayerAbstractGUI::PRESENTATION_MODE_EDIT:

                // fau: testNav - enable navigation toolbar in edit mode
                $navigationToolbarGUI->setDisabledStateEnabled(false);
                // fau.
                $this->showQuestionEditable($question_gui, $formAction, $isQuestionWorkedThrough, $instantResponse);

                if ($this->ctrl->getCmd() !== self::FINISH_TEST_CMD
                    && $this->logger->isLoggingEnabled()
                    && !$this->getObject()->getAnonymity()) {
                    $this->logger->logParticipantInteraction(
                        $this->logger->getInteractionFactory()->buildParticipantInteraction(
                            $this->object->getRefId(),
                            $question_id,
                            $this->user->getId(),
                            $this->logger->isIPLoggingEnabled() ? $_SERVER['REMOTE_ADDR'] : '',
                            TestParticipantInteractionTypes::QUESTION_SHOWN,
                            []
                        )
                    );
                }

                break;

            case ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW:

                if ($this->test_sequence->isQuestionOptional($question_gui->getObject()->getId())) {
                    $this->populateQuestionOptionalMessage();
                }

                $this->showQuestionViewable($question_gui, $formAction, $isQuestionWorkedThrough, $instantResponse);

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
                $question_gui,
                true
            );
            // fau.
        }

        // fau: testNav - add feedback modal
        if ($this->isForcedFeedbackNavUrlRegistered()) {
            $this->populateInstantResponseModal($question_gui, $this->getRegisteredForcedFeedbackNavUrl());
            $this->unregisterForcedFeedbackNavUrl();
        }
        // fau.
    }

    protected function editSolutionCmd()
    {
        $this->ctrl->setParameter($this, 'pmode', ilTestPlayerAbstractGUI::PRESENTATION_MODE_EDIT);
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function submitSolutionCmd()
    {
        if ($this->saveQuestionSolution(true, false)) {
            $question_id = $this->test_sequence->getQuestionForSequence(
                $this->getCurrentSequenceElement()
            );

            $this->removeIntermediateSolution();

            if ($this->object->isForceInstantFeedbackEnabled()) {
                $this->ctrl->setParameter($this, 'instresp', 1);

                $this->test_sequence->setQuestionChecked($question_id);
                $this->test_sequence->saveToDb();
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

    // fau: testNav - new function to revert probably auto-saved changes and show the last submitted question state
    protected function revertChangesCmd()
    {
        $this->removeIntermediateSolution();
        $this->setAnswerChangedParameter(false);
        $this->ctrl->saveParameter($this, 'sequence');
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }
    // fau.

    protected function discardSolutionCmd()
    {
        $current_sequence_element = $this->getCurrentSequenceElement();

        $current_question_obj = $this->getQuestionInstance(
            $this->test_sequence->getQuestionForSequence($current_sequence_element)
        );

        $current_question_obj->resetUsersAnswer(
            $this->test_session->getActiveId(),
            $this->test_session->getPass()
        );

        if ($this->logger->isLoggingEnabled()
            && !$this->getObject()->getAnonymity()) {
            $this->logger->logParticipantInteraction(
                $this->logger->getInteractionFactory()->buildParticipantInteraction(
                    $this->object->getRefId(),
                    $this->test_sequence->getQuestionForSequence($current_sequence_element),
                    $this->user->getId(),
                    $this->logger->isIPLoggingEnabled() ? $_SERVER['REMOTE_ADDR'] : '',
                    TestParticipantInteractionTypes::ANSWER_DELETED,
                    []
                )
            );
        }

        $this->ctrl->saveParameter($this, 'sequence');

        $this->ctrl->setParameter($this, 'pmode', ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW);

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function skipQuestionCmd()
    {
        $current_sequence_element = $this->getCurrentSequenceElement();
        $next_sequence_element = $this->test_sequence->getNextSequence($current_sequence_element);

        if (!$this->isValidSequenceElement($next_sequence_element)) {
            $next_sequence_element = $this->test_sequence->getFirstSequence();
        }

        if ($this->object->isPostponingEnabled()) {
            $this->test_sequence->postponeSequence($current_sequence_element);
            $this->test_sequence->saveToDb();
        }

        if ($this->logger->isLoggingEnabled()
            && !$this->getObject()->getAnonymity()) {
            $this->logger->logParticipantInteraction(
                $this->logger->getInteractionFactory()->buildParticipantInteraction(
                    $this->object->getRefId(),
                    $this->test_sequence->getQuestionForSequence($current_sequence_element),
                    $this->user->getId(),
                    $this->logger->isIPLoggingEnabled() ? $_SERVER['REMOTE_ADDR'] : '',
                    TestParticipantInteractionTypes::QUESTION_SKIPPED,
                    []
                )
            );
        }

        $this->ctrl->setParameter($this, 'sequence', $next_sequence_element);
        $this->ctrl->setParameter($this, 'pmode', '');

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
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
            null,
            $this->object->getId()
        );

        // ensure existing test sequence
        if (!$this->test_sequence->hasSequence()) {
            $this->test_sequence->createNewSequence($this->object->getQuestionCount(), $shuffle);
            $this->test_sequence->saveToDb();
        }

        $this->test_sequence->loadFromDb();
        $this->test_sequence->loadQuestions();

        if ($this->test_session->isObjectiveOriented()) {
            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($this->test_session);

            $objectivesAdapter->notifyTestStart($this->test_session, $this->object->getId());
            $objectivesAdapter->prepareTestPass($this->test_session, $this->test_sequence);

            $objectivesAdapter->buildQuestionRelatedObjectiveList(
                $this->test_sequence,
                $this->question_related_objectives_list
            );

            if ($this->test_sequence->hasOptionalQuestions()) {
                $this->adoptUserSolutionsFromPreviousPass();

                $this->test_sequence->reorderOptionalQuestionsToSequenceEnd();
                $this->test_sequence->saveToDb();
            }
        }

        $active_time_id = $this->object->startWorkingTime(
            $this->test_session->getActiveId(),
            $this->test_session->getPass()
        );
        ilSession::set("active_time_id", $active_time_id);

        $this->updateLearningProgressOnTestStart();

        $sequence_element = $this->test_sequence->getFirstSequence();

        $this->ctrl->setParameter($this, 'sequence', $sequence_element);
        $this->ctrl->setParameter($this, 'pmode', '');

        if ($this->logger->isLoggingEnabled()
            && !$this->getObject()->getAnonymity()) {
            $this->logger->logParticipantInteraction(
                $this->logger->getInteractionFactory()->buildParticipantInteraction(
                    $this->object->getRefId(),
                    null,
                    $this->user->getId(),
                    $this->logger->isIPLoggingEnabled() ? $_SERVER['REMOTE_ADDR'] : '',
                    TestParticipantInteractionTypes::TEST_RUN_STARTED,
                    []
                )
            );
        }

        if ($this->object->getListOfQuestionsStart()) {
            $this->ctrl->redirect($this, ilTestPlayerCommands::QUESTION_SUMMARY);
        }

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    /**
     * test accessible returns true if the user can perform the test
     */
    public function isTestAccessible(): bool
    {
        return 	!$this->isNrOfTriesReached()
                and !$this->isMaxProcessingTimeReached()
                and $this->object->startingTimeReached()
                and !$this->object->endingTimeReached();
    }

    /**
     * nr of tries exceeded
     */
    public function isNrOfTriesReached(): bool
    {
        return $this->object->hasNrOfTriesRestriction() && $this->object->isNrOfTriesReached($this->test_session->getPass());
    }

    /**
     * handle endingTimeReached
     * @private
     */

    public function endingTimeReached()
    {
        $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt("detail_ending_time_reached"), ilDatePresentation::formatDate(new ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX))));
        $this->test_session->increasePass();
        $this->test_session->setLastSequence(0);
        $this->test_session->saveToDb();

        $this->redirectBackCmd();
    }

    /**
    * Outputs a message when the maximum processing time is reached
    *
    * Outputs a message when the maximum processing time is reached
    *
    * @access public
    */
    public function max_processing_time_reached()
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
        $this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_submit_answers_confirm.html", "components/ILIAS/Test");
        $this->tpl->setCurrentBlock("adm_content");
        if ($this->object->isTestFinished($this->test_session->getActiveId())) {
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

    public function outProcessingTime(int $active_id): void
    {
        $starting_time = $this->object->getStartingTimeOfUser($active_id);
        $processing_time = $this->object->getProcessingTimeInSeconds($active_id);
        $processing_time_minutes = floor($processing_time / 60);
        $processing_time_seconds = $processing_time - $processing_time_minutes * 60;
        $str_processing_time = "";
        if ($processing_time_minutes > 0) {
            $str_processing_time = $processing_time_minutes . " "
                . ($processing_time_minutes == 1 ? $this->lng->txt("minute") : $this->lng->txt("minutes"));
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
            $str_time_left = $time_left_minutes . " "
                . ($time_left_minutes == 1 ? $this->lng->txt("minute") : $this->lng->txt("minutes"));
        }
        if ($time_left < 300) {
            if ($time_left_seconds > 0) {
                if (strlen($str_time_left) > 0) {
                    $str_time_left .= " " . $this->lng->txt("and") . " ";
                }
                $str_time_left .= $time_left_seconds . " "
                    . ($time_left_seconds == 1 ? $this->lng->txt("second") : $this->lng->txt("seconds"));
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
        iljQueryUtil::initjQuery();
        $template = new ilTemplate("tpl.workingtime.js", true, true, 'components/ILIAS/Test');
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
            if (!empty($matches)) {
                $template->setVariable("ENDYEAR", $matches[1]);
                $template->setVariable("ENDMONTH", $matches[2] - 1);
                $template->setVariable("ENDDAY", $matches[3]);
                $template->setVariable("ENDHOUR", $matches[4]);
                $template->setVariable("ENDMINUTE", $matches[5]);
                $template->setVariable("ENDSECOND", $matches[6]);
            }
        }
        $template->setVariable("YEARNOW", $datenow["year"]);
        $template->setVariable("MONTHNOW", $datenow["mon"] - 1);
        $template->setVariable("DAYNOW", $datenow["mday"]);
        $template->setVariable("HOURNOW", $datenow["hours"]);
        $template->setVariable("MINUTENOW", $datenow["minutes"]);
        $template->setVariable("SECONDNOW", $datenow["seconds"]);
        $template->setVariable("PTIME_M", $processing_time_minutes);
        $template->setVariable("PTIME_S", $processing_time_seconds);
        if ($this->ctrl->getCmd() == 'outQuestionSummary') {
            $template->setVariable("REDIRECT_URL", $this->ctrl->getFormAction($this, 'redirectAfterDashboardCmd'));
        } else {
            $template->setVariable("REDIRECT_URL", "");
        }
        $template->setVariable("CHECK_URL", $this->ctrl->getLinkTarget($this, 'checkWorkingTime', '', true));
        $this->tpl->addOnLoadCode($template->get());
    }

    /**
     * This is asynchronously called by tpl.workingtime.js to check for
     * changes in the user's processing time for a test. This includes
     * extra time added during the test, as this is checked by
     * ilObjTest::getProcessingTimeInSeconds(). The Javascript side
     * then updates the test timer without needing to reload the test page.
     */
    public function checkWorkingTimeCmd(): void
    {
        $active_id = $this->test_session->getActiveId();
        echo (string) $this->object->getProcessingTimeInSeconds($active_id);
        exit;
    }

    protected function showSideList($current_sequence_element): void
    {
        $question_summary_data = $this->service->getQuestionSummaryData($this->test_sequence);
        $questions = [];
        $active = 0;

        foreach ($question_summary_data as $idx => $row) {
            $title = ilLegacyFormElementsUtil::prepareFormOutput($row['title']);
            $description = '';
            if ($row['description'] !== '') {
                $description = ' title="' . htmlspecialchars($row['description']) . '" ';
            }

            if (!$row['disabled']) {
                $this->ctrl->setParameter($this, 'pmode', '');
                $this->ctrl->setParameter($this, 'sequence', $row['sequence']);
                $action = $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::SHOW_QUESTION);
                $this->ctrl->setParameter($this, 'pmode', ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW);
                $this->ctrl->setParameter($this, 'sequence', $this->getCurrentSequenceElement($current_sequence_element));
            }

            $status = ILIAS\UI\Component\Listing\Workflow\Step::NOT_STARTED;

            if ($row['worked_through'] || $row['isAnswered']) {
                $status = ILIAS\UI\Component\Listing\Workflow\Step::IN_PROGRESS;
            }

            $questions[] = $this->ui_factory->listing()->workflow()
                ->step($title, $description, $action)
                ->withStatus($status);
            $active = $row['sequence'] == $current_sequence_element ? $idx : $active;
        }

        $question_listing = $this->ui_factory->listing()->workflow()->linear(
            $this->lng->txt('mainbar_button_label_questionlist'),
            $questions
        )->withActive($active);


        $this->global_screen->tool()->context()->current()->addAdditionalData(
            ilTestPlayerLayoutProvider::TEST_PLAYER_QUESTIONLIST,
            $question_listing
        );
    }

    /**
     * Output of a summary of all test questions for test participants
     */
    public function outQuestionSummaryCmd()
    {
        $this->help->setScreenIdComponent('tst');
        $this->help->setScreenId('assessment');
        $this->help->setSubScreenId('question_summary');

        $this->tpl->addBlockFile(
            $this->getContentBlockName(),
            'adm_content',
            'tpl.il_as_tst_question_summary.html',
            'components/ILIAS/Test'
        );

        $this->global_screen->tool()->context()->current()->getAdditionalData()->replace(
            ilTestPlayerLayoutProvider::TEST_PLAYER_VIEW_TITLE,
            $this->getObject()->getTitle() . ' - ' . $this->lng->txt('question_summary')
        );

        $active_id = $this->test_session->getActiveId();
        $question_summary_data = $this->service->getQuestionSummaryData($this->test_sequence);

        $this->ctrl->setParameter($this, 'sequence', $this->testrequest->raw('sequence'));

        $table_gui = new ilListOfQuestionsTableGUI(
            $this,
            'showQuestion',
            $this->ui_factory,
            $this->ui_renderer
        );
        if (($this->object->getNrOfTries() - 1) === $this->test_session->getPass()) {
            $table_gui->setUserHasAttemptsLeft(false);
        }
        $table_gui->setShowPointsEnabled(!$this->object->getTitleOutput());
        $table_gui->setShowMarkerEnabled($this->object->getShowMarker());
        $table_gui->setFinishTestButtonEnabled(true);

        $table_gui->init();

        $table_gui->setData($question_summary_data);

        $this->tpl->setVariable('TABLE_LIST_OF_QUESTIONS', $table_gui->getHTML());

        if ($this->object->getEnableProcessingTime()) {
            $this->outProcessingTime($active_id);
        }

        if ($this->object->isShowExamIdInTestPassEnabled()) {
            $this->tpl->setCurrentBlock('exam_id_footer');
            $this->tpl->setVariable('EXAM_ID_VAL', ilObjTest::lookupExamId(
                $this->test_session->getActiveId(),
                $this->test_session->getPass(),
                $this->object->getId()
            ));
            $this->tpl->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
            $this->tpl->parseCurrentBlock();
        }
    }

    public function backFromFinishingCmd()
    {
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    /**
    * Creates an output of the solution of an answer compared to the correct solution
    */
    public function outCorrectSolution(): void
    {
        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_correct_solution.html", "components/ILIAS/Test");

        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $this->tpl->parseCurrentBlock();

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css"), "print");
        }

        $this->tpl->setCurrentBlock("adm_content");
        $solution = $this->getCorrectSolutionOutput($this->testrequest->raw("evaluation"), $this->testrequest->raw("active_id"), $this->testrequest->raw("pass"));
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
        $this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_finish_list_of_answers.html", "components/ILIAS/Test");

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
                $template = new ilTemplate("tpl.il_as_qpl_question_printview.html", true, true, "components/ILIAS/TestQuestionPool");
                $template->setVariable("COUNTER_QUESTION", $counter . ". ");
                $template->setVariable("QUESTION_TITLE", $question_gui->getObject()->getTitle());

                $show_question_only = ($this->object->getShowSolutionAnswersOnly()) ? true : false;
                $result_output = $question_gui->getSolutionOutput(
                    $active_id,
                    $pass,
                    false,
                    false,
                    $show_question_only,
                    $this->object->getShowSolutionFeedback()
                );
                $template->setVariable("SOLUTION_OUTPUT", $result_output);
                $this->tpl->setVariable("QUESTION_OUTPUT", $template->get());
                $this->tpl->parseCurrentBlock();
                $counter++;
            }
        }

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css"), "print");
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
        $user_data = $this->getAdditionalUsrDataHtmlAndPopulateWindowTitle($this->test_session, $active_id, true);
        $signature = $this->getResultsSignature();
        $this->tpl->setVariable("USER_DETAILS", $user_data);
        $this->tpl->setVariable("SIGNATURE", $signature);
        $this->tpl->setVariable("TITLE", $this->object->getTitle());
        $this->tpl->setVariable("TXT_TEST_PROLOG", $this->lng->txt("tst_your_answers"));
        $invited_user = &$this->object->getInvitedUsers($this->user->getId());
        $pagetitle = $this->object->getTitle() . ' - ' . $this->lng->txt('clientip') .
            ': ' . $_SERVER['REMOTE_ADDR'] . ' - ' .
            $this->lng->txt('matriculation') . ': ' .
            $invited_user[$this->user->getId()]['matriculation'];
        $this->tpl->setVariable('PAGETITLE', $pagetitle);
        $this->tpl->parseCurrentBlock();
    }

    /**
     * Returns the name of the current content block (depends on the kiosk mode setting)
     *
     * @return string The name of the content block
     */
    public function getContentBlockName(): string
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
            [ilRepositoryGUI::class, ilObjTestGUI::class, ilTestEvaluationGUI::class],
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

        $this->ctrl->redirectByClass('ilAssQuestionHintRequestGUI', ilAssQuestionHintRequestGUI::CMD_CONFIRM_REQUEST);
    }

    protected function isFirstQuestionInSequence($sequence_element): bool
    {
        return $sequence_element == $this->test_sequence->getFirstSequence();
    }

    protected function isLastQuestionInSequence($sequence_element): bool
    {
        return $sequence_element == $this->test_sequence->getLastSequence();
    }

    protected function handleQuestionActionCmd()
    {
        $question_id = $this->test_sequence->getQuestionForSequence(
            $this->getCurrentSequenceElement()
        );

        if (!$this->isParticipantsAnswerFixed($question_id)) {
            $this->updateWorkingTime();
            $this->saveQuestionSolution(false);
            // fau: testNav - add changed status of the question
            $this->setAnswerChangedParameter(true);
            // fau.
        }

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function showInstantResponseCmd()
    {
        $question_id = $this->test_sequence->getQuestionForSequence(
            $this->getCurrentSequenceElement()
        );

        if (!$this->isParticipantsAnswerFixed($question_id)) {
            if ($this->saveQuestionSolution(true)) {
                $this->removeIntermediateSolution();
                $this->setAnswerChangedParameter(false);
            } else {
                $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
            }
            $this->test_sequence->setQuestionChecked($question_id);
            $this->test_sequence->saveToDb();
        } elseif ($this->object->isForceInstantFeedbackEnabled()) {
            $this->test_sequence->setQuestionChecked($question_id);
            $this->test_sequence->saveToDb();
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

    protected function nextQuestionCmd()
    {
        $this->handleCheckTestPassValid();
        $last_sequence_element = $this->getCurrentSequenceElement();
        $next_sequence_element = $this->test_sequence->getNextSequence($last_sequence_element);

        $question_id = $this->test_sequence->getQuestionForSequence($last_sequence_element);
        $is_worked_through = $this->questionrepository->lookupResultRecordExist(
            $this->test_session->getActiveId(),
            $question_id,
            $this->test_session->getPass()
        );

        if (!$is_worked_through) {
            if ($this->logger->isLoggingEnabled()
                && !$this->getObject()->getAnonymity()) {
                $this->logger->logParticipantInteraction(
                    $this->logger->getInteractionFactory()->buildParticipantInteraction(
                        $this->object->getRefId(),
                        $question_id,
                        $this->user->getId(),
                        $this->logger->isIPLoggingEnabled() ? $_SERVER['REMOTE_ADDR'] : '',
                        TestParticipantInteractionTypes::QUESTION_SKIPPED,
                        []
                    )
                );
            }
            if ($this->object->isPostponingEnabled()) {
                $this->handleQuestionPostponing($question_id);
            }
        }

        if (!$this->isValidSequenceElement($next_sequence_element)) {
            $next_sequence_element = $this->test_sequence->getFirstSequence();
        }

        $this->ctrl->setParameter($this, 'sequence', $next_sequence_element);
        $this->ctrl->setParameter($this, 'pmode', '');

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function previousQuestionCmd()
    {
        $this->handleCheckTestPassValid();

        $sequence_element = $this->test_sequence->getPreviousSequence(
            $this->getCurrentSequenceElement()
        );

        if (!$this->isValidSequenceElement($sequence_element)) {
            $sequence_element = $this->test_sequence->getLastSequence();
        }

        $this->ctrl->setParameter($this, 'sequence', $sequence_element);
        $this->ctrl->setParameter($this, 'pmode', '');

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function prepareSummaryPage()
    {
        $this->tpl->addBlockFile(
            $this->getContentBlockName(),
            'adm_content',
            'tpl.il_as_tst_question_summary.html',
            'components/ILIAS/Test'
        );
    }

    protected function initTestPageTemplate()
    {
        $onload_js = <<<JS
    let key_event = (event) => {
        if( event.key === 13  && event.target.tagName.toLowerCase() === "a" ) {
            return;
        }
        if (event.key === 13 &&
            event.target.tagName.toLowerCase() !== "textarea" &&
            (event.target.tagName.toLowerCase() !== "input" || event.target.type.toLowerCase() !== "submit")) {
            event.preventDefault();
        }
    };

    let form = document.getElementById('taForm');
    form.onkeyup = key_event;
    form.onkeydown = key_event;
    form.onkeypress = key_event;
JS;
        $this->tpl->addOnLoadCode($onload_js);
        $this->tpl->addBlockFile(
            $this->getContentBlockName(),
            'adm_content',
            'tpl.il_as_tst_output.html',
            'components/ILIAS/Test'
        );
    }

    protected function handlePasswordProtectionRedirect()
    {
        /**
         * The test password is only checked once per session
         * to avoid errors during autosave if the password is
         * changed during a running test.
         * See Mantis #22536 for more details.
         */
        if ($this->test_session->isPasswordChecked() === true) {
            return;
        }

        if ($this->ctrl->getNextClass() === 'iltestpasswordprotectiongui') {
            return;
        }

        if (!$this->password_checker->isPasswordProtectionPageRedirectRequired()) {
            $this->test_session->setPasswordChecked(true);
            return;
        }

        $this->ctrl->setParameterByClass(self::class, 'lock', $this->getLockParameter());

        $next_command = $this->ctrl->getCmdClass() . '::' . ilTestPlayerCommands::START_TEST;
        $this->ctrl->setParameterByClass(ilTestPasswordProtectionGUI::class, 'nextCommand', $next_command);
        $this->ctrl->redirectByClass(ilTestPasswordProtectionGUI::class, 'showPasswordForm');
    }

    protected function isParticipantsAnswerFixed($question_id): bool
    {
        if ($this->object->isInstantFeedbackAnswerFixationEnabled() && $this->test_sequence->isQuestionChecked($question_id)) {
            return true;
        }

        if ($this->object->isFollowupQuestionAnswerFixationEnabled() && $this->test_sequence->isNextQuestionPresented($question_id)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    protected function getIntroductionPageButtonLabel(): string
    {
        return $this->lng->txt("save_introduction");
    }

    protected function initAssessmentSettings()
    {
        $this->ass_settings = new ilSetting('assessment');
    }

    /**
     * @param ilTestSession $test_session
     */
    protected function handleSkillTriggering(ilTestSession $test_session)
    {
        $questionList = $this->buildTestPassQuestionList();
        $questionList->load();

        $testResults = $this->object->getTestResult($test_session->getActiveId(), $test_session->getPass(), true);

        $skillEvaluation = new ilTestSkillEvaluation(
            $this->db,
            $this->logger,
            $this->object->getTestId(),
            $this->object->getRefId(),
            $this->skills_service->profile(),
            $this->skills_service->personal()
        );

        $skillEvaluation->setUserId($test_session->getUserId());
        $skillEvaluation->setActiveId($test_session->getActiveId());
        $skillEvaluation->setPass($test_session->getPass());

        $skillEvaluation->setNumRequiredBookingsForSkillTriggering((int) $this->ass_settings->get(
            'ass_skl_trig_num_answ_barrier',
            ilObjTestFolder::DEFAULT_SKL_TRIG_NUM_ANSWERS_BARRIER
        ));


        $skillEvaluation->init($questionList);
        $skillEvaluation->evaluate($testResults);

        $skillEvaluation->handleSkillTriggering();
    }

    protected function showAnswerOptionalQuestionsConfirmation()
    {
        $confirmation = new ilTestAnswerOptionalQuestionsConfirmationGUI($this->lng);

        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setCancelCmd('cancelAnswerOptionalQuestions');
        $confirmation->setConfirmCmd('confirmAnswerOptionalQuestions');

        $confirmation->build($this->object->isFixedTest());

        $this->populateHelperGuiContent($confirmation);
    }

    protected function confirmAnswerOptionalQuestionsCmd()
    {
        $this->test_sequence->setAnsweringOptionalQuestionsConfirmed(true);
        $this->test_sequence->saveToDb();

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
            $this->tpl->addBlockfile('CONTENT', 'adm_content', "tpl.il_as_tst_kiosk_mode_content.html", "components/ILIAS/Test");
            $this->tpl->setContent($this->ctrl->getHTML($helperGui));
        } else {
            $this->tpl->setVariable($this->getContentBlockName(), $this->ctrl->getHTML($helperGui));
        }
    }

    protected function getTestNavigationToolbarGUI(): ilTestNavigationToolbarGUI
    {
        $navigation_toolbar = new ilTestNavigationToolbarGUI($this->ctrl, $this);
        $navigation_toolbar->setSuspendTestButtonEnabled($this->object->getShowCancel());
        $navigation_toolbar->setUserPassOverviewEnabled($this->object->getUsrPassOverviewEnabled());
        $navigation_toolbar->setFinishTestCommand($this->getFinishTestCommand());
        return $navigation_toolbar;
    }

    protected function buildReadOnlyStateQuestionNavigationGUI($question_id): ilTestQuestionNavigationGUI
    {
        $navigationGUI = new ilTestQuestionNavigationGUI(
            $this->lng,
            $this->ui_factory,
            $this->ui_renderer
        );

        if (!$this->isParticipantsAnswerFixed($question_id)) {
            $navigationGUI->setEditSolutionCommand(ilTestPlayerCommands::EDIT_SOLUTION);
        }

        if ($this->object->getShowMarker()) {
            $solved_array = ilObjTest::_getSolvedQuestions($this->test_session->getActiveId(), $question_id);
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

    protected function buildEditableStateQuestionNavigationGUI($question_id): ilTestQuestionNavigationGUI
    {
        $navigationGUI = new ilTestQuestionNavigationGUI(
            $this->lng,
            $this->ui_factory,
            $this->ui_renderer
        );

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
            $activeId = $this->test_session->getActiveId();
            $pass = $this->test_session->getPass();

            $questionHintTracking = new ilAssQuestionHintTracking($question_id, $activeId, $pass);

            if ($questionHintTracking->requestsPossible()) {
                $navigationGUI->setRequestHintCommand(ilTestPlayerCommands::CONFIRM_HINT_REQUEST);
            }

            if ($questionHintTracking->requestsExist()) {
                $navigationGUI->setShowHintsCommand(ilTestPlayerCommands::SHOW_REQUESTED_HINTS_LIST);
            }
        }

        if ($this->object->getShowMarker()) {
            $solved_array = ilObjTest::_getSolvedQuestions($this->test_session->getActiveId(), $question_id);
            $solved = 0;

            if (count($solved_array) > 0) {
                $solved = array_pop($solved_array);
                $solved = $solved['solved'];
            }

            if ($solved === 1) {
                $navigationGUI->setQuestionMarkLinkTarget($this->ctrl->getLinkTarget($this, ilTestPlayerCommands::UNMARK_QUESTION_SAVE));
                $navigationGUI->setQuestionMarked(true);
            } else {
                $navigationGUI->setQuestionMarkLinkTarget($this->ctrl->getLinkTarget($this, ilTestPlayerCommands::MARK_QUESTION_SAVE));
                $navigationGUI->setQuestionMarked(false);
            }
        }
        return $navigationGUI;
    }

    protected function getFinishTestCommand(): string
    {
        if (!$this->object->getListOfQuestionsEnd()) {
            return ilTestPlayerCommands::FINISH_TEST;
        }

        return ilTestPlayerCommands::QUESTION_SUMMARY;
    }

    protected function populateInstantResponseModal(assQuestionGUI $question_gui, $navUrl)
    {
        $question_gui->setNavigationGUI(null);
        $question_gui->getQuestionHeaderBlockBuilder()->setQuestionAnswered(true);

        $answerFeedbackEnabled = $this->object->getSpecificAnswerFeedback();

        $solutionoutput = $question_gui->getSolutionOutput(
            $this->test_session->getActiveId(), 	#active_id
            $this->test_session->getPass(),		#pass
            false, 								#graphical_output
            false,								#result_output
            true, 								#show_question_only
            $answerFeedbackEnabled,				#show_feedback
            false, 								#show_correct_solution
            false, 								#show_manual_scoring
            true								#show_question_text
        );

        $pageoutput = $question_gui->outQuestionPage(
            "",
            $this->isShowingPostponeStatusReguired($question_gui->getObject()->getId()),
            $this->test_session->getActiveId(),
            $solutionoutput
        );

        $tpl = new ilTemplate('tpl.tst_player_response_modal.html', true, true, 'components/ILIAS/Test');

        // populate the instant response blocks in the
        $saved_tpl = $this->tpl;
        $this->tpl = $tpl;
        $this->populateInstantResponseBlocks($question_gui, true);
        $this->tpl = $saved_tpl;

        $tpl->setVariable('QUESTION_OUTPUT', $pageoutput);

        $button = $this->ui_factory->button()->primary($this->lng->txt('proceed'), $navUrl);
        $tpl->setVariable('BUTTON', $this->ui_renderer->render($button));

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
     * @see ilAssQuestionPreviewGUI::handleInstantResponseRendering()
     */
    protected function populateInstantResponseBlocks(assQuestionGUI $question_gui, $authorizedSolution)
    {
        $response_available = false;
        $jump_to_response = false;

        // This controls if the solution should be shown.
        // It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Solutions"
        if ($this->object->getInstantFeedbackSolution()) {
            $show_question_inline_score = $this->determineInlineScoreDisplay();

            // Notation of the params prior to getting rid of this crap in favor of a class
            $solutionoutput = $question_gui->getSolutionOutput(
                $this->test_session->getActiveId(),    #active_id
                $this->test_session->getPass(),                                                #pass
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
            $response_available = true;
            $jump_to_response = true;
        }

        $reachedPoints = $question_gui->getObject()->getAdjustedReachedPoints(
            $this->test_session->getActiveId(),
            ilObjTest::_getPass($this->test_session->getActiveId()),
            $authorizedSolution
        );

        $maxPoints = $question_gui->getObject()->getMaximumPoints();

        $solutionCorrect = ($reachedPoints == $maxPoints);

        // This controls if the score should be shown.
        // It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Results (Only Points)"
        if ($this->object->getAnswerFeedbackPoints()) {
            $this->populateScoreBlock($reachedPoints, $maxPoints);
            $response_available = true;
            $jump_to_response = true;
        }

        // This controls if the generic feedback should be shown.
        // It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Solutions"
        if ($this->object->getGenericAnswerFeedback()) {
            if ($this->populateGenericFeedbackBlock($question_gui, $solutionCorrect)) {
                $response_available = true;
                $jump_to_response = true;
            }
        }

        // This controls if the specific feedback should be shown.
        // It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Answer-Specific Feedback"
        if ($this->object->getSpecificAnswerFeedback()) {
            if ($question_gui->hasInlineFeedback()) {
                // Don't jump to the feedback below the question if some feedback is shown within the question
                $jump_to_response = false;
            } elseif ($this->populateSpecificFeedbackBlock($question_gui)) {
                $response_available = true;
                $jump_to_response = true;
            }
        }

        $this->populateFeedbackBlockHeader($jump_to_response);
        if (!$response_available) {
            if ($question_gui->hasInlineFeedback()) {
                $this->populateFeedbackBlockMessage($this->lng->txt('tst_feedback_is_given_inline'));
            } else {
                $this->populateFeedbackBlockMessage($this->lng->txt('tst_feedback_not_available_for_answer'));
            }
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

    protected function populateFeedbackBlockMessage(string $a_message)
    {
        $this->tpl->setCurrentBlock('instant_response_message');
        $this->tpl->setVariable('INSTANT_RESPONSE_MESSAGE', $a_message);
        $this->tpl->parseCurrentBlock();
    }


    protected function getCurrentSequenceElement(): int
    {
        if ($this->getSequenceElementParameter()) {
            return $this->getSequenceElementParameter();
        }

        return $this->test_session->getLastSequence();
    }

    protected function getSequenceElementParameter(): ?int
    {
        if ($this->testrequest->isset('sequence')) {
            return $this->testrequest->int('sequence');
        }

        return null;
    }

    protected function getPresentationModeParameter()
    {
        if ($this->testrequest->isset('pmode')) {
            return $this->testrequest->raw('pmode');
        }

        return null;
    }

    protected function getInstantResponseParameter()
    {
        if ($this->testrequest->isset('instresp')) {
            return $this->testrequest->raw('instresp');
        }

        return null;
    }

    protected function getNextCommandParameter()
    {
        $nextcmd = '';
        if ($this->testrequest->isset('nextcmd')) {
            $nextcmd = $this->testrequest->strVal('nextcmd');
        }

        return $nextcmd !== '' ? $nextcmd : null;
    }

    protected function getNextSequenceParameter(): ?int
    {
        if (isset($_POST['nextseq']) && is_numeric($_POST['nextseq'])) {
            return (int) $_POST['nextseq'];
        }

        return null;
    }

    protected function getNavigationUrlParameter(): ?string
    {
        if (isset($_POST['test_player_navigation_url'])) {
            $navigation_url = $_POST['test_player_navigation_url'];

            $navigation_url_parts = parse_url($navigation_url);
            $ilias_url_parts = parse_url(ilUtil::_getHttpPath());

            if (!isset($navigation_url_parts['host']) || ($ilias_url_parts['host'] === $navigation_url_parts['host'])) {
                return $navigation_url;
            }
        }

        return null;
    }

    protected function getAnswerChangedParameter(): bool
    {
        return !empty($this->testrequest->raw('test_answer_changed'));
    }

    protected function setAnswerChangedParameter(bool $changed = true)
    {
        $this->ctrl->setParameter($this, 'test_answer_changed', $changed ? '1' : '0');
    }

    protected function handleIntermediateSubmit()
    {
        if ($this->getAnswerChangedParameter()) {
            $this->saveQuestionSolution(false);
        } else {
            $this->removeIntermediateSolution();
        }
        $this->setAnswerChangedParameter($this->getAnswerChangedParameter());
    }

    protected function saveNavigationPreventConfirmation()
    {
        if (!empty($_POST['save_on_navigation_prevent_confirmation'])) {
            ilSession::set('save_on_navigation_prevent_confirmation', true);
        }

        if (!empty($_POST[self::FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM])) {
            ilSession::set(self::FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM, true);
        }
    }

    protected function getQuestionGuiInstance(int $question_id, bool $from_cache = true): object
    {
        $tpl = $this->tpl;

        if (!$from_cache || !isset($this->cached_question_guis[$question_id])) {
            $question_gui = $this->object->createQuestionGUI("", $question_id);
            $question_gui->setTargetGui($this);
            $question_gui->setPresentationContext(assQuestionGUI::PRESENTATION_CONTEXT_TEST);
            $question = $question_gui->getObject();
            $question->setShuffler($this->shuffler->getAnswerShuffleFor(
                $question_id,
                $this->test_session->getActiveId(),
                $this->test_session->getPass()
            ));
            $question_gui->setObject($question);
            $question_gui->populateJavascriptFilesRequiredForWorkForm($tpl);

            // hey: prevPassSolutions - determine solution pass index and configure gui accordingly
            $this->initTestQuestionConfig($question_gui->getObject());
            // hey.

            $this->cached_question_guis[$question_id] = $question_gui;
        }

        return $this->cached_question_guis[$question_id];
    }

    protected function getQuestionInstance(int $question_id, bool $from_cache = true): assQuestion
    {
        if ($from_cache && isset($this->cached_question_objects[$question_id])) {
            return $this->cached_question_objects[$question_id];
        }
        $question = assQuestion::instantiateQuestion($question_id);
        $ass_settings = new ilSetting('assessment');

        $process_locker_factory = new ilAssQuestionProcessLockerFactory($ass_settings, $this->db);
        $process_locker_factory->setQuestionId($question->getId());
        $process_locker_factory->setUserId($this->user->getId());
        $question->setProcessLocker($process_locker_factory->getLocker());

        $this->initTestQuestionConfig($question);

        $this->cached_question_objects[$question_id] = $question;
        return $question;
    }

    protected function initTestQuestionConfig(assQuestion $question_obj)
    {
        $question_obj->getTestPresentationConfig()->setPreviousPassSolutionReuseAllowed(
            $this->object->isPreviousSolutionReuseEnabled($this->test_session->getActiveId())
        );
    }

    protected function handleTearsAndAngerQuestionIsNull(int $question_id, $sequence_element): void
    {
        $this->logger->error(
            "INV SEQ:"
            . "active={$this->test_session->getActiveId()} "
            . "qId=$question_id seq=$sequence_element "
            . serialize($this->test_sequence)
        );

        $this->ctrl->setParameter($this, 'sequence', $this->test_sequence->getFirstSequence());
        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function populateMessageContent(string $content_html): void
    {
        if ($this->object->getKioskMode()) {
            $this->tpl->addBlockfile($this->getContentBlockName(), 'content', "tpl.il_as_tst_kiosk_mode_content.html", "components/ILIAS/Test");
            $this->tpl->setContent($content_html);
            return;
        }
        $this->tpl->setVariable($this->getContentBlockName(), $content_html);
    }

    protected function populateModals()
    {
        $this->populateDiscardSolutionModal();

        if ($this->object->isFollowupQuestionAnswerFixationEnabled()) {
            $this->populateNextLocksChangedModal();

            $this->populateNextLocksUnchangedModal();
        }
    }

    protected function populateDiscardSolutionModal()
    {
        $tpl = new ilTemplate('tpl.tst_player_confirmation_modal.html', true, true, 'components/ILIAS/Test');

        $tpl->setVariable('CONFIRMATION_TEXT', $this->lng->txt('discard_answer_confirmation'));

        $button = $this->ui_factory->button()->standard($this->lng->txt('discard_answer'), '#')
        ->withAdditionalOnLoadCode(
            fn($id) => "document.getElementById('$id').addEventListener(
                'click',
                 (event)=>{
                    event.target.name = 'cmd[discardSolution]';
                    event.target.form.requestSubmit(event.target);
                }
            )"
        );

        $tpl->setCurrentBlock('buttons');
        $tpl->setVariable('BUTTON', $this->ui_renderer->render($button));
        $tpl->parseCurrentBlock();

        $button = $this->ui_factory->button()->primary($this->lng->txt('cancel'), '#')
        ->withAdditionalOnLoadCode(
            fn($id) => "document.getElementById('$id').addEventListener(
                'click',
                 ()=>$('#tst_discard_solution_modal').modal('hide')
            );"
        );
        $tpl->setCurrentBlock('buttons');
        $tpl->setVariable('BUTTON', $this->ui_renderer->render($button));
        $tpl->parseCurrentBlock();

        $modal = ilModalGUI::getInstance();
        $modal->setId('tst_discard_solution_modal');
        $modal->setHeading($this->lng->txt('discard_answer'));
        $modal->setBody($tpl->get());

        $this->tpl->setCurrentBlock('discard_solution_modal');
        $this->tpl->setVariable('DISCARD_SOLUTION_MODAL', $modal->getHTML());
        $this->tpl->parseCurrentBlock();
    }

    protected function populateNextLocksUnchangedModal()
    {
        $modal = new ilTestPlayerConfirmationModal($this->ui_renderer);
        $modal->setModalId('tst_next_locks_unchanged_modal');

        $modal->setHeaderText($this->lng->txt('tst_nav_next_locks_empty_answer_header'));
        $modal->setConfirmationText($this->lng->txt('tst_nav_next_locks_empty_answer_confirm'));

        $button = $this->ui_factory->button()->standard($this->lng->txt('tst_proceed'), '#');
        $modal->addButton($button);

        $button = $this->ui_factory->button()->primary($this->lng->txt('cancel'), '#');
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

        $modal = new ilTestPlayerConfirmationModal($this->ui_renderer);
        $modal->setModalId('tst_next_locks_changed_modal');

        $modal->setHeaderText($this->lng->txt('tst_nav_next_locks_current_answer_header'));
        $modal->setConfirmationText($this->lng->txt('tst_nav_next_locks_current_answer_confirm'));

        $modal->setConfirmationCheckboxName(self::FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM);
        $modal->setConfirmationCheckboxLabel($this->lng->txt('tst_dont_show_msg_again_in_current_session'));

        $button = $this->ui_factory->button()->primary($this->lng->txt('tst_save_and_proceed'), '#');
        $modal->addButton($button);

        $button = $this->ui_factory->button()->standard($this->lng->txt('cancel'), '#');
        $modal->addButton($button);

        $this->tpl->setCurrentBlock('next_locks_changed_modal');
        $this->tpl->setVariable('NEXT_LOCKS_CHANGED_MODAL', $modal->getHTML());
        $this->tpl->parseCurrentBlock();
    }

    public const FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM = 'followup_qst_locks_prevent_confirmation';

    protected function setFollowUpQuestionLocksConfirmationPrevented()
    {
        ilSession::set(self::FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM, true);
    }

    protected function isFollowUpQuestionLocksConfirmationPrevented()
    {
        if (ilSession::get(self::FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM) == null) {
            return false;
        }

        return ilSession::get(self::FOLLOWUP_QST_LOCKS_PREVENT_CONFIRMATION_PARAM);
    }

    protected function populateQuestionEditControl(assQuestionGUI $question_gui): void
    {
        $config = [];
        $state = $question_gui->getObject()->lookupForExistingSolutions($this->test_session->getActiveId(), $this->test_session->getPass());
        $config['isAnswered'] = $state['authorized'];
        $config['isAnswerChanged'] = $state['intermediate'] || $this->getAnswerChangedParameter();
        $config['saveOnTimeReachedUrl'] = str_replace('&amp;', '&', $this->ctrl->getFormAction($this, ilTestPlayerCommands::AUTO_SAVE_ON_TIME_LIMIT));

        $config['autosaveUrl'] = '';
        $config['autosaveInterval'] = 0;
        if ($question_gui->getObject() instanceof QuestionAutosaveable && $this->object->getAutosave()) {
            $config['autosaveUrl'] = $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::AUTO_SAVE, '', true);
            $config['autosaveInterval'] = $this->object->getMainSettings()->getQuestionBehaviourSettings()->getAutosaveInterval();
        }

        $question_config = $question_gui->getObject()->getTestPresentationConfig();

        $config['withFormChangeDetection'] = $question_config->isFormChangeDetectionEnabled();

        // Flash and Java questions: changes are directly sent to ilias and have to be polled from there
        $config['withBackgroundChangeDetection'] = $question_config->isBackgroundChangeDetectionEnabled();
        $config['backgroundDetectorUrl'] = $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::DETECT_CHANGES, '', true);

        // Forced feedback will change the navigation saving command
        $config['forcedInstantFeedback'] = $this->object->isForceInstantFeedbackEnabled();
        $config['questionLocked'] = $this->isParticipantsAnswerFixed($question_gui->getObject()->getId());
        $config['nextQuestionLocks'] = $this->object->isFollowupQuestionAnswerFixationEnabled();
        $config['autosaveFailureMessage'] = $this->lng->txt('autosave_failed');

        $this->tpl->addJavascript('assets/js/ilTestPlayerQuestionEditControl.js');
        $this->tpl->addOnLoadCode('il.TestPlayerQuestionEditControl.init(' . json_encode($config) . ')');
    }
    // fau.

    protected function getQuestionsDefaultPresentationMode(): string
    {
        return self::PRESENTATION_MODE_EDIT;
    }

    protected function registerForcedFeedbackNavUrl(string $forced_feedback_nav_url): void
    {
        if (ilSession::get('forced_feedback_navigation_url') == null) {
            ilSession::set('forced_feedback_navigation_url', []);
        }
        $forced_feeback_navigation_url = ilSession::get('forced_feedback_navigation_url');
        $forced_feeback_navigation_url[$this->test_session->getActiveId()] = $forced_feedback_nav_url;
        ilSession::set('forced_feedback_navigation_url', $forced_feeback_navigation_url);
    }

    protected function getRegisteredForcedFeedbackNavUrl(): ?string
    {
        if (ilSession::get('forced_feedback_navigation_url') == null) {
            return null;
        }
        $forced_feedback_navigation_url = ilSession::get('forced_feedback_navigation_url');
        if (!isset($forced_feedback_navigation_url[$this->test_session->getActiveId()])) {
            return null;
        }

        return $forced_feedback_navigation_url[$this->test_session->getActiveId()];
    }

    protected function isForcedFeedbackNavUrlRegistered(): bool
    {
        return $this->getRegisteredForcedFeedbackNavUrl() !== null;
    }

    protected function unregisterForcedFeedbackNavUrl(): void
    {
        $forced_feedback_navigation_url = ilSession::get('forced_feedback_navigation_url');
        if (isset($forced_feedback_navigation_url[$this->test_session->getActiveId()])) {
            unset($forced_feedback_navigation_url[$this->test_session->getActiveId()]);
            ilSession::set('forced_feedback_navigation_url', $forced_feedback_navigation_url);
        }
    }

    protected function handleFileUploadCmd(): void
    {
        $this->updateWorkingTime();
        $this->saveQuestionSolution(false);
        $this->ctrl->redirect($this, ilTestPlayerCommands::SUBMIT_SOLUTION);
    }

    protected function updateLearningProgressOnTestStart(): void
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

        if (!$this->test_sequence->getPositionOfSequence($sequence_element)) {
            return false;
        }

        return true;
    }

    protected function submitSolutionAndNextCmd(): void
    {
        if ($this->object->isForceInstantFeedbackEnabled()) {
            $this->submitSolutionCmd();
            return;
        }

        if ($this->saveQuestionSolution(true, false)) {
            $this->test_sequence->getQuestionForSequence(
                $this->getCurrentSequenceElement()
            );

            $this->removeIntermediateSolution();

            $next_sequence_element = $this->test_sequence->getNextSequence($this->getCurrentSequenceElement());

            if (!$this->isValidSequenceElement($next_sequence_element)) {
                $next_sequence_element = $this->test_sequence->getFirstSequence();
            }

            $this->test_session->setLastSequence($next_sequence_element ?? 0);
            $this->test_session->saveToDb();

            $this->ctrl->setParameter($this, 'sequence', $next_sequence_element);
            $this->ctrl->setParameter($this, 'pmode', '');
        }

        $this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
    }

    protected function handleQuestionPostponing(
        int $question_id
    ): void {
        $this->test_sequence->postponeQuestion($question_id);
        $this->test_sequence->saveToDb();
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

    protected function performTearsAndAngerBrokenConfessionChecks(): bool
    {
        if ($this->test_session->getActiveId() > 0) {
            if ($this->test_sequence->hasRandomQuestionsForPass($this->test_session->getActiveId(), $this->test_session->getPass()) > 0) {
                $this->logger->info(
                    __METHOD__ . ' Random Questions allready exists for user ' .
                    $this->user->getId() . ' in test ' . $this->object->getTestId()
                );

                return true;
            }
        } else {
            $this->logger->info(__METHOD__ . ' ' . sprintf(
                $this->lng->txt("error_random_question_generation"),
                $this->user->getId(),
                $this->object->getTestId()
            ));

            return true;
        };

        return false;
    }

    protected function generateRandomTestPassForActiveUser(): void
    {
        $questionSetConfig = new ilTestRandomQuestionSetConfig(
            $this->tree,
            $this->db,
            $this->lng,
            $this->logger,
            $this->component_repository,
            $this->object,
            $this->questionrepository
        );
        $questionSetConfig->loadFromDb();

        $sourcePoolDefinitionFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory($this->db, $this->object);

        $sourcePoolDefinitionList = new ilTestRandomQuestionSetSourcePoolDefinitionList($this->db, $this->object, $sourcePoolDefinitionFactory);
        $sourcePoolDefinitionList->loadDefinitions();

        $this->process_locker->executeRandomPassBuildOperation(function () use ($questionSetConfig, $sourcePoolDefinitionList) {
            if (!$this->performTearsAndAngerBrokenConfessionChecks()) {
                $stagingPoolQuestionList = new ilTestRandomQuestionSetStagingPoolQuestionList($this->db, $this->component_repository);

                $questionSetBuilder = ilTestRandomQuestionSetBuilder::getInstance(
                    $this->db,
                    $this->lng,
                    $this->logger,
                    $this->object,
                    $questionSetConfig,
                    $sourcePoolDefinitionList,
                    $stagingPoolQuestionList
                );

                $questionSetBuilder->performBuild($this->test_session);
            }
        }, $sourcePoolDefinitionList->hasTaxonomyFilters());
    }

    protected function adoptUserSolutionsFromPreviousPass(): void
    {
        $ass_settings = new ilSetting('assessment');

        $userSolutionAdopter = new ilAssQuestionUserSolutionAdopter($this->db, $ass_settings);
        $userSolutionAdopter->setUserId($this->user->getId());
        $userSolutionAdopter->setActiveId($this->test_session->getActiveId());
        $userSolutionAdopter->setTargetPass($this->test_sequence->getPass());
        $userSolutionAdopter->setQuestionIds($this->test_sequence->getOptionalQuestions());

        $userSolutionAdopter->perform();
    }

    protected function handleTearsAndAngerNoObjectiveOrientedQuestion(): void
    {
        $this->tpl->setOnScreenMessage('failure', sprintf($this->lng->txt('tst_objective_oriented_test_pass_without_questions'), $this->object->getTitle()), true);
        $this->ctrl->redirectByClass(ilObjTestGUI::class);
    }

    protected function handlePrimaryButton(ilTestNavigationToolbarGUI $navigation_toolbar_gui, int $current_question_id): bool
    {
        $is_next_primary = true;

        if ($this->object->isForceInstantFeedbackEnabled()) {
            $is_next_primary = false;
        }

        $questions_missing_result = $this->questionrepository->getQuestionsMissingResultRecord(
            $this->test_session->getActiveId(),
            $this->test_session->getPass(),
            $this->test_sequence->getOrderedSequenceQuestions()
        );

        if ($questions_missing_result === []) {
            $navigation_toolbar_gui->setFinishTestButtonPrimary(true);
            return false;
        }

        if (count($questions_missing_result) === 1
            && $current_question_id === current($questions_missing_result)) {
            $navigation_toolbar_gui->setFinishTestButtonPrimary(true);
            return false;
        }

        return $is_next_primary;
    }

    protected function getTestPlayerTitle(): string
    {
        $title_content = $this->ui_factory->listing()->property();

        if ($this->object->getShowKioskModeParticipant()) {
            $pax_name_label = $this->lng->txt("conf_user_name");
            // this is a placeholder solution with inline html tags to differentiate the different elements
            // should be removed when a title component with grouping and visual weighting is available
            // see:  https://github.com/ILIAS-eLearning/ILIAS/pull/7311
            $pax_name_value = "<span class='il-test-kiosk-head__participant-name'>"
                . $this->user->getFullname() . "</span>";
            $title_content = $title_content->withProperty($pax_name_label, $pax_name_value, false);
        }

        if ($this->object->isShowExamIdInTestPassEnabled()) {
            $exam_id_label = $this->lng->txt("exam_id_label");
            $exam_id_value = ilObjTest::buildExamId(
                $this->test_session->getActiveId(),
                $this->test_session->getPass(),
                $this->object->getId()
            );
            $title_content = $title_content->withProperty($exam_id_label, $exam_id_value);
        }

        if ($this->object->getShowKioskModeTitle()) {
            $test_title_label = $this->lng->txt("test");
            $test_title_value = $this->object->getTitle();
            $title_content = $title_content->withProperty($test_title_label, $test_title_value, false);
        }

        return $this->ui_renderer->render($title_content);
    }
}
