<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";
include_once 'Modules/Test/classes/class.ilTestService.php';

/**
* Service GUI class for tests. This class is the parent class for all
* service classes which are called from ilObjTestGUI. This is mainly
* done to reduce the size of ilObjTestGUI to put command service functions
* into classes that could be called by ilCtrl.
*
* @ilCtrl_IsCalledBy ilTestServiceGUI: ilObjTestGUI
*
* @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author	Björn Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup ModulesTest
*/
class ilTestServiceGUI
{
	/**
	 * @var ilObjTest
	 */
	public $object = null;
	
	/**
	 * @var ilTestService
	 */
	public $service = null;

	/**
	 * @var ilDB
	 */
	protected $db;

	var $lng;
	var $tpl;
	
	/**
	 * @var ilCtrl
	 */
	var $ctrl;
	
	var $ilias;
	var $tree;
	var $ref_id;
	
	/**
	 * factory for test session
	 *
	 * @var ilTestSessionFactory 
	 */
	protected $testSessionFactory = null;
	
	/**
	 * factory for test session
	 *
	 * @var ilTestSequenceFactory 
	 */
	protected $testSequenceFactory = null;

	/**
	 * @var ilTestParticipantData
	 */
	protected $participantData;
	
	private $contextWithinTestPass = false;

	/**
	 * @return boolean
	 */
	public function isContextWithinTestPass()
	{
		return $this->contextWithinTestPass;
	}

	/**
	 * @param boolean $contextWithinTestPass
	 */
	public function setContextWithinTestPass($contextWithinTestPass)
	{
		$this->contextWithinTestPass = $contextWithinTestPass;
	}
	
	/**
	 * The constructor takes the test object reference as parameter 
	 *
	 * @param object $a_object Associated ilObjTest class
	 * @access public
	 */
	function ilTestServiceGUI(ilObjTest $a_object)
	{
		global $lng, $tpl, $ilCtrl, $ilias, $tree, $ilDB, $ilPluginAdmin;

		$this->db = $ilDB;
		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ilias =& $ilias;
		$this->object =& $a_object;
		$this->tree =& $tree;
		$this->ref_id = $a_object->ref_id;

		$this->service = new ilTestService($a_object);
		
		require_once 'Modules/Test/classes/class.ilTestSessionFactory.php';
		$this->testSessionFactory = new ilTestSessionFactory($this->object);
		
		require_once 'Modules/Test/classes/class.ilTestSequenceFactory.php';
		$this->testSequenceFactory = new ilTestSequenceFactory($this->db, $this->lng, $ilPluginAdmin, $this->object);
	}

	/**
	 * @param \ilTestParticipantData $participantData
	 */
	public function setParticipantData($participantData)
	{
		$this->participantData = $participantData;
	}

	/**
	 * @return \ilTestParticipantData
	 */
	public function getParticipantData()
	{
		return $this->participantData;
	}
	
