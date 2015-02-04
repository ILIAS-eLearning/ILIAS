<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestPlayerAbstractGUI.php';

/**
 * Output class for assessment test execution
 *
 * The ilTestOutputGUI class creates the output for the ilObjTestGUI
 * class when learners execute a test. This saves some heap space because 
 * the ilObjTestGUI class will be much smaller then
 *
 * @extends ilTestPlayerAbstractGUI
 * 
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilTestPlayerDynamicQuestionSetGUI: ilAssGenFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerDynamicQuestionSetGUI: ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerDynamicQuestionSetGUI: ilAssQuestionHintRequestGUI
 * @ilCtrl_Calls ilTestPlayerDynamicQuestionSetGUI: ilAssQuestionPageGUI
 * @ilCtrl_Calls ilTestPlayerDynamicQuestionSetGUI: ilTestDynamicQuestionSetStatisticTableGUI
 * @ilCtrl_Calls ilTestPlayerDynamicQuestionSetGUI: ilToolbarGUI
 * @ilCtrl_Calls ilTestPlayerDynamicQuestionSetGUI: ilTestSubmissionReviewGUI
 * @ilCtrl_Calls ilTestPlayerDynamicQuestionSetGUI: ilTestPasswordProtectionGUI
 */
class ilTestPlayerDynamicQuestionSetGUI extends ilTestPlayerAbstractGUI
{
	const CMD_SHOW_QUESTION_SELECTION = 'showQuestionSelection';
	const CMD_SHOW_QUESTION = 'showQuestion';
	const CMD_FROM_PASS_DELETION = 'fromPassDeletion';
		
	/**
	 * @var ilObjTestDynamicQuestionSetConfig
	 */
	private $dynamicQuestionSetConfig = null;

	/**
	 * @var ilTestSequenceDynamicQuestionSet
	 */
	protected $testSequence;

	/**
	 * @var ilTestSessionDynamicQuestionSet
	 */
	protected $testSession;
	
	/**
	 * execute command
	 */
	function executeCommand()
	{
		global $ilDB, $lng, $ilPluginAdmin, $ilTabs, $tree;

		$ilTabs->clearTargets();
		
		$this->ctrl->saveParameter($this, "sequence");
		$this->ctrl->saveParameter($this, "active_id");

		require_once 'Modules/Test/classes/class.ilObjTestDynamicQuestionSetConfig.php';
		$this->dynamicQuestionSetConfig = new ilObjTestDynamicQuestionSetConfig($tree, $ilDB, $ilPluginAdmin, $this->object);
		$this->dynamicQuestionSetConfig->loadFromDb();

		$testSessionFactory = new ilTestSessionFactory($this->object);
		$this->testSession = $testSessionFactory->getSession($_GET['active_id']);

		$this->ensureExistingTestSession($this->testSession);
		$this->initProcessLocker($this->testSession->getActiveId());
		
		$testSequenceFactory = new ilTestSequenceFactory($ilDB, $lng, $ilPluginAdmin, $this->object);
		$this->testSequence = $testSequenceFactory->getSequence($this->testSession);
		$this->testSequence->loadFromDb();

		include_once 'Services/jQuery/classes/class.iljQueryUtil.php';
		iljQueryUtil::initjQuery();
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initConnectionWithAnimation();
		if( $this->object->getKioskMode() )
		{
			include_once 'Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php';
			ilOverlayGUI::initJavascript();
		}
		
		$this->handlePasswordProtectionRedirect();
		
		$cmd = $this->ctrl->getCmd();
		$nextClass = $this->ctrl->getNextClass($this);
		
		switch($nextClass)
		{
			case 'ilassquestionpagegui':

				$questionId = $this->testSequence->getQuestionForSequence( $this->calculateSequence() );

				require_once "./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php";
				$page_gui = new ilAssQuestionPageGUI($questionId);
				$ret = $this->ctrl->forwardCommand($page_gui);
				break;

			case 'ilassquestionhintrequestgui':
				
				$questionGUI = $this->object->createQuestionGUI(
					"", $this->testSequenceFactory->getSequence()->getQuestionForSequence( $this->calculateSequence() )
				);

				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';
				$gui = new ilAssQuestionHintRequestGUI($this, self::CMD_SHOW_QUESTION, $this->testSession, $questionGUI);
				
				$this->ctrl->forwardCommand($gui);
				
				break;
				
			case 'ildynamicquestionsetstatistictablegui':
				
				$this->ctrl->forwardCommand( $this->buildQuestionSetFilteredStatisticTableGUI() );
				
				break;

			case 'iltestpasswordprotectiongui':
				require_once 'Modules/Test/classes/class.ilTestPasswordProtectionGUI.php';
				$gui = new ilTestPasswordProtectionGUI($this->ctrl, $this->tpl, $this->lng, $this, $this->passwordChecker);
				$ret = $this->ctrl->forwardCommand($gui);
				break;
			
			default:
				
				$cmd .= 'Cmd';
				$ret =& $this->$cmd();
				break;
		}
		
		return $ret;
	}

