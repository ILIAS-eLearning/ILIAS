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
require_once "./assessment/classes/class.assOrderingQuestion.php";

/**
* Ordering question GUI representation
*
* The ASS_OrderingQuestionGUI class encapsulates the GUI representation
* for ordering questions.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assOrderingQuestionGUI.php
* @modulegroup   Assessment
*/
class ASS_OrderingQuestionGUI extends ASS_QuestionGUI
{

	/**
	* ASS_OrderingQuestionGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_OrderingQuestionGUI object.
	*
	* @param integer $id The database id of a ordering question object
	* @access public
	*/
	function ASS_OrderingQuestionGUI(
			$id = -1
	)
	{
		$this->ASS_QuestionGUI();
		$this->object = new ASS_OrderingQuestion();
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
		return "qt_ordering";
	}

	function getCommand($cmd)
	{
		if (substr($cmd, 0, 6) == "delete")
		{
			$cmd = "delete";
		}
		if (substr($cmd, 0, 6) == "upload")
		{
			$cmd = "upload";
		}

		return $cmd;
	}


	/**
	* Creates an output of the edit form for the question
	*
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	function editQuestion($ok = true)
	{
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->getQuestionTemplate("qt_ordering");
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_ordering.html", true);
		$this->tpl->addBlockFile("OTHER_QUESTION_DATA", "other_question_data", "tpl.il_as_qpl_other_question_data.html", true);

		// Output of existing answers
		for ($i = 0; $i < $this->object->get_answer_count(); $i++)
		{
			$this->tpl->setCurrentBlock("deletebutton");
			$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("ANSWER_ORDER", $i);
			$this->tpl->parseCurrentBlock();

			$thisanswer = $this->object->get_answer($i);
			if ($this->object->get_ordering_type() == OQ_PICTURES)
			{
				$this->tpl->setCurrentBlock("order_pictures");
				$this->tpl->setVariable("ANSWER_ORDER", $i);
				$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $thisanswer->get_order() + 1);
				$this->tpl->setVariable("TEXT_ANSWER_PICTURE", $this->lng->txt("answer_picture"));

				$filename = $thisanswer->get_answertext();
				if ($filename)
				{
					$imagepath = $this->object->getImagePathWeb() . $thisanswer->get_answertext();
					$this->tpl->setVariable("UPLOADED_IMAGE", "<img src=\"$imagepath.thumb.jpg\" alt=\"" . $thisanswer->get_answertext() . "\" border=\"\" />");
					$this->tpl->setVariable("IMAGE_FILENAME", htmlspecialchars($thisanswer->get_answertext()));
					$this->tpl->setVariable("VALUE_ANSWER", "");
					//$thisanswer->get_answertext()
				}
				$this->tpl->setVariable("UPLOAD", $this->lng->txt("upload"));
			}
			elseif ($this->object->get_ordering_type() == OQ_TERMS)
			{
				$this->tpl->setCurrentBlock("order_terms");
				$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $thisanswer->get_order() + 1);
				$this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
				$this->tpl->setVariable("ANSWER_ORDER", $i);
				$this->tpl->setVariable("VALUE_ANSWER", htmlspecialchars($thisanswer->get_answertext()));
			}
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("answers");
			$anchor = "#answer_" . ($thisanswer->get_order() + 1);
			$this->tpl->setVariable("TEXT_SOLUTION_ORDER", $this->lng->txt("solution_order"));
			$this->tpl->setVariable("ANSWER_ORDER", $thisanswer->get_order());
			$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
			$this->tpl->setVariable("VALUE_ORDER", $thisanswer->get_solution_order());
			$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
			$this->tpl->setVariable("VALUE_ORDERING_POINTS", sprintf("%d", $thisanswer->get_points()));
			$this->tpl->parseCurrentBlock();
		}

		if (($this->ctrl->getCmd() == "addItem") and ($ok))
		{
			if ($this->object->get_ordering_type() == OQ_PICTURES)
			{
				$this->tpl->setCurrentBlock("order_pictures");
				$this->tpl->setVariable("ANSWER_ORDER", $this->object->get_answer_count());
				$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $this->object->get_answer_count() + 1);
				$this->tpl->setVariable("VALUE_ANSWER", "");
				$this->tpl->setVariable("UPLOAD", $this->lng->txt("upload"));
				$this->tpl->setVariable("TEXT_ANSWER_PICTURE", $this->lng->txt("answer_picture"));
			}
			elseif ($this->object->get_ordering_type() == OQ_TERMS)
			{
				$this->tpl->setCurrentBlock("order_terms");
				$this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
				$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $this->object->get_answer_count() + 1);
				$this->tpl->setVariable("ANSWER_ORDER", $this->object->get_answer_count());
				$this->tpl->setVariable("VALUE_ASNWER", "");
			}
			$this->tpl->parseCurrentBlock();

			// Create an empty answer
			$this->tpl->setCurrentBlock("answers");
			//$this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
			$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
			$anchor = "#answer_" . ($this->object->get_answer_count() + 1);
			$this->tpl->setVariable("TEXT_SOLUTION_ORDER", $this->lng->txt("solution_order"));
			$this->tpl->setVariable("ANSWER_ORDER", $this->object->get_answer_count());
			$this->tpl->setVariable("VALUE_ORDER", $this->object->get_max_solution_order() + 1);
			$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
			$this->tpl->setVariable("VALUE_ORDERING_POINTS", sprintf("%d", 0));
			$this->tpl->parseCurrentBlock();
		}
		// call to other question data i.e. estimated working time block
		$this->outOtherQuestionData();

		$this->tpl->setCurrentBlock("question_data");
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_SHUFFLE_ANSWERS", $this->lng->txt("shuffle_answers"));
		$this->tpl->setVariable("TXT_YES", $this->lng->txt("yes"));
		$this->tpl->setVariable("TXT_NO", $this->lng->txt("no"));
		if ($this->object->getShuffle())
		{
			$this->tpl->setVariable("SELECTED_YES", " selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SELECTED_NO", " selected=\"selected\"");
		}
		$this->tpl->setVariable("ORDERING_ID", $this->object->getId());
		$this->tpl->setVariable("VALUE_ORDERING_TITLE", htmlspecialchars($this->object->getTitle()));
		$this->tpl->setVariable("VALUE_ORDERING_COMMENT", htmlspecialchars($this->object->getComment()));
		$this->tpl->setVariable("VALUE_ORDERING_AUTHOR", htmlspecialchars($this->object->getAuthor()));
		$this->tpl->setVariable("VALUE_QUESTION", htmlspecialchars($this->object->get_question()));
		$this->tpl->setVariable("VALUE_ADD_ANSWER", $this->lng->txt("add_answer"));
		$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("type"));
		$this->tpl->setVariable("TEXT_TYPE_PICTURES", $this->lng->txt("order_pictures"));
		$this->tpl->setVariable("TEXT_TYPE_TERMS", $this->lng->txt("order_terms"));
		if ($this->object->get_ordering_type() == OQ_TERMS)
		{
			$this->tpl->setVariable("SELECTED_TERMS", " selected=\"selected\"");
		}
		elseif ($this->object->get_ordering_type() == OQ_PICTURES)
		{
			$this->tpl->setVariable("SELECTED_PICTURES", " selected=\"selected\"");
		}

		$this->tpl->setVariable("TEXT_SOLUTION_HINT", $this->lng->txt("solution_hint"));
		if ($this->object->getSolutionHint())
		{
			$this->tpl->setVariable("TEXT_VALUE_SOLUTION_HINT", " <a href=\"" . ILIAS_HTTP_PATH . "/content/lm_presentation.php?ref_id=" . $this->object->getSolutionHint() . "\" target=\"content\">" . $this->lng->txt("solution_hint"). "</a> ");
			$this->tpl->setVariable("BUTTON_REMOVE_SOLUTION", $this->lng->txt("remove_solution"));
			$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("change_solution"));
		}
		else
		{
			$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("add_solution"));
		}
		$this->tpl->setVariable("VALUE_SOLUTION_HINT", $this->object->getSolutionHint());

		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("SAVE_EDIT", $this->lng->txt("save_edit"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->ctrl->setParameter($this, "sel_question_types", "qt_ordering");
		$this->tpl->setVariable("ACTION_ORDERING_QUESTION",	$this->ctrl->getFormAction($this) . "#bottom");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->parseCurrentBlock();

		if ($this->error)
		{
			sendInfo($this->error);
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->parseCurrentBlock();
	}


	function addItem()
	{
		$ok = true;
		if (!$this->checkInput())
		{
			// You cannot add answers before you enter the required data
			$this->error .= $this->lng->txt("fill_out_all_required_fields_add_answer") . "<br />";
			$ok = false;
		}
		else
		{
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/answer_(\d+)/", $key, $matches))
				{
					if (!$value)
					{
						$ok = false;
					}
			 	}
			}
		}
		if (!$ok)
		{
			$this->error .= $this->lng->txt("fill_out_all_answer_fields") . "<br />";
		}

		$this->writePostData();
		$this->editQuestion($ok);
	}

	/**
	* delete matching pair
	*/
	function delete()
	{
		$this->writePostData();

		// Delete an answer if the delete button was pressed
		foreach ($_POST[cmd] as $key => $value)
		{
			if (preg_match("/delete_(\d+)/", $key, $matches))
			{
				$this->object->delete_answer($matches[1]);
			}
		}
		$this->editQuestion();
	}

