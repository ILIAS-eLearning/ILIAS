<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/Test/classes/class.ilTestServiceGUI.php';
require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Services/UIComponent/Button/classes/class.ilSubmitButton.php';

/**
 * Output class for assessment test execution
 *
 * The ilTestOutputGUI class creates the output for the ilObjTestGUI class when learners execute a test. This saves
 * some heap space because the ilObjTestGUI class will be much smaller then
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *          
 * @version		$Id$
 * 
 * @inGroup		ModulesTest
 * 
 */
abstract class ilTestPlayerAbstractGUI extends ilTestServiceGUI
{
	var $ref_id;
	var $saveResult;
	var $sequence;
	var $cmdCtrl;
	var $maxProcessingTimeReached;
	var $endingTimeReached;

	/**
	 * @var ilTestPasswordChecker
	 */
	protected $passwordChecker;

	/**
	 * @var ilTestProcessLocker
	 */
	protected $processLocker;

	/**
	* ilTestOutputGUI constructor
	*
	* @param ilObjTest $a_object
	*/
	public function __construct($a_object)
	{
		parent::ilTestServiceGUI($a_object);
		$this->ref_id = $_GET["ref_id"];
		
		global $rbacsystem, $ilUser;
		require_once 'Modules/Test/classes/class.ilTestPasswordChecker.php';
		$this->passwordChecker = new ilTestPasswordChecker($rbacsystem, $ilUser, $this->object);
		
		$this->processLocker = null;
	}
	
	protected function ensureExistingTestSession(ilTestSession $testSession)
	{
		if( !$testSession->getActiveId() )
		{
			global $ilUser;

			$testSession->setUserId($ilUser->getId());
			$testSession->setAnonymousId($_SESSION['tst_access_code'][$this->object->getTestId()]);
			$testSession->saveToDb();
		}
	}
	
	protected function initProcessLocker($activeId)
	{
		global $ilDB;
		
		$settings = new ilSetting('assessment');

		require_once 'Modules/Test/classes/class.ilTestProcessLockerFactory.php';
		$processLockerFactory = new ilTestProcessLockerFactory($settings, $ilDB);

		$processLockerFactory->setActiveId($activeId);
		
		$this->processLocker = $processLockerFactory->getLocker();
	}

	/**
	 * Save tags for tagging gui
	 *
	 * Needed this function here because the test info page 
	 * uses another class to send its form results
	 */
	function saveTagsCmd()
	{
		include_once("./Services/Tagging/classes/class.ilTaggingGUI.php");
		$tagging_gui = new ilTaggingGUI();
		$tagging_gui->setObject($this->object->getId(), $this->object->getType());
		$tagging_gui->saveInput();
		$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
	}

	public function outResultsToplistCmd()
	{
		global $ilCtrl;
		$ilCtrl->redirectByClass('ilTestToplistGUI', 'outResultsToplist');
	}

	/**
	 * updates working time and stores state saveresult to see if question has to be stored or not
	 */
	function updateWorkingTime() 
	{
		if ($_SESSION["active_time_id"])
		{
			$this->object->updateWorkingTime($_SESSION["active_time_id"]);
		}
	}

	/**
	 * saves the user input of a question
	 */
	abstract public function saveQuestionSolution($force = FALSE);

	abstract protected function canSaveResult();

	abstract protected function outWorkingForm($sequence = "", $test_id, $postpone_allowed, $directfeedback = false);

	/**
	* Creates the introduction page for a test
	*
	* Creates the introduction page for a test
	*/
	public function outIntroductionPageCmd()
	{
		if( $this->customRedirectRequired() )
		{
			$this->performCustomRedirect();
		}
		
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
		global $ilUser;
		$active_id = $this->testSession->getActiveId();
		$starting_time = $this->object->getStartingTimeOfUser($active_id);
		if ($starting_time === FALSE)
		{
			return FALSE;
		}
		else
		{
			return $this->object->isMaxProcessingTimeReached($starting_time, $active_id);
		}
	}

	protected function determineInlineScoreDisplay()
	{
		$show_question_inline_score = FALSE;
		if ($this->object->getAnswerFeedbackPoints())
		{
			$show_question_inline_score = TRUE;
			return $show_question_inline_score;
		}
		return $show_question_inline_score;
	}

	protected function populatePreviousButtons($sequence)
	{
		if ($this->isFirstPageInSequence( $sequence ))
		{
			$this->populatePreviousButtonsLeadingToIntroduction();
		}
		else
		{
			$this->populatePreviousButtonsLeadingToQuestion();
		}
	}

	protected function populateQuestionMarkingBlockAsUnmarked()
	{
		$this->tpl->setCurrentBlock( "isnotmarked" );
		$this->tpl->setVariable( "CMD_UNMARKED", 'markQuestion' );
		$this->tpl->setVariable( "IMAGE_UNMARKED", ilUtil::getImagePath( "marked_.svg" ) );
		$this->tpl->setVariable( "TEXT_UNMARKED", $this->lng->txt( "tst_question_mark" ) );
		$this->tpl->parseCurrentBlock();
	}

	protected function populateQuestionMarkingBlockAsMarked()
	{
		$this->tpl->setCurrentBlock( "ismarked" );
		$this->tpl->setVariable( "CMD_MARKED", 'unmarkQuestion' );
		$this->tpl->setVariable( "IMAGE_MARKED", ilUtil::getImagePath( "marked.svg" ) );
		$this->tpl->setVariable( "TEXT_MARKED", $this->lng->txt( "tst_remove_mark" ) );
		$this->tpl->parseCurrentBlock();
	}
	
	protected function populateNextButtonsLeadingToQuestion()
	{
		$this->populateUpperNextButtonBlockLeadingToQuestion();
		$this->populateLowerNextButtonBlockLeadingToQuestion();
	}

	protected function populateLowerNextButtonBlockLeadingToQuestion()
	{
		$button = ilSubmitButton::getInstance();
		$button->setPrimary( true );
		$button->setCommand( 'nextQuestion' );
		$button->setCaption( 'save_next' );
		$button->setId( 'bottomnextbutton' );

		$this->tpl->setCurrentBlock( "next_bottom" );
		$this->tpl->setVariable( "BTN_NEXT", $button->render());
		$this->tpl->parseCurrentBlock();
	}

	protected function populateUpperNextButtonBlockLeadingToQuestion()
	{
		$button = ilSubmitButton::getInstance();
		$button->setPrimary( true );
		$button->setCommand( 'nextQuestion' );
		$button->setCaption( 'save_next' );
		$button->setId( 'nextbutton' );

		$this->tpl->setCurrentBlock( "next" );
		$this->tpl->setVariable( "BTN_NEXT", $button->render());
		$this->tpl->parseCurrentBlock();
	}

	protected function populateNextButtonsLeadingToEndOfTest()
	{
		$this->populateUpperNextButtonBlockLeadingToEndOfTest();
		$this->populateLowerNextButtonBlockLeadingToEndOfTest();
	}

	protected function populateLowerNextButtonBlockLeadingToEndOfTest()
	{
		$button = ilSubmitButton::getInstance();
		$button->setPrimary( true );
		$button->setCommand( 'nextQuestion' );
		$button->setCaption( 'save_finish' );
		$button->setId( 'bottomnextbutton' );

		$this->tpl->setCurrentBlock( "next_bottom" );
		$this->tpl->setVariable( "BTN_NEXT", $button->render());
		$this->tpl->parseCurrentBlock();
	}

	protected function populateUpperNextButtonBlockLeadingToEndOfTest()
	{
		$button = ilSubmitButton::getInstance();
		$button->setPrimary( true );
		$button->setCommand( 'nextQuestion' );
		$button->setCaption( 'save_finish' );
		$button->setId( 'nextbutton' );

		$this->tpl->setCurrentBlock( "next" );
		$this->tpl->setVariable( "BTN_NEXT", $button->render());
		$this->tpl->parseCurrentBlock();
	}

