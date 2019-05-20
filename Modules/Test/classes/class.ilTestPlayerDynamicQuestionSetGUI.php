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
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$lng = $DIC['lng'];
		$ilPluginAdmin = $DIC['ilPluginAdmin'];
		$ilTabs = $DIC['ilTabs'];
		$tree = $DIC['tree'];

		$ilTabs->clearTargets();
		
		$this->ctrl->saveParameter($this, "sequence");
		$this->ctrl->saveParameter($this, "active_id");

		$this->initAssessmentSettings();

		require_once 'Modules/Test/classes/class.ilObjTestDynamicQuestionSetConfig.php';
		$this->dynamicQuestionSetConfig = new ilObjTestDynamicQuestionSetConfig($tree, $ilDB, $ilPluginAdmin, $this->object);
		$this->dynamicQuestionSetConfig->loadFromDb();

		$testSessionFactory = new ilTestSessionFactory($this->object);
		$this->testSession = $testSessionFactory->getSession($_GET['active_id']);

		$this->ensureExistingTestSession($this->testSession);
		$this->checkTestSessionUser($this->testSession);
		$this->initProcessLocker($this->testSession->getActiveId());
		
		$testSequenceFactory = new ilTestSequenceFactory($ilDB, $lng, $ilPluginAdmin, $this->object);
		$this->testSequence = $testSequenceFactory->getSequenceByTestSession($this->testSession);
		$this->testSequence->loadFromDb();

		if( $this->object->isInstantFeedbackAnswerFixationEnabled() )
		{
			$this->testSequence->setPreventCheckedQuestionsFromComingUpEnabled(true);
		}

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

				$questionId = $this->testSession->getCurrentQuestionId();

				require_once "./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php";
				$page_gui = new ilAssQuestionPageGUI($questionId);
				$ret = $this->ctrl->forwardCommand($page_gui);
				break;

			case 'ilassquestionhintrequestgui':

				$this->ctrl->saveParameter($this, 'pmode');
				
				$questionGUI = $this->object->createQuestionGUI(
					"", $this->testSession->getCurrentQuestionId()
				);

				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintTracking.php';
				$questionHintTracking = new ilAssQuestionHintTracking(
					$questionGUI->object->getId(), $this->testSession->getActiveId(), $this->testSession->getPass()
				);

				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';
				$gui = new ilAssQuestionHintRequestGUI(
					$this, ilTestPlayerCommands::SHOW_QUESTION, $questionGUI, $questionHintTracking
				);
				
// fau: testNav - save the 'answer changed status' for viewing hint requests
				$this->setAnswerChangedParameter($this->getAnswerChangedParameter());