	/**
	* upload matching picture
	*/
	function upload()
	{
		$this->writePostData();
		$this->editQuestion();
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
		$saved = false;

		// Delete all existing answers and create new answers from the form data
		$this->object->flush_answers();

		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		$this->object->set_question(ilUtil::stripSlashes($_POST["question"]));
		$this->object->setSolutionHint($_POST["solution_hint"]);
		$this->object->setShuffle($_POST["shuffle"]);

		// adding estimated working time
		$saved = $saved | $this->writeOtherPostData($result);
		$this->object->set_ordering_type($_POST["ordering_type"]);

		// Add answers from the form
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/answer_(\d+)/", $key, $matches))
			{
				if ($this->object->get_ordering_type() == OQ_PICTURES)
				{
					if ($_FILES[$key]["tmp_name"])
					{
						// upload the ordering picture
						if ($this->object->getId() <= 0)
						{
							$this->object->saveToDb();
							$saved = true;
							$this->error .= $this->lng->txt("question_saved_for_upload") . "<br />";
						}
						$upload_result = $this->object->set_image_file($_FILES[$key]['name'], $_FILES[$key]['tmp_name']);
						switch ($upload_result)
						{
							case 0:
								$_POST[$key] = $_FILES[$key]['name'];
								break;
							case 1:
								$this->error .= $this->lng->txt("error_image_upload_wrong_format") . "<br />";
								break;
							case 2:
								$this->error .= $this->lng->txt("error_image_upload_copy_file") . "<br />";
								break;
						}
					}
				}
				$points = $_POST["points_$matches[1]"];
				if (preg_match("/\d+/", $points))
				{
					if ($points < 0)
					{
						$points = 0.0;
						$this->error .= $this->lng->txt("negative_points_not_allowed") . "<br />";
					}
				}
				else
				{
					$points = 0.0;
				}
				$this->object->add_answer(
					ilUtil::stripSlashes($_POST["$key"]),
					ilUtil::stripSlashes($points),
					ilUtil::stripSlashes($matches[1]),
					ilUtil::stripSlashes($_POST["order_$matches[1]"])
				);
			}
		}