	protected function populateNextButtonsLeadingToSummary()
	{
		$this->populateUpperNextButtonBlockLeadingToSummary();
		$this->populateLowerNextButtonBlockLeadingToSummary();
	}

	protected function populateLowerNextButtonBlockLeadingToSummary()
	{
		$button = ilSubmitButton::getInstance();
		$button->setPrimary( true );
		$button->setCommand( 'nextQuestion' );
		$button->setCaption( 'question_summary' );
		$button->setId( 'bottomnextbutton' );

		$this->tpl->setCurrentBlock( "next_bottom" );
		$this->tpl->setVariable( "BTN_NEXT", $button->render());
		$this->tpl->parseCurrentBlock();
	}

	protected function populateUpperNextButtonBlockLeadingToSummary()
	{
		$button = ilSubmitButton::getInstance();
		$button->setPrimary( true );
		$button->setCommand( 'nextQuestion' );
		$button->setCaption( 'question_summary' );
		$button->setId( 'nextbutton' );

		$this->tpl->setCurrentBlock( "next" );
		$this->tpl->setVariable( "BTN_NEXT", $button->render());
		$this->tpl->parseCurrentBlock();
	}

	protected function populateCancelButtonBlock()
	{
		$this->tpl->setCurrentBlock('cancel_test');
		$this->tpl->setVariable('TEXT_CANCELTEST', $this->lng->txt('cancel_test'));
		$this->ctrl->setParameterByClass(get_class($this), 'cancelTest', 'true');
		$this->tpl->setVariable('HREF_CANCELTEXT', $this->ctrl->getLinkTargetByClass(get_class($this), 'outIntroductionPage'));
		$this->ctrl->setParameterByClass(get_class($this), 'cancelTest', null);
		$this->tpl->parseCurrentBlock();
	}

	protected function populateSummaryButtons()
	{
		$this->populateUpperSummaryButtonBlock();
		$this->populateLowerSummaryButtonBlock();
	}

	protected function populateLowerSummaryButtonBlock()
	{
		$this->tpl->setCurrentBlock( "summary_bottom" );
		$this->tpl->setVariable( "CMD_SUMMARY", 'showQuestionList' );
		$this->tpl->setVariable( "BTN_SUMMARY", $this->lng->txt( "question_summary" ) );
		$this->tpl->parseCurrentBlock();
	}

	protected function populateUpperSummaryButtonBlock()
	{
		$this->tpl->setCurrentBlock( "summary" );
		$this->tpl->setVariable( "CMD_SUMMARY", 'showQuestionList' );
		$this->tpl->setVariable( "BTN_SUMMARY", $this->lng->txt( "question_summary" ) );
		$this->tpl->parseCurrentBlock();
	}

	protected function populateQuestionSelectionButtons()
	{
		$this->populateUpperQuestionSelectionButtonBlock();
		$this->populateLowerQuestionSelectionButtonBlock();
	}

	protected function populateLowerQuestionSelectionButtonBlock()
	{
		$this->tpl->setCurrentBlock( "summary_bottom" );
		$this->tpl->setVariable( "CMD_SUMMARY", 'showQuestionSelection' );
		$this->tpl->setVariable( "BTN_SUMMARY", $this->lng->txt( "tst_change_dyn_test_question_selection" ) );
		$this->tpl->parseCurrentBlock();
	}

	protected function populateUpperQuestionSelectionButtonBlock()
	{
		$this->tpl->setCurrentBlock( "summary" );
		$this->tpl->setVariable( "CMD_SUMMARY", 'showQuestionSelection' );
		$this->tpl->setVariable( "BTN_SUMMARY", $this->lng->txt( "tst_change_dyn_test_question_selection" ) );
		$this->tpl->parseCurrentBlock();
	}

	protected function populatePostponeButtons()
	{
		$this->populateUpperPostponeButtonBlock();
		$this->populateLowerPostponeButtonBlock();
	}

	protected function populateLowerPostponeButtonBlock()
	{
		$this->tpl->setCurrentBlock( "postpone_bottom" );
		$this->tpl->setVariable( "CMD_POSTPONE", 'postponeQuestion' );
		$this->tpl->setVariable( "BTN_POSTPONE", $this->lng->txt( "postpone" ) );
		$this->tpl->parseCurrentBlock();
	}

	protected function populateUpperPostponeButtonBlock()
	{
		$this->tpl->setCurrentBlock( "postpone" );
		$this->tpl->setVariable( "CMD_POSTPONE", 'postponeQuestion' );
		$this->tpl->setVariable( "BTN_POSTPONE", $this->lng->txt( "postpone" ) );
		$this->tpl->parseCurrentBlock();
	}

	protected function populatePreviousButtonsLeadingToQuestion()
	{
		$this->populateUpperPreviousButtonBlock(
				'previousQuestion', $this->lng->txt( "save_previous" )
		);
		$this->populateLowerPreviousButtonBlock(
				'previousQuestion', $this->lng->txt( "save_previous" )
		);
	}

	protected function populatePreviousButtonsLeadingToIntroduction()
	{
		$this->populateUpperPreviousButtonBlock(
				'previousQuestion', $this->getIntroductionPageButtonLabel()
		);
		$this->populateLowerPreviousButtonBlock(
				'previousQuestion', $this->getIntroductionPageButtonLabel()
		);
	}

	protected function populateLowerPreviousButtonBlock($cmd, $label)
	{
		$this->tpl->setCurrentBlock( "prev_bottom" );
		$this->tpl->setVariable("CMD_PREV", $cmd);
		$this->tpl->setVariable("BTN_PREV", $label);
		$this->tpl->parseCurrentBlock();
	}

	protected function populateUpperPreviousButtonBlock($cmd, $label)
	{
		$this->tpl->setCurrentBlock( "prev" );
		$this->tpl->setVariable("CMD_PREV", $cmd);
		$this->tpl->setVariable("BTN_PREV", $label);
		$this->tpl->parseCurrentBlock();
	}

	protected function populateSpecificFeedbackBlock($question_gui)
	{
		$this->tpl->setCurrentBlock( "specific_feedback" );
		$this->tpl->setVariable( "SPECIFIC_FEEDBACK",
								 $question_gui->getSpecificFeedbackOutput(
									 $this->testSession->getActiveId(),
									 NULL
								 )
		);
		$this->tpl->parseCurrentBlock();
	}

	protected function populateGenericFeedbackBlock($question_gui)
	{
		$this->tpl->setCurrentBlock( "answer_feedback" );
		$this->tpl->setVariable( "ANSWER_FEEDBACK",
								 $question_gui->getAnswerFeedbackOutput( $this->testSession->getActiveId(),
																		 NULL
								 )
		);
		$this->tpl->parseCurrentBlock();
	}

	protected function populateScoreBlock($reachedPoints, $maxPoints)
	{
		$scoreInformation = sprintf(
				$this->lng->txt( "you_received_a_of_b_points" ), $reachedPoints, $maxPoints
		);
		
		$this->tpl->setCurrentBlock( "received_points_information" );
		$this->tpl->setVariable("RECEIVED_POINTS_INFORMATION", $scoreInformation);
		$this->tpl->parseCurrentBlock();
	}

	protected function populateSolutionBlock($solutionoutput)
	{
		$this->tpl->setCurrentBlock( "solution_output" );
		$this->tpl->setVariable( "CORRECT_SOLUTION", $this->lng->txt( "tst_best_solution_is" ) );
		$this->tpl->setVariable( "QUESTION_FEEDBACK", $solutionoutput );
		$this->tpl->parseCurrentBlock();
	}