	/**
	 * execute command
	 */
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->getCommand($cmd);
		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}
		return $ret;
	}

	/**
	 * Retrieves the ilCtrl command
	 *
	 * @access public
	 */
	function getCommand($cmd)
	{
		return $cmd;
	}

	/**
	 * @return bool
	 */
	protected function isPdfDeliveryRequest()
	{
		if( !isset($_GET['pdf']) )
		{
			return false;
		}

		if( !(bool)$_GET['pdf'] )
		{
			return false;
		}

		return true;
	}
	
	/**
	 * Returns the pass overview for a given active ID
	 *
	 * @return string HTML code of the pass overview
	 * @access public
	 * 
	 * @deprecated
	 */
	public function getPassOverview($active_id, $targetclass = "", $targetcommand = "", $short = FALSE, $hide_details = FALSE, $adminMode = false)
	{
		require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
		require_once 'Modules/Test/classes/tables/class.ilTestPassOverviewTableGUI.php';

		$table = new ilTestPassOverviewTableGUI(
			$this,
			'',
			$short ? ilTestPassOverviewTableGUI::CONTEXT_SHORT : ilTestPassOverviewTableGUI::CONTEXT_LONG,
			(isset($_GET['pdf']) && $_GET['pdf'] == 1)
		);
		$data  = array();

		$counted_pass = $this->object->_getResultPass($active_id);
		$reached_pass = $this->object->_getPass($active_id);
		
		require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
		$testPassesSelector = new ilTestPassesSelector($GLOBALS['ilDB'], $this->object);
		$testPassesSelector->setAdminModeEnabled($adminMode);
		$testPassesSelector->setActiveId($active_id);
		$lastFinishedPass = $this->testSessionFactory->getSession($active_id)->getLastFinishedPass();
		$testPassesSelector->setLastFinishedPass($lastFinishedPass);

		foreach($testPassesSelector->getReportablePasses() as $pass)
		{
			$row = array();

				if(!$short)
				{
					$result_array =& $this->object->getTestResult($active_id, $pass);
					if(!$result_array['pass']['total_max_points'])
					{
						$percentage = 0;
					}
					else
					{
						$percentage = ($result_array['pass']['total_reached_points'] / $result_array['pass']['total_max_points']) * 100;
					}
					$total_max             = $result_array['pass']['total_max_points'];
					$total_reached         = $result_array['pass']['total_reached_points'];
					$total_requested_hints = $result_array['pass']['total_requested_hints'];
				}
				if(!$hide_details)
				{
					if(strlen($targetclass) && strlen($targetcommand))
					{
						$this->ctrl->setParameterByClass($targetclass, 'active_id', $active_id);
						$this->ctrl->setParameterByClass($targetclass, 'pass', $pass);

						$aslgui = new ilAdvancedSelectionListGUI();
						$aslgui->setListTitle($this->lng->txt('actions'));
						$aslgui->setId($pass);
						$aslgui->addItem(
							$this->lng->txt('tst_pass_details'),
							'tst_pass_details',
							$this->ctrl->getLinkTargetByClass($targetclass, $targetcommand)
						);
						if($this->object->isPassDeletionAllowed() && $pass != $counted_pass)
						{
							$aslgui->addItem(
								$this->lng->txt('delete'),
								'tst_pass_delete',
								$this->ctrl->getLinkTargetByClass($targetclass, 'confirmDeletePass')
							);
							$delete_entry = true;
						}
						
						if($delete_entry == true)
						{
							$row['pass_details'] = $aslgui->getHTML();
						}
						else
						{
							$row['pass_details'] = '<a href="'.$this->ctrl->getLinkTargetByClass($targetclass, $targetcommand).'">'.$this->lng->txt('tst_pass_details').'</a>';
						}
					}
				}

				if(!$short)
				{
					if(($pass == $counted_pass))
					{
						$row['scored'] = '&otimes;';
					}
					else
					{
						$row['scored'] = '';
					}
				}

				$row['pass'] = $pass + 1;
				$row['date'] = $this->object->getPassFinishDate($active_id, $pass);
				if(!$short)
				{
					$row['answered'] = $result_array['pass']['num_workedthrough'] . ' ' . strtolower($this->lng->txt('of')) . ' ' . (count($result_array) - 2);
					if($this->object->isOfferingQuestionHintsEnabled())
					{
						$row['hints'] = $total_requested_hints;
					}
					$row['reached']    = $total_reached . ' ' . strtolower($this->lng->txt('of')) . ' ' . $total_max;
					$row['percentage'] = $percentage;
				}
				
				$data[] = $row;
		}

		$table->setData($data);
		return $table->getHTML();
	}

	/**
	 * Returns the list of answers of a users test pass
	 *
	 * @param array $result_array An array containing the results of the users test pass (generated by ilObjTest::getTestResult)
	 * @param integer $active_id Active ID of the active user
	 * @param integer $pass Test pass
	 * @param boolean $show_solutions TRUE, if the solution output should be shown in the answers, FALSE otherwise
	 * @return string HTML code of the list of answers
	 * @access public
	 */
	function getPassListOfAnswers(&$result_array, $active_id, $pass, $show_solutions = FALSE, $only_answered_questions = FALSE, $show_question_only = FALSE, $show_reached_points = FALSE, $anchorNav = false)
	{
		$maintemplate = new ilTemplate("tpl.il_as_tst_list_of_answers.html", TRUE, TRUE, "Modules/Test");

		$counter = 1;
		// output of questions with solutions
		foreach ($result_array as $question_data)
		{
			if (($question_data["workedthrough"] == 1) || ($only_answered_questions == FALSE))
			{
				$template = new ilTemplate("tpl.il_as_qpl_question_printview.html", TRUE, TRUE, "Modules/TestQuestionPool");
				$question = $question_data["qid"];
				if (is_numeric($question))
				{
					$maintemplate->setCurrentBlock("printview_question");
					$question_gui = $this->object->createQuestionGUI("", $question);
					if (is_object($question_gui))
					{
						if($anchorNav)
						{
							$template->setCurrentBlock('block_id');
							$template->setVariable('BLOCK_ID', "detailed_answer_block_act_{$active_id}_qst_{$question}");
							$template->parseCurrentBlock();

							$template->setCurrentBlock('back_anchor');
							$template->setVariable('HREF_BACK_ANCHOR', "#pass_details_tbl_row_act_{$active_id}_qst_{$question}");
							$template->setVariable('TXT_BACK_ANCHOR', $this->lng->txt('tst_back_to_question_list'));
							$template->parseCurrentBlock();
						}

						if ($show_reached_points)
						{
							$template->setCurrentBlock("result_points");
							$template->setVariable("RESULT_POINTS", $this->lng->txt("tst_reached_points") . ": " . $question_gui->object->getReachedPoints($active_id, $pass) . " " . $this->lng->txt("of") . " " . $question_gui->object->getMaximumPoints());
							$template->parseCurrentBlock();
						}
						$template->setVariable("COUNTER_QUESTION", $counter.". ");
						$template->setVariable("TXT_QUESTION_ID", $this->lng->txt('question_id_short'));
						$template->setVariable("QUESTION_ID", $question_gui->object->getId());
						$template->setVariable("QUESTION_TITLE", $this->object->getQuestionTitle($question_gui->object->getTitle()));

						$show_question_only = ($this->object->getShowSolutionAnswersOnly()) ? TRUE : FALSE;

						$showFeedback = !$this->isContextWithinTestPass() && $this->object->getShowSolutionFeedback();
						$show_solutions = !$this->isContextWithinTestPass() && $show_solutions;
						
						if($show_solutions)
						{
							$compare_template = new ilTemplate('tpl.il_as_tst_answers_compare.html', TRUE, TRUE, 'Modules/Test');
							$compare_template->setVariable("HEADER_PARTICIPANT", $this->lng->txt('tst_header_participant'));
							$compare_template->setVariable("HEADER_SOLUTION", $this->lng->txt('tst_header_solution'));
							$result_output = $question_gui->getSolutionOutput($active_id, $pass, $show_solutions, FALSE, $show_question_only, $showFeedback);
							$best_output   = $question_gui->getSolutionOutput($active_id, $pass, FALSE, FALSE, $show_question_only, FALSE, TRUE);

							$compare_template->setVariable('PARTICIPANT', $result_output);
							$compare_template->setVariable('SOLUTION', $best_output);
							$template->setVariable('SOLUTION_OUTPUT', $compare_template->get());
						}
						else
						{
							$result_output = $question_gui->getSolutionOutput($active_id, $pass, $show_solutions, FALSE, $show_question_only, $showFeedback);
							$template->setVariable('SOLUTION_OUTPUT', $result_output);
						}

						$maintemplate->setCurrentBlock("printview_question");
						$maintemplate->setVariable("QUESTION_PRINTVIEW", $template->get());
						$maintemplate->parseCurrentBlock();
						$counter ++;
					}
				}
			}
		}
		$maintemplate->setVariable("RESULTS_OVERVIEW", sprintf($this->lng->txt("tst_eval_results_by_pass"), $pass + 1));
		return $maintemplate->get();
	}
	
	/**
	 * Returns the list of answers of a users test pass and offers a scoring option
	 *
	 * @param array $result_array An array containing the results of the users test pass (generated by ilObjTest::getTestResult)
	 * @param integer $active_id Active ID of the active user
	 * @param integer $pass Test pass
	 * @param boolean $show_solutions TRUE, if the solution output should be shown in the answers, FALSE otherwise
	 * @return string HTML code of the list of answers
	 * @access public
	 * 
	 * @deprecated
	 */
	function getPassListOfAnswersWithScoring(&$result_array, $active_id, $pass, $show_solutions = FALSE)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		
		$maintemplate = new ilTemplate("tpl.il_as_tst_list_of_answers.html", TRUE, TRUE, "Modules/Test");

		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		$scoring = ilObjAssessmentFolder::_getManualScoring();
		
		$counter = 1;
		// output of questions with solutions
		foreach ($result_array as $question_data)
		{
			$question = $question_data["qid"];
			if (is_numeric($question))
			{
				$question_gui = $this->object->createQuestionGUI("", $question);
				if (in_array($question_gui->object->getQuestionTypeID(), $scoring))
				{
					$template = new ilTemplate("tpl.il_as_qpl_question_printview.html", TRUE, TRUE, "Modules/TestQuestionPool");
					$scoretemplate = new ilTemplate("tpl.il_as_tst_manual_scoring_points.html", TRUE, TRUE, "Modules/Test");
					#mbecker: No such block. $this->tpl->setCurrentBlock("printview_question");
					$template->setVariable("COUNTER_QUESTION", $counter.". ");
					$template->setVariable("QUESTION_TITLE", $this->object->getQuestionTitle($question_gui->object->getTitle()));
					$points = $question_gui->object->getMaximumPoints();
					if ($points == 1)
					{
						$template->setVariable("QUESTION_POINTS", $points . " " . $this->lng->txt("point"));
					}
					else
					{
						$template->setVariable("QUESTION_POINTS", $points . " " . $this->lng->txt("points"));
					}
					
					$show_question_only = ($this->object->getShowSolutionAnswersOnly()) ? TRUE : FALSE;
					$result_output = $question_gui->getSolutionOutput($active_id, $pass, $show_solutions, FALSE, $show_question_only, $this->object->getShowSolutionFeedback(), FALSE, TRUE);

					$solout = $question_gui->object->getSuggestedSolutionOutput();
					if (strlen($solout))
					{
						$scoretemplate->setCurrentBlock("suggested_solution");
						$scoretemplate->setVariable("TEXT_SUGGESTED_SOLUTION", $this->lng->txt("solution_hint"));
						$scoretemplate->setVariable("VALUE_SUGGESTED_SOLUTION", $solout);
						$scoretemplate->parseCurrentBlock();
					}
					
					$scoretemplate->setCurrentBlock("feedback");
					$scoretemplate->setVariable("FEEDBACK_NAME_INPUT", $question);
					$feedback = $this->object->getManualFeedback($active_id, $question, $pass);
					$scoretemplate->setVariable("VALUE_FEEDBACK", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($feedback, TRUE)));
					$scoretemplate->setVariable("TEXT_MANUAL_FEEDBACK", $this->lng->txt("set_manual_feedback"));
					$scoretemplate->parseCurrentBlock();

					$scoretemplate->setVariable("NAME_INPUT", $question);
					$this->ctrl->setParameter($this, "active_id", $active_id);
					$this->ctrl->setParameter($this, "pass", $pass);
					$scoretemplate->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "manscoring"));
					$scoretemplate->setVariable("LABEL_INPUT", $this->lng->txt("tst_change_points_for_question"));
					$scoretemplate->setVariable("VALUE_INPUT", " value=\"" . assQuestion::_getReachedPoints($active_id, $question_data["qid"], $pass) . "\"");
					$scoretemplate->setVariable("VALUE_SAVE", $this->lng->txt("save"));
					
					$template->setVariable("SOLUTION_OUTPUT", $result_output);
					$maintemplate->setCurrentBlock("printview_question");
					$maintemplate->setVariable("QUESTION_PRINTVIEW", $template->get());
					$maintemplate->setVariable("QUESTION_SCORING", $scoretemplate->get());
					$maintemplate->parseCurrentBlock();
				}
				$counter ++;
			}
		}
		if ($counter == 1)
		{
			// no scorable questions found
			$maintemplate->setVariable("NO_QUESTIONS_FOUND", $this->lng->txt("manscoring_questions_not_found"));
		}
		$maintemplate->setVariable("RESULTS_OVERVIEW", sprintf($this->lng->txt("manscoring_results_pass"), $pass+1));

		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initDomEvent();

		return $maintemplate->get();
	}

	protected function getPassDetailsOverview($result_array, $active_id, $pass, $targetGUI, $targetCMD, $questionDetailsCMD, $questionAnchorNav)
	{
		$this->ctrl->setParameter($targetGUI, 'active_id', $active_id);
		$this->ctrl->setParameter($targetGUI, 'pass', $pass);

		$tableGUI = $this->buildPassDetailsOverviewTableGUI($targetGUI, $targetCMD);

		$tableGUI->setAnswerListAnchorEnabled($questionAnchorNav);
		$tableGUI->setSingleAnswerScreenCmd($questionDetailsCMD);
		$tableGUI->setShowHintCount($this->object->isOfferingQuestionHintsEnabled());
		$tableGUI->setActiveId($active_id);
		$tableGUI->setPass($pass);
		$tableGUI->setShowSuggestedSolution(false);

		$tableGUI->initColumns()->initFilter();

		$usersQuestionSolutions = array();

		foreach($result_array as $key => $val)
		{
			if($key === 'test' || $key === 'pass')
			{
				continue;
			}

			if( $this->object->getShowSolutionSuggested() && strlen($val['solution']) )
			{
				$tableGUI->setShowSuggestedSolution(true);
			}

			$usersQuestionSolutions[$key] = $val;
		}

		$tableGUI->setFilterCommand($targetCMD.'SetTableFilter');
		$tableGUI->setResetCommand($targetCMD.'ResetTableFilter');

		$tableGUI->setData($usersQuestionSolutions);

		return $this->ctrl->getHTML($tableGUI);
	}

	/**
	 * Returns HTML code for a signature field
	 *
 	 * @return string HTML code of the date and signature field for the test results
	 * @access public
	 */
	function getResultsSignature()
	{
		if ($this->object->getShowSolutionSignature() && !$this->object->getAnonymity())
		{
			$template = new ilTemplate("tpl.il_as_tst_results_userdata_signature.html", TRUE, TRUE, "Modules/Test");
			$template->setVariable("TXT_DATE", $this->lng->txt("date"));
			$old_value = ilDatePresentation::useRelativeDates();
			ilDatePresentation::setUseRelativeDates(false);
			$template->setVariable("VALUE_DATE", ilDatePresentation::formatDate(new ilDate(time(), IL_CAL_UNIX)));
			ilDatePresentation::setUseRelativeDates($old_value);
			$template->setVariable("TXT_SIGNATURE", $this->lng->txt("tst_signature"));
			$template->setVariable("IMG_SPACER", ilUtil::getImagePath("spacer.png"));
			return $template->get();
		}
		else
		{
			return "";
		}
	}
	
	/**
	 * Returns the user data for a test results output
	 *
	 * @param ilTestSession|ilTestSessionDynamicQuestionSet
	 * @param integer $user_id The user ID of the user
	 * @param boolean $overwrite_anonymity TRUE if the anonymity status should be overwritten, FALSE otherwise
	 * @return string HTML code of the user data for the test results
	 * @access public
	 */
	function getResultsUserdata($testSession, $active_id, $overwrite_anonymity = FALSE)
	{
		if(!is_object($testSession)) throw new TestException();
		$template = new ilTemplate("tpl.il_as_tst_results_userdata.html", TRUE, TRUE, "Modules/Test");
		include_once './Services/User/classes/class.ilObjUser.php';
		$user_id = $this->object->_getUserIdFromActiveId($active_id);
		if (strlen(ilObjUser::_lookupLogin($user_id)) > 0)
		{
			$user = new ilObjUser($user_id);
		}
		else
		{
			$user = new ilObjUser();
			$user->setLastname($this->lng->txt("deleted_user"));
		}
		$t = $testSession->getSubmittedTimestamp();
		if (!$t)
		{
			$t = $this->object->_getLastAccess($testSession->getActiveId());
		}
		$print_date = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));

		$title_matric = "";
		if (strlen($user->getMatriculation()) && (($this->object->getAnonymity() == FALSE) || ($overwrite_anonymity)))
		{
			$template->setCurrentBlock("user_matric");
			$template->setVariable("TXT_USR_MATRIC", $this->lng->txt("matriculation"));
			$template->parseCurrentBlock();
			$template->setCurrentBlock("user_matric_value");
			$template->setVariable("VALUE_USR_MATRIC", $user->getMatriculation());
			$template->parseCurrentBlock();
			$template->touchBlock("user_matric_separator");
			$title_matric = " - " . $this->lng->txt("matriculation") . ": " . $user->getMatriculation();
		}

		$invited_user = array_pop($this->object->getInvitedUsers($user_id));
		if (strlen($invited_user["clientip"]))
		{
			$template->setCurrentBlock("user_clientip");
			$template->setVariable("TXT_CLIENT_IP", $this->lng->txt("client_ip"));
			$template->parseCurrentBlock();
			$template->setCurrentBlock("user_clientip_value");
			$template->setVariable("VALUE_CLIENT_IP", $invited_user["clientip"]);
			$template->parseCurrentBlock();
			$template->touchBlock("user_clientip_separator");
			$title_client = " - " . $this->lng->txt("clientip") . ": " . $invited_user["clientip"];
		}

		$template->setVariable("TXT_TEST_TITLE", $this->lng->txt("title"));
		$template->setVariable("VALUE_TEST_TITLE", $this->object->getTitle());
		$template->setVariable("TXT_USR_NAME", $this->lng->txt("name"));
		$uname = $this->object->userLookupFullName($user_id, $overwrite_anonymity);
		$template->setVariable("VALUE_USR_NAME", $uname);
		$template->setVariable("TXT_TEST_DATE", $this->lng->txt("tst_tst_date"));
		$template->setVariable("TXT_PRINT_DATE", $this->lng->txt("tst_print_date"));
		$old_value = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);
		$template->setVariable("VALUE_TEST_DATE", ilDatePresentation::formatDate(new ilDateTime(ilUtil::date_mysql2time($t), IL_CAL_UNIX)));
		$template->setVariable("VALUE_PRINT_DATE", ilDatePresentation::formatDate(new ilDateTime($print_date, IL_CAL_UNIX)));
		ilDatePresentation::setUseRelativeDates($old_value);
		
		// change the pagetitle
		$pagetitle = ": " . $this->object->getTitle() . $title_matric . $title_client;
		$this->tpl->setHeaderPageTitle($pagetitle);
		
		return $template->get();
	}

	/**
	 * Returns an output of the solution to an answer compared to the correct solution
	 *
	 * @param integer $question_id Database ID of the question
	 * @param integer $active_id Active ID of the active user
	 * @param integer $pass Test pass
	 * @return string HTML code of the correct solution comparison
	 * @access public
	 */
	function getCorrectSolutionOutput($question_id, $active_id, $pass)
	{
		global $ilUser;

		$test_id = $this->object->getTestId();
		$question_gui = $this->object->createQuestionGUI("", $question_id);

		$template = new ilTemplate("tpl.il_as_tst_correct_solution_output.html", TRUE, TRUE, "Modules/Test");
		$show_question_only = ($this->object->getShowSolutionAnswersOnly()) ? TRUE : FALSE;
		$result_output = $question_gui->getSolutionOutput($active_id, $pass, TRUE, FALSE, $show_question_only, $this->object->getShowSolutionFeedback(), FALSE, FALSE, TRUE);
		$best_output = $question_gui->getSolutionOutput($active_id, $pass, FALSE, FALSE, $show_question_only, FALSE, TRUE, FALSE, FALSE);
		if( $this->object->getShowSolutionFeedback() && $_GET['cmd'] != 'outCorrectSolution' )
		{
			$specificAnswerFeedback = $question_gui->getSpecificFeedbackOutput($active_id, $pass);
			if( strlen($specificAnswerFeedback) )
			{
				$template->setCurrentBlock("outline_specific_feedback");
				$template->setVariable("OUTLINE_SPECIFIC_FEEDBACK", $specificAnswerFeedback);
				$template->parseCurrentBlock();
			}
		}
		if ($this->object->isBestSolutionPrintedWithResult() && strlen($best_output))
		{
			$template->setCurrentBlock("best_solution");
			$template->setVariable("TEXT_BEST_SOLUTION", $this->lng->txt("tst_best_solution_is"));
			$template->setVariable("BEST_OUTPUT", $best_output);
			$template->parseCurrentBlock();
		}
		$template->setVariable("TEXT_YOUR_SOLUTION", $this->lng->txt("tst_your_answer_was"));
		$maxpoints = $question_gui->object->getMaximumPoints();
		if ($maxpoints == 1)
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getQuestionTitle($question_gui->object->getTitle()) . " (" . $maxpoints . " " . $this->lng->txt("point") . ")");
		}
		else
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getQuestionTitle($question_gui->object->getTitle()) . " (" . $maxpoints . " " . $this->lng->txt("points") . ")");
		}
		$template->setVariable("SOLUTION_OUTPUT", $result_output);
		$template->setVariable("RECEIVED_POINTS", sprintf($this->lng->txt("you_received_a_of_b_points"), $question_gui->object->getReachedPoints($active_id, $pass), $maxpoints));
		$template->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$template->setVariable("BACKLINK_TEXT", "&lt;&lt; " . $this->lng->txt("back"));
		return $template->get();
	}

	/**
	 * Output of the pass overview for a test called by a test participant
	 *
	 * @param ilTestSession|ilTestSessionDynamicQuestionSet $testSession
	 * @param integer $active_id
	 * @param integer $pass
	 * @param boolean $show_pass_details
	 * @param boolean $show_answers
	 * @param boolean $show_question_only
	 * @param boolean $show_reached_points
	 * @access public
	 */
	function getResultsOfUserOutput($testSession, $active_id, $pass, $targetGUI, $show_pass_details = TRUE, $show_answers = TRUE, $show_question_only = FALSE, $show_reached_points = FALSE)
	{
		global $ilias, $tpl;

		include_once("./Services/UICore/classes/class.ilTemplate.php");
		$template = new ilTemplate("tpl.il_as_tst_results_participant.html", TRUE, TRUE, "Modules/Test");

		if( $this->participantData instanceof ilTestParticipantData )
		{
			$user_id = $this->participantData->getUserIdByActiveId($active_id);
			$uname = $this->participantData->getConcatedFullnameByActiveId($active_id, false);
		}
		else
		{
			$user_id = $this->object->_getUserIdFromActiveId($active_id);
			$uname = $this->object->userLookupFullName($user_id, TRUE);
		}
		
		if (((array_key_exists("pass", $_GET)) && (strlen($_GET["pass"]) > 0)) || (!is_null($pass)))
		{
			if (is_null($pass))	$pass = $_GET["pass"];
		}

		$user_data = $this->getResultsUserdata($testSession, $active_id, TRUE);

		if (!is_null($pass))
		{
			$result_array =& $this->object->getTestResult($active_id, $pass);
			$command_solution_details = "";
			if ($show_pass_details)
			{
				$detailsoverview = $this->getPassDetailsOverview($result_array, $active_id, $pass, $targetGUI, "getResultsOfUserOutput", $command_solution_details, $show_answers);
			}

			$user_id = $this->object->_getUserIdFromActiveId($active_id);
			$showAllAnswers = TRUE;
			if ($this->object->isExecutable($testSession, $user_id))
			{
				$showAllAnswers = FALSE;
			}
			if ($show_answers)
			{
				$list_of_answers = $this->getPassListOfAnswers(
					$result_array, $active_id, $pass, $_SESSION['tst_results_show_best_solutions'],
					$showAllAnswers, $show_question_only, $show_reached_points, $show_pass_details
				);
			}

			$template->setVariable("LIST_OF_ANSWERS", $list_of_answers);
			//$template->setVariable("PASS_RESULTS_OVERVIEW", sprintf($this->lng->txt("tst_results_overview_pass"), $pass + 1));
			$template->setVariable("PASS_DETAILS", $detailsoverview);

			$signature = $this->getResultsSignature();
			$template->setVariable("SIGNATURE", $signature);
			
			if ($this->object->isShowExamIdInTestResultsEnabled())
			{
				$template->setCurrentBlock('exam_id_footer');
				$template->setVariable('EXAM_ID_VAL', $this->object->lookupExamId(
					$testSession->getActiveId(), $pass
				));
				$template->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
				$template->parseCurrentBlock();
			}
		}

		$template->setCurrentBlock('participant_back_anchor');
		$template->setVariable("HREF_PARTICIPANT_BACK_ANCHOR", "#tst_results_toolbar");
		$template->setVariable("TXT_PARTICIPANT_BACK_ANCHOR", $this->lng->txt('tst_back_to_top'));
		$template->parseCurrentBlock();

		$template->setCurrentBlock('participant_block_id');
		$template->setVariable("PARTICIPANT_BLOCK_ID", "participant_active_{$active_id}");
		$template->parseCurrentBlock();

		$template->setVariable("TEXT_HEADING", sprintf($this->lng->txt("tst_result_user_name"), $uname));
		$template->setVariable("USER_DATA", $user_data);

		if( $this->isGradingMessageRequired() )
		{
			$template->setCurrentBlock('grading_message');
			$template->setVariable('GRADING_MESSAGE', $this->getGradingMessage($active_id));
			$template->parseCurrentBlock();
		}

		$template->parseCurrentBlock();

		return $template->get();
	}

	/**
	 * Returns the user and pass data for a test results output
	 *
	 * @param integer $active_id The active ID of the user
	 * @return string HTML code of the user data for the test results
	 * @access public
	 */
	function getResultsHeadUserAndPass($active_id, $pass)
	{
		$template = new ilTemplate("tpl.il_as_tst_results_head_user_pass.html", TRUE, TRUE, "Modules/Test");
		include_once './Services/User/classes/class.ilObjUser.php';
		$user_id = $this->object->_getUserIdFromActiveId($active_id);
		if (strlen(ilObjUser::_lookupLogin($user_id)) > 0)
		{
			$user = new ilObjUser($user_id);
		}
		else
		{
			$user = new ilObjUser();
			$user->setLastname($this->lng->txt("deleted_user"));
		}
		$title_matric = "";
		if (strlen($user->getMatriculation()) && (($this->object->getAnonymity() == FALSE)))
		{
			$template->setCurrentBlock("user_matric");
			$template->setVariable("TXT_USR_MATRIC", $this->lng->txt("matriculation"));
			$template->parseCurrentBlock();
			$template->setCurrentBlock("user_matric_value");
			$template->setVariable("VALUE_USR_MATRIC", $user->getMatriculation());
			$template->parseCurrentBlock();
			$template->touchBlock("user_matric_separator");
			$title_matric = " - " . $this->lng->txt("matriculation") . ": " . $user->getMatriculation();
		}

		$invited_user = array_pop($this->object->getInvitedUsers($user_id));
		if (strlen($invited_user["clientip"]))
		{
			$template->setCurrentBlock("user_clientip");
			$template->setVariable("TXT_CLIENT_IP", $this->lng->txt("client_ip"));
			$template->parseCurrentBlock();
			$template->setCurrentBlock("user_clientip_value");
			$template->setVariable("VALUE_CLIENT_IP", $invited_user["clientip"]);
			$template->parseCurrentBlock();
			$template->touchBlock("user_clientip_separator");
			$title_client = " - " . $this->lng->txt("clientip") . ": " . $invited_user["clientip"];
		}

		$template->setVariable("TXT_USR_NAME", $this->lng->txt("name"));
		$uname = $this->object->userLookupFullName($user_id, FALSE);
		$template->setVariable("VALUE_USR_NAME", $uname);
		$template->setVariable("TXT_PASS", $this->lng->txt("scored_pass"));
		$template->setVariable("VALUE_PASS", $pass);
		return $template->get();
	}

	/**
	 * Creates a HTML representation for the results of a given question in a test
	 *
	 * @param integer $question_id The original id of the question
	 * @param integer $test_id The test id
	 * @return string HTML code of the question results
	 */
	public function getQuestionResultForTestUsers($question_id, $test_id)
	{
		// REQUIRED, since we call this object regardless of the loop
		$question_gui = $this->object->createQuestionGUI("", $question_id);

		$foundusers = $this->object->getParticipantsForTestAndQuestion($test_id, $question_id);
		$output     = '';
		foreach($foundusers as $active_id => $passes)
		{
			$resultpass = $this->object->_getResultPass($active_id);
			for($i = 0; $i < count($passes); $i++)
			{
				if(($resultpass !== null) && ($resultpass == $passes[$i]["pass"]))
				{
					if($output)
					{
						$output .= "<br /><br /><br />";
					}

					// check if re-instantiation is really neccessary
					$question_gui = $this->object->createQuestionGUI("", $passes[$i]["qid"]);
					$output .= $this->getResultsHeadUserAndPass($active_id, $resultpass + 1);
					$output .= $question_gui->getSolutionOutput(
						$active_id,
						$resultpass,
						$graphicalOutput = FALSE,
						$result_output = FALSE,
						$show_question_only = FALSE,
						$show_feedback = FALSE
					);
				}
			}
		}

		require_once './Modules/Test/classes/class.ilTestPDFGenerator.php';
		ilTestPDFGenerator::generatePDF($output, ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD, $question_gui->object->getTitle());
	}

	/**
	 * @return ilTestPassDetailsOverviewTableGUI
	 */
	protected function buildPassDetailsOverviewTableGUI($targetGUI, $targetCMD)
	{
		require_once 'Modules/Test/classes/tables/class.ilTestPassDetailsOverviewTableGUI.php';
		$tableGUI = new ilTestPassDetailsOverviewTableGUI($this->ctrl, $targetGUI, $targetCMD);
		$tableGUI->setIsPdfGenerationRequest($this->isPdfDeliveryRequest());
		return $tableGUI;
	}
	
	protected function isGradingMessageRequired()
	{
		if( $this->object->isShowGradingStatusEnabled() )
		{
			return true;
		}

		if( $this->object->isShowGradingMarkEnabled() )
		{
			return true;
		}

		if( $this->object->areObligationsEnabled() )
		{
			return true;
		}
		
		return false;
	}

	/**
	 * @param integer $activeId
	 * @return string
	 */
	protected function getGradingMessage($activeId)
	{
		require_once 'Modules/Test/classes/class.ilTestGradingMessageBuilder.php';
		$gradingMessageBuilder = new ilTestGradingMessageBuilder($this->lng, $this->object);
		
		$gradingMessageBuilder->setActiveId($activeId);
		$gradingMessageBuilder->build();
		
		return $gradingMessageBuilder->getMessage();
	}
}

// internal sort function to sort the result array
function sortResults($a, $b)
{
	$sort = ($_GET["sort"]) ? ($_GET["sort"]) : "nr";
	$sortorder = ($_GET["sortorder"]) ? ($_GET["sortorder"]) : "asc";
	if (strcmp($sortorder, "asc")) 
	{
		$smaller = 1;
		$greater = -1;
	} 
	else 
	{
		$smaller = -1;
		$greater = 1;
	}
	if ($a[$sort] == $b[$sort]) return 0;
	return ($a[$sort] < $b[$sort]) ? $smaller : $greater;
}