		if ($saved)
		{
			// If the question was saved automatically before an upload, we have to make
			// sure, that the state after the upload is saved. Otherwise the user could be
			// irritated, if he presses cancel, because he only has the question state before
			// the upload process.
			$this->object->saveToDb();
			$_GET["q_id"] = $this->object->getId();
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
		
		$output = $this->outQuestionPage("ORDERING_QUESTION", $is_postponed);
		$solutionoutput = preg_replace("/.*?(<div[^<]*?ilc_Question.*?<\/div>).*/", "\\1", $output);
		$solutionoutput = preg_replace("/\"ord/", "\"solution_ord", $solutionoutput);
		$solutionoutput = preg_replace("/name\=\"order_/", "name=\"solution_order_", $solutionoutput);
		// set solutions
		if ($test_id)
		{
			$solutions =& $this->object->getSolutionValues($test_id);
			foreach ($solutions as $idx => $solution_value)
			{
				$repl_str = "dummy=\"ord".$solution_value->value1."\"";
//echo "<br>".$repl_str;
				$output = str_replace($repl_str, $repl_str." value=\"".$solution_value->value2."\"", $output);
			}
		}

		foreach ($this->object->answers as $idx => $answer)
		{
			$repl_str = "dummy=\"solution_ord$idx\"";
			$solutionoutput = str_replace($repl_str, $repl_str." value=\"" . $answer->get_solution_order() . "\"", $solutionoutput);
			$solutionoutput = preg_replace("/(<tr.*?dummy=\"solution_ord$idx.*?)<\/tr>/", "\\1<td>" . "<em>(" . $answer->get_points() . " " . $this->lng->txt("points") . ")</em>" . "</td></tr>", $solutionoutput);
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
		$this->tpl->setVariable("ORDERING_QUESTION", $output.$solutionoutput.$received_points);
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
		$user_order = array();
		foreach ($results as $key => $value)
		{
			$user_order[$value["order"]] = $value["answer_id"];
		}
		ksort($user_order);
		$user_order = array_values($user_order);
		
		$answer_order = array();
		foreach ($this->answers as $key => $answer)
		{
			$answer_order[$answer->get_solution_order()] = $key;
		}
		ksort($answer_order);
		$answer_order = array_values($answer_order);
		foreach ($this->object->answers as $key => $answer)
		{
			$this->tpl->setCurrentBlock("tablerow");
			$array_key = array_search($key);
			if ($user_order[$array_key] == $answer_order[$array_key])
			{
				$this->tpl->setVariable("ANSWER_IMAGE", ilUtil::getImagePath("right.png", true));
				$this->tpl->setVariable("ANSWER_IMAGE_TITLE", $this->lng->txt("answer_is_right"));
			}
			else
			{
				$this->tpl->setVariable("ANSWER_IMAGE", ilUtil::getImagePath("wrong.png", true));
				$this->tpl->setVariable("ANSWER_IMAGE_TITLE", $this->lng->txt("answer_is_wrong"));
			}
			if ($this->object->get_ordering_type() == OQ_PICTURES)
			{
				$answertext = "<img src=\"" . $this->object->getImagePathWeb() . $answer->get_answertext() . ".thumb.jpg\" alt=\"" . $this->lng->txt("selected_image") . "\" />";
			}
			else
			{
				$answertext = "&quot;<em>" . $answer->get_answertext() . "</em>&quot;";
			}
			$this->tpl->setVariable("ANSWER_DESCRIPTION", $answertext);
			$this->tpl->setVariable("ANSWER_DESCRIPTION_CONNECTOR", $this->lng->txt("with_order") . " &quot;<em>" . $results[$key]["order"] . "</em>&quot;");
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* check input fields
	*/
	function checkInput()
	{
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
		{
			return false;
		}
		return true;
	}

	function addSuggestedSolution()
	{
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
		$this->getQuestionTemplate("qt_ordering");
		parent::addSuggestedSolution();
	}
}
?>
