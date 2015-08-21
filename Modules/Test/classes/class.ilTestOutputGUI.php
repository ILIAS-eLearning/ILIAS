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
	 * Execute Command
	 */
	public function executeCommand()
	{
		global $ilDB, $ilPluginAdmin, $lng, $ilTabs;

		$this->checkReadAccess();

		$ilTabs->clearTargets();
		
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		$this->ctrl->saveParameter($this, "sequence");
		$this->ctrl->saveParameter($this, "pmode");
		$this->ctrl->saveParameter($this, "active_id");

		$testSessionFactory = new ilTestSessionFactory($this->object);
		$this->testSession = $testSessionFactory->getSession($_GET['active_id']);
		
		$this->ensureExistingTestSession($this->testSession);
		
		$this->initProcessLocker($this->testSession->getActiveId());
		
		$testSequenceFactory = new ilTestSequenceFactory($ilDB, $lng, $ilPluginAdmin, $this->object);
		$this->testSequence = $testSequenceFactory->getSequence($this->testSession);
		$this->testSequence->loadFromDb();
		$this->testSequence->loadQuestions();

		include_once 'Services/jQuery/classes/class.iljQueryUtil.php';
		iljQueryUtil::initjQuery();
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initConnectionWithAnimation();
		
		$this->handlePasswordProtectionRedirect();
		
		$cmd = $this->getCommand($cmd);
		
		switch($next_class)
		{
			case 'ilassquestionpagegui':

				$this->checkTestExecutable();
				
				$questionId = $this->testSequence->getQuestionForSequence( $this->calculateSequence() );

				require_once "./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php";
				$page_gui = new ilAssQuestionPageGUI($questionId);
				$ret = $this->ctrl->forwardCommand($page_gui);
				break;

			case 'iltestsubmissionreviewgui':

				$this->checkTestExecutable();

				require_once './Modules/Test/classes/class.ilTestSubmissionReviewGUI.php';
				$gui = new ilTestSubmissionReviewGUI($this, $this->object, $this->testSession);
				$ret = $this->ctrl->forwardCommand($gui);
				break;

			case 'ilassquestionhintrequestgui':

				$this->checkTestExecutable();

				$questionGUI = $this->object->createQuestionGUI(
					"", $this->testSequence->getQuestionForSequence( $this->calculateSequence() )
				);

				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintTracking.php';
				$questionHintTracking = new ilAssQuestionHintTracking(
					$questionGUI->object->getId(), $this->testSession->getActiveId(), $this->testSession->getPass()
				);

				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';
				$gui = new ilAssQuestionHintRequestGUI($this, 'redirectQuestion', $questionGUI, $questionHintTracking);

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

				if( $this->isTestExecutionCommand($cmd) )
				{
					$this->checkTestExecutable();
				}

				$cmd .= 'Cmd';
				$ret =& $this->$cmd();
				break;
		}
		return $ret;
	}

	protected function isTestExecutionCommand($cmd)
	{
		return true;
	}

	protected function startTestCmd()
	{
		global $ilUser;

		$_SESSION['tst_pass_finish'] = 0;

		// ensure existing test session
		$this->testSession->setUserId($ilUser->getId());
		$this->testSession->setAnonymousId($_SESSION["tst_access_code"][$this->object->getTestId()]);
		$this->testSession->setObjectiveOrientedContainerId($this->getObjectiveOrientedContainerId());
		$this->testSession->saveToDb();

		$active_id = $this->testSession->getActiveId();
		$this->ctrl->setParameter($this, "active_id", $active_id);

		$shuffle = $this->object->getShuffleQuestions();
		if ($this->object->isRandomTest())
		{
			$this->generateRandomTestPassForActiveUser();

			$this->object->loadQuestions();
			$shuffle = FALSE; // shuffle is already done during the creation of the random questions
		}

		assQuestion::_updateTestPassResults(
			$active_id, $this->testSession->getPass(), $this->object->areObligationsEnabled(), null, $this->object->id
		);

		// ensure existing test sequence
		if( !$this->testSequence->hasSequence() )
		{
			$this->testSequence->createNewSequence($this->object->getQuestionCount(), $shuffle);
			$this->testSequence->saveToDb();
		}

		if( $this->testSession->isObjectiveOriented() )
		{
			$this->testSequence->loadFromDb();
			$this->testSequence->loadQuestions();

			$this->filterTestSequenceByObjectives(
				$this->testSession, $this->testSequence
			);
		}

		$active_time_id = $this->object->startWorkingTime(
			$this->testSession->getActiveId(), $this->testSession->getPass()
		);
		$_SESSION["active_time_id"] = $active_time_id;

		$this->ctrl->setParameter($this, 'sequence', $this->testSequence->getFirstSequence());
		$this->ctrl->setParameter($this, 'pmode', $this->getDefaultPresentationMode());

		if ($this->object->getListOfQuestionsStart())
		{
			$this->ctrl->redirect($this, ilTestPlayerCommands::QUESTION_SUMMARY);
		}

		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	/**
	 * Called when a user answered a question to perform a redirect after POST.
	 * This is called for security reasons to prevent users sending a form twice.
	 * --> description up to date ??
	 *
	 * @access public
	 */
	protected function redirectQuestionCmd()
	{
		global $ilUser;

		switch ($_GET["activecommand"])
		{
			case "next":
				break;
			case "previous":
				break;
			case "postpone":
				$this->sequence = $this->calculateSequence();
				$nextSequence = $this->testSequence->getNextSequence($this->sequence);
				$this->testSequence->postponeSequence($this->sequence);
				$this->testSequence->saveToDb();
				$this->testSession->setLastSequence($nextSequence);
				$this->testSession->saveToDb();
				$this->sequence = $nextSequence;
				$this->outTestPage(false);
				break;
			case "setmarked":
				break;
			case "resetmarked":
				break;
			case "directfeedback":
				break;
			case "handleQuestionAction":
				$this->sequence = $this->calculateSequence();
				$this->testSession->setLastSequence($this->sequence);
				$this->testSession->saveToDb();
				$this->outTestPage(false);
				break;
			case "summary":
				$this->ctrl->redirect($this, "outQuestionSummary");
				break;
			case "summary_obligations":
				$this->ctrl->redirect($this, "outQuestionSummaryWithObligationsInfo");
				break;
			case "summary_obligations_only":
				$this->ctrl->redirect($this, "outObligationsOnlySummary");
				break;
			case "start":
				break;
			case "resume":
				break;
				
			case 'test_submission_overview':
				require_once './Modules/Test/classes/class.ilTestSubmissionReviewGUI.php';
				$this->ctrl->redirectByClass('ilTestSubmissionReviewGUI', "show");
				break;
			
			case "back":
			case "gotoquestion":
			default:
				break;
		}
	}
	
	private function isValidSequenceElement($sequenceElement)
	{
		if( $sequenceElement === false )
		{
			return false;
		}
		
		if( $sequenceElement < 1 )
		{
			return false;
		}
		
		if( !$this->testSequence->getPositionOfSequence($sequenceElement) )
		{
			return false;
		}
		
		return true;
	}
	
	protected function showQuestionCmd()
	{
		$_SESSION['tst_pass_finish'] = 0;

		$_SESSION["active_time_id"]= $this->object->startWorkingTime(
			$this->testSession->getActiveId(), $this->testSession->getPass()
		);

		$sequenceElement = $this->getSequenceElementParameter();
		$presentationMode = $this->getPresentationModeParameter();
		$instantResponse = $this->getInstantResponseParameter();

		if( !$this->isValidSequenceElement($sequenceElement) )
		{
			$sequenceElement = $this->testSequence->getFirstSequence();
		}

		$this->testSession->setLastSequence($sequenceElement);
		$this->testSession->setLastPresentationMode($presentationMode);
		$this->testSession->saveToDb();

		$questionId = $this->testSequence->getQuestionForSequence($sequenceElement);

		if( !(int)$questionId && $this->testSession->isObjectiveOriented() )
		{
			ilUtil::sendFailure(
				sprintf($this->lng->txt('tst_objective_oriented_test_pass_without_questions'), $this->object->getTitle()), true
			);
			
			$this->performCustomRedirect();
		}

		if( $this->isParticipantsAnswerFixed($questionId) )
		{
			$presentationMode = ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW;
			$instantResponse = true;
		}
		
		$questionGui = $this->buildQuestionGUI($questionId, $sequenceElement);

		if( !($questionGui instanceof assQuestionGUI) )
		{
			$this->handleTearsAndAngerQuestionIsNull($questionId, $sequenceElement);
		}

		$formAction = $this->ctrl->getFormAction($this);
		$this->prepareTestPage($presentationMode, $sequenceElement, $formAction);

		switch($presentationMode)
		{
			case ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW:

				$this->showQuestionViewable($questionGui);
				break;

			case ilTestPlayerAbstractGUI::PRESENTATION_MODE_EDIT:

				$this->showQuestionEditable($questionGui, $instantResponse, $formAction);
				break;
		}

		if ($instantResponse)
		{
			$this->populateInstantResponseBlocks($questionGui);
		}

		$this->populateQuestionNavigation(
			$sequenceElement, $presentationMode == ilTestPlayerAbstractGUI::PRESENTATION_MODE_EDIT
		);
		$this->populateIntermediateSolutionSaver($questionGui);
		$this->populateObligationIndicatorIfRequired($questionGui);
	}

	protected function showQuestionViewable(assQuestionGUI $questionGui)
	{
		$questionGui->setNavigationGUI($this->buildReadOnlyStateQuestionNavigationGUI(
			$questionGui->object->getId()
		));
		
		$solutionoutput = $questionGui->getSolutionOutput(
			$this->testSession->getActiveId(), 	#active_id
			null, 								#pass
			false, 								#graphical_output
			false,								#result_output
			true, 								#show_question_only
			false,								#show_feedback
			false, 								#show_correct_solution
			false, 								#show_manual_scoring
			true								#show_question_text
		);

		$pageoutput = $questionGui->outQuestionPage(
			"",
			$this->testSequence->isPostponedQuestion($questionGui->object->getId()),
			$this->testSession->getActiveId(),
			$solutionoutput
		);

		$this->tpl->setVariable('QUESTION_OUTPUT', $pageoutput);
	}

	protected function showQuestionEditable(assQuestionGUI $questionGui, $instantResponse, $formAction)
	{
		$questionGui->setNavigationGUI($this->buildEditableStateQuestionNavigationGUI(
			$questionGui->object->getId(), $this->populateCharSelectorIfRequired()
		));

		$isPostponed = $this->testSequence->isPostponedQuestion($questionGui->object->getId());

		$answerFeedbackEnabled = (
			$instantResponse && $this->object->getSpecificAnswerFeedback()
		);

		if( isset($_GET['save_error']) && $_GET['save_error'] == 1 && isset($_SESSION['previouspost']) )
		{
			$userPostSolution = $_SESSION['previouspost'];
			unset($_SESSION['previouspost']);
		}
		else
		{
			$userPostSolution = false;
		}

		// Answer specific feedback is rendered into the display of the test question with in the concrete question types outQuestionForTest-method.
		// Notation of the params prior to getting rid of this crap in favor of a class
		$questionGui->outQuestionForTest(
			$formAction, 							#form_action
			$this->testSession->getActiveId(),		#active_id
			NULL, 									#pass
			$isPostponed, 							#is_postponed
			$userPostSolution, 						#user_post_solution
			$answerFeedbackEnabled					#answer_feedback == inline_specific_feedback
		);
		// The display of specific inline feedback and specific feedback in an own block is to honor questions, which
		// have the possibility to embed the specific feedback into their output while maintaining compatibility to
		// questions, which do not have such facilities. E.g. there can be no "specific inline feedback" for essay
		// questions, while the multiple-choice questions do well.
	}

	protected function editSolutionCmd()
	{
		$this->ctrl->setParameter($this, 'pmode', ilTestPlayerAbstractGUI::PRESENTATION_MODE_EDIT);
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	protected function submitSolutionCmd()
	{
		if( $this->saveQuestionSolution() )
		{
			$this->ctrl->setParameter($this, 'pmode', ilTestPlayerAbstractGUI::PRESENTATION_MODE_VIEW);
		}

		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	protected function discardSolutionCmd()
	{
		$sequenceElement = $this->getSequenceElementParameter();

		$guestionGUI = $this->buildQuestionGUI(
			$this->testSequence->getQuestionForSequence($sequenceElement), $sequenceElement
		);

		$guestionGUI->object->removeExistingSolutions(
			$this->testSession->getActiveId(), $this->testSession->getPass()
		);

		// reset answered state

		$sequenceElement = $this->testSequence->getNextSequence($sequenceElement);

		$this->ctrl->setParameter($this, 'sequence', $sequenceElement);
		$this->ctrl->setParameter($this, 'pmode', $this->getDefaultPresentationMode());

		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	protected function nextQuestionCmd()
	{
		$sequenceElement = $this->testSequence->getNextSequence(
			$this->getSequenceElementParameter()
		);

		$this->ctrl->setParameter($this, 'sequence', $sequenceElement);
		$this->ctrl->setParameter($this, 'pmode', $this->getDefaultPresentationMode());

		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	protected function previousQuestionCmd()
	{
		$sequenceElement = $this->testSequence->getPreviousSequence(
			$this->getSequenceElementParameter()
		);

		$this->ctrl->setParameter($this, 'sequence', $sequenceElement);
		$this->ctrl->setParameter($this, 'pmode', $this->getDefaultPresentationMode());

		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	protected function isFirstPageInSequence($sequenceElement)
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
	 * saves the user input of a question
	 */
	public function saveQuestionSolution($intermediate = false, $force = false)
	{
		$this->updateWorkingTime();
		$this->saveResult = FALSE;
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
		// save question solution
		if ($this->canSaveResult() || $force)
		{
			// but only if the ending time is not reached
			$q_id = $this->testSequence->getQuestionForSequence($_GET["sequence"]);
			if (is_numeric($q_id) && (int)$q_id) 
			{
				$question_gui = $this->object->createQuestionGUI("", $q_id);
				if ($this->object->getJavaScriptOutput())
				{
					$question_gui->object->setOutputType(OUTPUT_JAVASCRIPT);
				}
				$pass = NULL;
				$active_id = $this->testSession->getActiveId();
				if ($this->object->isRandomTest())
				{
					$pass = $this->object->_getPass($active_id);
				}
				$this->saveResult = $question_gui->object->persistWorkingState(
						$active_id, $pass, $this->object->areObligationsEnabled(), $intermediate
				);

				if( !$intermediate && $this->testSession->isObjectiveOriented() )
				{
					$this->updateContainerObjectivesWithAnsweredQuestion(
						$this->testSession, $this->testSequence, $question_gui->object
					);
				}
			}
		}

		if ($this->saveResult == FALSE)
		{
			$this->ctrl->setParameter($this, "save_error", "1");
			$_SESSION["previouspost"] = $_POST;
		}

		return $this->saveResult;
	}

	protected function showInstantResponseCmd()
	{
		$questionId = $this->testSequence->getQuestionForSequence(
			$this->getSequenceElementParameter()
		);
		
		if( !$this->isParticipantsAnswerFixed($questionId) )
		{
			$this->saveQuestionSolution(true);
			
			$this->testSequence->setQuestionChecked($questionId);
			$this->testSequence->saveToDb();
		}
		
		$this->ctrl->setParameter($this, 'instresp', 1);
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	protected function showQuestionListCmd()
	{
		$questionId = $this->testSequence->getQuestionForSequence(
			$this->getSequenceElementParameter()
		);

		if ($this->saveResult == FALSE)
		{
			$this->ctrl->setParameter($this, "activecommand", "");
			$this->ctrl->redirect($this, "redirectQuestion");
		}
		else
		{
			$this->ctrl->setParameter($this, "activecommand", "summary");
			$this->ctrl->redirect($this, "redirectQuestion");
		}
	}

	protected function showQuestionListWithoutSavingCmd()
	{
		$this->ctrl->setParameter($this, "activecommand", "summary");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

	/**
	 * Postpone a question to the end of the test
	 *
	 * @access public
	 */
	protected function postponeQuestionCmd()
	{
		$questionId = $this->testSequence->getQuestionForSequence($_GET["sequence"]);

		if( !$this->isParticipantsAnswerFixed($questionId) )
		{
			$this->saveQuestionSolution();
		}

		$this->ctrl->setParameter($this, "activecommand", "postpone");
		$this->ctrl->redirect($this, "redirectQuestion");
	}

	protected function handleQuestionActionCmd()
	{
		$questionId = $this->testSequence->getQuestionForSequence($_GET["sequence"]);

		if( !$this->isParticipantsAnswerFixed($questionId) )
		{
			$this->updateWorkingTime();
			$this->saveQuestionSolution();
		}

		$this->ctrl->setParameter($this, 'activecommand', 'handleQuestionAction');
		$this->ctrl->redirect($this, 'redirectQuestion');
	}

	protected function performTearsAndAngerBrokenConfessionChecks()
	{
		if ($this->testSession->getActiveId() > 0)
		{
			if ($this->testSequence->hasRandomQuestionsForPass($this->testSession->getActiveId(), $this->testSession->getPass()) > 0)
			{
				// Something went wrong. Maybe the user pressed the start button twice
				// Questions already exist so there is no need to create new questions

				global $ilLog, $ilUser;

				$ilLog->write(
					__METHOD__.' Random Questions allready exists for user '.
					$ilUser->getId().' in test '.$this->object->getTestId()
				);

				return true;
			}
		}
		else
		{
			// This may not happen! If it happens, raise a fatal error...

			global $ilLog, $ilUser;

			$ilLog->write(__METHOD__.' '.sprintf(
				$this->lng->txt("error_random_question_generation"), $ilUser->getId(), $this->object->getTestId()
			));
			
			return true;
		};

		return false;
	}

	protected function generateRandomTestPassForActiveUser()
	{
		global $tree, $ilDB, $ilPluginAdmin;

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetConfig.php';
		$questionSetConfig = new ilTestRandomQuestionSetConfig($tree, $ilDB, $ilPluginAdmin, $this->object);
		$questionSetConfig->loadFromDb();

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
		$sourcePoolDefinitionFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory($ilDB, $this->object);

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
		$sourcePoolDefinitionList = new ilTestRandomQuestionSetSourcePoolDefinitionList($ilDB, $this->object, $sourcePoolDefinitionFactory);
		$sourcePoolDefinitionList->loadDefinitions();

		$this->processLocker->requestRandomPassBuildLock($sourcePoolDefinitionList->hasTaxonomyFilters());
		
		if( !$this->performTearsAndAngerBrokenConfessionChecks() )
		{
			require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolQuestionList.php';
			$stagingPoolQuestionList = new ilTestRandomQuestionSetStagingPoolQuestionList($ilDB, $ilPluginAdmin);

			require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetBuilder.php';
			$questionSetBuilder = ilTestRandomQuestionSetBuilder::getInstance($ilDB, $this->object, $questionSetConfig, $sourcePoolDefinitionList, $stagingPoolQuestionList);

			$questionSetBuilder->performBuild($this->testSession);
		}
		
		$this->processLocker->releaseRandomPassBuildLock();
	}

	protected function getObjectiveOrientedContainerId()
	{
		require_once 'Modules/Course/classes/Objectives/class.ilLOSettings.php';
		
		return (int)ilLOSettings::isObjectiveTest($this->testSession->getRefId());
	}
	
	protected function filterTestSequenceByObjectives(ilTestSession $testSession, ilTestSequence $testSequence)
	{
		require_once 'Modules/Course/classes/Objectives/class.ilLOTestQuestionAdapter.php';
		
		ilLOTestQuestionAdapter::filterQuestions($testSession, $testSequence);
	}
	
	protected function updateContainerObjectivesWithAnsweredQuestion(ilTestSession $testSession, ilTestSequence $testSequence, assQuestion $question)
	{
		require_once 'Modules/Course/classes/Objectives/class.ilLOTestQuestionAdapter.php';

		ilLOTestQuestionAdapter::updateObjectiveStatus($testSession, $testSequence, $question);

		$testSequence->saveToDb();
	}
	
	protected function customRedirectRequired()
	{
		return $this->testSession->isObjectiveOriented();
	}
	
	protected function performCustomRedirect()
	{
		$containerRefId = current(ilObject::_getAllReferences($this->testSession->getObjectiveOrientedContainerId()));
		
		require_once 'Services/Link/classes/class.ilLink.php';
		$redirectTarget = ilLink::_getLink($containerRefId);

		ilUtil::redirect($redirectTarget);
	}

	/**
	 * @param $sequence
	 * @param $questionId
	 * @param $ilLog
	 */
	protected function handleTearsAndAngerQuestionIsNull($questionId, $sequenceElement)
	{
		global $ilLog;

		$ilLog->write("INV SEQ:"
			."active={$this->testSession->getActiveId()} "
			."qId=$questionId seq=$sequenceElement "
			. serialize($this->testSequence)
		);

		$ilLog->logStack('INV SEQ');

		$this->ctrl->setParameter($this, 'sequence', $this->testSequence->getFirstSequence());
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}

	/**
	 * @param $questionId
	 * @param $sequenceElement
	 * @return object
	 */
	protected function buildQuestionGUI($questionId, $sequenceElement)
	{
		$questionGui = $this->object->createQuestionGUI("", $questionId);
		$questionGui->setSequenceNumber($this->testSequence->getPositionOfSequence($sequenceElement));
		$questionGui->setQuestionCount($this->testSequence->getUserQuestionCount());
		$questionGui->setTargetGui($this);
		$questionGui->object->setOutputType(OUTPUT_JAVASCRIPT);
		return $questionGui;
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

		if ($this->object->isRandomTest())
		{
			if (!$this->testSequence->hasRandomQuestionsForPass($active_id, $this->testSession->getPass()))
			{
				// create a new set of random questions
				$this->generateRandomTestPassForActiveUser();
			}
		}

		$shuffle = $this->object->getShuffleQuestions();
		if ($this->object->isRandomTest())
		{
			$shuffle = FALSE;
		}

		assQuestion::_updateTestPassResults(
			$active_id, $this->testSession->getPass(), $this->object->areObligationsEnabled(), null, $this->object->id
		);

		// ensure existing test sequence
		if( !$this->testSequence->hasSequence() )
		{
			$this->testSequence->createNewSequence($this->object->getQuestionCount(), $shuffle);
			$this->testSequence->saveToDb();
		}

		if ($this->object->getListOfQuestionsStart())
		{
			$this->ctrl->redirect($this, ilTestPlayerCommands::QUESTION_SUMMARY);
		}
		
		$this->ctrl->setParameter($this, 'sequence', $this->testSession->getLastSequence());
		$this->ctrl->setParameter($this, 'pmode', $this->testSession->getLastPresentationMode());
		$this->ctrl->redirect($this, ilTestPlayerCommands::SHOW_QUESTION);
	}
}