// fau.
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
	 * @return integer
	 */
	protected function getCurrentQuestionId()
	{
		return $this->testSession->getCurrentQuestionId();
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
			$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION_SELECTION);
		}
		
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}
	
	protected function startTestCmd()
	{
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
			$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION_SELECTION);
		}
		
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}
	
	protected function showQuestionSelectionCmd()
	{
		$this->prepareSummaryPage();
		
		$this->testSequence->loadQuestions(
				$this->dynamicQuestionSetConfig, $this->testSession->getQuestionSetFilterSelection()
		);
		
		$this->testSequence->cleanupQuestions($this->testSession);

		$this->testSequence->saveToDb();
			
		require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		$toolbarGUI = new ilToolbarGUI();
		
		require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
		$button = ilLinkButton::getInstance();
		$button->setUrl($this->getStartTestFromQuestionSelectionLink());
		$button->setCaption($this->getEnterTestButtonLangVar());
		$button->setPrimary(true);
		$toolbarGUI->addButtonInstance($button);

		if( $this->object->getShowCancel() )
		{
			$button = ilLinkButton::getInstance();
			$button->setUrl($this->ctrl->getLinkTarget(
				$this, ilTestPlayerCommands::SUSPEND_TEST
			));
			$button->setCaption('cancel_test');
			$toolbarGUI->addButtonInstance($button);
		}
		
		if( $this->object->isPassDeletionAllowed() )
		{
			require_once 'Modules/Test/classes/confirmations/class.ilTestPassDeletionConfirmationGUI.php';
			
			$toolbarGUI->addButton(
				$this->lng->txt('tst_dyn_test_pass_deletion_button'),
				$this->getPassDeletionTarget(ilTestPassDeletionConfirmationGUI::CONTEXT_DYN_TEST_PLAYER)
			);
		}
		
		$filteredData = array($this->buildQuestionSetAnswerStatisticRowArray(
			$this->testSequence->getFilteredQuestionsData(), $this->testSequence->getTrackedQuestionList()
		)); #vd($filteredData);
		$filteredTableGUI = $this->buildQuestionSetFilteredStatisticTableGUI();
		$filteredTableGUI->setData($filteredData);

		$completeData = array($this->buildQuestionSetAnswerStatisticRowArray(
			$this->testSequence->getCompleteQuestionsData(), $this->testSequence->getTrackedQuestionList()
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

		
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION_SELECTION);
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
		
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION_SELECTION);
	}

	protected function previousQuestionCmd()
	{
		// nothing to do, won't be called
	}

	protected function fromPassDeletionCmd()
	{
		$this->resetCurrentQuestion();
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}
	
	protected function nextQuestionCmd()
	{
		$isWorkedThrough = assQuestion::_isWorkedThrough(
			$this->testSession->getActiveId(), $this->testSession->getCurrentQuestionId(), $this->testSession->getPass()
		);

		if( !$isWorkedThrough )
		{
			$this->testSequence->setQuestionPostponed($this->testSession->getCurrentQuestionId());
			$this->testSequence->saveToDb();
		}
		
		$this->resetCurrentQuestion();
		
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}
	
	protected function markQuestionCmd()
	{
		global $DIC;
		$ilUser = $DIC['ilUser'];
		$this->object->setQuestionSetSolved(1, $this->testSession->getCurrentQuestionId(), $ilUser->getId());
		
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	protected function unmarkQuestionCmd()
	{
		global $DIC;
		$ilUser = $DIC['ilUser'];
		$this->object->setQuestionSetSolved(0, $this->testSession->getCurrentQuestionId(), $ilUser->getId());
		
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	protected function editSolutionCmd()
	{
		$this->ctrl->setParameter($this, 'pmode', ilTestPlayerAbstractGUI::PRESENTATION_MODE_EDIT);
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	protected function submitSolutionAndNextCmd()
	{
		if( $this->object->isForceInstantFeedbackEnabled() )
		{
			return $this->submitSolutionCmd();
		}

		if( $this->saveQuestionSolution(true, false) )
		{
			$questionId = $this->testSession->getCurrentQuestionId();

			$this->getQuestionInstance($questionId)->removeIntermediateSolution(
				$this->testSession->getActiveId(), $this->testSession->getPass()
			);

			$this->persistQuestionAnswerStatus();

			$this->ctrl->setParameter($this, 'pmode', '');

			$this->resetCurrentQuestion();
		}

		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}
	
	protected function submitSolutionCmd()
	{
		if( $this->saveQuestionSolution(true, false) )
		{
			$questionId = $this->testSession->getCurrentQuestionId();

			$this->getQuestionInstance($questionId)->removeIntermediateSolution(
				$this->testSession->getActiveId(), $this->testSession->getPass()
			);
			
			$this->persistQuestionAnswerStatus();

			if( $this->object->isForceInstantFeedbackEnabled() )
			{
				$this->ctrl->setParameter($this, 'instresp', 1);

				$this->testSequence->unsetQuestionPostponed($questionId);
				$this->testSequence->setQuestionChecked($questionId);
				$this->testSequence->saveToDb();
			}

			if( $this->getNextCommandParameter() )
			{
				if( $this->getNextSequenceParameter() )
				{
					$this->ctrl->setParameter($this, 'sequence', $this->getNextSequenceParameter());
					$this->ctrl->setParameter($this, 'pmode', '');
				}

				$this->ctrl->redirect($this, $this->getNextCommandParameter());
			}

			$this->ctrl->setParameter($this, 'pmode', ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW);
		}
		else
		{
			$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
		}

// fau: testNav - remember to prevent the navigation confirmation
		$this->saveNavigationPreventConfirmation();
// fau.

// fau: testNav - handle navigation after saving
		if ($this->getNavigationUrlParameter())
		{
			ilUtil::redirect($this->getNavigationUrlParameter());
		}
		else
		{
			$this->ctrl->saveParameter($this, 'sequence');
		}
// fau.
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	protected function discardSolutionCmd()
	{
		$questionId = $this->testSession->getCurrentQuestionId();

		$currentQuestionOBJ = $this->getQuestionInstance($questionId);

		$currentQuestionOBJ->resetUsersAnswer(
			$this->testSession->getActiveId(), $this->testSession->getPass()
		);
		
		$this->ctrl->setParameter($this, 'pmode', ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW);

		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	protected function skipQuestionCmd()
	{
		$this->nextQuestionCmd();
	}

	protected function isCheckedQuestionResettingConfirmationRequired()
	{
		if( !$this->getResetCheckedParameter() )
		{
			return false;
		}
		
		if( $this->testSession->getQuestionSetFilterSelection()->isAnswerStatusSelectionWrongAnswered() )
		{
			$this->testSequence->loadQuestions(
				$this->dynamicQuestionSetConfig, $this->testSession->getQuestionSetFilterSelection()
			);

			if( $this->testSequence->hasFilteredQuestionListCheckedQuestions() )
			{
				return true;
			}
		}

		return false;
	}
	
	protected function showQuestionCmd()
	{
		$this->updateWorkingTime();
		
		$this->testSequence->loadQuestions(
				$this->dynamicQuestionSetConfig, $this->testSession->getQuestionSetFilterSelection()
		);
		
		$this->testSequence->cleanupQuestions($this->testSession);
		
		if( $this->isCheckedQuestionResettingConfirmationRequired() )
		{
			$this->showCheckedQuestionResettingConfirmation();
			return;
		}
		
		if( $this->testSequence->getQuestionSet()->getSelectionQuestionList()->isInList($this->getQuestionIdParameter()) )
		{
			$this->testSession->setCurrentQuestionId($this->getQuestionIdParameter());
		}
		else
		{
			$this->resetQuestionIdParameter();
		}
		
		if( !$this->testSession->getCurrentQuestionId() )
		{
			$upComingQuestionId = $this->testSequence->getUpcomingQuestionId();
			
			$this->testSession->setCurrentQuestionId($upComingQuestionId);
			
			// seems to be a first try of freezing answers not too hard
			/*if( $this->testSequence->isQuestionChecked($upComingQuestionId) )
			{
				$this->testSequence->setQuestionUnchecked($upComingQuestionId);
			}*/
		}

		$navigationToolbarGUI = $this->getTestNavigationToolbarGUI();
		$navigationToolbarGUI->setQuestionSelectionButtonEnabled(true);

		if( $this->testSession->getCurrentQuestionId() )
		{
			$questionGui = $this->getQuestionGuiInstance($this->testSession->getCurrentQuestionId());
			$this->testSequence->setCurrentQuestionId($this->testSession->getCurrentQuestionId());

			$questionGui->setQuestionCount(
				$this->testSequence->getLastPositionIndex()
			);
			$questionGui->setSequenceNumber(
				$this->testSequence->getCurrentPositionIndex($this->testSession->getCurrentQuestionId())
			);

			if( !($questionGui instanceof assQuestionGUI) )
			{
				$this->handleTearsAndAngerQuestionIsNull(
					$this->testSession->getCurrentQuestionId(), $this->testSession->getCurrentQuestionId()
				);
			}

			$isQuestionWorkedThrough = assQuestion::_isWorkedThrough(
				$this->testSession->getActiveId(), $this->testSession->getCurrentQuestionId(), $this->testSession->getPass()
			);

			require_once 'Modules/Test/classes/class.ilTestQuestionHeaderBlockBuilder.php';
			$headerBlockBuilder = new ilTestQuestionHeaderBlockBuilder($this->lng);
			$headerBlockBuilder->setHeaderMode(
				// avoid legacy setting combination: ctm without question titles
				$this->object->getTitleOutput() == 2 ? 1 : $this->object->getTitleOutput()
			);
			$headerBlockBuilder->setQuestionTitle($questionGui->object->getTitle());
			$headerBlockBuilder->setQuestionPoints($questionGui->object->getPoints());
			/* avoid showing Qst X of Y within CTMs
			$headerBlockBuilder->setQuestionPosition(
				$this->testSequence->getCurrentPositionIndex($this->testSession->getCurrentQuestionId())
			);
			$headerBlockBuilder->setQuestionCount($this->testSequence->getLastPositionIndex());
			*/
			$headerBlockBuilder->setQuestionPostponed($this->testSequence->isPostponedQuestion(
				$this->testSession->getCurrentQuestionId())
			);
			$headerBlockBuilder->setQuestionObligatory(
				$this->object->areObligationsEnabled() && ilObjTest::isQuestionObligatory($this->object->getId())
			);
			$questionGui->setQuestionHeaderBlockBuilder($headerBlockBuilder);

// fau: testNav - always use edit mode, except for fixed answer
			if( $this->isParticipantsAnswerFixed($this->testSession->getCurrentQuestionId()) )
			{
				$instantResponse = true;
				$presentationMode = ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW;
			}
			else
			{
				$instantResponse = $this->getInstantResponseParameter();
				$presentationMode = ilTestPlayerAbstractGUI::PRESENTATION_MODE_EDIT;
			}
// fau.

			$this->prepareTestPage($presentationMode,
				$this->testSession->getCurrentQuestionId(), $this->testSession->getCurrentQuestionId()
			);
			
			$this->ctrl->setParameter($this, 'sequence', $this->testSession->getCurrentQuestionId());
			$this->ctrl->setParameter($this, 'pmode', $presentationMode);
			$formAction = $this->ctrl->getFormAction($this, ilTestPlayerCommands::SUBMIT_INTERMEDIATE_SOLUTION);
			
			switch($presentationMode)
			{
				case ilTestPlayerAbstractGUI::PRESENTATION_MODE_EDIT:

// fau: testNav - enable navigation toolbar in edit mode
					$navigationToolbarGUI->setDisabledStateEnabled(false);
// fau.
					$this->showQuestionEditable($questionGui, $formAction, $isQuestionWorkedThrough, $instantResponse);
					
					break;

				case ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW:

					$this->showQuestionViewable($questionGui, $formAction, $isQuestionWorkedThrough, $instantResponse);
					
					break;

				default:

					require_once 'Modules/Test/exceptions/class.ilTestException.php';
					throw new ilTestException('no presentation mode given');
			}
			
			$navigationToolbarGUI->build();
			$this->populateTestNavigationToolbar($navigationToolbarGUI);

// fau: testNav - enable the question navigation in edit mode
			$this->populateQuestionNavigation(
				$this->testSession->getCurrentQuestionId(), false, $this->object->isForceInstantFeedbackEnabled()
			);
// fau.

			if ($instantResponse)
			{
// fau: testNav - always use authorized solution for instant feedback
				$this->populateInstantResponseBlocks(
					$questionGui, true
				);
// fau.
				$this->testSession->getQuestionSetFilterSelection()->setForcedQuestionIds(array());
			}

// fau: testNav - add feedback modal
			if ($this->isForcedFeedbackNavUrlRegistered())
			{
				$this->populateInstantResponseModal($questionGui, $this->getRegisteredForcedFeedbackNavUrl());
				$this->unregisterForcedFeedbackNavUrl();
			}
// fau.
		}
		else
		{
			$this->prepareTestPage(ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW, null, null);

			$navigationToolbarGUI->build();
			$this->populateTestNavigationToolbar($navigationToolbarGUI);
			
			$this->outCurrentlyFinishedPage();
		}
		
		$this->testSequence->saveToDb();
		$this->testSession->saveToDb();
	}
	
	protected function showInstantResponseCmd()
	{
		$questionId = $this->testSession->getCurrentQuestionId();
		
		$filterSelection = $this->testSession->getQuestionSetFilterSelection();

		$filterSelection->setForcedQuestionIds(array($this->testSession->getCurrentQuestionId()));

		$this->testSequence->loadQuestions($this->dynamicQuestionSetConfig, $filterSelection);
		$this->testSequence->cleanupQuestions($this->testSession);
		$this->testSequence->saveToDb();

		if( !$this->isParticipantsAnswerFixed($questionId) )
		{
			if( $this->saveQuestionSolution(true) )
			{
				$this->removeIntermediateSolution();
				$this->persistQuestionAnswerStatus();
				$this->setAnswerChangedParameter(false);
			}
			else
			{
				$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
			}
			$this->testSequence->unsetQuestionPostponed($questionId);
			$this->testSequence->setQuestionChecked($questionId);
			$this->testSequence->saveToDb();
		}

		$this->ctrl->setParameter(
			$this, 'sequence', $this->testSession->getCurrentQuestionId()
		);

		$this->ctrl->setParameter($this, 'instresp', 1);
		
// fau: testNav - handle navigation after feedback
		if ($this->getNavigationUrlParameter())
		{
			$this->saveNavigationPreventConfirmation();
			$this->registerForcedFeedbackNavUrl($this->getNavigationUrlParameter());
		}
// fau.
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}
	
	protected function handleQuestionActionCmd()
	{
		$questionId = $this->testSession->getCurrentQuestionId();

		if( $questionId && !$this->isParticipantsAnswerFixed($questionId) )
		{
			$this->saveQuestionSolution(false);
// fau: testNav - add changed status of the question
			$this->setAnswerChangedParameter(true);
// fau.
		}

		$this->ctrl->setParameter(
				$this, 'sequence', $this->testSession->getCurrentQuestionId()
		);

		$this->ctrl->saveParameter($this, 'pmode');
		
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	private function outCurrentlyFinishedPage()
	{
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
		
		$tpl = new ilTemplate('tpl.test_currently_finished_msg.html', true, true, 'Modules/Test');
		$tpl->setVariable('TEST_CURRENTLY_FINISHED_MSG', $msgHtml);
		
		$this->tpl->setVariable('QUESTION_OUTPUT', $tpl->get());
	}
	
	protected function isFirstQuestionInSequence($sequenceElement)
	{
		return !$this->testSequence->trackedQuestionExists();
	}

	protected function isLastQuestionInSequence($sequenceElement)
	{
		return false; // always
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
	public function saveQuestionSolution($authorized = true, $force = false)
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
				$questionGUI = $this->object->createQuestionGUI("", $qId);
				
				if( $this->object->getJavaScriptOutput() )
				{
					$questionGUI->object->setOutputType(OUTPUT_JAVASCRIPT);
				}
				
				$activeId = $this->testSession->getActiveId();
				
				$this->saveResult = $questionGUI->object->persistWorkingState(
						$activeId, $pass = null, $this->object->areObligationsEnabled(), $authorized
				);
			
				if( $authorized && $this->object->isSkillServiceToBeConsidered() )
				{
					$this->handleSkillTriggering($this->testSession);
				}
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

	protected function buildQuestionSetAnswerStatisticRowArray($questions, $trackedQuestions)
	{
		$questionAnswerStats = array(
			'total_all' => count($questions),
			'total_open' => 0,
			'non_answered_notseen' => 0,
			'non_answered_skipped' => 0,
			'wrong_answered' => 0,
			'correct_answered' => 0
		);

		foreach($questions as $key => $value )
		{
			switch( $value['question_answer_status'] )
			{
				case ilAssQuestionList::QUESTION_ANSWER_STATUS_NON_ANSWERED:
					if( isset($trackedQuestions[$key]) )
					{
						$questionAnswerStats['non_answered_skipped']++;
					}
					else
					{
						$questionAnswerStats['non_answered_notseen']++;
					}
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

		$gui->setFilterSelection($this->testSession->getQuestionSetFilterSelection());
		$gui->initFilter();
		$gui->setFilterCommand('filterQuestionSelection');
		$gui->setResetCommand('resetQuestionSelection');
		
		return $gui;
	}
		
	private function buildQuestionSetStatisticTableGUI($tableId)
	{
		require_once 'Modules/Test/classes/tables/class.ilTestDynamicQuestionSetStatisticTableGUI.php';
		$gui = new ilTestDynamicQuestionSetStatisticTableGUI(
				$this->ctrl, $this->lng, $this, ilTestPlayerCommands::SHOW_QUESTION_SELECTION, $tableId
		);

		return $gui;
	}
	
	private function getEnterTestButtonLangVar()
	{
		if( $this->testSequence->trackedQuestionExists() )
		{
			return 'tst_resume_dyn_test_with_cur_quest_sel';
		}
		
		return 'tst_start_dyn_test_with_cur_quest_sel';
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

		$this->testSequence->saveToDb();
	}

	private function resetCurrentQuestion()
	{
		$this->testSession->setCurrentQuestionId(null);
		$this->testSession->saveToDb();

		$this->ctrl->setParameter($this, 'sequence', $this->testSession->getCurrentQuestionId());
		$this->ctrl->setParameter($this, 'pmode', '');
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
	
	protected function resetQuestionIdParameter()
	{
		$this->resetSequenceElementParameter();
	}
	
	protected function getQuestionIdParameter()
	{
		return $this->getSequenceElementParameter();
	}
	
	protected function getResetCheckedParameter()
	{
		if( isset($_GET['reset_checked']) )
		{
			return $_GET['reset_checked'];
		}

		return null;

	}

	public function outQuestionSummaryCmd($fullpage = true, $contextFinishTest = false, $obligationsNotAnswered = false, $obligationsFilter = false)
	{
		$this->testSequence->loadQuestions(
			$this->dynamicQuestionSetConfig, $this->testSession->getQuestionSetFilterSelection()
		);

		$this->testSequence->setCurrentQuestionId($this->testSession->getCurrentQuestionId());
		
		parent::outQuestionSummaryCmd($fullpage, $contextFinishTest, $obligationsNotAnswered, $obligationsFilter);
	}
	
	protected function showCheckedQuestionResettingConfirmation()
	{
		require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirmation = new ilConfirmationGUI();
		$confirmation->setFormAction($this->ctrl->getFormAction($this));
		$confirmation->setHeaderText($this->lng->txt('tst_dyn_unfreeze_answers_confirmation'));
		$confirmation->setConfirm($this->lng->txt('tst_dyn_unfreeze_answers'), ilTestPlayerCommands::UNFREEZE_ANSWERS);
		$confirmation->setCancel($this->lng->txt('tst_dyn_keep_answ_freeze'), ilTestPlayerCommands::SHOW_QUESTION);

		$this->populateMessageContent($confirmation->getHtml());
	}
	
	protected function unfreezeCheckedQuestionsAnswersCmd()
	{
		$this->testSequence->loadQuestions(
			$this->dynamicQuestionSetConfig, $this->testSession->getQuestionSetFilterSelection()
		);

		$this->testSequence->resetFilteredQuestionListsCheckedStatus();
		$this->testSequence->saveToDb();

		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	protected function populateQuestionNavigation($sequenceElement, $disabled, $primaryNext)
	{
		if( !$this->isLastQuestionInSequence($sequenceElement) )
		{
			$this->populateNextButtons($disabled, $primaryNext);
		}
	}
	
	protected function getStartTestFromQuestionSelectionLink()
	{
		$this->ctrl->setParameter($this, 'reset_checked', 1);		
		$link = $this->ctrl->getLinkTarget($this, ilTestPlayerCommands::SHOW_QUESTION);
		$this->ctrl->setParameter($this, 'reset_checked', '');

		return $link;
	}

	protected function isShowingPostponeStatusReguired($questionId)
	{
		return false;
	}

	protected function buildTestPassQuestionList()
	{
		global $DIC;
		$ilPluginAdmin = $DIC['ilPluginAdmin'];

		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
		$questionList = new ilAssQuestionList($this->db, $this->lng, $ilPluginAdmin);
		$questionList->setParentObjId($this->dynamicQuestionSetConfig->getSourceQuestionPoolId());
		$questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_ORIGINALS);

		return $questionList;
	}

	protected function isQuestionSummaryFinishTestButtonRequired()
	{
		return false;
	}
	
	protected function isOptionalQuestionAnsweringConfirmationRequired($sequenceKey)
	{
		return false;
	}
}
