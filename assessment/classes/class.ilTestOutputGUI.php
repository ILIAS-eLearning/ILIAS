<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("./assessment/classes/class.ilObjTest.php");

/**
* Output class for assessment test execution
*
* The ilTestOutputGUI class creates the output for the ilObjTestGUI
* class when learners execute a test. This saves some heap space because 
* the ilObjTestGUI class will be much smaller then
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.ilTestOutputGUI.php
* @modulegroup   assessment
*/
class ilTestOutputGUI
{
	var $object;
	var $lng;
	var $tpl;
	var $ctrl;
	var $ilias;
	var $tree;

	var $saveResult;
	var $sequence;
	var $cmdCtrl;
	var $maxProcessingTimeReached;
	var $endingTimeReached;

/**
* ilSurveyExecutionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the ilSurveyExecutionGUI object.
*
* @param object $a_object Associated ilObjSurvey class
* @access public
*/
  function ilTestOutputGUI($a_object)
  {
		global $lng, $tpl, $ilCtrl, $ilias, $tree;

    $this->lng =& $lng;
    $this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ilias =& $ilias;
		$this->object =& $a_object;
		$this->tree =& $tree;
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->saveParameter($this, "sequence", $_GET["sequence"]);

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
* Retrieves the ilCtrl command
*
* @access public
*/
	function getCommand($cmd)
	{
		return $cmd;
	}
	
	/**
	 * updates working time and stores state saveresult to see if question has to be stored or not
	 */
	
	function updateWorkingTime() {
		// todo: check update within summary and back
		// todo: back in summary does not work
		// todo: check working time in summary
		
		global $ilUser;
		
		// command which do not require update
		
		//print_r($_GET);
				//print_r($_POST);
		$negs =  //is_numeric($_GET["set_solved"]) || is_numeric($_GET["question_id"]) ||  
				  isset($_POST["cmd"]["start"]) || isset($_POST["cmd"]["resume"]) || 
				  isset($_POST["cmd"]["showresults"]) || isset($_POST["cmd"]["deleteresults"])|| 
				  isset($_POST["cmd"]["confirmdeleteresults"]) || isset($_POST["cmd"]["canceldeleteresults"]) ||
				  isset($_POST["cmd"]["submit_answers"]) || isset($_POST["cmd"]["confirm_submit_answers"]) ||
				  isset($_POST["cmd"]["cancel_show_answers"]) || isset($_POST["cmd"]["show_answers"]);
		
		// all other commands which require update
		$pos  = count($_POST["cmd"])>0 | isset($_GET["sequence"]);
				
		if ($pos==true && $negs==false)		
		{
			// set new finish time for test
			if ($_SESSION["active_time_id"]) // && $this->object->getEnableProcessingTime())
			{
				$this->object->updateWorkingTime($_SESSION["active_time_id"]);
				//echo "updating Worktime<br>";
			}	
		}		
	}	

	function saveQuestionSolution()
	{
		$this->saveResult = false;
		// save question solution
		if ($this->canSaveResult())
		{
			// but only if the ending time is not reached
			$q_id = $this->object->getQuestionIdFromActiveUserSequence($_GET["sequence"]);
			if (is_numeric($q_id)) 
			{
				global $ilUser;
				
				$question_gui = $this->object->createQuestionGUI("", $q_id);
				if ($ilUser->prefs["tst_javascript"])
				{
					$question_gui->object->setOutputType(OUTPUT_JAVASCRIPT);
				}
				$this->saveResult = $question_gui->object->saveWorkingData($this->object->getTestId());
			}												
		}			
	}
	
	/**
	 * returns if answers can be saved
	 * 
	 */
	 function canSaveResult() 
	 {
	 	$do_save = (($_POST["cmd"]["next"] || $_POST["cmd"]["previous"] || $_POST["cmd"]["postpone"]
	 				|| ($_POST["cmd"]["summary"] && !$_GET["sort_summary"]) || ($_GET["cmd"]["selectImagemapRegion"])  
					|| $_POST["cmd"]["directfeedback"] ||  $_POST["cmd"]["setsolved"]  || $_POST["cmd"]["resetsolved"] 
				    ) && (isset ($_GET["sequence"]) && is_numeric ($_GET["sequence"])));

	 	return $do_save == true &&				
				!$this->isEndingTimeReached() && !$this->isMaxProcessingTimeReached();
	 }
	 
	/**
	* set Locator
	*
	* @param	object	tree object
	* @param	integer	reference id
	* @param	scriptanme that is used for linking; if not set adm_object.php is used
	* @access	public
	*/
	function setLocator($a_tree = "", $a_id = "", $scriptname="repository.php")
	{
		include_once "./classes/class.ilLocatorGUI.php";
		$ilias_locator = new ilLocatorGUI(false);
		if (!is_object($a_tree))
		{
			$a_tree =& $this->tree;
		}
		if (!($a_id))
		{
			$a_id = $_GET["ref_id"];
		}

		$path = $a_tree->getPathFull($a_id);
		//check if object isn't in tree, this is the case if parent_parent is set
		// TODO: parent_parent no longer exist. need another marker
		if ($a_parent_parent)
		{
			$subObj =& $this->ilias->obj_factory->getInstanceByRefId($a_ref_id);

			$path[] = array(
				"id"	 => $a_ref_id,
				"title"  => $this->lng->txt($subObj->getTitle())
				);
		}

		// this is a stupid workaround for a bug in PEAR:IT
		$modifier = 1;
		if (isset($_GET["obj_id"]))
		{
			$modifier = 0;
		}

		// ### AA 03.11.10 added new locator GUI class ###
		$i = 1;
		if (!defined("ILIAS_MODULE")) 
		{
			foreach ($path as $key => $row)
			{
				$ilias_locator->navigate($i++, $row["title"], 
										 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH).
										 "/adm_object.php?ref_id=".$row["child"],"");
			}
		} 
		else 
		{
			foreach ($path as $key => $row)
			{
				if (strcmp($row["title"], "ILIAS") == 0) 
				{
					$row["title"] = $this->lng->txt("repository");
				}
				if ($this->ref_id == $row["child"]) 
				{
					if ($_GET["cmd"]) 
					{
						$param = "&cmd=" . $_GET["cmd"];
					} 
					else 
					{
						$param = "";
					}
					$ilias_locator->navigate($i++, $row["title"], 
											 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . 
											 "/assessment/test.php" . "?crs_show_result=".$_GET['crs_show_result'].
											 "&ref_id=".$row["child"] . $param,"");

					if ($this->sequence) 
					{
						if (($this->sequence <= $this->object->getQuestionCount()) and (!$_POST["cmd"]["showresults"])) 
						{
							$ilias_locator->navigate($i++, $this->object->getQuestionTitle($this->sequence), 
													 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . 
													 "/assessment/test.php" . "?crs_show_result=".$_GET['crs_show_result'].
													 "&ref_id=".$row["child"] . $param . 
													 "&sequence=" . $this->sequence,"");
						} 
						else 
						{
						}
					} 
					else 
					{
						if ($_POST["cmd"]["summary"] or isset($_GET["sort_summary"]))
						{
							$ilias_locator->navigate($i++, $this->lng->txt("summary"), 
													 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . 
													 "/assessment/test.php" . "?crs_show_result=0".
													 "&ref_id=".$row["child"] . $param . 
												 "&sequence=" . $_GET["sequence"]."&order=".$_GET["order"]."&sort_summary=".$_GET["sort_summary"],"");
						}
					}
				} 
				else 
				{
					if ($row["child"] == $this->object->getRefId())
					{
						$ilias_locator->navigate($i++, $row["title"], 
							$this->ctrl->getLinkTargetByClass(get_class($this), $this->ctrl->getCmd()), "");
					}	
					else
					{
						$ilias_locator->navigate($i++, $row["title"], 
												 ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/" . 
												 $scriptname."?"."ref_id=".$row["child"],"");
					}
				}
			}

			if (isset($_GET["obj_id"]))
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);
				$ilias_locator->navigate($i++,$obj_data->getTitle(),
										 $scriptname."?".$frameset."ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"],"");
			}
		}
		$ilias_locator->output();
	}
	
	function prepareOutput()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_tst_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");		
			
		$title = $this->object->getTitle();
		
		// header icon
		$this->tpl->setCurrentBlock("header_image");
		$icon = ilUtil::getImagePath("icon_tst_b.gif");
		$this->tpl->setVariable("IMG_HEADER", $icon);
		$this->tpl->parseCurrentBlock();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}
		
		$this->setLocator();
				
		// catch feedback message
		sendInfo();
	}

	/**
	* Creates the introduction page for a test
	*
	* Creates the introduction page for a test
	*
	* @access public
	*/
	function outIntroductionPage()
	{
		global $ilUser;
		global $rbacsystem;

		$this->prepareOutput();
		
		if (!$rbacsystem->checkAccess("read", $this->object->getRefId())) 
		{
			// only with read access it is possible to run the test
			$this->ilias->raiseError($this->lng->txt("cannot_execute_test"),$this->ilias->error_obj->MESSAGE);
		}
		
		// todo: max_processing_reached
		
		$maxprocessingtimereached = $this->isMaxProcessingTimeReached();

		$add_parameter = $this->getAddParameter();
		$active = $this->object->getActiveTestUser();
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_introduction.html", true);
		$this->tpl->setCurrentBlock("info_row");
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_type") . ":");
		$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt($this->object->test_types[$this->object->getTestType()]));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("description") . ":");
		$this->tpl->setVariable("TEXT_INFO_COL2", $this->object->getDescription());
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_sequence") . ":");
		$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt(($this->object->getSequenceSettings() == TEST_FIXED_SEQUENCE)? "tst_sequence_fixed":"tst_sequence_postpone"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_score_reporting") . ":");
		$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt(($this->object->getScoreReporting() == REPORT_AFTER_QUESTION)?"tst_report_after_question":"tst_report_after_test"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_count_partial_solutions") . ":");
		if ($this->object->getCountSystem() == COUNT_PARTIAL_SOLUTIONS)
		{
			$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt("tst_count_partial_solutions"));
		}
		else
		{
			$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt("tst_count_correct_solutions"));
		}
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_score_mcmr_questions") . ":");
		if ($this->object->getMCScoring() == SCORE_ZERO_POINTS_WHEN_UNANSWERED)
		{
			$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt("tst_score_mcmr_zero_points_when_unanswered"));
		}
		else
		{
			$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt("tst_score_mcmr_use_scoring_system"));
		}
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_nr_of_tries") . ":");

		$num_of = $this->object->getNrOfTries();
		if (!$num_of) {
			$num_of = $this->lng->txt("unlimited");
		}
		$this->tpl->setVariable("TEXT_INFO_COL2", $num_of);
		$this->tpl->parseCurrentBlock();
		if ((($this->object->getNrOfTries() == 0) || ($this->object->getNrOfTries() > 1)) && ($this->object->getTestType() != TYPE_VARYING_RANDOMTEST))
		{
			if ($this->object->getHidePreviousResults() == 1)
			{
				$this->tpl->setCurrentBlock("info_row");
				$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_hide_previous_results") . ":");
				$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt("tst_hide_previous_results_introduction"));
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($num_of != 1)
		{
			// display number of tries of the user
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_nr_of_tries_of_user") . ":");
			$tries = $active->tries;
			if (!$tries)
			{
				$tries = $this->lng->txt("tst_no_tries");
			}
			$this->tpl->setVariable("TEXT_INFO_COL2", $tries);
			$this->tpl->parseCurrentBlock();
		}

		if ($this->object->getEnableProcessingTime())
		{
	 		$working_time = $this->object->getCompleteWorkingTime($ilUser->id);
			$processing_time = $this->object->getProcessingTimeInSeconds();
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_processing_time") . ":");
			$time_seconds = $processing_time;
			$time_hours    = floor($time_seconds/3600);
			$time_seconds -= $time_hours   * 3600;
			$time_minutes  = floor($time_seconds/60);
			$time_seconds -= $time_minutes * 60;
			$this->tpl->setVariable("TEXT_INFO_COL2", sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_time_already_spent") . ":");
			$time_seconds = $working_time;
			$time_hours    = floor($time_seconds/3600);
			$time_seconds -= $time_hours   * 3600;
			$time_minutes  = floor($time_seconds/60);
			$time_seconds -= $time_minutes * 60;
			$this->tpl->setVariable("TEXT_INFO_COL2", sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
			$this->tpl->parseCurrentBlock();
		}

		if ($this->object->getStartingTime())
		{
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_starting_time") . ":");
			$this->tpl->setVariable("TEXT_INFO_COL2", ilFormat::formatDate(ilFormat::ftimestamp2datetimeDB($this->object->getStartingTime())));
			$this->tpl->parseCurrentBlock();
		}
		if ($this->object->getEndingTime())
		{
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_ending_time") . ":");
			$this->tpl->setVariable("TEXT_INFO_COL2", ilFormat::formatDate(ilFormat::ftimestamp2datetimeDB($this->object->getEndingTime())));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("info");
		$this->tpl->setVariable("TEXT_USE_JAVASCRIPT", $this->lng->txt("tst_use_javascript"));
		if ($ilUser->prefs["tst_javascript"])
		{
			$this->tpl->setVariable("CHECKED_JAVASCRIPT", "checked=\"checked\" ");
		}
		$this->tpl->parseCurrentBlock();
		$seq = 1;
		if ($active) {
			$seq = $active->lastindex;
		}
		$add_sequence = "&sequence=$seq";

		if ($this->showTestResults())
		{
			$first_seq = $this->object->getFirstSequence();
			$add_sequence = "&sequence=".$first_seq;

			if(!$first_seq)
			{
				sendInfo($this->lng->txt('crs_all_questions_answered_successfully'));
			}
		}
				
		// from here we have test type specific handling
		
		$test_disabled = !$this->isTestAccessible();
		
		if ($test_disabled) 
		{
			$add_sequence = "";
		}
		if ($this->isTestResumable() && $this->isTestAccessible())
		{
			// RESUME BLOCK 
			$this->tpl->setCurrentBlock("resume");
			if ($seq == 1)
			{
				if(!$this->showTestResults() or $first_seq)
				{
					$this->tpl->setVariable("BTN_RESUME", $this->lng->txt("tst_start_test"));
				}
			}
			else
			{
				$this->tpl->setVariable("BTN_RESUME", $this->lng->txt("tst_resume_test"));
			}
			
			// disable resume button
			if ($test_disabled) {
				$this->tpl->setVariable("DISABLED", " disabled");
			}
			$this->tpl->parseCurrentBlock();
		} else {
		// Start a new Test
			if ($this->isTestAccessible()// ($this->object->startingTimeReached() and !$this->object->endingTimeReached()) 
						//or ($this->object->getTestType() != TYPE_ASSESSMENT and !$this->object->isOnlineTest())
					)
			{
				$this->tpl->setCurrentBlock("start");
				$this->tpl->setVariable("BTN_START", $this->lng->txt("tst_start_test"));
				if (!$this->object->isComplete())
				{
					$this->tpl->setVariable("DISABLED", " disabled=\"disabled\"");
					$test_disabled = true;
				}
				$this->tpl->parseCurrentBlock();
			}							
		}
						
		// we have results
		if ($active && $active->tries > 0) 
		{
			// DELETE RESULTS only available for non Online Exams
			if (!$this->object->isOnlineTest())
			{
				// if resume is active it is possible to reset the test
				$this->tpl->setCurrentBlock("delete_results");
				$this->tpl->setVariable("BTN_DELETERESULTS", $this->lng->txt("tst_delete_results"));
				$this->tpl->parseCurrentBlock();
			}			
						
			// RESULT BLOCK if we can show result because we have data
			if ($this->canShowTestResults()) 
			{
				$this->tpl->setCurrentBlock("results");
				$this->tpl->setVariable("BTN_RESULTS", $this->lng->txt("tst_show_results"));				
				$this->tpl->parseCurrentBlock();
			}
			
			// Show results in a new print frame
			if ($this->object->isActiveTestSubmitted()) 
			{
				$add_parameter2 = "?ref_id=" . $_GET["ref_id"];
				$this->tpl->setCurrentBlock("show_printview");
				$this->tpl->setVariable("BTN_ANSWERS", $this->lng->txt("tst_show_answer_print_sheet"));	
				$this->tpl->setVariable("PRINT_VIEW_HREF", $this->ctrl->getLinkTargetByClass(get_class($this), "answersheet"));				
				$this->tpl->parseCurrentBlock();				
			}			
						
			// Result Date not reached
			if (!$this->canShowTestResults()) 
			{
					$this->tpl->setCurrentBlock("report_date_not_reached");
					preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->object->getReportingDate(), $matches);
					$reporting_date = date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]));
					$this->tpl->setVariable("RESULT_DATE_NOT_REACHED", sprintf($this->lng->txt("report_date_not_reached"), $reporting_date));
					$this->tpl->parseCurrentBlock();
				}
			
			/**
			 * time has ended, but the user has logged out and wants to submit his results
			 */
			if ($this->object->isOnlineTest() and $test_disabled) {
				if (!$this->object->isActiveTestSubmitted($ilUser->getId())) {
					$this->tpl->setCurrentBlock("show_summary");				
					$this->tpl->setVariable("BTN_SUMMARY", $this->lng->txt("save_finish"));
					$this->tpl->parseCurrentBlock();
				} else {
					sendInfo($this->lng->txt("tst_already_submitted"));					
				}
			} 			
		}
		

		$this->tpl->setCurrentBlock("adm_content");

		// test is disabled
		if ($test_disabled)
		{
			if (!$this->object->startingTimeReached() or $this->object->endingTimeReached())
			{
				$this->tpl->setCurrentBlock("startingtime");
				$this->tpl->setVariable("IMAGE_STARTING_TIME", ilUtil::getImagePath("time.gif", true));
			
				if (!$this->object->startingTimeReached())
				{
					$this->tpl->setVariable("ALT_STARTING_TIME_NOT_REACHED", $this->lng->txt("starting_time_not_reached"));
					$this->tpl->setVariable("TEXT_STARTING_TIME_NOT_REACHED", sprintf($this->lng->txt("detail_starting_time_not_reached"), ilFormat::ftimestamp2datetimeDB($this->object->getStartingTime())));
				}
				else
				{
					$this->tpl->setVariable("ALT_STARTING_TIME_NOT_REACHED", $this->lng->txt("ending_time_reached"));
					$this->tpl->setVariable("TEXT_STARTING_TIME_NOT_REACHED", sprintf($this->lng->txt("detail_ending_time_reached"), ilFormat::ftimestamp2datetimeDB($this->object->getEndingTime())));
				}
				$this->tpl->parseCurrentBlock();
			}
			
			if ($this->isNrOfTriesReached())				
			{
				$this->tpl->setVariable("MAXIMUM_NUMBER_OF_TRIES_REACHED", $this->lng->txt("maximum_nr_of_tries_reached"));
			}
			if ($this->isMaxProcessingTimeReached())
			{
				sendInfo($this->lng->txt("detail_max_processing_time_reached"));					
			}				
			if (!$this->object->isComplete())
			{
				sendInfo($this->lng->txt("warning_test_not_complete"));
			}
		}		
		$introduction = $this->object->getIntroduction();
		$introduction = preg_replace("/\n/i", "<br />", $introduction);
		$this->tpl->setVariable("TEXT_INTRODUCTION", $introduction);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this) . "$add_sequence");
		$this->tpl->parseCurrentBlock();
	}
	
	function isMaxProcessingTimeReached () 
	{
		global $ilUser;
 
		if (!is_bool($this->maxProcessingTimeReached))
			$this->maxProcessingTimeReached = (($this->object->getEnableProcessingTime()) && ($this->object->getCompleteWorkingTime($ilUser->id) > $this->object->getProcessingTimeInSeconds()));
		
		return $this->maxProcessingTimeReached;
	}
	
	function isEndingTimeReached () 
	{
		global $ilUser;
		if (!is_bool($this->endingTimeReached))			
			$this->endingTimeReached = $this->object->endingTimeReached() && ($this->object->getTestType() == TYPE_ASSESSMENT || $this->object->isOnlineTest());
			
		return $this->endingTimeReached;
	}

	function getAddParameter()
	{
		return "?ref_id=" . $_GET["ref_id"] . "&cmd=" . $_GET["cmd"] . '&crs_show_result='. (int) $_GET['crs_show_result'];
	}

	/**
	* Returns the calling script of the GUI class
	*
	* @access	public
	*/
	function getCallingScript()
	{
		return "test.php";
	}