	/**
	 * Resume a test at the last position
	 */
	protected function resumePlayerCmd()
	{
		if ($this->object->checkMaximumAllowedUsers() == FALSE)
		{
			return $this->showMaximumAllowedUsersReachedMessage();
		}
		
		$this->handleUserSettings();
		
		if( $this->dynamicQuestionSetConfig->isAnyQuestionFilterEnabled() )
		{
			$this->ctrl->redirect($this, self::CMD_SHOW_QUESTION_SELECTION);
		}
		
		$this->ctrl->redirect($this, self::CMD_SHOW_QUESTION);
	}
	
	protected function startTestCmd()
	{
		global $ilUser;
		
		$this->testSession->setCurrentQuestionId(null); // no question "came up" yet
		
		$this->testSession->saveToDb();
		
		$this->ctrl->setParameter($this, 'active_id', $this->testSession->getActiveId());

		assQuestion::_updateTestPassResults($this->testSession->getActiveId(), $this->testSession->getPass(), false, null, $this->object->id);

		$_SESSION['active_time_id'] = $this->object->startWorkingTime(
				$this->testSession->getActiveId(), $this->testSession->getPass()
		);
		
		$this->ctrl->saveParameter($this, 'tst_javascript');
		
		if( $this->dynamicQuestionSetConfig->isAnyQuestionFilterEnabled() )
		{
			$this->ctrl->redirect($this, self::CMD_SHOW_QUESTION_SELECTION);
		}
		
		$this->ctrl->redirect($this, self::CMD_SHOW_QUESTION);
	}
	
	protected function showQuestionSelectionCmd()
	{
		$this->prepareSummaryPage();
		
		$questionId = $this->testSession->getCurrentQuestionId();
		
		if( $questionId && !$this->isParticipantsAnswerFixed($questionId) )
		{
			$this->updateWorkingTime();
			$this->saveQuestionSolution();
			$this->persistQuestionAnswerStatus();

			$this->testSequence->unsetQuestionPostponed($questionId);
		}
		
		$this->testSequence->loadQuestions(
				$this->dynamicQuestionSetConfig, $this->testSession->getQuestionSetFilterSelection()
		);
		
		$this->testSequence->cleanupQuestions($this->testSession);

		$this->testSequence->saveToDb();
			
		require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		$toolbarGUI = new ilToolbarGUI();
		
		$toolbarGUI->addButton(
			$this->getEnterTestButtonLangVar(), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_QUESTION),
			'', '', '', '', 'submit emphsubmit'
		);
		
		if( $this->object->isPassDeletionAllowed() )
		{
			require_once 'Modules/Test/classes/confirmations/class.ilTestPassDeletionConfirmationGUI.php';
			
			$toolbarGUI->addButton(
				$this->lng->txt('tst_dyn_test_pass_deletion_button'),
				$this->getPassDeletionTarget(ilTestPassDeletionConfirmationGUI::CONTEXT_DYN_TEST_PLAYER)
			);
		}
		
		$filteredData = array($this->buildQuestionSetAnswerStatisticRowArray(
			$this->testSequence->getFilteredQuestionsData(), $this->getMarkedQuestions()
		)); #vd($filteredData);
		$filteredTableGUI = $this->buildQuestionSetFilteredStatisticTableGUI();
		$filteredTableGUI->setData($filteredData);

		$completeData = array($this->buildQuestionSetAnswerStatisticRowArray(
			$this->testSequence->getCompleteQuestionsData(), $this->getMarkedQuestions()
		)); #vd($completeData);
		$completeTableGUI = $this->buildQuestionSetCompleteStatisticTableGUI();
		$completeTableGUI->setData($completeData);

		$content = $this->ctrl->getHTML($toolbarGUI);
		$content .= $this->ctrl->getHTML($filteredTableGUI);
		$content .= $this->ctrl->getHTML($completeTableGUI);

		$this->tpl->setVariable('TABLE_LIST_OF_QUESTIONS', $content);	

