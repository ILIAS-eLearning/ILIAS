<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

require_once "./assessment/classes/class.assQuestionGUI.php";
require_once "./assessment/classes/class.assTextQuestion.php";

/**
* Text question GUI representation
*
* The ASS_TextQuestionGUI class encapsulates the GUI representation
* for text questions.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assTextQuestionGUI.php
* @modulegroup   assessment
*/
class ASS_TextQuestionGUI extends ASS_QuestionGUI
{
	/**
	* ASS_TextQuestionGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_TextQuestionGUI object.
	*
	* @param integer $id The database id of a text question object
	* @access public
	*/
	function ASS_TextQuestionGUI(
			$id = -1
	)
	{
		$this->ASS_QuestionGUI();
		$this->object = new ASS_TextQuestion();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	/**
	* Returns the question type string
	*
	* Returns the question type string
	*
	* @result string The question type string
	* @access public
	*/
	function getQuestionType()
	{
		return "qt_text";
	}

	/**
	* Creates an output of the edit form for the question
	*
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	function editQuestion()
	{
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$javascript = "<script type=\"text/javascript\">function initialSelect() {\n%s\n}</script>";
		// single response
		$this->getQuestionTemplate("qt_text");
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_text_question.html", true);
		// call to other question data i.e. estimated working time block
		$this->outOtherQuestionData();

		$internallinks = array(
			"lm" => $this->lng->txt("obj_lm"),
			"st" => $this->lng->txt("obj_st"),
			"pg" => $this->lng->txt("obj_pg"),
			"glo" => $this->lng->txt("glossary_term")
		);
		foreach ($internallinks as $key => $value)
		{
			$this->tpl->setCurrentBlock("internallink");
			$this->tpl->setVariable("TYPE_INTERNAL_LINK", $key);
			$this->tpl->setVariable("TEXT_INTERNAL_LINK", $value);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("HeadContent");
		$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_text_question.title.focus();"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("question_data");
		$this->tpl->setVariable("TEXT_QUESTION_ID", $this->object->getId());
		$this->tpl->setVariable("VALUE_TEXT_QUESTION_TITLE", htmlspecialchars($this->object->getTitle()));
		$this->tpl->setVariable("VALUE_TEXT_QUESTION_COMMENT", htmlspecialchars($this->object->getComment()));
		$this->tpl->setVariable("VALUE_TEXT_QUESTION_AUTHOR", htmlspecialchars($this->object->getAuthor()));
		$questiontext = $this->object->get_question();
		$questiontext = preg_replace("/<br \/>/", "\n", $questiontext);
		$this->tpl->setVariable("VALUE_QUESTION", htmlspecialchars($questiontext));
		if ($this->object->getMaxNumOfChars())
		{
			$this->tpl->setVariable("VALUE_MAXCHARS", htmlspecialchars($this->object->getMaxNumOfChars()));
		}
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_MAXCHARS", $this->lng->txt("maxchars"));
		$this->tpl->setVariable("DESCRIPTION_MAXCHARS", $this->lng->txt("description_maxchars"));
		$this->tpl->setVariable("TEXT_SOLUTION_HINT", $this->lng->txt("solution_hint"));
		if (count($this->object->suggested_solutions))
		{
			$solution_array = $this->object->getSuggestedSolution(0);
			$href = ASS_Question::_getInternalLinkHref($solution_array["internal_link"]);
			$this->tpl->setVariable("TEXT_VALUE_SOLUTION_HINT", " <a href=\"$href\" target=\"content\">" . $this->lng->txt("solution_hint"). "</a> ");
			$this->tpl->setVariable("BUTTON_REMOVE_SOLUTION", $this->lng->txt("remove"));
			$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("change"));
			$this->tpl->setVariable("VALUE_SOLUTION_HINT", $solution_array["internal_link"]);
		}
		else
		{
			$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("add"));
		}
		$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
		$this->tpl->setVariable("SAVE_EDIT", $this->lng->txt("save_edit"));
		$this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->ctrl->setParameter($this, "sel_question_types", "qt_text");
		$this->tpl->setVariable("ACTION_TEXT_QUESTION", $this->ctrl->getFormAction($this));

		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"initialSelect();\""); 
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Sets the extra fields i.e. estimated working time of a question from a posted create/edit form
	*
	* Sets the extra fields i.e. estimated working time of a question from a posted create/edit form
	*
	* @access private
	*/
	function outOtherQuestionData()
	{
		$this->tpl->setCurrentBlock("other_question_data");
		$est_working_time = $this->object->getEstimatedWorkingTime();
		$this->tpl->setVariable("TEXT_WORKING_TIME", $this->lng->txt("working_time"));
		$this->tpl->setVariable("TIME_FORMAT", $this->lng->txt("time_format"));
		$this->tpl->setVariable("VALUE_WORKING_TIME", ilUtil::makeTimeSelect("Estimated", false, $est_working_time[h], $est_working_time[m], $est_working_time[s]));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* check input fields
	*/
	function checkInput()
	{
		$cmd = $this->ctrl->getCmd();

		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
		{
			return false;
		}
		return true;
	}

	/**
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	function writePostData()
	{
		$result = 0;
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
		{
			$result = 1;
		}

		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		$questiontext = ilUtil::stripSlashes($_POST["question"], true, "<strong><em><code><cite>");
		$questiontext = preg_replace("/\n/", "<br />", $questiontext);
		$this->object->set_question($questiontext);
		$this->object->setSuggestedSolution($_POST["solution_hint"], 0);
		$this->object->setMaxNumOfChars($_POST["maxchars"]);

		$saved = $this->writeOtherPostData($result);

		// Set the question id from a hidden form parameter
		if ($_POST["text_question_id"] > 0)
		{
			$this->object->setId($_POST["text_question_id"]);
		}
		
		return $result;
	}

	/**
	* Creates the question output form for the learner
	*
	* Creates the question output form for the learner
	*
	* @access public
	*/
	function outWorkingForm($test_id = "", $is_postponed = false, $showsolution = 0)
	{
		global $ilUser;
		$output = $this->outQuestionPage("MULTIPLE_CHOICE_QUESTION", $is_postponed);
//		preg_match("/(<div[^<]*?ilc_Question.*?<\/div>)/is", $output, $matches);
//		$solutionoutput = $matches[1];
		$solutionoutput = preg_replace("/.*?(<div[^<]*?ilc_Question.*?<\/div>).*/", "\\1", $output);
		$solutionoutput = preg_replace("/\"mc/", "\"solution_mc", $solutionoutput);
		$solutionoutput = preg_replace("/multiple_choice_result/", "solution_multiple_choice_result", $solutionoutput);
		// set solutions
		if ($test_id)
		{
			$solutions =& $this->object->getSolutionValues($test_id);
			foreach ($solutions as $idx => $solution_value)
			{
				$repl_str = "dummy=\"mc".$solution_value->value1."\"";
//echo "<br>".htmlentities($repl_str);
				$output = str_replace($repl_str, $repl_str." checked=\"checked\"", $output);
			}
		}
		
		foreach ($this->object->answers as $idx => $answer)
		{
			if ($answer->isStateChecked())
			{
				$repl_str = "dummy=\"solution_mc$idx\"";
				$solutionoutput = str_replace($repl_str, $repl_str." checked=\"checked\"", $solutionoutput);
			}
			$solutionoutput = preg_replace("/(<tr.*?dummy=\"solution_mc$idx.*?)<\/tr>/", "\\1<td>" . "<em>(" . $answer->get_points() . " " . $this->lng->txt("points") . ")</em>" . "</td></tr>", $solutionoutput);
		}

		$solutionoutput = "<p>" . $this->lng->txt("correct_solution_is") . ":</p><p>$solutionoutput</p>";
		if ($test_id) 
		{
			$received_points = "<p>" . sprintf($this->lng->txt("you_received_a_of_b_points"), $this->object->getReachedPoints($ilUser->id, $test_id), $this->object->getMaximumPoints()) . "</p>";
		}
		if (!$showsolution)
		{
			$solutionoutput = "";
			$received_points = "";
		}
		$this->tpl->setVariable("MULTIPLE_CHOICE_QUESTION", $output.$solutionoutput.$received_points);
	}

	/**
	* Creates an output of the user's solution
	*
	* Creates an output of the user's solution
	*
	* @access public
	*/
	function outUserSolution($user_id, $test_id)
	{
		$results = $this->object->getReachedInformation($user_id, $test_id);
		foreach ($this->object->answers as $key => $answer)
		{
			$selected = 0;
			$this->tpl->setCurrentBlock("tablerow");
			if ($answer->isStateChecked())
			{
				$right = 0;
				foreach ($results as $reskey => $resvalue)
				{
					if ($resvalue["value"] == $key)
					{
						$right = 1;
						$selected = 1;
					}
				}
			}
			elseif ($answer->isStateUnchecked())
			{
				$right = 1;
				foreach ($results as $reskey => $resvalue)
				{
					if ($resvalue["value"] == $key)
					{
						$right = 0;
						$selected = 1;
					}
				}
			}
			if ($right)
			{
				$this->tpl->setVariable("ANSWER_IMAGE", ilUtil::getImagePath("right.png", true));
				$this->tpl->setVariable("ANSWER_IMAGE_TITLE", $this->lng->txt("answer_is_right"));
			}
			else
			{
				$this->tpl->setVariable("ANSWER_IMAGE", ilUtil::getImagePath("wrong.png", true));
				$this->tpl->setVariable("ANSWER_IMAGE_TITLE", $this->lng->txt("answer_is_wrong"));
			}
			if ($this->object->get_response() == RESPONSE_SINGLE)
			{
				$state = $this->lng->txt("unselected");
			}
			else
			{
				$state = $this->lng->txt("checkbox_unchecked");
			}
			if ($selected)
			{
				if ($this->object->get_response() == RESPONSE_SINGLE)
				{
					$state = $this->lng->txt("selected");
				}
				else
				{
					$state = $this->lng->txt("checkbox_checked");
				}
			}
			$this->tpl->setVariable("ANSWER_DESCRIPTION", "$state: " . "&quot;<em>" . $answer->get_answertext() . "</em>&quot;");
			$this->tpl->parseCurrentBlock();
		}
	}
	
	function addSuggestedSolution()
	{
		$_SESSION["subquestion_index"] = 0;
		if ($_POST["cmd"]["addSuggestedSolution"])
		{
			$this->writePostData();
			if (!$this->checkInput())
			{
				sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
				$this->editQuestion();
				return;
			}
		}
		$this->object->saveToDb();
		$_GET["q_id"] = $this->object->getId();
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->getQuestionTemplate("qt_text");
		parent::addSuggestedSolution();
	}
}
?>