/**
* Output of the learners view of an existing test
*
* Output of the learners view of an existing test
*
* @access public
*/
	function deleteresults() 
	{
		$this->prepareOutput();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_delete_results_confirm.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_CONFIRM_DELETE_RESULTS", $this->lng->txt("tst_confirm_delete_results"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_delete_results"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	function confirmdeleteresults()
	{
		global $ilUser;
		
		$this->object->deleteResults($ilUser->id);
		sendInfo($this->lng->txt("tst_confirm_delete_results_info"), true);
		$this->ctrl->redirect($this, "outIntroductionPage"); 
	}
	
	function canceldeleteresults()
	{
		$this->ctrl->redirect($this, "outIntroductionPage"); 
	}

	function handleStartCommands()
	{
		global $ilUser;
		
		// create new time dataset and set start time
		$active_time_id = $this->object->startWorkingTime($ilUser->id);
		$_SESSION["active_time_id"] = $active_time_id;
		
		if ($_POST["chb_javascript"])
		{
			$ilUser->setPref("tst_javascript", 1);
			$ilUser->writePref("tst_javascript", 1);
		}
		else
		{
			$ilUser->setPref("tst_javascript", 0);
			$ilUser->writePref("tst_javascript", 0);
		}
		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			global $ilias;
			
			$ilias->auth->setIdle(0, false);					
				
			if (!$this->object->isActiveTestSubmitted()) 
			{
				$_POST["cmd"]["summary"]="1";
			} 			
		}
	}
	
	function outShortResult($user_question_order) 
	{
		$this->tpl->setCurrentBlock("percentage");
		$this->tpl->setVariable("PERCENTAGE", (int)(($this->sequence / count($user_question_order))*200));
		$this->tpl->setVariable("PERCENTAGE_VALUE", (int)(($this->sequence / count($user_question_order))*100));
		$this->tpl->setVariable("HUNDRED_PERCENT", "200");
		$this->tpl->setVariable("TEXT_COMPLETED", $this->lng->txt("completed") . ": ");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("percentage_bottom");
		$this->tpl->setVariable("PERCENTAGE", (int)(($this->sequence / count($user_question_order))*200));
		$this->tpl->setVariable("PERCENTAGE_VALUE", (int)(($this->sequence / count($user_question_order))*100));
		$this->tpl->setVariable("HUNDRED_PERCENT", "200");
		$this->tpl->setVariable("TEXT_COMPLETED", $this->lng->txt("completed") . ": ");
		$this->tpl->parseCurrentBlock();				
	}

	/**
	* Creates the learners output of a question
	*
	* Creates the learners output of a question
	*
	* @access public
	*/
	function outWorkingForm($sequence = 1, $finish = false, $test_id, $active, $postpone_allowed, $user_question_order, $directfeedback = 0)
	{
		global $ilUser;
		
		include_once("classes/class.ilObjStyleSheet.php");
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
		ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
		ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();
		$question_gui = $this->object->createQuestionGUI("", $this->object->getQuestionIdFromActiveUserSequence($sequence));
		if ($ilUser->prefs["tst_javascript"])
		{
			$question_gui->object->setOutputType(OUTPUT_JAVASCRIPT);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_preview.html", true);

		$is_postponed = false;
		if (is_object($active))
		{			
			if (!preg_match("/(^|\D)" . $question_gui->object->getId() . "($|\D)/", $active->postponed) and 
				!($active->postponed == $question_gui->object->getId()))
			{
				$is_postponed = false;
			}
			else
			{
				$is_postponed = true;
			}
		}

		$formaction = $this->ctrl->getFormAction($this) . "&sequence=$sequence";
				
		// output question
		switch ($question_gui->getQuestionType())
		{
			case "qt_imagemap":
				$formaction = $this->ctrl->getLinkTargetByClass(get_class($this), "selectImagemapRegion") . "&sequence=$sequence";
				$question_gui->outWorkingForm($test_id, $is_postponed, $directfeedback, $formaction, true);
				$info =& $question_gui->object->getReachedInformation($ilUser->id, $test_id);
				if (strcmp($info[0]["value"], "") != 0)
				{
					$formaction .= "&selImage=" . $info[0]["value"];
				}
				break;

			default:
				$_SESSION["reorder"] = $formaction;
				$question_gui->setSequenceNumber ($sequence);
				$question_gui->outWorkingForm($test_id, $is_postponed, $directfeedback);
				break;
		}

		if(!$_GET['crs_show_result'])
		{
			if ($sequence == 1)
			{
				$this->tpl->setCurrentBlock("prev");
				$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_introduction"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("prev_bottom");
				$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_introduction"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("prev");
				$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_previous"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("prev_bottom");
				$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_previous"));
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($postpone_allowed)
		{
			if (!$is_postponed)
			{
				$this->tpl->setCurrentBlock("postpone");
				$this->tpl->setVariable("BTN_POSTPONE", $this->lng->txt("postpone"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("postpone_bottom");
				$this->tpl->setVariable("BTN_POSTPONE", $this->lng->txt("postpone"));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if ($this->object->isOnlineTest() && !$finish) {
			$this->tpl->setCurrentBlock("summary");
			$this->tpl->setVariable("BTN_SUMMARY", $this->lng->txt("summary"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("summary_bottom");
			$this->tpl->setVariable("BTN_SUMMARY", $this->lng->txt("summary"));
			$this->tpl->parseCurrentBlock();
		}

		if (!$this->object->isOnlineTest()) {
			$this->tpl->setCurrentBlock("cancel_test");
			$this->tpl->setVariable("TEXT_CANCELTEST", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("TEXT_ALTCANCELTEXT", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("TEXT_TITLECANCELTEXT", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("HREF_IMGCANCELTEST", $this->ctrl->getLinkTargetByClass(get_class($this), "outIntroductionPage") . "&cancelTest=true");
			$this->tpl->setVariable("HREF_CANCELTEXT", $this->ctrl->getLinkTargetByClass(get_class($this), "outIntroductionPage") . "&cancelTest=true");
			$this->tpl->setVariable("IMAGE_CANCEL", ilUtil::getImagePath("cancel.png"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("cancel_test_bottom");
			$this->tpl->setVariable("TEXT_CANCELTEST", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("TEXT_ALTCANCELTEXT", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("TEXT_TITLECANCELTEXT", $this->lng->txt("cancel_test"));
			$this->tpl->setVariable("HREF_IMGCANCELTEST", $this->ctrl->getLinkTargetByClass(get_class($this), "outIntroductionPage") . "&cancelTest=true");
			$this->tpl->setVariable("HREF_CANCELTEXT", $this->ctrl->getLinkTargetByClass(get_class($this), "outIntroductionPage") . "&cancelTest=true");
			$this->tpl->setVariable("IMAGE_CANCEL", ilUtil::getImagePath("cancel.png"));
			$this->tpl->parseCurrentBlock();			
		}		

		if ($finish)
		{
			if (!$this->object->isOnlineTest()) {
				$this->tpl->setCurrentBlock("next");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_finish") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("next_bottom");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_finish") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();
			} else {
				$this->tpl->setCurrentBlock("next");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("summary") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("next_bottom");
				$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("summary") . " &gt;&gt;");
				$this->tpl->parseCurrentBlock();				
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("next");
			$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_next") . " &gt;&gt;");
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("next_bottom");
			$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_next") . " &gt;&gt;");
			$this->tpl->parseCurrentBlock();
		}

		
		
		if ($this->object->isOnlineTest()) {
			$solved_array = ilObjTest::_getSolvedQuestions($this->object->test_id, $ilUser->getId(), $question_gui->object->getId());
			$solved = 0;
			
			if (count ($solved_array) > 0) {
				$solved = array_pop($solved_array);
				$solved = $solved->solved;
			}			
			
			if ($solved==1) 
			{
			 	$solved = ilUtil::getImagePath("solved.png", true);
			 	$solved_cmd = "resetsolved";
			 	$solved_txt = $this->lng->txt("tst_qst_resetsolved");
			} else 
			{				 
				$solved = ilUtil::getImagePath("not_solved.png", true);
				$solved_cmd = "setsolved";
				$solved_txt = $this->lng->txt("tst_qst_setsolved");
			}			
			$solved = "<input align=\"middle\" border=\"0\" alt=\"".$this->lng->txt("tst_qst_solved_state_click_to_change")."\" name=\"cmd[$solved_cmd]\" type=\"image\" src=\"$solved\">&nbsp;<small>$solved_txt</small>";
			
			$this->tpl->setCurrentBlock("question_status");
			$this->tpl->setVariable("TEXT_QUESTION_STATUS_LABEL", $this->lng->txt("tst_question_solved_state").":");
			$this->tpl->setVariable("TEXT_QUESTION_STATUS", $solved);
			$this->tpl->parseCurrentBlock();			
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $formaction);

		$this->tpl->parseCurrentBlock();
	}
	
	function start()
	{
		if ($this->object->isRandomTest())
		{
			$this->object->generateRandomQuestions();
			$this->object->loadQuestions();
		}
		$this->handleStartCommands();
		$this->sequence = $this->getSequence();	
		$this->object->setActiveTestUser($this->sequence);
		$this->outTestPage();
	}
	
	function resume()
	{
		$this->handleStartCommands();
		$this->sequence = $this->getSequence();	
		$this->object->setActiveTestUser($this->sequence);
		$this->outTestPage();
	}

	function setsolved()
	{
		global $ilUser;
		$this->saveQuestionSolution();
		$this->sequence = $this->getSequence();	
		$this->object->setActiveTestUser($this->sequence);
		$value = ($_POST["cmd"]["resetsolved"])?0:1;			
		$q_id  = $this->object->getQuestionIdFromActiveUserSequence($_GET["sequence"]);		
		$this->object->setQuestionSetSolved($value , $q_id, $ilUser->getId());
 		$this->outTestPage();
	}

	function resetsolved()
	{
		global $ilUser;
		$this->saveQuestionSolution();
		$this->sequence = $this->getSequence();	
		$this->object->setActiveTestUser($this->sequence);
		$value = ($_POST["cmd"]["resetsolved"])?0:1;			
		$q_id  = $this->object->getQuestionIdFromActiveUserSequence($_GET["sequence"]);		
		$this->object->setQuestionSetSolved($value , $q_id, $ilUser->getId());
		$this->outTestPage();
	}
	
	function next()
	{
		$this->saveQuestionSolution();
		$this->sequence = $this->getSequence();	
		$this->object->setActiveTestUser($this->sequence);
		if ($this->sequence > $this->object->getQuestionCount())
		{
			$this->finishTest();
		}
		else
		{
			$this->outTestPage();
		}
	}
	
	function gotoQuestion()
	{
		$this->sequence = $this->getSequence();	
		$this->object->setActiveTestUser($this->sequence);
		$this->outTestPage();
	}
	
	function summary()
	{
		$this->saveQuestionSolution();
		$this->outTestSummary();
	}
	
	function backFromSummary()
	{
		$this->sequence = $this->getSequence();
		$this->object->setActiveTestUser($this->sequence);
		$this->outTestPage();
	}

	function postpone()
	{
		$this->saveQuestionSolution();
		$this->sequence = $this->getSequence();	
		$postpone = $this->sequence;
		$this->object->setActiveTestUser($this->sequence, $postpone);
		$this->outTestPage();
	}
	
	function selectImagemapRegion()
	{
		$this->saveQuestionSolution();
		$this->sequence = $this->getSequence();	
		$this->object->setActiveTestUser($this->sequence);
		$this->outTestPage();
	}
	
	function previous()
	{
		$this->saveQuestionSolution();
		$this->sequence = $this->getSequence();	
		$this->object->setActiveTestUser($this->sequence);
		// sequence = 0
		if (!$this->sequence)
		{
			// show introduction page
			$this->outIntroductionPage();
		}
		else
		{
			$this->outTestPage();
		}
	}
	
	function finishTest()
	{
		global $ilUser;
		
		if ($this->object->getTestType() == TYPE_VARYING_RANDOMTEST)
		{
			// create a new set of random questions if more passes are allowed
			$actualpass = $this->object->_getPass($ilUser->id, $this->object->getTestId());
			$maxpass = $this->object->getNrOfTries();
			if (($maxpass == 0) || (($actualpass+1) < ($maxpass)))
			{
				$this->object->generateRandomQuestions($actualpass+1);
			}
		}
		
		if ($this->object->isOnlineTest() && !$this->object->isActiveTestSubmitted($ilUser->getId())) 
		{
			$this->outTestSummary();
			return;
		}
			
			
		$this->object->setActiveTestUser(1, "", true);
			
		if (!$this->object->canViewResults()) 
		{
			$this->outIntroductionPage($maxprocessingtimereached);
		}
		else
		{
//			if ($this->object->getTestType() == TYPE_VARYING_RANDOMTEST)
//			{
				$this->outResults();
//			}
//			else
//			{
//				$this->outTestResults();
//			}
		}
		// Update objectives
		include_once './course/classes/class.ilCourseObjectiveResult.php';

		$tmp_obj_res =& new ilCourseObjectiveResult($ilUser->getId());
		$tmp_obj_res->updateResults($this->object->getTestResult($ilUser->getId()));
		unset($tmp_obj_res);

		if($_GET['crs_show_result'])
		{
			ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?ref_id=".(int) $_GET['crs_show_result']));
		}
	}
	
	function outTestPage()
	{
		global $rbacsystem;

		$this->prepareOutput();
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_tst_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");		
			
		if (!$rbacsystem->checkAccess("read", $this->object->getRefId())) 
		{
			// only with read access it is possible to run the test
			$this->ilias->raiseError($this->lng->txt("cannot_execute_test"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->prepareRequestVariables();
		
		$this->onRunObjectEnter();
		
		// update working time and set saveResult state
		$this->updateWorkingTime();
					
		if ($this->handleCommands())
			return;
		if ($this->isMaxProcessingTimeReached())
		{
			$this->maxProcessingTimeReached();
			return;
		}
		
		if ($this->isEndingTimeReached())
		{
			$this->endingTimeReached();
			return;
		}
			
		if ($this->object->getScoreReporting() == REPORT_AFTER_QUESTION)
		{
			$this->tpl->setCurrentBlock("direct_feedback");
			$this->tpl->setVariable("TEXT_DIRECT_FEEDBACK", $this->lng->txt("direct_feedback"));
			$this->tpl->parseCurrentBlock();
		}
		
		// show next/previous question
	
		if ($this->sequence == $this->object->getQuestionCount())
		{
			$finish = true;
		}
		else
		{
			$finish = false;
		}

		$postpone = false;

		if ($this->object->getSequenceSettings() == TEST_POSTPONE)
		{
			$postpone = true;
		}

		$active = $this->object->getActiveTestUser();

		$user_question_order =& $this->object->getAllQuestionsForActiveUser();
		if(!$_GET['crs_show_result'])
		{
			$this->outShortResult($user_question_order);
		}
			
		if ($this->object->getEnableProcessingTime())
		{
			$this->outProcessingTime();
		}

		$this->outWorkingForm($this->sequence, $finish, $this->object->getTestId(), $active, $postpone, $user_question_order, $_POST["cmd"]["directfeedback"], $show_summary);
	}

	/**
	 * prepare Request variables e.g. some get parameters have to be mapped to post params
	 */
	function prepareRequestVariables()
	{
		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			if ($_GET["sort_summary"])
			//	sort summary: click on title to sort in summary
				$_POST["cmd"]["summary"]="1";
	
			if ($_POST["cmd"]["cancel_show_answers"]) 
			{
			// cancel_show_answers: click on back in show_answer view
				if ($this->isTestAccessible()) 
				{	// everythings ok goto summary
					$_POST["cmd"]["summary"]="1";
				} 
					else 
				{
					$_POST["cmd"]["run"]="1";
					unset($_GET ["sequence"]);
				}			
			}
			
			if ($_POST["cmd"]["show_answers"] or $_POST["cmd"]["back"] or $_POST["cmd"]["submit_answers"] or $_POST["cmd"]["run"]) 
			{
				unset($_GET ["sort_summary"]);			
				unset($_GET ["setsolved"]);
				unset($_GET ["resetsolved"]);
				if ($_POST["cmd"]["show_answers"]  or $_POST["cmd"]["submit_answers"] or $_POST["cmd"]["run"])					
					unset($_GET ["sequence"]);		
			}			
		}
		else
		{
			// set showresult cmd if pressed on sort in result overview
			if ($_GET["sortres"])
				$_POST["cmd"]["showresults"] = 1;
		}
	}
	
	/**
	 * what to when entering the run object
	 */
	function onRunObjectEnter()
	{
		// cancel Test if it's not online test
		if ($_POST["cmd"]["cancelTest"])
		{
			$this->handleCancelCommand();
		}		
		// check online exams access restrictions due to participants and client ip
		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			$this->checkOnlineTestAccess();
		}
	}	

	/**
	 * check access restrictions like client ip, partipating user etc. 
	 *
	 */
		
	function checkOnlineTestAccess() 
	{
		global $ilUser;
		
		// check if user is invited to participate
		$user = $this->object->getInvitedUsers($ilUser->getId());
		if (!is_array ($user) || count($user)!=1)
		{
				sendInfo($this->lng->txt("user_not_invited"), true);
				$path = $this->tree->getPathFull($this->object->getRefID());
				ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
				exit();
		}
			
		$user = array_pop($user);
		// check if client ip is set and if current remote addr is equal to stored client-ip			
		if (strcmp($user->clientip,"")!=0 && strcmp($user->clientip,$_SERVER["REMOTE_ADDR"])!=0)
		{
			sendInfo($this->lng->txt("user_wrong_clientip"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			exit();
		}		
	}	
	
	/**
	 * get next or previous sequence
	 */
	
	function getSequence() 
	{
		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			if ($this->object->isActiveTestSubmitted()) return "";
		}
		$sequence = $_GET["sequence"];
		if (!$sequence) $sequence = 1;
		$saveResult = $this->saveResult;
		
		if (isset($_POST["cmd"]["next"]) && $saveResult == true)
		{
			if($_GET['crs_show_result'])
			{
				$sequence = $this->object->incrementSequenceByResult($sequence);
			}
			else
			{
				$sequence++;
			}
		}
		elseif (($_POST["cmd"]["previous"]) and ($sequence != 0) and ($saveResult))
		{
			if($_GET['crs_show_result'])
			{
				$sequence = $this->object->decrementSequenceByResult($sequence);
			}
			else
			{
				$sequence--;
			}
		}
		
		return $sequence;
	}
	
	/**
	 * handle standard commands like confirmation, deletes, evaluation
	 */
	function handleCommands()
	{
		global $ilUser;
		
		if ($_GET["evaluation"])
		{
			$this->outEvaluationForm();
			return true;
		}

		return false;
	}
	
	/**
	 * test accessible returns true if the user can perform the test
	 */
	
	function isTestAccessible() 
	{		
		return 	!$this->isNrOfTriesReached() 				
			 	and	 !$this->isMaxProcessingTimeReached()
			 	and  $this->object->startingTimeReached()
			 	and  !$this->isEndingTimeReached();
	}

	/**
	 * nr of tries exceeded
	 */
	function isNrOfTriesReached() 
	{
		$active = $this->object->getActiveTestUser();
		return $this->object->hasNrOfTriesRestriction() && is_object($active) && $this->object->isNrOfTriesReached($active->tries);	
	}
	
	/**
	 * resumable is when there exists a test and the restrictions (time, nr of tries etc) don't prevent an access
	 */
	
	function isTestResumable() 
	{
		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			if ($this->object->isActiveTestSubmitted()) return false;
		}

		$active = $this->object->getActiveTestUser();		
		return is_object($active) && $this->object->startingTimeReached() && !$this->object->endingTimeReached();
	}
	
	/**
	 * handle cancel command
	 */
		
	function handleCancelCommand()
	{
		sendInfo($this->lng->txt("test_cancelled"), true);
		$path = $this->tree->getPathFull($this->object->getRefID());
		ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
		exit();
	}
	
	/**
	 * showTestResults returns true if the according request is set
	 */
	function showTestResults() 
	{
		return $_GET['crs_show_result'];// && $this->obj->canViewResults();
	}
	
	/**
	 * can show test results returns true if there exist results and the results may be viewed
	 */
	function canShowTestResults() 
	{
		$active = $this->object->getActiveTestUser();
		$result = ($active->tries > 0) && $this->object->canViewResults();
		if ($this->object->getTestType() == TYPE_ONLINE_TEST)
		{
			return $result && $this->object->isActiveTestSubmitted();
		}
		return $result;
	}
	
	function outResults()
	{
		if ($this->object->getTestType() == TYPE_VARYING_RANDOMTEST)
		{
			$this->outResultsOverview();
		}
		else
		{
			$this->outTestResults();
		}
	}
	
/**
* Output of the learner overview for a varying random test
*
* Output of the learner overview for a varying random test
*
* @access public
*/
	function outResultsOverview()
	{
		global $ilUser;
		
		if ($this->object->getTestType() != TYPE_VARYING_RANDOMTEST)
		{
			$this->ctrl->redirect($this, "outIntroductionPage");
		}
		$this->prepareOutput();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_finish.html", true);
		$this->tpl->addBlockFile("TEST_RESULTS", "results", "tpl.il_as_tst_varying_results.html", true);
		$user_id = $ilUser->id;
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$reached_pass = $this->object->_getPass($ilUser->id, $this->object->getTestId());
		for ($pass = 0; $pass <= $reached_pass; $pass++)
		{
			$finishdate = $this->object->getPassFinishDate($ilUser->id, $this->object->getTestId(), $pass);
			if ($finishdate > 0)
			{
				$result_array =& $this->object->getTestResult($user_id, $pass);
				if (!$result_array["test"]["total_max_points"])
				{
					$percentage = 0;
				}
				else
				{
					$percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
				}
				$total_max = $result_array["test"]["total_max_points"];
				$total_reached = $result_array["test"]["total_reached_points"];
				$this->tpl->setCurrentBlock("result_row");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$pass % 2]);
				$this->tpl->setVariable("VALUE_PASS", $pass + 1);
				$this->tpl->setVariable("VALUE_DATE", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($finishdate), "date"));
				$this->tpl->setVariable("VALUE_ANSWERED", $this->object->getAnsweredQuestionCount($ilUser->id, $this->object->getTestId(), $pass) . " " . strtolower($this->lng->txt("of")) . " " . (count($result_array)-1));
				$this->tpl->setVariable("VALUE_REACHED", $total_reached . " " . strtolower($this->lng->txt("of")) . " " . $total_max);
				$this->tpl->setVariable("VALUE_PERCENTAGE", sprintf("%.2f", $percentage) . "%");
				if ($this->object->canViewResults())
				{
					$this->tpl->setVariable("HREF_PASS_DETAILS", "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "passDetails")."&pass=$pass\">" . $this->lng->txt("tst_pass_details") . "</a>");
				}
				$this->tpl->parseCurrentBlock();
			}
		}
		$this->tpl->setCurrentBlock("results");
		$this->tpl->setVariable("PASS_COUNTER", $this->lng->txt("pass"));
		$this->tpl->setVariable("DATE", $this->lng->txt("date"));
		$this->tpl->setVariable("ANSWERED_QUESTIONS", $this->lng->txt("tst_answered_questions"));
		$this->tpl->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
		$this->tpl->setVariable("PERCENTAGE_CORRECT", $this->lng->txt("tst_percent_solved"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BACK_TO_INTRODUCTION", $this->lng->txt("tst_results_back_introduction"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("test_user_name");
		$this->tpl->setVariable("USER_NAME", sprintf($this->lng->txt("tst_result_user_name"), $ilUser->getFullname()));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->parseCurrentBlock();
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
		$this->ctrl->saveParameter($this, "pass");
		$this->outTestResults(false, $_GET["pass"]);
	}
	
/**
* Output of the learners view of an existing test
*
* Output of the learners view of an existing test
*
* @access public
*/
	function outTestResults($print = false, $pass = NULL) 
	{
		global $ilUser;

		function sort_percent($a, $b) {
			if (strcmp($_GET["order"], "ASC")) {
				$smaller = 1;
				$greater = -1;
			} else {
				$smaller = -1;
				$greater = 1;
			}
			if ($a["percent"] == $b["percent"]) {
				if ($a["nr"] == $b["nr"]) return 0;
		 	 	return ($a["nr"] < $b["nr"]) ? -1 : 1;
			}
			return ($a["percent"] < $b["percent"]) ? $smaller : $greater;
		}

		function sort_nr($a, $b) {
			if (strcmp($_GET["order"], "ASC")) {
				$smaller = 1;
				$greater = -1;
			} else {
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			return ($a["nr"] < $b["nr"]) ? $smaller : $greater;
		}

		$this->prepareOutput();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_finish.html", true);
		$user_id = $ilUser->id;
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$this->tpl->addBlockFile("TEST_RESULTS", "results", "tpl.il_as_tst_results.html", true);
		$result_array =& $this->object->getTestResult($user_id, $pass);

		if (!$result_array["test"]["total_max_points"])
		{
			$percentage = 0;
		}
		else
		{
			$percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
		}
		$total_max = $result_array["test"]["total_max_points"];
		$total_reached = $result_array["test"]["total_reached_points"];
		$img_title_percent = "";
		$img_title_nr = "";
		switch ($_GET["sortres"]) {
			case "percent":
				usort($result_array, "sort_percent");
				$img_title_percent = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"\" />";
				if (strcmp($_GET["order"], "ASC") == 0) {
					$sortpercent = "DESC";
				} else {
					$sortpercent = "ASC";
				}
				break;
			case "nr":
				usort($result_array, "sort_nr");
				$img_title_nr = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"\" />";
				if (strcmp($_GET["order"], "ASC") == 0) {
					$sortnr = "DESC";
				} else {
					$sortnr = "ASC";
				}
				break;
		}
		if (!$sortpercent) {
			$sortpercent = "ASC";
		}
		if (!$sortnr) {
			$sortnr = "ASC";
		}

		foreach ($result_array as $key => $value) {
			if (preg_match("/\d+/", $key)) {
				$this->tpl->setCurrentBlock("question");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				if ($this->object->isOnlineTest())
					$this->tpl->setVariable("VALUE_QUESTION_TITLE", $value["title"]);
				else
					$this->tpl->setVariable("VALUE_QUESTION_TITLE", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "run") . "&evaluation=" . $value["qid"] . "\">" . $value["title"] . "</a>");
				$this->tpl->setVariable("VALUE_MAX_POINTS", $value["max"]);
				$this->tpl->setVariable("VALUE_REACHED_POINTS", $value["reached"]);
				if (preg_match("/http/", $value["solution"]))
				{
					$this->tpl->setVariable("SOLUTION_HINT", "<a href=\"".$value["solution"]."\" target=\"content\">" . $this->lng->txt("solution_hint"). "</a>");
				}
				else
				{
					if ($value["solution"])
					{
						$this->tpl->setVariable("SOLUTION_HINT", $this->lng->txt($value["solution"]));
					}
					else
					{
						$this->tpl->setVariable("SOLUTION_HINT", "");
					}
				}
				$this->tpl->setVariable("VALUE_PERCENT_SOLVED", $value["percent"]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}

		$this->tpl->setCurrentBlock("question");
		$this->tpl->setVariable("COLOR_CLASS", "std");
		$this->tpl->setVariable("VALUE_QUESTION_COUNTER", "<strong>" . $this->lng->txt("total") . "</strong>");
		$this->tpl->setVariable("VALUE_QUESTION_TITLE", "");
		$this->tpl->setVariable("SOLUTION_HINT", "");
		$this->tpl->setVariable("VALUE_MAX_POINTS", "<strong>" . sprintf("%d", $total_max) . "</strong>");
		$this->tpl->setVariable("VALUE_REACHED_POINTS", "<strong>" . sprintf("%d", $total_reached) . "</strong>");
		$this->tpl->setVariable("VALUE_PERCENT_SOLVED", "<strong>" . sprintf("%2.2f", $percentage) . " %" . "</strong>");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("results");
		$this->tpl->setVariable("QUESTION_COUNTER", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "passDetails") . "&sortres=nr&order=$sortnr\">" . $this->lng->txt("tst_question_no") . "</a>$img_title_nr");
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("SOLUTION_HINT_HEADER", $this->lng->txt("solution_hint"));
		$this->tpl->setVariable("MAX_POINTS", $this->lng->txt("tst_maximum_points"));
		$this->tpl->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
		$this->tpl->setVariable("PERCENT_SOLVED", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "passDetails") . "&sortres=percent&order=$sortpercent\">" . $this->lng->txt("tst_percent_solved") . "</a>$img_title_percent");
		$mark_obj = $this->object->mark_schema->get_matching_mark($percentage);
		if ($mark_obj)
		{
			if ($mark_obj->get_passed()) {
				$mark = $this->lng->txt("tst_result_congratulations");
			} else {
				$mark = $this->lng->txt("tst_result_sorry");
			}
			$mark .= "<br />" . $this->lng->txt("tst_your_mark_is") . ": &quot;" . $mark_obj->get_official_name() . "&quot;";
		}
		if ($this->object->ects_output)
		{
			$ects_mark = $this->object->getECTSGrade($total_reached, $total_max);
			$mark .= "<br />" . $this->lng->txt("tst_your_ects_mark_is") . ": &quot;" . $ects_mark . "&quot; (" . $this->lng->txt("ects_grade_". strtolower($ects_mark) . "_short") . ": " . $this->lng->txt("ects_grade_". strtolower($ects_mark)) . ")";
		}
		$this->tpl->setVariable("USER_FEEDBACK", $mark);
		if ($this->object->getTestType() == TYPE_VARYING_RANDOMTEST)
		{
			$this->tpl->setVariable("BACK_TO_OVERVIEW", $this->lng->txt("tst_results_back_overview"));
		}
		else
		{
			$this->tpl->setVariable("BACK_TO_OVERVIEW", $this->lng->txt("tst_results_back_introduction"));
		}
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("test_user_name");
		$this->tpl->setVariable("USER_NAME", sprintf($this->lng->txt("tst_result_user_name"), $ilUser->getFullname()));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	function show_answers()
	{
		global $ilUser;
		$this->outShowAnswers(true, $ilUser);
	}
	
	function outShowAnswers($isForm, &$ilUser) 
	{
		$this->prepareOutput();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_answers_sheet_details.html", true);		
		$this->outShowAnswersDetails($isForm, $ilUser);
	}
	
	function outShowAnswersDetails($isForm, &$ilUser) 
	{
		$tpl = &$this->tpl;		 				
		$invited_users = array_pop($this->object->getInvitedUsers($ilUser->getId()));
		$active = $this->object->getActiveTestUser($ilUser->getId());
		$t = $active->submittimestamp;
		
		$add_parameter = $this->getAddParameter();
		
		// output of submit date and signature
		if ($active->submitted)
		{
			// only display submit date when it exists (not in the summary but in the print form)
			$tpl->setCurrentBlock("freefield_bottom");
			$tpl->setVariable("TITLE", $this->object->getTitle());
			$tpl->setVariable("TXT_DATE", $this->lng->txt("date"));
			$tpl->setVariable("VALUE_DATE", strftime("%Y-%m-%d %H:%M:%S", ilUtil::date_mysql2time($t)));
			$tpl->setVariable("TXT_ANSWER_SHEET", $this->lng->txt("tst_answer_sheet"));
	
			$freefieldtypes = array ("freefield_bottom" => 	array(	array ("title" => $this->lng->txt("tst_signature"), "length" => 300)));
	/*					"freefield_top" => 		array (	array ("title" => $this->lng->txt("semester"), "length" => 300), 
															array ("title" => $this->lng->txt("career"), "length" => 300)
															 ),*/
						
			
			
			foreach ($freefieldtypes as $type => $freefields) {
				$counter = 0;
	
				while ($counter < count ($freefields)) {
					$freefield = $freefields[$counter];
					
					//$tpl->setCurrentBlock($type);
				
					$tpl->setVariable("TXT_FREE_FIELD", $freefield["title"]);
					$tpl->setVariable("VALUE_FREE_FIELD", "<img height=\"30px\" border=\"0\" src=\"".ilUtil :: getImagePath("spacer.gif", false)."\" width=\"".$freefield["length"]."px\" />");
				
					$counter ++;
				
					//$tpl->parseCurrentBlock($type);
				}
			}
			$tpl->parseCurrentBlock();
		}

		$counter = 1;
		
		// output of questions with solutions
		foreach ($this->object->questions as $question) 
		{
			$tpl->setCurrentBlock("question");
			$question_gui = $this->object->createQuestionGUI("", $question);

			//$tpl->setVariable("EDIT_QUESTION", $this->getCallingScript().$this->getAddParameter()."&sequence=".$counter);
			$tpl->setVariable("COUNTER_QUESTION", $counter.". ");
			$tpl->setVariable("QUESTION_TITLE", $question_gui->object->getTitle());
			
			$idx = $this->object->test_id;
			
			switch ($question_gui->getQuestionType()) {
				case "qt_imagemap" :
					$question_gui->outWorkingForm($idx, false, $show_solutions=false, $formaction, $show_question_page=false, $show_solution_only = false, $ilUser);
					break;
				case "qt_javaapplet" :
					$question_gui->outWorkingForm("", $is_postponed = false, $showsolution = 0, $show_question_page=false, $show_solution_only = false, $ilUser);
					break;
				default :
					$question_gui->outWorkingForm($idx, $is_postponed = false, $showsolution = 0, $show_question_page=false, $show_solution_only = false, $ilUser);
			}
			$tpl->parseCurrentBlock();
			$counter ++;
		}
		// output of submit buttons
		if ($isForm && !$active->submitted) 
		{
			$tpl->setCurrentBlock("confirm");
			$tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("tst_submit_answers_txt"));
			$tpl->setVariable("BTN_CANCEL", $this->lng->txt("back"));
			$tpl->setVariable("BTN_OK", $this->lng->txt("tst_submit_answers"));
			$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
			$tpl->parseCurrentBlock();
		}
		
		// output of non-block elements
		$tpl->setCurrentBlock("adm_content");
		$tpl->setVariable("TXT_TEST_TITLE", $this->lng->txt("title"));
		$tpl->setVariable("VALUE_TEST_TITLE", $this->object->getTitle());
		$tpl->setVariable("TXT_USR_NAME", $this->lng->txt("name"));
		$tpl->setVariable("VALUE_USR_NAME", $ilUser->getLastname().", ".$ilUser->getFirstname());
		$tpl->setVariable("TXT_USR_MATRIC", $this->lng->txt("matriculation"));
		$tpl->setVariable("VALUE_USR_MATRIC", $ilUser->getMatriculation());
		$tpl->setVariable("TXT_CLIENT_IP", $this->lng->txt("client_ip"));
		$tpl->setVariable("VALUE_CLIENT_IP", $invited_users->clientip);
		$tpl->setVariable("TXT_TEST_PROLOG", $this->lng->txt("tst_your_answers"));
		
		$tpl->parseCurrentBlock();
		
	}
	
	/**
	 * handle endingTimeReached
	 * @private
	 */
	
	function endingTimeReached() 
	{
		sendInfo(sprintf($this->lng->txt("detail_ending_time_reached"), ilFormat::ftimestamp2datetimeDB($this->object->getEndingTime())));
		$this->object->setActiveTestUser(1, "", true);
		if (!$this->object->canViewResults()) 
		{
			$this->outIntroductionPage();
		}
		else
		{
			if ($this->object->isOnlineTest())
				$this->outTestSummary();
			else
				$this->outResults();
		}
	}
	
	
	function maxProcessingTimeReached()
	{
		sendInfo($this->lng->txt("detail_max_processing_time_reached"));
		$this->object->setActiveTestUser(1, "", true);
		if (!$this->object->canViewResults()) 
		{
			$this->outIntroductionPage();
		}
		else
		{
			if ($this->object->isOnlineTest())
				$this->outTestSummary();
			else					
				$this->outResults();
		}
	}		

	/**
	* confirm submit results
	* if confirm then results are submitted and the screen will be redirected to the startpage of the test
	* @access public
	*/
	function confirmSubmitAnswers() 
	{
		$this->prepareOutput();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_submit_answers_confirm.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		if ($this->object->isActiveTestSubmitted()) 
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
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	*	printAnswer Object can only be called if the test is submitted, otherwise we generate an error.
	*
	*/
	function printAnswersObject()
	{
		global $ilUser,$rbacsystem;
		if ((!$rbacsystem->checkAccess("read", $this->ref_id))) 
		{
			// allow only read and write access
			sendInfo($this->lng->txt("cannot_edit_test"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}
		
		if (!$this->object->isActiveTestSubmitted($ilUser->getId())) 
		{
			sendInfo($this->lng->txt("test_not_submitted"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			ilUtil::redirect($this->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			return;
		}


		$this->object->setActiveTestSubmitted($ilUser->getId());

		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_print_answers_sheet.html", true, true);
		$this->tpl->setVariable("PRINT_CSS", "./templates/default/print_answers.css");
		$this->tpl->setVariable ("FRAME_TITLE", $this->object->getTitle());
		$this->tpl->setVariable ("FRAME_CLIENTIP",$_SERVER["REMOTE_ADDR"]);		
		$this->tpl->setVariable ("FRAME_MATRICULATION",$ilUser->getMatriculation());

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_answers_sheet_details.html", true);
		
		$this->outShowAnswersDetails(false, $ilUser); 
	}
	
	function _printAnswerSheets($users) 
	{	
		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_print_answers_sheet.html", true, true);
		$this->tpl->setVariable("PRINT_CSS", "./templates/default/print_answers.css");
		$this->tpl->setVariable("TITLE", $this->object->getTitle());		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_answers_sheet_details.html", true);
		
		foreach ($users as $user_id) {
			if ($this->object->isActiveTestSubmitted($user_id)) {
				$this->outShowAnswersDetails(false, new ilObjUser ($user_id));
			}
		}
	}
	
	function _printResultSheets($users) 
	{	
		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_print_results.html", true, true);
		$this->tpl->setVariable("PRINT_CSS", "./templates/default/print_results.css");
		$this->tpl->setVariable("TITLE", $this->object->getTitle());		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_result_details.html", true);
		
		foreach ($users as $user_id) {
			if ($this->object->isActiveTestSubmitted($user_id)) {
				$this->outPrintUserResults($user_id);			
			}
		}
	}	
	
	function resultsheetObject() 
	{
		global $rbacsystem, $ilUser, $ilErr;
		
		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			$ilErr->raiseError($this->lng->txt("cannot_edit_test"),$ilErr->WARNING);
			return;
		}
		
		$user_id = (int) $_GET["user_id"];
		$user = $this->object->getInvitedUsers($user_id);
		if (!is_array ($user) || count($user)!=1)
		{
			$ilErr->raiseError($this->lng->txt("user_not_invited"),$ilErr->WARNING);
			return;
		}
			
		$this->outPrintTestResults($user_id);	
	}
	
	function answersheet() 
	{
		global $rbacsystem, $ilUser, $ilErr;
		
		$user_id = (int) $_GET["user_id"];
		
		// user has to have at least read permission
		if ((!$rbacsystem->checkAccess("read", $this->ref_id))) 
		{
			// allow only read and write access
			$ilErr->raiseError($this->lng->txt("cannot_read_test"),$ilErr->WARNING); 
			return;
		}

		// if GET["user_id"] is not set, then we assume that the user the current ilias user
		// that means he does not have to have write permissions to see the results! 

		if (!isset($_GET["user_id"])) {
			if (!$rbacsystem->checkAccess("write", $this->ref_id)) 
			{
				// allow only read and write access
				$ilErr->raiseError($this->lng->txt("cannot_edit_test"),$ilErr->WARNING); 
				return;
			}
			$user_id = $ilUser->getId();
		} else 
			$user_id = (int) $_GET["user_id"];
		
		// getInvitedUsers and see if the requested user belongs to the test
		$users = $this->object->getInvitedUsers($user_id);
		if (!is_array ($users) || count($users)!=1)
		{
			$ilErr->raiseError($this->lng->txt("user_not_invited"),$ilErr->WARNING); 
			return;
		}
		
		// create a new UserObject to be passed for showing answers
		$userObject = new IlObjUser ($user_id);	
		
		// geht the invited user, we need the registered client ip in the print screen
		$user = array_pop ($users);
		
		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_print_answers_sheet.html", true, true);
		$this->tpl->setVariable("PRINT_CSS", "./templates/default/print_answers.css");
		$this->tpl->setVariable("FRAME_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("FRAME_CLIENTIP", $user->clientip);		
		$this->tpl->setVariable("FRAME_MATRICULATION",$userObject->getMatriculation());
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_answers_sheet_details.html", true);
		
		// pass the user to the output procedure
		$this->outShowAnswersDetails(false, $userObject);			
	}
	
/**
* Output of the learners view of an existing test
*
* Output of the learners view of an existing test
*
* @access public
*/
	function outPrintTestResults($user_id) 
	{
		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_print_results.html", true, true);
		$this->tpl->setVariable("PRINT_CSS", "./templates/default/print_results.css");
		$this->tpl->setVariable("TITLE", $this->object->getTitle());
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_result_details.html", true);			
		
		$this->outPrintUserResults ($user_id);
	}
	
	function outPrintUserResults($user_id) 
	{
		$user = new IlObjUser ($user_id);
		$active = $this->object->getActiveTestUser($user_id);
		$t = $active->submittimestamp;
		
		$print_date = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
		
		$this->tpl->setVariable("TXT_TEST_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("VALUE_TEST_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("TXT_USR_NAME", $this->lng->txt("name"));
		$this->tpl->setVariable("VALUE_USR_NAME", $user->getLastname().", ".$user->getFirstname());
		$this->tpl->setVariable("TXT_USR_MATRIC", $this->lng->txt("matriculation"));
		$this->tpl->setVariable("VALUE_USR_MATRIC", $user->getMatriculation());
		$this->tpl->setVariable("TXT_TEST_DATE", $this->lng->txt("tst_tst_date"));
		$this->tpl->setVariable("VALUE_TEST_DATE", strftime("%Y-%m-%d %H:%M:%S",ilUtil::date_mysql2time($t)));
		$this->tpl->setVariable("TXT_PRINT_DATE", $this->lng->txt("tst_print_date"));
		$this->tpl->setVariable("VALUE_PRINT_DATE", strftime("%Y-%m-%d %H:%M:%S",$print_date));
		

		$add_parameter = $this->getAddParameter();
		
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;

		$result_array =& $this->object->getTestResult($user_id);

		if (!$result_array["test"]["total_max_points"])
		{
			$percentage = 0;
		}
		else
		{
			$percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
		}
		
		$total_max = $result_array["test"]["total_max_points"];
		$total_reached = $result_array["test"]["total_reached_points"];

		foreach ($result_array as $key => $value) {
			if (preg_match("/\d+/", $key)) {
				$title = $value["title"];
				$this->tpl->setCurrentBlock("question");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				$this->tpl->setVariable("VALUE_QUESTION_TITLE", $title);
				$this->tpl->setVariable("VALUE_MAX_POINTS", $value["max"]);
				$this->tpl->setVariable("VALUE_REACHED_POINTS", $value["reached"]);
				$this->tpl->setVariable("VALUE_PERCENT_SOLVED", $value["percent"]);
				$this->tpl->parseCurrentBlock("question");
				$counter++;
			}
		}

		$this->tpl->setCurrentBlock("adm_content");

		$this->tpl->setVariable("QUESTION_COUNTER", $this->lng->txt("tst_question_no"));
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("SOLUTION_HINT_HEADER", $this->lng->txt("solution_hint"));
		$this->tpl->setVariable("MAX_POINTS", $this->lng->txt("tst_maximum_points"));
		$this->tpl->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
		$this->tpl->setVariable("PERCENT_SOLVED", $this->lng->txt("tst_percent_solved"));

		// SUM
		$this->tpl->setVariable("TOTAL", $this->lng->txt("total"));
		$this->tpl->setVariable("TOTAL_MAX_POINTS", $total_max);
		$this->tpl->setVariable("TOTAL_REACHED_POINTS",  $total_reached);
		$this->tpl->setVariable("TOTAL_PERCENT_SOLVED", sprintf("%01.2f",$percentage)." %");



		$mark_obj = $this->object->mark_schema->get_matching_mark($percentage);
		if ($mark_obj)
		{
			$mark .= "<br /><strong>" . $this->lng->txt("tst_mark") . ": &quot;" . $mark_obj->get_official_name() . "&quot;</strong>";
		}
		if ($this->object->ects_output)
		{
			$ects_mark = $this->object->getECTSGrade($total_reached, $total_max);
			$mark .= "<br />" . $this->lng->txt("tst_your_ects_mark_is") . ": &quot;" . $ects_mark . "&quot; (" . $this->lng->txt("ects_grade_". strtolower($ects_mark) . "_short") . ": " . $this->lng->txt("ects_grade_". strtolower($ects_mark)) . ")";
		}	
 
		$this->tpl->setVariable("GRADE", $mark);
		$this->tpl->setVariable("TITLE", $this->object->getTitle());
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->parseCurrentBlock();
	}
	
	function outProcessingTime() 
	{
		global $ilUser;
		$this->tpl->setCurrentBlock("enableprocessingtime");
		$working_time = $this->object->getCompleteWorkingTime($ilUser->id);
		$processing_time = $this->object->getProcessingTimeInSeconds();
		$time_seconds = $working_time;
		$time_hours    = floor($time_seconds/3600);
		$time_seconds -= $time_hours   * 3600;
		$time_minutes  = floor($time_seconds/60);
		$time_seconds -= $time_minutes * 60;
		$this->tpl->setVariable("USER_WORKING_TIME", $this->lng->txt("tst_time_already_spent") . ": " . sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
		$time_seconds = $processing_time;
		$time_hours    = floor($time_seconds/3600);
		$time_seconds -= $time_hours   * 3600;
		$time_minutes  = floor($time_seconds/60);
		$time_seconds -= $time_minutes * 60;
		$this->tpl->setVariable("MAXIMUM_PROCESSING_TIME", $this->lng->txt("tst_processing_time") . ": " . sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
		$this->tpl->parseCurrentBlock();
	}
	
/**
	* Output of the learners view of an existing test without evaluation
	*
	* @access public
	*/
	function outTestSummary() 
	{
		global $ilUser;

		// handle solved state
		if (is_numeric($_GET["set_solved"]) && is_numeric($_GET["question_id"]))		 
		{
			$this->object->setQuestionSetSolved($_GET["set_solved"] , $_GET["question_id"], $ilUser->getId());
		}
			
		function sort_title($a, $b) 
		{
			if (strcmp($_GET["order"], "ASC")) 
			{
				$smaller = 1;
				$greater = -1;
			} 
			else 
			{
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			if (strcmp($a["title"],$b["title"])< 0)
				return $smaller;
			else if (strcmp($a["title"],$b["title"])> 0)
				return $greater;
			return 0;
		}
		
		
		function sort_nr($a, $b) 
		{
			if (strcmp($_GET["order"], "ASC")) 
			{
				$smaller = 1;
				$greater = -1;
			} 
			else 
			{
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			return ($a["nr"] < $b["nr"]) ? $smaller : $greater;
		}
		
		function sort_visited($a, $b) 
		{
			if (strcmp($_GET["order"], "ASC")) 
			{
				$smaller = 1;
				$greater = -1;
			} 
			else 
			{
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) 
				return 0;
			return ($a["visited"] < $b["visited"]) ? $smaller : $greater;
		}

		
		function sort_solved($a, $b) 
		{
			if (strcmp($_GET["order"], "ASC")) 
			{
				$smaller = 1;
				$greater = -1;
			} 
			else 
			{
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			return ($a["solved"] < $b["solved"]) ? $smaller : $greater;
		}

		$this->prepareOutput();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_summary.html", true);
		$user_id = $ilUser->id;
		$color_class = array ("tblrow1", "tblrow2");
		$counter = 0;
		
		$result_array = & $this->object->getTestSummary($user_id);
		
		$img_title_nr = "";
		$img_title_title = "";
		$img_title_solved = "";
		
		if (!$_GET["sort_summary"] )
		{
			$_GET["sort_summary"]  = "nr";
			$_GET["order"] = "ASC";
		} 
		
		switch ($_GET["sort_summary"]) 
		{
			case nr:
				usort($result_array, "sort_nr");
				$img_title_nr = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"\" />";
				if (strcmp($_GET["order"], "ASC") == 0) 
				{
					$sortnr = "DESC";
				} 
				else 
				{
					$sortnr = "ASC";
				}
				break;			
			
			case "title":
				usort($result_array, "sort_title");
				$img_title_title = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"\" />";
				if (strcmp($_GET["order"], "ASC") == 0) 
				{
					$sorttitle = "DESC";
				} 
				else 
				{
					$sorttitle = "ASC";
				}
				break;
			case "solved":
				usort($result_array, "sort_solved");
				$img_title_solved = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"\" />";
				if (strcmp($_GET["order"], "ASC") == 0) 
				{
					$sortsolved = "DESC";
				} 
				else 
				{
					$sortsolved = "ASC";
				}
				break;			
		}
		if (!$sorttitle) 
		{
			$sorttitle = "ASC";
		}
		if (!$sortsolved) 
		{
			$sortsolved = "ASC";
		}
		if (!$sortnr) 
		{
			$sortnr = "ASC";
		}
		
		$img_solved = " <img border=\"0\"  align=\"middle\" src=\"" . ilUtil::getImagePath("solved.png", true) . "\" alt=\"".$this->lng->txt("tst_click_to_change_state")."\" />";
		$img_not_solved = " <img border=\"0\" align=\"middle\" src=\"" . ilUtil::getImagePath("not_solved.png", true) . "\" alt=\"".$this->lng->txt("tst_click_to_change_state")."\" />";
		$goto_question =  " <img border=\"0\" align=\"middle\" src=\"" . ilUtil::getImagePath("goto_question.png", true) . "\" alt=\"".$this->lng->txt("tst_qst_goto")."\" />";
		
		$disabled = $this->isMaxProcessingTimeReached() | $this->object->endingTimeReached();
		
		foreach ($result_array as $key => $value) 
		{
			if (preg_match("/\d+/", $key)) 
			{
				$this->tpl->setCurrentBlock("question");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				$this->tpl->setVariable("VALUE_QUESTION_TITLE", $value["title"]);
				$this->tpl->setVariable("VALUE_QUESTION_VISITED", ($value["visited"] > 0) ? " checked=\"checked\" ": ""); 
				$this->tpl->setVariable("VALUE_QUESTION_SOLVED", ($value["solved"] > 0) ?$img_solved : $img_not_solved);  
				if (!$disabled)
				{
					$this->ctrl->setParameter($this, "sequence", $value["nr"]);
					$this->tpl->setVariable("VALUE_QUESTION_HREF_GOTO", "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "gotoQuestion")."\">");
					$this->ctrl->setParameter($this, "sequence", $_GET["sequence"]);
				}
				$this->tpl->setVariable("VALUE_QUESTION_GOTO", $goto_question);
				$solvedvalue = (($value["solved"]) ? "0" : "1");
				$this->tpl->setVariable("VALUE_QUESTION_HREF_SET_SOLVED", $this->ctrl->getLinkTargetByClass(get_class($this), "outTestSummary")."&order=".$_GET["order"]."&sort_summary=".$_GET["sort_summary"]."&set_solved=" . $solvedvalue."&question_id=".$value["qid"]);
				$this->tpl->setVariable("VALUE_QUESTION_SET_SOLVED", ($value["solved"] > 0) ?$this->lng->txt("tst_qst_resetsolved"):$this->lng->txt("tst_qst_setsolved"));
				$this->tpl->setVariable("VALUE_QUESTION_DESCRIPTION", $value["description"]);
				$this->tpl->setVariable("VALUE_QUESTION_POINTS", $value["points"]."&nbsp;".$this->lng->txt("points_short"));
				$this->tpl->parseCurrentBlock();
				$counter ++;
			}
		}

		$this->tpl->setCurrentBlock("results");
		$this->tpl->setVariable("QUESTION_ACTION","actions");
		$this->tpl->setVariable("QUESTION_COUNTER","<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "outTestSummary")."&order=$sortnr&sort_summary=nr\">".$this->lng->txt("tst_qst_order")."</a>".$img_title_nr);
		$this->tpl->setVariable("QUESTION_TITLE", "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "outTestSummary")."&order=$sorttitle&sort_summary=title\">".$this->lng->txt("tst_question_title")."</a>".$img_title_title);
		$this->tpl->setVariable("QUESTION_VISITED", "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "outTestSummary")."&order=$sortvisited&sort_summary=visited\">".$this->lng->txt("tst_question_visited")."</a>".$img_title_visited);
		$this->tpl->setVariable("QUESTION_SOLVED", "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "outTestSummary")."&order=$sortsolved&sort_summary=solved\">".$this->lng->txt("tst_question_solved_state")."</a>".$img_title_solved);
		$this->tpl->setVariable("QUESTION_POINTS", $this->lng->txt("tst_maximum_points"));
		$this->tpl->setVariable("USER_FEEDBACK", $this->lng->txt("tst_qst_summary_text"));
		$this->tpl->setVariable("TXT_SHOW_AND_SUBMIT_ANSWERS", $this->lng->txt("save_finish"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));	
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("summary"));		
		$this->tpl->parseCurrentBlock();
		
		if (!$disabled) 
		{
			$this->tpl->setCurrentBlock("back");
			$this->tpl->setVariable("TXT_BACK", $this->lng->txt("back"));
			$this->tpl->parseCurrentBlock();
		} 
		else 
		{
			sendinfo($this->lng->txt("detail_max_processing_time_reached"));
		}
		
		if ($this->object->getEnableProcessingTime())
			$this->outProcessingTime();
	}
	
	function finalSubmission()
	{
		global $ilias, $ilUser;
		
		$this->object->setActiveTestSubmitted($ilUser->id);
		$ilias->auth->setIdle($ilias->ini->readVariable("session","expire"), false);
		$ilias->auth->setExpire(0);
		$this->outIntroductionPage();
	}
	
}
?>