	protected function populateCharSelector()
	{
		global $ilSetting;
		if ($ilSetting->get('char_selector_availability') > 0)
		{
			require_once 'Services/UIComponent/CharSelector/classes/class.ilCharSelectorGUI.php';
			$char_selector = ilCharSelectorGUI::_getCurrentGUI($this->object);
			if ($char_selector->getConfig()->getAvailability() == ilCharSelectorConfig::ENABLED)
			{
				$char_selector->addToPage();
				$this->tpl->setCurrentBlock('char_selector');
                $this->tpl->setVariable("CHAR_SELECTOR_IMAGE", ilUtil::getImagePath('icon_omega_test.svg','Services/UIComponent/CharSelector'));
				$this->tpl->setVariable("CHAR_SELECTOR_TEXT", $this->lng->txt('char_selector'));
				$this->tpl->setVariable("CHAR_SELECTOR_TEMPLATE", $char_selector->getSelectorHtml());
				$this->tpl->parseCurrentBlock();
			}
		}
	}

	protected function showSideList()
	{
		global $ilUser;

		$show_side_list = $ilUser->getPref('side_list_of_questions');
		$this->tpl->setCurrentBlock('view_sidelist');
		$this->tpl->setVariable('IMAGE_SIDELIST',
			($show_side_list) ? ilUtil::getImagePath('view_remove.png'
			) : ilUtil::getImagePath('view_choose.png')
		);
		$this->tpl->setVariable('TEXT_SIDELIST',
			($show_side_list) ? $this->lng->txt('tst_hide_side_list'
			) : $this->lng->txt('tst_show_side_list')
		);
		$this->tpl->parseCurrentBlock();
		if($show_side_list)
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "ta_split.css", "Modules/Test"), "screen");
			$this->outQuestionSummaryCmd(false);
		}
	}
	
	protected function populateSyntaxStyleBlock()
	{
		$this->tpl->setCurrentBlock( "SyntaxStyle" );
		$this->tpl->setVariable( "LOCATION_SYNTAX_STYLESHEET",
								 ilObjStyleSheet::getSyntaxStylePath()
		);
		$this->tpl->parseCurrentBlock();
	}

	protected function populateContentStyleBlock()
	{
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$this->tpl->setCurrentBlock( "ContentStyle" );
		$this->tpl->setVariable( "LOCATION_CONTENT_STYLESHEET",
								 ilObjStyleSheet::getContentStylePath( 0 )
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
		if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
		{
			$this->object->setAccessCodeSession($_POST["anonymous_id"]);
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
		$isFirstTestStartRequest = false;
		
		$this->processLocker->requestTestStartLockCheckLock();
		
		if( $this->testSession->lookupTestStartLock() != $this->getLockParameter() )
		{
			$this->testSession->persistTestStartLock($this->getLockParameter());
			$isFirstTestStartRequest = true;
		}

		$this->processLocker->releaseTestStartLockCheckLock();
		
		if( $isFirstTestStartRequest )
		{
			$this->handleUserSettings();
			$this->ctrl->redirect($this, "initTest");
		}
		
		$this->ctrl->redirectByClass("ilobjtestgui", "redirectToInfoScreen");
	}

	public function getLockParameter()
	{
		if( isset($_POST['lock']) && strlen($_POST['lock']) )
		{
			return $_POST['lock'];
		}
		elseif( isset($_GET['lock']) && strlen($_GET['lock']) )
		{
			return $_GET['lock'];
		}

		return null;
	}

	/**
	 * Resume a test at the last position
	 */
	protected function resumePlayerCmd()
	{
		$this->handleUserSettings();

		$this->ctrl->setParameter($this, "activecommand", "resume");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

	/**
	 * Start a test for the first time after a redirect
	 */
	protected function initTestCmd()
	{
		if ($this->object->checkMaximumAllowedUsers() == FALSE)
		{
			return $this->showMaximumAllowedUsersReachedMessage();
		}

		if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
		{
			$this->object->setAccessCodeSession($this->object->createNewAccessCode());
			$this->ctrl->redirect($this, "displayCode");
		}

		$this->object->unsetAccessCodeSession();
		$this->ctrl->redirect($this, 'startTest');
	}
	
	function displayCodeCmd()
	{
		$this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_anonymous_code_presentation.html", "Modules/Test");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_ANONYMOUS_CODE_CREATED", $this->lng->txt("tst_access_code_created"));
		$this->tpl->setVariable("TEXT_ANONYMOUS_CODE", $this->object->getAccessCodeSession());
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("CONTINUE", $this->lng->txt("continue_work"));
		$this->tpl->parseCurrentBlock();
	}
	
	function codeConfirmedCmd()
	{
		$this->ctrl->setParameter($this, "activecommand", "start");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

	/**
	 * Handles some form parameters on starting and resuming a test
	 */
	public function handleUserSettings()
	{
		global $ilUser;

		if ($_POST["chb_javascript"])
		{
			$ilUser->writePref("tst_javascript", 1);
		}
		else
		{
			$ilUser->writePref("tst_javascript", 0);
		}
		
		// hide previous results
		if ($this->object->getNrOfTries() != 1)
		{
			if ($this->object->getUsePreviousAnswers() == 1)
			{
				if ($_POST["chb_use_previous_answers"])
				{
					$ilUser->writePref("tst_use_previous_answers", 1);
				}
				else
				{ 
					$ilUser->writePref("tst_use_previous_answers", 0);
				}
			}
		}
	}

	/**
	 * Calculates the sequence to determine the next question
	 */
	public function calculateSequence() 
	{
		$sequence = $_GET["sequence"];
		if (!$sequence) $sequence = $this->testSequence->getFirstSequence();
		if (array_key_exists("save_error", $_GET))
		{
			if ($_GET["save_error"] == 1)
			{
				return $sequence;
			}
		}
		switch ($_GET["activecommand"])
		{
			case "next":
				$sequence = $this->testSequence->getNextSequence($sequence);
				break;
			case "previous":
				$sequence = $this->testSequence->getPreviousSequence($sequence);
				break;
		}
		return $sequence;
	}

	function redirectAfterAutosaveCmd()
	{
		$active_id = $this->testSession->getActiveId();
		$actualpass = $this->object->_getPass($active_id);
		
		$this->performTestPassFinishedTasks($actualpass);

		$this->testSession->setLastFinishedPass($this->testSession->getPass());
		$this->testSession->increaseTestPass();

		$this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_redirect_autosave.html", "Modules/Test");	
		$this->tpl->setVariable("TEXT_REDIRECT", $this->lng->txt("redirectAfterSave"));
		$this->tpl->setCurrentBlock("HeadContent");
		$this->tpl->setVariable("CONTENT_BLOCK", "<meta http-equiv=\"refresh\" content=\"5; url=" . $this->ctrl->getLinkTarget($this, "afterTestPassFinished") . "\">");
		$this->tpl->parseCurrentBlock();
	}

	function autosaveCmd()
	{
		$result = "";
		if (is_array($_POST) && count($_POST) > 0)
		{
			$res = $this->saveQuestionSolution(TRUE);
			if ($res)
			{
				$result = $this->lng->txt("autosave_success");
			}
			else
			{
				$result = $this->lng->txt("autosave_failed");
			}
		}
		if (!$this->canSaveResult())
		{
			// this was the last action in the test, saving is no longer allowed
			$result = $this->ctrl->getLinkTarget($this, "redirectAfterAutosave", "", true);
		}
		echo $result;
		exit;
	}
	
	/**
	 * Toggle side list
	 */
	public function togglesidelistCmd()
	{
		global $ilUser;

		$show_side_list = $ilUser->getPref('side_list_of_questions');
		$ilUser->writePref('side_list_of_questions', !$show_side_list);
		$this->saveQuestionSolution();
		$this->ctrl->redirect($this, "redirectQuestion");
	}
	
	/**
	 * Set a question solved
	 */
	protected function markQuestionCmd()
	{
		$this->saveQuestionSolution();
		$this->ctrl->setParameter($this, "activecommand", "setmarked");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

	/**
	 * Set a question unsolved
	 */
	protected function unmarkQuestionCmd()
	{
		$this->saveQuestionSolution();
		$this->ctrl->setParameter($this, "activecommand", "resetmarked");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

	/**
	 * Go to the question with the active sequence
	 */
	protected function gotoQuestionCmd()
	{
		if (is_array($_POST) && count($_POST) > 0) $this->saveQuestionSolution();
		$this->ctrl->setParameter($this, "sequence", $_GET["sequence"]);
		$this->ctrl->setParameter($this, "activecommand", "gotoquestion");
		$this->ctrl->saveParameter($this, "tst_javascript");
		if (strlen($_GET['qst_selection'])) $_SESSION['qst_selection'] = $_GET['qst_selection'];
		$this->ctrl->redirect($this, "redirectQuestion");
	}

	/**
	 * Go back to the last active question from the summary
	 *
	 * Go back to the last active question from the summary
	 */
	public function backFromSummaryCmd()
	{
		$this->ctrl->setParameter($this, "activecommand", "back");
		$this->ctrl->redirect($this, "redirectQuestion");
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
		global $ilUser;

		require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirmation = new ilConfirmationGUI();
		$confirmation->setFormAction($this->ctrl->getFormAction($this, 'confirmFinish'));
		$confirmation->setHeaderText($this->lng->txt("tst_finish_confirmation_question"));
		$confirmation->setConfirm($this->lng->txt("tst_finish_confirm_button"), 'confirmFinish');
		$confirmation->setCancel($this->lng->txt("tst_finish_confirm_cancel_button"), 'backConfirmFinish');

		if($this->object->getKioskMode())
		{
			$this->tpl->addBlockfile($this->getContentBlockName(), 'content', "tpl.il_as_tst_kiosk_mode_content.html", "Modules/Test");
			$this->tpl->setContent($confirmation->getHtml());
		}
		else
		{
			$this->tpl->setVariable($this->getContentBlockName(), $confirmation->getHtml());
		}
	}

	function finishTestCmd($requires_confirmation = true)
	{
		global $ilUser, $ilAuth;

		unset($_SESSION["tst_next"]);

		$active_id = $this->testSession->getActiveId();
		$actualpass = $this->object->_getPass($active_id);

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

		// Obligations fulfilled? redirectQuestion : one or the other summary -> no finish
		if( $this->object->areObligationsEnabled() && !$allObligationsAnswered )
		{
			if( $this->object->getListOfQuestions() )
			{
				$_GET['activecommand'] = 'summary_obligations';
			}
			else
			{
				$_GET['activecommand'] = 'summary_obligations_only';
			}

			$this->redirectQuestionCmd();
			return;
		}

		// Examview enabled & !reviewed & requires_confirmation? test_submission_overview (review gui)
		if ($this->object->getEnableExamview() && !isset($_GET['reviewed']) && $requires_confirmation)
		{
			$_GET['activecommand'] = 'test_submission_overview';
			$this->redirectQuestionCmd();
			return;
		}

		// Last try in limited tries & !confirmed
		if (($requires_confirmation) && ($actualpass == $this->object->getNrOfTries() - 1))
		{
			// show confirmation page
			return $this->confirmFinishTestCmd();
		}

		// Last try in limited tries & confirmed?
		if(($actualpass == $this->object->getNrOfTries() - 1) && (!$requires_confirmation))
		{
			$ilAuth->setIdle(ilSession::getIdleValue(), false);
			$ilAuth->setExpire(0);
			switch($this->object->getMailNotification())
			{
				case 1:
					$this->object->sendSimpleNotification($active_id);
					break;
				case 2:
					$this->object->sendAdvancedNotification($active_id);
					break;
			}
		}

		// Non-last try finish
		if(!$_SESSION['tst_pass_finish'])
		{
			if(!$_SESSION['tst_pass_finish']) $_SESSION['tst_pass_finish'] = 1;
			if($this->object->getMailNotificationType() == 1)
			{
				switch($this->object->getMailNotification())
				{
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

		$this->testSession->setLastFinishedPass($this->testSession->getPass());
		$this->testSession->increaseTestPass();

		$this->ctrl->redirect($this, 'afterTestPassFinished');
	}

	protected function performTestPassFinishedTasks($finishedPass)
	{
		if( !$this->testSession->isSubmitted() )
		{
			$this->testSession->setSubmitted(1);
			$this->testSession->setSubmittedTimestamp(date('Y-m-d H:i:s'));
			$this->testSession->saveToDb();
		}

		if( $this->object->getEnableArchiving() )
		{
			$this->archiveParticipantSubmission($this->testSession->getActiveId(), $finishedPass);
		}
	}

	protected function afterTestPassFinishedCmd()
	{
		$activeId = $this->testSession->getActiveId();
		$lastFinishedPass = $this->testSession->getLastFinishedPass();

		// handle test signature

		if ( $this->isTestSignRedirectRequired($activeId, $lastFinishedPass) )
		{
			$this->ctrl->redirectByClass('ilTestSignatureGUI', 'invokeSignaturePlugin');
		}

		if(!$_GET['skipfinalstatement'])
		{
			if ($this->object->getShowFinalStatement())
			{
				$this->ctrl->redirect($this, 'showFinalStatement');
			}
		}

		// redirect after test
		$redirection_mode = $this->object->getRedirectionMode();
		$redirection_url  = $this->object->getRedirectionUrl();
		if( $redirection_url && $redirection_mode && !$this->object->canViewResults() )
		{
			if( $redirection_mode == REDIRECT_KIOSK )
			{
				if( $this->object->getKioskMode() )
				{
					ilUtil::redirect($redirection_url);
				}
			}
			else
			{
				ilUtil::redirect($redirection_url);
			}
		}

		// custom after test redirect (ilTestOutput - objective oriented sessions)
		if( $this->customRedirectRequired() )
		{
			$this->performCustomRedirect();
		}

		// default redirect (pass results)
		$this->redirectBackCmd();
	}

	protected function isTestSignRedirectRequired($activeId, $lastFinishedPass)
	{
		if( !$this->object->getSignSubmission() )
		{
			return false;
		}

		if( !is_null(ilSession::get("signed_{$activeId}_{$lastFinishedPass}")) )
		{
			return false;
		}

		global $ilPluginAdmin;

		$activePlugins = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, 'Test', 'tsig');

		if( !count($activePlugins) )
		{
			return false;
		}
		
		return true;
	}

	/**
	 * @param $active
	 *
	 * @return void
	 */
	protected function archiveParticipantSubmission( $active, $pass )
	{
		require_once 'class.ilTestEvaluationGUI.php';
		$testevaluationgui = new ilTestEvaluationGUI($this->object);
		$results = $this->object->getTestResult($active,$pass);
		$results_output = $testevaluationgui->getPassListOfAnswers($results, $active, $pass, false, false, false, false);

		require_once './Modules/Test/classes/class.ilTestArchiver.php';
		global $ilSetting;
		$inst_id = $ilSetting->get('inst_id', null);
		$archiver = new ilTestArchiver($this->object->getId());

		$path =  ilUtil::getWebspaceDir() . '/assessment/'. $this->object->getId() . '/exam_pdf';
		if (!is_dir($path))
		{
			ilUtil::makeDirParents($path);
		}
		$filename = $path . '/exam_N' . $inst_id . '-' . $this->object->getId()
					. '-' . $active . '-' . $pass . '.pdf';

		require_once 'class.ilTestPDFGenerator.php';
		ilTestPDFGenerator::generatePDF($results_output, ilTestPDFGenerator::PDF_OUTPUT_FILE, $filename);
		//$template->setVariable("PDF_FILE_LOCATION", $filename);
		// Participant submission
		$archiver->handInParticipantSubmission( $active, $pass, $filename, $results_output );
		//$archiver->handInParticipantMisc( $active, $pass, 'signature_gedoens.sig', $filename );
		//$archiver->handInParticipantQuestionMaterial( $active, $pass, 123, 'file_upload.pdf', $filename );

		global $ilias;
		$questions = $this->object->getQuestions();
		foreach ($questions as $question_id)
		{
			$question_object = $this->object->getQuestionDataset( $question_id );
			if ($question_object->type_tag == 'assFileUpload')
			{
				// Pfad: /data/default/assessment/tst_2/14/21/files/file_14_4_1370417414.png
				// /data/ - klar
				// /assessment/ - Konvention
				// /tst_2/ = /tst_<test_id> (ilObjTest)
				// /14/ = /<active_fi>/
				// /21/ = /<question_id>/ (question_object)
				// /files/ - Konvention
				// file_14_4_1370417414.png = file_<active_fi>_<pass>_<some timestamp>.<ext>

				$candidate_path =
					$ilias->ini_ilias->readVariable( 'server', 'absolute_path' ) . ilTestArchiver::DIR_SEP
						. $ilias->ini_ilias->readVariable( 'clients', 'path' ) . ilTestArchiver::DIR_SEP
						. $ilias->client_id . ilTestArchiver::DIR_SEP
						. 'assessment' . ilTestArchiver::DIR_SEP
						. 'tst_' . $this->object->test_id . ilTestArchiver::DIR_SEP
						. $active . ilTestArchiver::DIR_SEP
						. $question_id . ilTestArchiver::DIR_SEP
						. 'files' . ilTestArchiver::DIR_SEP;
				$handle = opendir( $candidate_path );
				while ($handle !== false && ($file = readdir( $handle )) !== false)
				{
					if ($file != null)
					{
						$filename_start = 'file_' . $active . '_' . $pass . '_';

						if (strpos( $file, $filename_start ) === 0)
						{
							$archiver->handInParticipantQuestionMaterial( $active, $pass, $question_id, $file, $file );
						}
					}
				}
			}
		}
		$passdata = $this->object->getTestResult($active, $pass);
		$overview = $testevaluationgui->getPassListOfAnswers($passdata, $active, $pass, true, false, false, true);
		$filename = ilUtil::getWebspaceDir() . '/assessment/scores-'.$this->object->getId() . '-' . $active . '-' . $pass . '.pdf';
		ilTestPDFGenerator::generatePDF($overview, ilTestPDFGenerator::PDF_OUTPUT_FILE, $filename);
		$archiver->handInTestResult($active, $pass, $filename);
		unlink($filename);
		
		return;
	}
	
	public function redirectBackCmd()
	{
		if(!$this->object->canViewResults()) 
		{
			$this->outIntroductionPageCmd();
		}
		else
		{
			$this->ctrl->redirectByClass("ilTestEvaluationGUI", "outUserResultsOverview");
		}
	}
	
	/*
	* Presents the final statement of a test
	*/
	public function showFinalStatementCmd()
	{
		$template = new ilTemplate("tpl.il_as_tst_final_statement.html", TRUE, TRUE, "Modules/Test");
		$this->ctrl->setParameter($this, "skipfinalstatement", 1);
		$template->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "afterTestPassFinished"));
		$template->setVariable("FINALSTATEMENT", $this->object->prepareTextareaOutput($this->object->getFinalStatement(), true));
		$template->setVariable("BUTTON_CONTINUE", $this->lng->txt("btn_next"));
		$this->tpl->setVariable($this->getContentBlockName(), $template->get());
	}
	
	public function getKioskHead()
	{
		global $ilUser;
		
		$template = new ilTemplate('tpl.il_as_tst_kiosk_head.html', true, true, 'Modules/Test');
		if ($this->object->getShowKioskModeTitle())
		{
			$template->setCurrentBlock("kiosk_show_title");
			$template->setVariable("TEST_TITLE", $this->object->getTitle());
			$template->parseCurrentBlock();
		}
		if ($this->object->getShowKioskModeParticipant())
		{
			$template->setCurrentBlock("kiosk_show_participant");
			$template->setVariable("PARTICIPANT_NAME_TXT", $this->lng->txt("login_as"));
			$template->setVariable("PARTICIPANT_NAME", $ilUser->getFullname());
			$template->setVariable("PARTICIPANT_LOGIN", $ilUser->getLogin());
			$template->setVariable("PARTICIPANT_MATRICULATION", $ilUser->getMatriculation());
			$template->setVariable("PARTICIPANT_EMAIL", $ilUser->getEmail());
			$template->parseCurrentBlock();
		}
		if ($this->object->isShowExamIdInTestPassEnabled())
		{
			$exam_id = ilObjTest::buildExamId(
				$this->testSession->getActiveId() , $this->testSession->getPass(), $this->object->getId()
			);
			
			$template->setCurrentBlock("kiosk_show_exam_id");
			$template->setVariable("EXAM_ID_TXT", $this->lng->txt("exam_id"));
			$template->setVariable(	"EXAM_ID", $exam_id);
			$template->parseCurrentBlock();			
		}
		return $template->get();
	}
	
	/**
	 * Outputs the question of the active sequence
	 */
	function outTestPage($directfeedback)
	{
		global $rbacsystem, $ilUser;

		$this->prepareTestPageOutput();
		
		if (!$rbacsystem->checkAccess("read", $this->object->getRefId())) 
		{
			// only with read access it is possible to run the test
			$this->ilias->raiseError($this->lng->txt("cannot_execute_test"),$this->ilias->error_obj->MESSAGE);
		}
		
		if ($this->isMaxProcessingTimeReached())
		{
			$this->maxProcessingTimeReached();
			return;
		}
		
		if ($this->object->endingTimeReached())
		{
			$this->endingTimeReached();
			return;
		}
			
		if ($this->object->getKioskMode())
		{
			$this->populateKioskHead();
		}
		
		$this->tpl->setVariable("TEST_ID", $this->object->getTestId());
		$this->tpl->setVariable("LOGIN", $ilUser->getLogin());
		$this->tpl->setVariable("SEQ_ID", $this->sequence);
		$this->tpl->setVariable("QUEST_ID", $this->testSequence->questions[$this->sequence]);
		 		
		if ($this->object->getEnableProcessingTime())
		{
			$this->outProcessingTime($this->testSession->getActiveId());
		}

		$this->tpl->setVariable("FORM_TIMESTAMP", time());
		
		$this->tpl->setVariable("PAGETITLE", "- " . $this->object->getTitle());
				
		$postpone = ( $this->object->getSequenceSettings() == TEST_POSTPONE );
		
		if ($this->object->isShowExamIdInTestPassEnabled() && !$this->object->getKioskMode())
		{
			$this->tpl->setCurrentBlock('exam_id_footer');
			$this->tpl->setVariable('EXAM_ID_VAL', ilObjTest::lookupExamId(
					$this->testSession->getActiveId(), $this->testSession->getPass(), $this->object->getId()
			));
			$this->tpl->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
			$this->tpl->parseCurrentBlock();
		}				
		
		$this->outWorkingForm($this->sequence, $this->object->getTestId(), $postpone, $directfeedback);
	}

/**
* check access restrictions like client ip, partipating user etc.
*
* check access restrictions like client ip, partipating user etc.
*
* @access public
*/
	function checkOnlineTestAccess() 
	{
		global $ilUser;
		
		// check if user is invited to participate
		$user = $this->object->getInvitedUsers($ilUser->getId());
		if (!is_array ($user) || count($user)!=1)
		{
				ilUtil::sendInfo($this->lng->txt("user_not_invited"), true);
				$this->ctrl->redirectByClass("ilobjtestgui", "backToRepository");
		}
			
		$user = array_pop($user);
		// check if client ip is set and if current remote addr is equal to stored client-ip			
		if (strcmp($user["clientip"],"")!=0 && strcmp($user["clientip"],$_SERVER["REMOTE_ADDR"])!=0)
		{
			ilUtil::sendInfo($this->lng->txt("user_wrong_clientip"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "backToRepository");
		}		
	}	

	
/**
 * test accessible returns true if the user can perform the test
 */
	function isTestAccessible() 
	{		
		return 	!$this->isNrOfTriesReached() 				
			 	and	 !$this->isMaxProcessingTimeReached()
			 	and  $this->object->startingTimeReached()
			 	and  !$this->object->endingTimeReached();
	}

/**
 * nr of tries exceeded
 */
	function isNrOfTriesReached() 
	{
		return $this->object->hasNrOfTriesRestriction() && $this->object->isNrOfTriesReached($this->testSession->getPass());	
	}
	
/**
* Output of the learners view of an existing test pass
*
* Output of the learners view of an existing test pass
*
* @access public
*/
	function passDetails()
	{
		if (array_key_exists("pass", $_GET) && (strlen($_GET["pass"]) > 0))
		{
			$this->ctrl->saveParameter($this, "pass");
			$this->ctrl->saveParameter($this, "active_id");
			$this->outTestResults(false, $_GET["pass"]);
		}
		else
		{
			$this->outTestResults(false);
		}
	}
	
	/**
	 * handle endingTimeReached
	 * @private
	 */
	
	function endingTimeReached() 
	{
		ilUtil::sendInfo(sprintf($this->lng->txt("detail_ending_time_reached"), ilFormat::ftimestamp2datetimeDB($this->object->getEndingTime())));
		$this->testSession->increasePass();
		$this->testSession->setLastSequence(0);
		$this->testSession->saveToDb();
		if (!$this->object->canViewResults()) 
		{
			$this->outIntroductionPage();
		}
		else
		{
			$this->ctrl->redirectByClass("ilTestEvaluationGUI", "outUserResultsOverview");
		}
	}
	
/**
* Outputs a message when the maximum processing time is reached
*
* Outputs a message when the maximum processing time is reached
*
* @access public
*/
	function maxProcessingTimeReached()
	{
		$this->outIntroductionPage();
	}		

	/**
	* confirm submit results
	* if confirm then results are submitted and the screen will be redirected to the startpage of the test
	* @access public
	*/
	function confirmSubmitAnswers() 
	{
		$this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_submit_answers_confirm.html", "Modules/Test");
		$this->tpl->setCurrentBlock("adm_content");
		if ($this->object->isTestFinished($this->testSession->getActiveId()))
		{
			$this->tpl->setCurrentBlock("not_submit_allowed");
			$this->tpl->setVariable("TEXT_ALREADY_SUBMITTED", $this->lng->txt("tst_already_submitted"));
			$this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_show_answer_sheet"));
		} else 
		{
			$this->tpl->setCurrentBlock("submit_allowed");
			$this->tpl->setVariable("TEXT_CONFIRM_SUBMIT_RESULTS", $this->lng->txt("tst_confirm_submit_answers"));
			$this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_submit_results"));
		}
		$this->tpl->setVariable("BTN_BACK", $this->lng->txt("back"));		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "finalSubmission"));
		$this->tpl->parseCurrentBlock();
	}
	
	function outProcessingTime($active_id) 
	{
		global $ilUser;

		$starting_time = $this->object->getStartingTimeOfUser($active_id);
		$processing_time = $this->object->getProcessingTimeInSeconds($active_id);
		$processing_time_minutes = floor($processing_time / 60);
		$processing_time_seconds = $processing_time - $processing_time_minutes * 60;
		$str_processing_time = "";
		if ($processing_time_minutes > 0)
		{
			$str_processing_time = $processing_time_minutes . " " . ($processing_time_minutes == 1 ? $this->lng->txt("minute") : $this->lng->txt("minutes"));
		}
		if ($processing_time_seconds > 0)
		{
			if (strlen($str_processing_time) > 0) $str_processing_time .= " " . $this->lng->txt("and") . " ";
			$str_processing_time .= $processing_time_seconds . " " . ($processing_time_seconds == 1 ? $this->lng->txt("second") : $this->lng->txt("seconds"));
		}
		$time_left = $starting_time + $processing_time - mktime();
		$time_left_minutes = floor($time_left / 60);
		$time_left_seconds = $time_left - $time_left_minutes * 60;
		$str_time_left = "";
		if ($time_left_minutes > 0)
		{
			$str_time_left = $time_left_minutes . " " . ($time_left_minutes == 1 ? $this->lng->txt("minute") : $this->lng->txt("minutes"));
		}
		if ($time_left < 300)
		{
			if ($time_left_seconds > 0)
			{
				if (strlen($str_time_left) > 0) $str_time_left .= " " . $this->lng->txt("and") . " ";
				$str_time_left .= $time_left_seconds . " " .  ($time_left_seconds == 1 ? $this->lng->txt("second") : $this->lng->txt("seconds"));
			}
		}
		$date = getdate($starting_time);
		$formattedStartingTime = ilDatePresentation::formatDate(new ilDateTime($date,IL_CAL_FKT_GETDATE));
		/*
		$formattedStartingTime = ilFormat::formatDate(
			$date["year"]."-".
			sprintf("%02d", $date["mon"])."-".
			sprintf("%02d", $date["mday"])." ".
			sprintf("%02d", $date["hours"]).":".
			sprintf("%02d", $date["minutes"]).":".
			sprintf("%02d", $date["seconds"])
		);
		*/
		$datenow = getdate();
		$this->tpl->setCurrentBlock("enableprocessingtime");
		$this->tpl->setVariable("USER_WORKING_TIME", 
			sprintf(
				$this->lng->txt("tst_time_already_spent"),
				$formattedStartingTime,
				$str_processing_time
			)
		);
		$this->tpl->setVariable("USER_REMAINING_TIME", sprintf($this->lng->txt("tst_time_already_spent_left"), $str_time_left));
		$this->tpl->parseCurrentBlock();
		$template = new ilTemplate("tpl.workingtime.js.html", TRUE, TRUE, 'Modules/Test');
		$template->setVariable("STRING_MINUTE", $this->lng->txt("minute"));
		$template->setVariable("STRING_MINUTES", $this->lng->txt("minutes"));
		$template->setVariable("STRING_SECOND", $this->lng->txt("second"));
		$template->setVariable("STRING_SECONDS", $this->lng->txt("seconds"));
		$template->setVariable("STRING_TIMELEFT", $this->lng->txt("tst_time_already_spent_left"));
		$template->setVariable("AND", strtolower($this->lng->txt("and")));
		$template->setVariable("YEAR", $date["year"]);
		$template->setVariable("MONTH", $date["mon"]-1);
		$template->setVariable("DAY", $date["mday"]);
		$template->setVariable("HOUR", $date["hours"]);
		$template->setVariable("MINUTE", $date["minutes"]);
		$template->setVariable("SECOND", $date["seconds"]);
		if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->object->getEndingTime(), $matches))
		{
			$template->setVariable("ENDYEAR", $matches[1]);
			$template->setVariable("ENDMONTH", $matches[2]-1);
			$template->setVariable("ENDDAY", $matches[3]);
			$template->setVariable("ENDHOUR", $matches[4]);
			$template->setVariable("ENDMINUTE", $matches[5]);
			$template->setVariable("ENDSECOND", $matches[6]);
		}
		$template->setVariable("YEARNOW", $datenow["year"]);
		$template->setVariable("MONTHNOW", $datenow["mon"]-1);
		$template->setVariable("DAYNOW", $datenow["mday"]);
		$template->setVariable("HOURNOW", $datenow["hours"]);
		$template->setVariable("MINUTENOW", $datenow["minutes"]);
		$template->setVariable("SECONDNOW", $datenow["seconds"]);
		$template->setVariable("PTIME_M", $processing_time_minutes);
		$template->setVariable("PTIME_S", $processing_time_seconds);
		
		$this->tpl->setCurrentBlock("HeadContent");
		$this->tpl->setVariable("CONTENT_BLOCK", $template->get());
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	 * Output of a summary of all test questions for test participants
	 */
	public function outQuestionSummaryCmd($fullpage = true, $contextFinishTest = false, $obligationsNotAnswered = false, $obligationsFilter = false) 
	{
		if( $fullpage )
		{
			$this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_question_summary.html", "Modules/Test");
		}
		
		if( $obligationsNotAnswered )
		{
			ilUtil::sendFailure($this->lng->txt('not_all_obligations_answered'));
		}
		
		$active_id = $this->testSession->getActiveId();
		$result_array = & $this->testSequence->getSequenceSummary($obligationsFilter);
		
		$marked_questions = array();
		
		if( $this->object->getKioskMode() && $fullpage )
		{
			$head = $this->getKioskHead();
			if( strlen($head) )
			{
				$this->tpl->setCurrentBlock("kiosk_options");
				$this->tpl->setVariable("KIOSK_HEAD", $head);
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if( $this->object->getShowMarker() )
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$marked_questions = ilObjTest::_getSolvedQuestions($active_id);
		}
		
		$data = array();
		
		foreach( $result_array as $key => $value )
		{
			$this->ctrl->setParameter($this, "sequence", $value["sequence"]);
			
			$href = $this->ctrl->getLinkTargetByClass(get_class($this), "gotoQuestion");
			
			$this->tpl->setVariable("VALUE_QUESTION_TITLE", "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "gotoQuestion")."\">" . $this->object->getQuestionTitle($value["title"]) . "</a>");
			
			$this->ctrl->setParameter($this, "sequence", $_GET["sequence"]);
			
			$description = "";
			if( $this->object->getListOfQuestionsDescription() )
			{
				$description = $value["description"];
			}
			
			$points = "";
			if( !$this->object->getTitleOutput() )
			{
				$points = $value["points"]."&nbsp;".$this->lng->txt("points_short");
			}
			
			$marked = false;
			if( count($marked_questions) )
			{
				if( array_key_exists($value["qid"], $marked_questions) )
				{
					$obj = $marked_questions[$value["qid"]];
					if( $obj["solved"] == 1 )
					{
						$marked = true;
					}
				} 
			}
			
			array_push($data, array(
				'order' => $value["nr"],
				'href' => $href,
				'title' => $this->object->getQuestionTitle($value["title"]),
				'description' => $description,
				'worked_through' => ($value["worked_through"]) ? true : false,
				'postponed' => ($value["postponed"]) ? $this->lng->txt("postponed") : '',
				'points' => $points,
				'marked' => $marked,
				'sequence' => $value["sequence"],
				'obligatory' => $value['obligatory'],
				'isAnswered' => $value['isAnswered']
			));
		}
		
		$this->ctrl->setParameter($this, "sequence", $_GET["sequence"]);
		
		if( $fullpage )
		{
			include_once "./Modules/Test/classes/tables/class.ilListOfQuestionsTableGUI.php";
			$table_gui = new ilListOfQuestionsTableGUI($this, 'backFromSummary');
			
			$table_gui->setShowPointsEnabled( !$this->object->getTitleOutput() );
			$table_gui->setShowMarkerEnabled( $this->object->getShowMarker() );
			$table_gui->setObligationsNotAnswered( $obligationsNotAnswered );
			$table_gui->setShowObligationsEnabled( $this->object->areObligationsEnabled() );
			$table_gui->setObligationsFilterEnabled( $obligationsFilter );

			$table_gui->init();
				
			$table_gui->setData($data);

			$this->tpl->setVariable('TABLE_LIST_OF_QUESTIONS', $table_gui->getHTML());	
			
			if( $this->object->getEnableProcessingTime() )
			{
				$this->outProcessingTime($active_id);
			}
		}
		else
		{
			$template = new ilTemplate('tpl.il_as_tst_list_of_questions_short.html', true, true, 'Modules/Test');
			
			foreach( $data as $row )
			{
				if( strlen($row['description']) )
				{
					$template->setCurrentBlock('description');
					$template->setVariable("DESCRIPTION", $row['description']);
					$template->parseCurrentBlock();
				}
				
				$active = ($row['sequence'] == $this->sequence) ? ' active' : '';
				
				$template->setCurrentBlock('item');
				$template->setVariable('CLASS', ($row['walked_through']) ? ('answered'.$active) : ('unanswered'.$active));
				$template->setVariable('ITEM', ilUtil::prepareFormOutput($row['title']));
				$template->setVariable('SEQUENCE', $row['sequence']);
				$template->parseCurrentBlock();
			}

			require_once 'Services/UIComponent/Panel/classes/class.ilPanelGUI.php';
			$panel = ilPanelGUI::getInstance();
			$panel->setHeadingStyle(ilPanelGUI::HEADING_STYLE_SUBHEADING);
			$panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
			$panel->setHeading($this->lng->txt('list_of_questions'));
			$panel->setBody($template->get());

			$this->tpl->setVariable('LIST_OF_QUESTIONS', $panel->getHTML());
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
	
	function showMaximumAllowedUsersReachedMessage()
	{
		$this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_max_allowed_users_reached.html", "Modules/Test");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("MAX_ALLOWED_USERS_MESSAGE", sprintf($this->lng->txt("tst_max_allowed_users_message"), $this->object->getAllowedUsersTimeGap()));
		$this->tpl->setVariable("MAX_ALLOWED_USERS_HEADING", sprintf($this->lng->txt("tst_max_allowed_users_heading"), $this->object->getAllowedUsersTimeGap()));
		$this->tpl->setVariable("BACK_TO_INTRODUCTION", $this->lng->txt("tst_results_back_introduction"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	function backConfirmFinishCmd()
	{
		$this->ctrl->redirect($this, 'gotoQuestion');
	}
	
	/**
	* Creates an output of the solution of an answer compared to the correct solution
	*
	* @access public
	*/
	function outCorrectSolution()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_correct_solution.html", "Modules/Test");

		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
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
	function showListOfAnswers($active_id, $pass = NULL, $top_data = "", $bottom_data = "")
	{
		global $ilUser;

		$this->tpl->addBlockFile($this->getContentBlockName(), "adm_content", "tpl.il_as_tst_finish_list_of_answers.html", "Modules/Test");

		$result_array =& $this->object->getTestResult($active_id, $pass);

		$counter = 1;
		// output of questions with solutions
		foreach ($result_array as $question_data)
		{
			$question = $question_data["qid"];
			if (is_numeric($question))
			{
				$this->tpl->setCurrentBlock("printview_question");
				$question_gui = $this->object->createQuestionGUI("", $question);
				$template = new ilTemplate("tpl.il_as_qpl_question_printview.html", TRUE, TRUE, "Modules/TestQuestionPool");
				$template->setVariable("COUNTER_QUESTION", $counter.". ");
				$template->setVariable("QUESTION_TITLE", $question_gui->object->getTitle());
				
				$show_question_only = ($this->object->getShowSolutionAnswersOnly()) ? TRUE : FALSE;
				$result_output = $question_gui->getSolutionOutput($active_id, $pass, FALSE, FALSE, $show_question_only, $this->object->getShowSolutionFeedback());				
				$template->setVariable("SOLUTION_OUTPUT", $result_output);
				$this->tpl->setVariable("QUESTION_OUTPUT", $template->get());
				$this->tpl->parseCurrentBlock();
				$counter ++;
			}
		}

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
		}
		if (strlen($top_data))
		{
			$this->tpl->setCurrentBlock("top_data");
			$this->tpl->setVariable("TOP_DATA", $top_data);
			$this->tpl->parseCurrentBlock();
		}
		
		if (strlen($bottom_data))
		{
			$this->tpl->setCurrentBlock("bottom_data");
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
			$this->tpl->setVariable("BOTTOM_DATA", $bottom_data);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_ANSWER_SHEET", $this->lng->txt("tst_list_of_answers"));
		$user_data = $this->getResultsUserdata($this->testSession, $active_id, TRUE);
		$signature = $this->getResultsSignature();
		$this->tpl->setVariable("USER_DETAILS", $user_data);
		$this->tpl->setVariable("SIGNATURE", $signature);
		$this->tpl->setVariable("TITLE", $this->object->getTitle());
		$this->tpl->setVariable("TXT_TEST_PROLOG", $this->lng->txt("tst_your_answers"));
		$invited_user =& $this->object->getInvitedUsers($ilUser->getId());
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
		if ($this->object->getKioskMode())
		{
			$this->tpl->setBodyClass("kiosk");
			$this->tpl->setAddFooter(FALSE);
			return "CONTENT";
		}
		else
		{
			return "ADM_CONTENT";
		}
	}

	function outUserResultsOverviewCmd()
	{
		$this->ctrl->redirectByClass(
			array('ilRepositoryGUI', 'ilObjTestGUI', 'ilTestEvaluationGUI'), "outUserResultsOverview"
		);
	}

	function outUserListOfAnswerPassesCmd()
	{
		$this->ctrl->redirectByClass(
			array('ilRepositoryGUI', 'ilObjTestGUI', 'ilTestEvaluationGUI'), "outUserListOfAnswerPasses"
		);
	}

	/**
	 * Go to requested hint list
	 */
	protected function showRequestedHintListCmd()
	{
		$this->saveQuestionSolution();
		
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';
		$this->ctrl->redirectByClass('ilAssQuestionHintRequestGUI', ilAssQuestionHintRequestGUI::CMD_SHOW_LIST);
	}
	
	/**
	 * Go to hint request confirmation
	 */
	protected function confirmHintRequestCmd()
	{
		$this->saveQuestionSolution();
		
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';
		$this->ctrl->redirectByClass('ilAssQuestionHintRequestGUI', ilAssQuestionHintRequestGUI::CMD_CONFIRM_REQUEST);
	}
	
	/**
	 * renders the elements for the question related navigation
	 * 
	 * @access private
	 * @global ilTemplate $tpl
	 * @global ilLanguage $lng
	 * @param assQuestionGUI $questionGUI 
	 */
	protected function fillQuestionRelatedNavigation(assQuestionGUI $questionGUI)
	{
		global $tpl, $lng;
		
		$parseQuestionRelatedNavigation = false;
		
		switch( 1 )
		{
			case $this->object->getSpecificAnswerFeedback():
			case $this->object->getGenericAnswerFeedback():
			case $this->object->getAnswerFeedbackPoints():
			case $this->object->getInstantFeedbackSolution():
			
				$tpl->setCurrentBlock("direct_feedback");
				$tpl->setVariable("CMD_SHOW_INSTANT_RESPONSE", 'showInstantResponse');
				$tpl->setVariable("TEXT_SHOW_INSTANT_RESPONSE", $lng->txt("check"));
				$tpl->parseCurrentBlock();

				$parseQuestionRelatedNavigation = true;
		}
		
		if( $this->object->isOfferingQuestionHintsEnabled() )
		{
			$questionId = $questionGUI->object->getId();
			$activeId = $this->testSession->getActiveId();
			$pass = $this->testSession->getPass();

			require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintTracking.php';
			$questionHintTracking = new ilAssQuestionHintTracking($questionId, $activeId, $pass);
			
			$requestsExist = $questionHintTracking->requestsExist();
			$requestsPossible = $questionHintTracking->requestsPossible();
			
			if( $requestsPossible )
			{
				if( $requestsExist )
				{
					$buttonText = $lng->txt("button_request_next_question_hint");
				}
				else
				{
					$buttonText = $lng->txt("button_request_question_hint");
				}

				$tpl->setCurrentBlock("button_request_next_question_hint");
				$tpl->setVariable("CMD_REQUEST_NEXT_QUESTION_HINT", 'confirmHintRequest');
				$tpl->setVariable("TEXT_REQUEST_NEXT_QUESTION_HINT", $buttonText);
				$tpl->parseCurrentBlock();

				$parseQuestionRelatedNavigation = true;
			}

			if( $requestsExist )
			{
				$tpl->setCurrentBlock("button_show_requested_question_hints");
				$tpl->setVariable("CMD_SHOW_REQUESTED_QUESTION_HINTS", 'showRequestedHintList');
				$tpl->setVariable("TEXT_SHOW_REQUESTED_QUESTION_HINTS", $lng->txt("button_show_requested_question_hints"));
				$tpl->parseCurrentBlock();

				$parseQuestionRelatedNavigation = true;
			}
		}
		
		if( $parseQuestionRelatedNavigation )
		{
			$tpl->setCurrentBlock("question_related_navigation");
			$tpl->parseCurrentBlock();
		}
	}
	
	abstract protected function isFirstPageInSequence($sequence);
	
	abstract protected function isLastQuestionInSequence(assQuestionGUI $questionGUI);
	
	
	abstract protected function handleQuestionActionCmd();
	
	abstract protected function showInstantResponseCmd();
	
	abstract protected function nextQuestionCmd();
	
	abstract protected function previousQuestionCmd();
	
	abstract protected function postponeQuestionCmd();
	
	
	
	protected function getMarkedQuestions()
	{
		if( $this->object->getShowMarker() )
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$marked_questions = ilObjTest::_getSolvedQuestions($this->testSession->getActiveId());
		}
		else
		{
			$marked_questions = array();
		}
		
		return $marked_questions;
	}
	
	protected function prepareSummaryPage()
	{
		$this->tpl->addBlockFile(
			$this->getContentBlockName(), 'adm_content', 'tpl.il_as_tst_question_summary.html', 'Modules/Test'
		);

		if ($this->object->getShowCancel())
		{
			$this->populateCancelButtonBlock();
		}

		if ($this->object->getKioskMode())
		{
			$this->populateKioskHead();
		}
	}
	
	protected function prepareTestPageOutput()
	{
		$this->tpl->addBlockFile(
			$this->getContentBlockName(), 'adm_content', 'tpl.il_as_tst_output.html', 'Modules/Test'
		);
	}
	
	protected function populateKioskHead()
	{
		ilUtil::sendInfo(); // ???
		
		$head = $this->getKioskHead();
		
		if (strlen($head))
		{
			$this->tpl->setCurrentBlock("kiosk_options");
			$this->tpl->setVariable("KIOSK_HEAD", $head);
			$this->tpl->parseCurrentBlock();
		}
	}

	protected function handlePasswordProtectionRedirect()
	{
		if( $this->ctrl->getNextClass() == 'iltestpasswordprotectiongui' )
		{
			return;
		}
		
		if( !$this->passwordChecker->isPasswordProtectionPageRedirectRequired() )
		{
			return;
		}
		
		$this->ctrl->setParameter($this, 'lock', $this->getLockParameter());
		
		$nextCommand = $this->ctrl->getCmdClass().'::'.$this->ctrl->getCmd();
		$this->ctrl->setParameterByClass('ilTestPasswordProtectionGUI', 'nextCommand', $nextCommand);
		$this->ctrl->redirectByClass('ilTestPasswordProtectionGUI', 'showPasswordForm');
	}

	protected function isParticipantsAnswerFixed($questionId)
	{
		if( !$this->object->isInstantFeedbackAnswerFixationEnabled() )
		{
			return false;
		}

		if( !$this->testSequence->isQuestionChecked($questionId) )
		{
			return false;
		}

		return true;
	}
	
	protected function customRedirectRequired()
	{
		return false;
	}
	
	protected function performCustomRedirect()
	{
		return;
	}

	/**
	 * @return string
	 */
	protected function getIntroductionPageButtonLabel()
	{
		if( $this->testSession->isObjectiveOriented() )
		{
			return $this->lng->txt("save_back_to_objective_container");
		}
		
		return $this->lng->txt("save_introduction");
	}
}
