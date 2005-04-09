<?php

class CommandControl 
{	
	var $gui;
	var $obj;
	var $lng;
	var $tpl;
	
	function CommandControl (&$gui, &$object) {
		$this->gui = & $gui;
		$this->obj = & $object;
		$this->lng = & $gui->lng;
		$this->tpl = & $gui->tpl;
	}
	
	function prepareRequestVariables (){
	}
	
	function onRunObjectEnter (){		
	}	
	
	function handleCommands () {
	}
		
}

class DefaultTestCommandControl extends CommandControl {
	
	function DefaultTestCommandControl (&$gui, &$object) {
		parent::CommandControl($gui, $object);
	}
			
	function prepareRequestVariables (){
		// set showresult cmd if pressed on sort in result overview
		if ($_GET["sortres"])
			$_POST["cmd"]["showresults"] = 1; 
	}
	
	function onRunObjectEnter (){
		// cancel Test if it's not online test
		if ($_POST["cmd"]["cancelTest"])
		{
			$this->gui->handleCancelCommand();
		}		
		// check online exams access restrictions due to participants and client ip
	}	
	
	function handleCommands () {
		global $ilUser;
		if ($_POST["cmd"]["confirmdeleteresults"])
		{
			$this->obj->deleteResults($ilUser->id);
			sendInfo($this->gui->lng->txt("tst_confirm_delete_results_info"));
		}
		
		if ($_POST["cmd"]["deleteresults"])
		{
			$this->gui->confirmDeleteResults();
			return true;
		}
		
		if ($_GET["evaluation"])
		{
			$this->gui->outEvaluationForm();
			return true;
		}

		if (($_POST["cmd"]["showresults"]) or ($_GET["sortres"]))
		{
			$this->gui->outTestResults();
			return true;
		}
		
		return false;		
	}
	
	function handleStartCommands () {
		global $ilUser;
			
		if ($_POST["cmd"]["start"] && $this->obj->isRandomTest())
		{
			if ($this->obj->getRandomQuestionCount() > 0)
			{
				$qpls =& $this->obj->getRandomQuestionpools();
				$rndquestions = $this->obj->randomSelectQuestions($this->obj->getRandomQuestionCount(), 0, 1, $qpls);
				$allquestions = array();
				foreach ($rndquestions as $question_id)
				{
					array_push($allquestions, $question_id);
				}
				srand ((float)microtime()*1000000);
				shuffle($allquestions);
				foreach ($allquestions as $question_id)
				{
					$this->obj->saveRandomQuestion($question_id);
				}
				$this->obj->loadQuestions();
			}
			else
			{
				$qpls =& $this->obj->getRandomQuestionpools();
				$allquestions = array();
				foreach ($qpls as $key => $value)
				{
					if ($value["count"] > 0)
					{
						$rndquestions = $this->obj->randomSelectQuestions($value["count"], $value["qpl"], 1);
						foreach ($rndquestions as $question_id)
						{
							array_push($allquestions, $question_id);
						}
					}
				}
				srand ((float)microtime()*1000000);
				shuffle($allquestions);
				foreach ($allquestions as $question_id)
				{
					$this->obj->saveRandomQuestion($question_id);
				}
				$this->obj->loadQuestions();
			}
		}
			
		// create new time dataset and set start time
		$active_time_id = $this->obj->startWorkingTime($ilUser->id);
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
		
		return true;
				
	}
		
	function handleCancelCommand (){
		sendInfo($this->gui->lng->txt("test_cancelled"), true);
		$path = $this->gui->tree->getPathFull($this->obj->getRefID());
		ilUtil::redirect($this->gui->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
		exit();
	}
	
	function getSequence () {
		$sequence = $_GET["sequence"];
		$saveResult = $this->gui->saveResult;
		
		if ($_POST["cmd"]["deleteresults"] or $_POST["cmd"]["canceldeleteresults"] or $_POST["cmd"]["confirmdeleteresults"])
		{
			// reset sequence. it is not needed for test reset
			$sequence = "";
		}
							
		if (isset($_POST["cmd"]["next"]) and $saveResult == true)
		{
			if($_GET['crs_show_result'])
			{
				$sequence = $this->obj->incrementSequenceByResult($sequence);
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
				$sequence = $this->obj->decrementSequenceByResult($sequence);
			}
			else
			{
				$sequence--;
			}
		}		
		
		return $sequence;
	}
	
	// logic functions to determin control flow
	
	function isTestResumable () {
		$active = $this->obj->getActiveTestUser();		
		return is_object($active) && $this->obj->startingTimeReached() && !$this->obj->endingTimeReached();
	}
	
	function isNrOfTriesReached () {
		$active = $this->obj->getActiveTestUser();
		return $this->obj->hasNrOfTriesRestriction() && is_object($active) && $this->obj->isNrOfTriesReached ($active->tries);	
	}
	
	function isTestAccessible() {		
		return 	!$this->isNrOfTriesReached() 				
			 	and	 !$this->gui->isMaxProcessingTimeReached()
			 	and  $this->obj->startingTimeReached()
			 	and  !$this->gui->isEndingTimeReached();
	}
	
	function showTestResults () {
		return $_GET['crs_show_result'];// && $this->obj->canViewResults();
	}
	
	function canShowTestResults () {
		$active = $this->obj->getActiveTestUser();
		return ($active->tries > 0) and $this->obj->canViewResults();
	}
	
}




?>