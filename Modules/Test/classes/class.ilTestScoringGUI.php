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

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";
include_once "./Modules/Test/classes/class.ilTestServiceGUI.php";

/**
* Scoring class for tests
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesTest
* @extends ilTestServiceGUI
*/
class ilTestScoringGUI extends ilTestServiceGUI
{
	
/**
* ilTestScoringGUI constructor
*
* The constructor takes the test object reference as parameter 
*
* @param object $a_object Associated ilObjTest class
* @access public
*/
  function ilTestScoringGUI($a_object)
  {
		parent::ilTestServiceGUI($a_object);
		$this->ctrl->saveParameter($this, "active_id");
		$this->ctrl->saveParameter($this, "pass");
	}
	
	/**
	* Selects a participant for manual scoring
	*/
	function selectParticipant()
	{
		$this->manscoring($_POST["participants"]);
	}

	/**
	* Shows the test scoring GUI
	*
	* @param integer $active_id The acitve ID of the participant to score
	*/
	function manscoring($active_id = 0)
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}

		include_once "./classes/class.ilObjAssessmentFolder.php";
		$scoring = ilObjAssessmentFolder::_getManualScoring();
		if (count($scoring) == 0)
		{
			// allow only if question types are marked for manual scoring
			ilUtil::sendInfo($this->lng->txt("manscoring_not_allowed"));
			return;
		}
		
		if ((!($active_id > 0)) && (array_key_exists("active_id", $_GET)))
		{
			if (strlen($_GET["active_id"]))	$active_id = $_GET["active_id"];
		}
		$pass = $this->object->_getResultPass($active_id);
		if (array_key_exists("pass", $_GET))
		{
			if (strlen($_GET["pass"]))
			{
				$maxpass = $this->object->_getMaxPass($active_id);	
				if ($_GET["pass"] <= $maxpass) $pass = $_GET["pass"];
			}
		}
		
		$participants =& $this->object->getTestParticipants();
		if (count($participants) == 0)	
		{
			ilUtil::sendInfo($this->lng->txt("tst_participants_no"));
			return;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_manual_scoring.html", "Modules/Test");
		$counter = 1;
		foreach ($participants as $participant_active_id => $data)
		{
			$this->tpl->setCurrentBlock("participants");
			$this->tpl->setVariable("ID_PARTICIPANT", $data->active_id);
			$suffix = "";
			if ($this->object->getAnonymity())
			{
				$suffix = " " . $counter++;
			}
			if ($active_id > 0)
			{
				if ($active_id == $data->active_id)
				{
					$this->tpl->setVariable("SELECTED_PARTICIPANT", " selected=\"selected\""); 
				}
			}
			$this->tpl->setVariable("TEXT_PARTICIPANT", $this->object->userLookupFullName($data->usr_id, FALSE, TRUE, $suffix)); 
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
		$this->tpl->setVariable("BUTTON_SELECT", $this->lng->txt("select"));
		$this->tpl->setVariable("TEXT_SELECT_USER", $this->lng->txt("manscoring_select_user"));
		
		if ($active_id > 0)
		{
			// print pass overview
			if ($this->object->getNrOfTries() != 1)
			{
				$overview = $this->getPassOverview($active_id, "iltestscoringgui", "manscoring");
				$this->tpl->setVariable("PASS_OVERVIEW", $overview);
			}
			// print pass details with scoring
			if (strlen($pass))
			{
				$result_array =& $this->object->getTestResult($active_id, $pass);
				$scoring = $this->getPassListOfAnswersWithScoring($result_array, $active_id, $pass, TRUE);
				$this->tpl->setVariable("SCORING_DATA", $scoring);
			}
		}
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "selectParticipant"));
		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex");
		include_once "./classes/class.ilObject.php";
		$obj_id = ilObject::_lookupObjectId($_GET["ref_id"]);
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, "fdb", "assessment");
	}
	
	/**
	* Sets the points of a question manually
	*/
	function setPointsManual()
	{
		if (array_key_exists("question", $_POST))
		{
			$keys = array_keys($_POST["question"]);
			$question_id = $keys[0];
			$points = $_POST["question"][$question_id];
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$maxpoints = assQuestion::_getMaximumPoints($question_id);
			$result = assQuestion::_setReachedPoints($_GET["active_id"], $question_id, $points, $maxpoints, $_GET["pass"]);
			if ($result) 
			{
				ilUtil::sendInfo($this->lng->txt("tst_change_points_done"));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("tst_change_points_not_done"));
			}
		}
		$this->manscoring();
	}
	
	function setFeedbackManual()
	{
		if (array_key_exists("feedback", $_POST))
		{
			$feedbacks = array_keys($_POST["feedback"]);
			$question_id = $feedbacks[0];
			include_once "./classes/class.ilObjAdvancedEditing.php";
			$feedback = ilUtil::stripSlashes($_POST["feedback"][$question_id], FALSE, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
			$result = $this->object->saveManualFeedback($_GET["active_id"], $question_id, $_GET["pass"], $feedback);
			if ($result) 
			{
				ilUtil::sendInfo($this->lng->txt("tst_set_feedback_done"));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("tst_set_feedback_not_done"));
			}
		}
		$this->setPointsManual();
	}

}

?>