		if( $this->object->getEnableProcessingTime() )
		{
			$this->outProcessingTime($this->testSession->getActiveId());
		}
	}
	
	protected function filterQuestionSelectionCmd()
	{
		$tableGUI = $this->buildQuestionSetFilteredStatisticTableGUI();
		$tableGUI->writeFilterToSession();

		$taxFilterSelection = array();
		$answerStatusFilterSelection = ilAssQuestionList::ANSWER_STATUS_FILTER_ALL_NON_CORRECT;
		
		foreach( $tableGUI->getFilterItems() as $item )
		{
			if( strpos($item->getPostVar(), 'tax_') !== false )
			{
				$taxId = substr( $item->getPostVar(), strlen('tax_') );
				$taxFilterSelection[$taxId] = $item->getValue();
			}
			elseif( $item->getPostVar() == 'question_answer_status' )
			{
				$answerStatusFilterSelection = $item->getValue();
			}
		}
		
		$this->testSession->getQuestionSetFilterSelection()->setTaxonomySelection($taxFilterSelection);
		$this->testSession->getQuestionSetFilterSelection()->setAnswerStatusSelection($answerStatusFilterSelection);
		$this->testSession->saveToDb();
		
		$this->testSequence->resetTrackedQuestionList();
		$this->testSequence->saveToDb();
		
		$this->ctrl->redirect($this, 'showQuestionSelection');
	}
	
	protected function resetQuestionSelectionCmd()
	{
		$tableGUI = $this->buildQuestionSetFilteredStatisticTableGUI();
		$tableGUI->resetFilter();
		
		$this->testSession->getQuestionSetFilterSelection()->setTaxonomySelection( array() );
		$this->testSession->getQuestionSetFilterSelection()->setAnswerStatusSelection( null );
		$this->testSession->saveToDb();
		
		$this->testSequence->resetTrackedQuestionList();
		$this->testSequence->saveToDb();
		
		$this->ctrl->redirect($this, 'showQuestionSelection');
	}

	protected function showTrackedQuestionListCmd()
	{
		if( !$this->dynamicQuestionSetConfig->isPreviousQuestionsListEnabled() )
		{
			$this->ctrl->redirect($this, self::CMD_SHOW_QUESTION);
		}
		
		$this->prepareSummaryPage();

		$questionId = $this->testSession->getCurrentQuestionId();

		if( $questionId && !$this->isParticipantsAnswerFixed($questionId) )
		{
			$this->updateWorkingTime();
			$this->saveQuestionSolution();
			$this->persistQuestionAnswerStatus();

			$this->testSequence->unsetQuestionPostponed($questionId);
		}

		$this->testSequence->loadQuestions(
				$this->dynamicQuestionSetConfig, $this->testSession->getQuestionSetFilterSelection()
		);
		
		$this->testSequence->cleanupQuestions($this->testSession);
		
		$this->testSequence->saveToDb();
		
		$data = $this->buildQuestionsTableDataArray(
			$this->testSequence->getTrackedQuestionList( $this->testSession->getCurrentQuestionId() ),
			$this->getMarkedQuestions()
		);
		
		include_once "./Modules/Test/classes/tables/class.ilTrackedQuestionsTableGUI.php";
		$table_gui = new ilTrackedQuestionsTableGUI(
				$this, 'showTrackedQuestionList', $this->object->getSequenceSettings(), $this->object->getShowMarker()
		);
		
		$table_gui->setData($data);

		$this->tpl->setVariable('TABLE_LIST_OF_QUESTIONS', $table_gui->getHTML());	

		if( $this->object->getEnableProcessingTime() )
		{
			$this->outProcessingTime($this->testSession->getActiveId());
		}
	}

	protected function previousQuestionCmd()
	{
		
	}

	protected function fromPassDeletionCmd()
	{
		$this->resetCurrentQuestion();
		$this->ctrl->redirect($this, 'showQuestion');
	}
	
	protected function nextQuestionCmd()
	{
		$questionId = $this->testSession->getCurrentQuestionId();

		if( $questionId && !$this->isParticipantsAnswerFixed($questionId) )
		{
			$this->updateWorkingTime();
			$this->saveQuestionSolution();
			$this->persistQuestionAnswerStatus();

			$this->testSequence->unsetQuestionPostponed($questionId);
			$this->testSequence->saveToDb();
		}

		$this->resetCurrentQuestion();
		
		$this->ctrl->redirect($this, 'showQuestion');
	}
	
	protected function postponeQuestionCmd()
	{
		$questionId = $this->testSession->getCurrentQuestionId();

		if( $questionId && !$this->isParticipantsAnswerFixed($questionId) )
		{
			$this->updateWorkingTime();
			$this->saveQuestionSolution();
			$this->persistQuestionAnswerStatus();

			$this->testSequence->setQuestionPostponed($questionId);
		}
		
		$this->testSession->setCurrentQuestionId(null);
		
		$this->testSequence->saveToDb();
		$this->testSession->saveToDb();
		
		$this->ctrl->redirect($this, 'showQuestion');
	}
	
	protected function markQuestionCmd()
	{
		$questionId = $this->testSession->getCurrentQuestionId();

		if( $questionId && !$this->isParticipantsAnswerFixed($questionId) )
		{
			$this->updateWorkingTime();
			$this->saveQuestionSolution();
			$this->persistQuestionAnswerStatus();

			$this->testSequence->unsetQuestionPostponed($questionId);
			$this->testSequence->saveToDb();
		}

		global $ilUser;
		$this->object->setQuestionSetSolved(1, $this->testSession->getCurrentQuestionId(), $ilUser->getId());
		
		$this->ctrl->redirect($this, 'showQuestion');
	}

	protected function unmarkQuestionCmd()
	{
		$questionId = $this->testSession->getCurrentQuestionId();

		if( $questionId && !$this->isParticipantsAnswerFixed($questionId) )
		{
			$this->updateWorkingTime();
			$this->saveQuestionSolution();
			$this->persistQuestionAnswerStatus();

			$this->testSequence->unsetQuestionPostponed($questionId);
			$this->testSequence->saveToDb();
		}

		global $ilUser;
		$this->object->setQuestionSetSolved(0, $this->testSession->getCurrentQuestionId(), $ilUser->getId());
		
		$this->ctrl->redirect($this, 'showQuestion');
	}
	
	protected function gotoQuestionCmd()
	{
		$this->testSequence->loadQuestions(
				$this->dynamicQuestionSetConfig, $this->testSession->getQuestionSetFilterSelection()
		);
		
		$this->testSequence->cleanupQuestions($this->testSession);
		
		if( isset($_GET['sequence']) && (int)$_GET['sequence'] )
		{
			$this->testSession->setCurrentQuestionId( (int)$_GET['sequence'] );
			$this->testSession->saveToDb();
			
			$this->ctrl->setParameter(
					$this, 'sequence', $this->testSession->getCurrentQuestionId()
			);
		}
		
		$this->ctrl->redirect($this, 'showQuestion');
	}
	
	protected function showQuestionCmd()
	{
		$this->handleJavascriptActivationStatus();

		$this->testSequence->loadQuestions(
				$this->dynamicQuestionSetConfig, $this->testSession->getQuestionSetFilterSelection()
		);
		
		$this->testSequence->cleanupQuestions($this->testSession);
		
		if( !$this->testSession->getCurrentQuestionId() )
		{
			$upComingQuestionId = $this->testSequence->getUpcomingQuestionId();
			
			$this->testSession->setCurrentQuestionId($upComingQuestionId);
			
			if( $this->testSequence->isQuestionChecked($upComingQuestionId) )
			{
				$this->testSequence->setQuestionUnchecked($upComingQuestionId);
			}
		}
		
		if( $this->testSession->getCurrentQuestionId() )
		{
			$this->ctrl->setParameter(
					$this, 'sequence', $this->testSession->getCurrentQuestionId()
			);

			$this->outTestPage(false);
		}
		else
		{
			$this->outCurrentlyFinishedPage();
		}
		
		$this->testSequence->saveToDb();
		$this->testSession->saveToDb();
	}
	
	protected function showInstantResponseCmd()
	{
		$questionId = $this->testSession->getCurrentQuestionId();

		if( $questionId && !$this->isParticipantsAnswerFixed($questionId) )
		{
			$this->updateWorkingTime();
			$this->saveQuestionSolution();
			$this->persistQuestionAnswerStatus();

			$this->testSequence->unsetQuestionPostponed($questionId);
			$this->testSequence->setQuestionChecked($questionId);
		}

		$this->handleJavascriptActivationStatus();

		$filterSelection = $this->testSession->getQuestionSetFilterSelection();
		
		$filterSelection->setForcedQuestionIds(array($this->testSession->getCurrentQuestionId()));
		
		$this->testSequence->loadQuestions($this->dynamicQuestionSetConfig, $filterSelection);
		
		$this->testSequence->cleanupQuestions($this->testSession);
		
		$this->ctrl->setParameter(
				$this, 'sequence', $this->testSession->getCurrentQuestionId()
		);

		$this->outTestPage(true);
		
		$this->testSequence->saveToDb();
		$this->testSession->saveToDb();
	}
	
	protected function handleQuestionActionCmd()
	{
		$questionId = $this->testSession->getCurrentQuestionId();

		if( $questionId && !$this->isParticipantsAnswerFixed($questionId) )
		{
			$this->updateWorkingTime();
			$this->saveQuestionSolution();
			$this->persistQuestionAnswerStatus();

			$this->testSequence->unsetQuestionPostponed($questionId);
			$this->testSequence->saveToDb();
		}

		$this->ctrl->setParameter(
				$this, 'sequence', $this->testSession->getCurrentQuestionId()
		);
		
		$this->ctrl->redirect($this, 'showQuestion');
	}
	
	/**
	 * Creates the learners output of a question
	 */
	protected function outWorkingForm($sequence = "", $test_id, $postpone_allowed, $directfeedback = false)
	{
		global $ilUser;
		
		$_SESSION["active_time_id"] = $this->object->startWorkingTime(
				$this->testSession->getActiveId(), $this->testSession->getPass()
		);

		$this->populateContentStyleBlock();
		$this->populateSyntaxStyleBlock();

		$question_gui = $this->object->createQuestionGUI(
				"", $this->testSession->getCurrentQuestionId()
		);

		if( !is_object($question_gui) )
		{
			global $ilLog;

			$ilLog->write(
				"INV SEQ: active={$this->testSession->getActiveId()} qId={$this->testSession->getCurrentQuestionId()} "
				.serialize($this->testSequence)
			);

			$ilLog->logStack('INV SEQ');

			$this->resetCurrentQuestion();
			$this->ctrl->redirect($this, 'showQuestion');
		}

		$question_gui->setTargetGui($this);
		
		$question_gui->setQuestionCount(
				$this->testSequence->getLastPositionIndex()
		);
		$question_gui->setSequenceNumber( $this->testSequence->getCurrentPositionIndex(
				$this->testSession->getCurrentQuestionId()
		));
		
		$this->ctrl->setParameter($this, 'sequence', $this->testSession->getCurrentQuestionId());		
		
		if ($this->object->getJavaScriptOutput())
		{
			$question_gui->object->setOutputType(OUTPUT_JAVASCRIPT);
		}

		$is_postponed = $this->testSequence->isPostponedQuestion($question_gui->object->getId());
		$formaction = $this->ctrl->getFormAction($this);

		// output question
		$user_post_solution = FALSE;
		if( isset($_SESSION['previouspost']) )
		{
			$user_post_solution = $_SESSION['previouspost'];
			unset($_SESSION['previouspost']);
		}

		global $ilNavigationHistory;
		$ilNavigationHistory->addItem($_GET["ref_id"], $this->ctrl->getLinkTarget($this, "resumePlayer"), "tst");

		// Determine $answer_feedback: It should hold a boolean stating if answer-specific-feedback is to be given.
		// It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Answer-Specific Feedback"
		// $directfeedback holds a boolean stating if the instant feedback was requested using the "Check" button.
		$answer_feedback = FALSE;
		if (($directfeedback) && ($this->object->getSpecificAnswerFeedback()))
		{
			$answer_feedback = TRUE;
		}

		if( $this->isParticipantsAnswerFixed($this->testSession->getCurrentQuestionId()) )
		{
			$solutionoutput = $question_gui->getSolutionOutput(
				$this->testSession->getActiveId(), 	#active_id
				NULL, 												#pass
				FALSE, 												#graphical_output
				false,				#result_output
				true, 												#show_question_only
				$answer_feedback,									#show_feedback
				false, 												#show_correct_solution
				FALSE, 												#show_manual_scoring
				true												#show_question_text
			);

			$pageoutput = $question_gui->outQuestionPage(
				"", $this->testSequence->isPostponedQuestion($this->testSession->getCurrentQuestionId()),
				$this->testSession->getActiveId(),
				$solutionoutput
			);
			
			$this->tpl->setVariable("QUESTION_OUTPUT", $pageoutput);
			$this->tpl->setVariable("FORMACTION", $formaction);

			$directfeedback = true;
		}
		else
		{
			// Answer specific feedback is rendered into the display of the test question with in the concrete question types outQuestionForTest-method.
			// Notation of the params prior to getting rid of this crap in favor of a class
			$question_gui->outQuestionForTest(
					$formaction, 										#form_action
					$this->testSession->getActiveId(), 	#active_id
					NULL, 												#pass
					$is_postponed, 										#is_postponed
					$user_post_solution, 								#user_post_solution
					$answer_feedback									#answer_feedback == inline_specific_feedback
				);
			// The display of specific inline feedback and specific feedback in an own block is to honor questions, which
			// have the possibility to embed the specific feedback into their output while maintaining compatibility to
			// questions, which do not have such facilities. E.g. there can be no "specific inline feedback" for essay
			// questions, while the multiple-choice questions do well.

			$this->fillQuestionRelatedNavigation($question_gui);
		}

		if ($directfeedback)
		{
			// This controls if the solution should be shown.
			// It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Solutions"			
			if ($this->object->getInstantFeedbackSolution())
			{
				$show_question_inline_score = $this->determineInlineScoreDisplay();
				
				// Notation of the params prior to getting rid of this crap in favor of a class
				$solutionoutput = $question_gui->getSolutionOutput(
					$this->testSession->getActiveId(), 	#active_id
					NULL, 												#pass
					TRUE, 												#graphical_output
					$show_question_inline_score,						#result_output
					FALSE, 												#show_question_only
					FALSE,												#show_feedback
					TRUE, 												#show_correct_solution
					FALSE, 												#show_manual_scoring
					FALSE												#show_question_text
				);
				$this->populateSolutionBlock( $solutionoutput );
			}
			
			// This controls if the score should be shown.
			// It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Results (Only Points)"				
			if ($this->object->getAnswerFeedbackPoints())
			{
				$reachedPoints = $question_gui->object->getAdjustedReachedPoints($this->testSession->getActiveId(), NULL);
				$maxPoints = $question_gui->object->getMaximumPoints();

				$this->populateScoreBlock( $reachedPoints, $maxPoints );
			}
			
			// This controls if the generic feedback should be shown.
			// It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Solutions"				
			if ($this->object->getGenericAnswerFeedback())
			{
				$this->populateGenericFeedbackBlock( $question_gui );
			}
			
			// This controls if the specific feedback should be shown.
			// It gets the parameter "Scoring and Results" -> "Instant Feedback" -> "Show Answer-Specific Feedback"
			if ($this->object->getSpecificAnswerFeedback())
			{
				$this->populateSpecificFeedbackBlock( $question_gui );				
			}
		}

		$this->populatePreviousButtons( $this->testSession->getCurrentQuestionId() );

		if( $postpone_allowed )
		{
			$this->populatePostponeButtons();
		}

		if ($this->object->getShowCancel()) 
		{
			$this->populateCancelButtonBlock();
		}		

		if ($this->isLastQuestionInSequence( $question_gui ))
		{
			if ($this->object->getListOfQuestionsEnd()) 
			{
				$this->populateNextButtonsLeadingToSummary();				
			} 
			else 
			{
				$this->populateNextButtonsLeadingToEndOfTest();
			}
		}
		else
		{
			$this->populateNextButtonsLeadingToQuestion();
		}
		
		if( $this->dynamicQuestionSetConfig->isAnyQuestionFilterEnabled() )
		{
			$this->populateQuestionSelectionButtons();
		}
		
		if ($this->object->getShowMarker())
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$solved_array = ilObjTest::_getSolvedQuestions($this->testSession->getActiveId(), $question_gui->object->getId());
			$solved = 0;
			
			if (count ($solved_array) > 0) 
			{
				$solved = array_pop($solved_array);
				$solved = $solved["solved"];
			}
			
			if ($solved==1) 
			{
				$this->populateQuestionMarkingBlockAsMarked();
			} 
			else 
			{
				$this->populateQuestionMarkingBlockAsUnmarked();
			}
		}
		
		$this->populateCharSelector();

		if ($this->object->getJavaScriptOutput())
		{
			$this->tpl->setVariable("JAVASCRIPT_TITLE", $this->lng->txt("disable_javascript"));
			$this->ctrl->setParameter($this, "tst_javascript", "0");
			$this->tpl->setVariable("JAVASCRIPT_URL", $this->ctrl->getLinkTarget($this, "gotoQuestion"));
		}
		else
		{
			$this->tpl->setVariable("JAVASCRIPT_TITLE", $this->lng->txt("enable_javascript"));
			$this->ctrl->setParameter($this, "tst_javascript", "1");
			$this->tpl->setVariable("JAVASCRIPT_URL", $this->ctrl->getLinkTarget($this, "gotoQuestion"));
		}

		if ($question_gui->object->supportsJavascriptOutput())
		{
			$this->tpl->touchBlock("jsswitch");
		}

		$this->tpl->addJavaScript(ilUtil::getJSLocation("autosave.js", "Modules/Test"));
		
		$this->tpl->setVariable("AUTOSAVE_URL", $this->ctrl->getFormAction($this, "autosave", "", true));

		if ($question_gui->isAutosaveable()&& $this->object->getAutosave())
		{
			$this->tpl->touchBlock('autosave');
			//$this->tpl->setVariable("BTN_SAVE", "Zwischenspeichern");
			//$this->tpl->setVariable("CMD_SAVE", "gotoquestion_{$sequence}");
			//$this->tpl->setVariable("AUTOSAVEFORMACTION", str_replace("&amp;", "&", $this->ctrl->getFormAction($this)));
			$this->tpl->setVariable("AUTOSAVEFORMACTION", str_replace("&amp;", "&", $this->ctrl->getLinkTarget($this, "autosave")));
			$this->tpl->setVariable("AUTOSAVEINTERVAL", $this->object->getAutosaveIval());
		}
		
		if( $this->object->areObligationsEnabled() && ilObjTest::isQuestionObligatory($question_gui->object->getId()) )
		{
		    $this->tpl->touchBlock('question_obligatory');
		    $this->tpl->setVariable('QUESTION_OBLIGATORY', $this->lng->txt('required_field'));
		}
	}

	private function outCurrentlyFinishedPage()
	{
		$this->prepareTestPageOutput();
		
		$this->populatePreviousButtons( $this->testSession->getCurrentQuestionId() );
			
		if ($this->object->getKioskMode())
		{
			$this->populateKioskHead();
		}

		if ($this->object->getEnableProcessingTime())
		{
			$this->outProcessingTime($this->testSession->getActiveId());
		}

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("FORM_TIMESTAMP", time());
		
		$this->tpl->setVariable("PAGETITLE", "- " . $this->object->getTitle());
		
		if ($this->object->isShowExamIdInTestPassEnabled() && !$this->object->getKioskMode())
		{
			$this->tpl->setCurrentBlock('exam_id');
			$this->tpl->setVariable('EXAM_ID', ilObjTest::lookupExamId(
				$this->testSession->getActiveId(), $this->testSession->getPass(), $this->object->getId()
			));
			$this->tpl->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
			$this->tpl->parseCurrentBlock();
		}
		
		if ($this->object->getShowCancel()) 
		{
			$this->populateCancelButtonBlock();
		}
		
		if( $this->dynamicQuestionSetConfig->isAnyQuestionFilterEnabled() )
		{
			$this->populateQuestionSelectionButtons();
		}
		
		if( $this->testSequence->openQuestionExists() )
		{
			$message = $this->lng->txt('tst_dyn_test_msg_currently_finished_selection');
		}
		else
		{
			$message = $this->lng->txt('tst_dyn_test_msg_currently_finished_completely');
			$message .= "<br /><br />{$this->buildFinishPagePassDeletionLink()}";
		}
		
		$msgHtml = $this->tpl->getMessageHTML($message);
		
		$this->tpl->addBlockFile(
				'QUESTION_OUTPUT', 'test_currently_finished_msg_block',
				'tpl.test_currently_finished_msg.html', 'Modules/Test'
		);
		
		$this->tpl->setCurrentBlock('test_currently_finished_msg_block');
		$this->tpl->setVariable('TEST_CURRENTLY_FINISHED_MSG', $msgHtml);
		$this->tpl->parseCurrentBlock();

	}
	
	protected function isFirstPageInSequence($sequence)
	{
		return !$this->testSequence->trackedQuestionExists();
	}

	protected function isLastQuestionInSequence(assQuestionGUI $questionGUI)
	{
		return false; // always
	}
	
	protected function handleJavascriptActivationStatus()
	{
		global $ilUser;
		
		if( isset($_GET['tst_javascript']) )
		{
			$ilUser->writePref('tst_javascript', $_GET['tst_javascript']);
		}
	}
	
	/**
	 * Returns TRUE if the answers of the current user could be saved
	 *
	 * @return boolean TRUE if the answers could be saved, FALSE otherwise
	 */
	 protected function canSaveResult() 
	 {
		 return !$this->object->endingTimeReached();
	 }
	 
	/**
	 * saves the user input of a question
	 */
	public function saveQuestionSolution($force = FALSE)
	{
		// what is this formtimestamp ??
		if (!$force)
		{
			$formtimestamp = $_POST["formtimestamp"];
			if (strlen($formtimestamp) == 0) $formtimestamp = $_GET["formtimestamp"];
			if ($formtimestamp != $_SESSION["formtimestamp"])
			{
				$_SESSION["formtimestamp"] = $formtimestamp;
			}
			else
			{
				return FALSE;
			}
		}
		
		// determine current question
		
		$qId = $this->testSession->getCurrentQuestionId();
		
		if( !$qId || $qId != $_GET["sequence"])
		{
			return false;
		}
		
		// save question solution
		
		$this->saveResult = FALSE;

		if ($this->canSaveResult($qId) || $force)
		{
				global $ilUser;
				
				$questionGUI = $this->object->createQuestionGUI("", $qId);
				
				if( $this->object->getJavaScriptOutput() )
				{
					$questionGUI->object->setOutputType(OUTPUT_JAVASCRIPT);
				}
				
				$activeId = $this->testSession->getActiveId();
				
				$this->saveResult = $questionGUI->object->persistWorkingState(
						$activeId, $pass = null, $this->object->areObligationsEnabled()
				);
		}
		
		if ($this->saveResult == FALSE)
		{
			$this->ctrl->setParameter($this, "save_error", "1");
			$_SESSION["previouspost"] = $_POST;
		}
		
		return $this->saveResult;
	}
	
	private function isQuestionAnsweredCorrect($questionId, $activeId, $pass)
	{
		$questionGUI = $this->object->createQuestionGUI("", $questionId);

		$reachedPoints = assQuestion::_getReachedPoints($activeId, $questionId, $pass);
		$maxPoints = $questionGUI->object->getMaximumPoints();
		
		if($reachedPoints < $maxPoints)
		{
			return false;
		}
		
		return true;
	}


	protected function populatePreviousButtons($sequence)
	{
		if( !$this->dynamicQuestionSetConfig->isPreviousQuestionsListEnabled() )
		{
			return;
		}
		
		if( $this->isFirstPageInSequence($sequence) )
		{
			return;
		}
		
		$this->populateUpperPreviousButtonBlock(
				'showTrackedQuestionList', "&lt;&lt; " . $this->lng->txt( "save_previous" )
		);
		$this->populateLowerPreviousButtonBlock(
				'showTrackedQuestionList', "&lt;&lt; " . $this->lng->txt( "save_previous" )
		);
	}
	
	protected function buildQuestionsTableDataArray($questions, $marked_questions)
	{
		$data = array();
		
		foreach($questions as $key => $value )
		{
			$this->ctrl->setParameter($this, 'sequence', $value['question_id']);
			$href = $this->ctrl->getLinkTarget($this, 'gotoQuestion');
			$this->ctrl->setParameter($this, 'sequence', '');
			
			$description = "";
			if( $this->object->getListOfQuestionsDescription() )
			{
				$description = $value["description"];
			}
			
			$marked = false;
			if( count($marked_questions) )
			{
				if( isset($marked_questions[$value["question_id"]]) )
				{
					if( $marked_questions[$value["question_id"]]["solved"] == 1 )
					{
						$marked = true;
					}
				} 
			}
			
			array_push($data, array(
				'href' => $href,
				'title' => $this->object->getQuestionTitle($value["title"]),
				'description' => $description,
				'worked_through' => $this->testSequence->isAnsweredQuestion($value["question_id"]),
				'postponed' => $this->testSequence->isPostponedQuestion($value["question_id"]),
				'marked' => $marked
			));
		}
		
		return $data;
	}

	protected function buildQuestionSetAnswerStatisticRowArray($questions, $marked_questions)
	{
		$questionAnswerStats = array(
			'total_all' => count($questions),
			'total_open' => 0,
			'non_answered' => 0,
			'wrong_answered' => 0,
			'correct_answered' => 0,
			'postponed' => 0,
			'marked' => 0
		);

		foreach($questions as $key => $value )
		{
			switch( $value['question_answer_status'] )
			{
				case ilAssQuestionList::QUESTION_ANSWER_STATUS_NON_ANSWERED:
					$questionAnswerStats['non_answered']++;
					$questionAnswerStats['total_open']++;
					break;
				case ilAssQuestionList::QUESTION_ANSWER_STATUS_WRONG_ANSWERED:
					$questionAnswerStats['wrong_answered']++;
					$questionAnswerStats['total_open']++;
					break;
				case ilAssQuestionList::QUESTION_ANSWER_STATUS_CORRECT_ANSWERED:
					$questionAnswerStats['correct_answered']++;
					break;
			}

			if( $this->testSequence->isPostponedQuestion($value["question_id"]) )
			{
				$questionAnswerStats['postponed']++;
			}

			if( isset($marked_questions[$value["question_id"]]) )
			{
				if( $marked_questions[$value["question_id"]]["solved"] == 1 )
				{
					$questionAnswerStats['marked']++;
				}
			}
		}

		return $questionAnswerStats;
	}

	private function buildQuestionSetCompleteStatisticTableGUI()
	{
		require_once 'Modules/Test/classes/tables/class.ilTestDynamicQuestionSetStatisticTableGUI.php';
		$gui = $this->buildQuestionSetStatisticTableGUI(
			ilTestDynamicQuestionSetStatisticTableGUI::COMPLETE_TABLE_ID
		);

		$gui->initTitle('tst_dynamic_question_set_complete');
		$gui->initColumns('tst_num_all_questions');

		return $gui;
	}
	
	private function buildQuestionSetFilteredStatisticTableGUI()
	{
		require_once 'Modules/Test/classes/tables/class.ilTestDynamicQuestionSetStatisticTableGUI.php';
		$gui = $this->buildQuestionSetStatisticTableGUI(
			ilTestDynamicQuestionSetStatisticTableGUI::FILTERED_TABLE_ID
		);

		$gui->initTitle('tst_dynamic_question_set_selection');
		$gui->initColumns('tst_num_selected_questions');

		require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
		$gui->setTaxIds(ilObjTaxonomy::getUsageOfObject(
			$this->dynamicQuestionSetConfig->getSourceQuestionPoolId()
		));

		$gui->setTaxonomyFilterEnabled($this->dynamicQuestionSetConfig->isTaxonomyFilterEnabled());
		$gui->setAnswerStatusFilterEnabled($this->dynamicQuestionSetConfig->isAnswerStatusFilterEnabled());

		$gui->initFilter();
		$gui->setFilterCommand('filterQuestionSelection');
		$gui->setResetCommand('resetQuestionSelection');
		
		return $gui;
	}
		
	private function buildQuestionSetStatisticTableGUI($tableId)
	{
		require_once 'Modules/Test/classes/tables/class.ilTestDynamicQuestionSetStatisticTableGUI.php';
		$gui = new ilTestDynamicQuestionSetStatisticTableGUI(
				$this->ctrl, $this->lng, $this, 'showQuestionSelection', $tableId
		);
		
		$gui->setShowNumMarkedQuestionsEnabled($this->object->getShowMarker());
		$gui->setShowNumPostponedQuestionsEnabled($this->object->getSequenceSettings());

		return $gui;
	}
	
	private function getEnterTestButtonLangVar()
	{
		if( $this->testSequence->trackedQuestionExists() )
		{
			return $this->lng->txt('tst_resume_dyn_test_with_cur_quest_sel');
		}
		
		return $this->lng->txt('tst_start_dyn_test_with_cur_quest_sel');
	}

	protected function persistQuestionAnswerStatus()
	{
		$questionId = $this->testSession->getCurrentQuestionId();
		$activeId = $this->testSession->getActiveId();
		$pass = $this->testSession->getPass();

		if($this->isQuestionAnsweredCorrect($questionId, $activeId, $pass))
		{
			$this->testSequence->setQuestionAnsweredCorrect($questionId);
		}
		else
		{
			$this->testSequence->setQuestionAnsweredWrong($questionId);
		}
	}

	private function resetCurrentQuestion()
	{
		$this->testSession->setCurrentQuestionId(null);

		$this->testSequence->saveToDb();
		$this->testSession->saveToDb();

		$this->ctrl->setParameter($this, 'sequence', $this->testSession->getCurrentQuestionId());
	}

	/**
	 * @return string
	 */
	private function buildFinishPagePassDeletionLink()
	{
		$href = $this->getPassDeletionTarget();

		$label = $this->lng->txt('tst_dyn_test_msg_pass_deletion_link');

		return "<a href=\"{$href}\">{$label}</a>";
	}

	/**
	 * @return string
	 */
	private function getPassDeletionTarget()
	{
		require_once 'Modules/Test/classes/confirmations/class.ilTestPassDeletionConfirmationGUI.php';
		
		$this->ctrl->setParameterByClass('ilTestEvaluationGUI', 'context', ilTestPassDeletionConfirmationGUI::CONTEXT_DYN_TEST_PLAYER);
		$this->ctrl->setParameterByClass('ilTestEvaluationGUI', 'active_id', $this->testSession->getActiveId());
		$this->ctrl->setParameterByClass('ilTestEvaluationGUI', 'pass', $this->testSession->getPass());

		return $this->ctrl->getLinkTargetByClass('ilTestEvaluationGUI', 'confirmDeletePass');
	}
}
