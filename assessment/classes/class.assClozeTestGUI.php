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

include_once "./assessment/classes/class.assQuestionGUI.php";

/**
* Cloze test question GUI representation
*
* The ASS_ClozeTestGUI class encapsulates the GUI representation
* for cloze test questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.assClozeTestGUI.php
* @modulegroup   Assessment
*/
class ASS_ClozeTestGUI extends ASS_QuestionGUI
{
	/**
	* ASS_ClozeTestGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_ClozeTestGUI object.
	*
	* @param integer $id The database id of a image map question object
	* @access public
	*/
	function ASS_ClozeTestGUI(
			$id = -1
	)
	{
		$this->ASS_QuestionGUI();
		include_once "./assessment/classes/class.assClozeTest.php";
		$this->object = new ASS_ClozeTest();
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
		return "qt_cloze";
	}


	function getCommand($cmd)
	{
		if (substr($cmd, 0, 6) == "delete")
		{
			$cmd = "delete";
		}
		if (substr($cmd, 0, 10) == "addTextGap")
		{
			$cmd = "addTextGap";
		}
		if (substr($cmd, 0, 12) == "addSelectGap")
		{
			$cmd = "addSelectGap";
		}
		if (substr($cmd, 0, 20) == "addSuggestedSolution")
		{
			$cmd = "addSuggestedSolution";
		}
		if (substr($cmd, 0, 23) == "removeSuggestedSolution")
		{
			$cmd = "removeSuggestedSolution";
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
	function editQuestion()
	{
		include_once "./assessment/classes/class.assQuestion.php";
		$internallinks = array(
			"lm" => $this->lng->txt("obj_lm"),
			"st" => $this->lng->txt("obj_st"),
			"pg" => $this->lng->txt("obj_pg"),
			"glo" => $this->lng->txt("glossary_term")
		);
		//$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->getQuestionTemplate("qt_cloze");
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_cloze_question.html", true);
		for ($i = 0; $i < $this->object->getGapCount(); $i++)
		{
			$gap = $this->object->getGap($i);
			if ($gap[0]->getClozeType() == CLOZE_TEXT)
			{
				$this->tpl->setCurrentBlock("textgap_value");
				foreach ($gap as $key => $value)
				{
					$this->tpl->setVariable("TEXT_VALUE", $this->lng->txt("value"));
					$this->tpl->setVariable("VALUE_TEXT_GAP", htmlspecialchars($value->getAnswertext()));
					$this->tpl->setVariable("VALUE_GAP_COUNTER", "$i" . "_" . "$key");
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("VALUE_INDEX", $key);
					$this->tpl->setVariable("VALUE_STATUS_COUNTER", $key);
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
					$this->tpl->setVariable("VALUE_TEXT_GAP_POINTS", sprintf("%d", $value->getPoints()));
					$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
					$this->tpl->parseCurrentBlock();
				}

				foreach ($internallinks as $key => $value)
				{
					$this->tpl->setCurrentBlock("textgap_internallink");
					$this->tpl->setVariable("TYPE_INTERNAL_LINK", $key);
					$this->tpl->setVariable("TEXT_INTERNAL_LINK", $value);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("textgap_suggested_solution");
				$this->tpl->setVariable("TEXT_SOLUTION_HINT", $this->lng->txt("solution_hint"));
				if (array_key_exists($i, $this->object->suggested_solutions))
				{
					$solution_array = $this->object->getSuggestedSolution($i);
					$href = ASS_Question::_getInternalLinkHref($solution_array["internal_link"]);
					$this->tpl->setVariable("TEXT_VALUE_SOLUTION_HINT", " <a href=\"$href\" target=\"content\">" . $this->lng->txt("solution_hint"). "</a> ");
					$this->tpl->setVariable("BUTTON_REMOVE_SOLUTION", $this->lng->txt("remove"));
					$this->tpl->setVariable("VALUE_GAP_COUNTER_REMOVE", $i);
					$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("change"));
					$this->tpl->setVariable("VALUE_SOLUTION_HINT", $solution_array["internal_link"]);
				}
				else
				{
					$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("add"));
				}
				$this->tpl->setVariable("VALUE_GAP_COUNTER", $i);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("textgap");
				$this->tpl->setVariable("ADD_TEXT_GAP", $this->lng->txt("add_gap"));
				$this->tpl->setVariable("VALUE_GAP_COUNTER", "$i");
				$this->tpl->parseCurrentBlock();
			}
			elseif ($gap[0]->getClozeType() == CLOZE_SELECT)
			{
				$this->tpl->setCurrentBlock("selectgap_value");
				foreach ($gap as $key => $value)
				{
					$this->tpl->setVariable("TEXT_VALUE", $this->lng->txt("value"));
					$this->tpl->setVariable("VALUE_SELECT_GAP", htmlspecialchars($value->getAnswertext()));
					$this->tpl->setVariable("VALUE_GAP_COUNTER", "$i" . "_" . "$key");
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("VALUE_INDEX", $key);
					$this->tpl->setVariable("VALUE_STATUS_COUNTER", $key);
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
					$this->tpl->setVariable("VALUE_SELECT_GAP_POINTS", sprintf("%d", $value->getPoints()));
					$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
					$this->tpl->parseCurrentBlock();
				}

				foreach ($internallinks as $key => $value)
				{
					$this->tpl->setCurrentBlock("selectgap_internallink");
					$this->tpl->setVariable("TYPE_INTERNAL_LINK", $key);
					$this->tpl->setVariable("TEXT_INTERNAL_LINK", $value);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("selectgap_suggested_solution");
				$this->tpl->setVariable("TEXT_SOLUTION_HINT", $this->lng->txt("solution_hint"));
				if (array_key_exists($i, $this->object->suggested_solutions))
				{
					$solution_array = $this->object->getSuggestedSolution($i);
					$href = ASS_Question::_getInternalLinkHref($solution_array["internal_link"]);
					$this->tpl->setVariable("TEXT_VALUE_SOLUTION_HINT", " <a href=\"$href\" target=\"content\">" . $this->lng->txt("solution_hint"). "</a> ");
					$this->tpl->setVariable("BUTTON_REMOVE_SOLUTION", $this->lng->txt("remove"));
					$this->tpl->setVariable("VALUE_GAP_COUNTER_REMOVE", $i);
					$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("change"));
					$this->tpl->setVariable("VALUE_SOLUTION_HINT", $solution_array["internal_link"]);
				}
				else
				{
					$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("add"));
				}
				$this->tpl->setVariable("VALUE_GAP_COUNTER", $i);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("selectgap");
				$this->tpl->setVariable("ADD_SELECT_GAP", $this->lng->txt("add_gap"));
				$this->tpl->setVariable("TEXT_SHUFFLE_ANSWERS", $this->lng->txt("shuffle_answers"));
				$this->tpl->setVariable("VALUE_GAP_COUNTER", "$i");
				if ($gap[0]->getShuffle())
				{
					$this->tpl->setVariable("SELECTED_YES", " selected=\"selected\"");
				}
				else
				{
					$this->tpl->setVariable("SELECTED_NO", " selected=\"selected\"");
				}
				$this->tpl->setVariable("TXT_YES", $this->lng->txt("yes"));
				$this->tpl->setVariable("TXT_NO", $this->lng->txt("no"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("answer_row");
			$name = $gap[0]->getName();
			if (!$name)
			{
				$name = $this->lng->txt("gap") . " " . ($i+1);
			}
			$this->tpl->setVariable("TEXT_GAP_NAME", $name);
			$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("type"));
			if ($gap[0]->getClozeType() == CLOZE_SELECT)
			{
				$this->tpl->setVariable("SELECTED_SELECT_GAP", " selected=\"selected\"");
			}
			else
			{
				$this->tpl->setVariable("SELECTED_TEXT_GAP", " selected=\"selected\"");
			}
			$this->tpl->setVariable("TEXT_TEXT_GAP", $this->lng->txt("text_gap"));
			$this->tpl->setVariable("TEXT_SELECT_GAP", $this->lng->txt("select_gap"));
			$this->tpl->setVariable("VALUE_GAP_COUNTER", $i);
			$this->tpl->parseCurrentBlock();
		}

		// call to other question data i.e. estimated working time block
		$this->outOtherQuestionData();

		$this->tpl->setCurrentBlock("HeadContent");
		$javascript = "<script type=\"text/javascript\">function initialSelect() {\n%s\n}</script>";
		if (preg_match("/addTextGap_(\d+)/", $this->ctrl->getCmd(), $matches))
		{
			$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_cloze_test.textgap_" . $matches[1] . "_" .(count($this->object->gaps[$matches[1]]) - 1).".focus(); document.frm_cloze_test.textgap_" . $matches[1] . "_" .(count($this->object->gaps[$matches[1]]) - 1).".scrollIntoView(\"true\");"));
		}
		else if (preg_match("/addSelectGap_(\d+)/", $this->ctrl->getCmd(), $matches))
		{
			$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_cloze_test.selectgap_" . $matches[1] . "_" .(count($this->object->gaps[$matches[1]]) - 1).".focus(); document.frm_cloze_test.selectgap_" . $matches[1] . "_" .(count($this->object->gaps[$matches[1]]) - 1).".scrollIntoView(\"true\");"));
		}
		else
		{
			$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_cloze_test.title.focus();"));
		}
		$this->tpl->parseCurrentBlock();
		
		// Add textgap rating options
		$textgap_options = array(
			array("ci", $this->lng->txt("cloze_textgap_case_insensitive")),
			array("cs", $this->lng->txt("cloze_textgap_case_sensitive")),
			array("l1", sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "1")),
			array("l2", sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "2")),
			array("l3", sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "3")),
			array("l4", sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "4")),
			array("l5", sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "5"))
		);
		$textgap_rating = $this->object->getTextgapRating();
		foreach ($textgap_options as $textgap_option)
		{
			$this->tpl->setCurrentBlock("textgap_rating");
			$this->tpl->setVariable("TEXTGAP_VALUE", $textgap_option[0]);
			$this->tpl->setVariable("TEXTGAP_TEXT", $textgap_option[1]);
			if (strcmp($textgap_rating, $textgap_option[0]) == 0)
			{
				$this->tpl->setVariable("SELECTED_TEXTGAP_VALUE", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("question_data");
		$this->tpl->setVariable("VALUE_CLOZE_TITLE", htmlspecialchars($this->object->getTitle()));
		$this->tpl->setVariable("VALUE_CLOZE_COMMENT", htmlspecialchars($this->object->getComment()));
		$this->tpl->setVariable("VALUE_CLOZE_AUTHOR", htmlspecialchars($this->object->getAuthor()));
		$cloze_text = $this->object->getClozeText();
		$cloze_text = preg_replace("/<br \/>/", "\n", $cloze_text);
		$this->tpl->setVariable("VALUE_CLOZE_TEXT", $cloze_text);
		$this->tpl->setVariable("TEXT_CREATE_GAPS", $this->lng->txt("create_gaps"));
		$this->tpl->setVariable("CLOZE_ID", $this->object->getId());

		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_CLOZE_TEXT", $this->lng->txt("cloze_text"));
		$this->tpl->setVariable("TEXT_CLOSE_HINT", htmlspecialchars($this->lng->txt("close_text_hint")));
		$this->tpl->setVariable("TEXTGAP_RATING", $this->lng->txt("cloze_textgap_rating"));
		$this->tpl->setVariable("TEXT_GAP_DEFINITION", $this->lng->txt("gap_definition"));
		$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
		$this->tpl->setVariable("SAVE_EDIT", $this->lng->txt("save_edit"));
		$this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
		$this->ctrl->setParameter($this, "sel_question_types", "qt_cloze");
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("qt_cloze"));
		$this->tpl->setVariable("ACTION_CLOZE_TEST", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"initialSelect();\""); 
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

		// Delete all existing gaps and create new gaps from the form data
		$this->object->flushGaps();

		if (!$this->checkInput())
		{
			$result = 1;
		}

		if (($result) and ($_POST["cmd"]["add"]))
		{
			// You cannot create gaps before you enter the required data
			sendInfo($this->lng->txt("fill_out_all_required_fields_create_gaps"));
			$_POST["cmd"]["add"] = "";
		}

		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		$this->object->setTextgapRating($_POST["textgap_rating"]);
		include_once "./classes/class.ilObjAssessmentFolder.php";
		$cloze_text = ilUtil::stripSlashes($_POST["clozetext"], true, ilObjAssessmentFolder::_getUsedHTMLTagsAsString()."<gap>");
		$cloze_text = preg_replace("/\n/", "<br />", $cloze_text);
		$this->object->setClozeText($cloze_text);
		// adding estimated working time
		$saved = $saved | $this->writeOtherPostData($result);
		$this->object->suggested_solutions = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^solution_hint_(\d+)/", $key, $matches))
			{
				if ($value)
				{
					$this->object->setSuggestedSolution($value, $matches[1]);
				}
			}
		}
		
		if ($this->ctrl->getCmd() != "createGaps")
		{
			$result = $this->setGapValues();
			$this->setShuffleState();

			foreach ($_POST as $key => $value)
			{
				// Set the cloze type of the gap
				if (preg_match("/clozetype_(\d+)/", $key, $matches))
				{
					$this->object->setClozeType($matches[1], $value);
				}
			}
		}

		$this->object->updateAllGapParams();
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
	* delete
	*/
	function delete()
	{
		$this->writePostData();
		foreach ($_POST["cmd"] as $key => $value)
		{
			// Check, if one of the gap values was deleted
			if (preg_match("/delete_(\d+)_(\d+)/", $key, $matches))
			{
				$selectgap = "selectgap_" . $matches[1] . "_" . $matches[2];
				$this->object->deleteAnswertextByIndex($matches[1], $matches[2]);
			}
		}
		$this->editQuestion();
	}

	function addSelectGap()
	{
		$this->writePostData();

		$len = strlen("addSelectGap_");
		$i = substr($this->ctrl->getCmd(), $len);
		$this->object->setAnswertext(
			ilUtil::stripSlashes($i),
			ilUtil::stripSlashes($this->object->getGapTextCount($i)),
			"",
			1
		);

		$this->editQuestion();
	}

	function addTextGap()
	{
		$this->writePostData();

		$len = strlen("addTextGap_");
		$i = substr($this->ctrl->getCmd(), $len);
		$this->object->setAnswertext(
			ilUtil::stripSlashes($i),
			ilUtil::stripSlashes($this->object->getGapTextCount($i)),
			"",
			1
		);

		$this->editQuestion();
	}

	function setGapValues($a_apply_text = true)
	{
//echo "<br>SETGapValues:$a_apply_text:";
		$result = 0;
		foreach ($_POST as $key => $value)
		{
			// Set gap values
			if (preg_match("/textgap_(\d+)_(\d+)/", $key, $matches))
			{
				$answer_array = $this->object->getGap($matches[1]);
				if (strlen($value) > 0)
				{
					// Only change gap values <> empty string
					if (array_key_exists($matches[2], $answer_array))
					{
						if ($a_apply_text)
						{
							if (strcmp($value, $answer_array[$matches[2]]->getAnswertext()) != 0)
							{
								$this->object->setAnswertext(
									ilUtil::stripSlashes($matches[1]),
									ilUtil::stripSlashes($matches[2]),
									ilUtil::stripSlashes($value)
								);
							}
							if (preg_match("/\d+/", $_POST["points_$matches[1]_$matches[2]"]))
							{
								$points = $_POST["points_$matches[1]_$matches[2]"];
								if ($points < 0)
								{
									$result = 1;
									$this->setErrorMessage($this->lng->txt("negative_points_not_allowed"));
								}
							}
							else
							{
								$points = 0.0;
							}
							$this->object->setSingleAnswerPoints($matches[1], $matches[2], $points);
							$this->object->setSingleAnswerState($matches[1], $matches[2], 1);
						}
						else
						{
							if (strcmp($value, $answer_array[$matches[2]]->getAnswertext()) == 0)
							{
								if (preg_match("/\d+/", $_POST["points_$matches[1]_$matches[2]"]))
								{
									$points = $_POST["points_$matches[1]_$matches[2]"];
									if ($points < 0)
									{
										$result = 1;
										$this->setErrorMessage($this->lng->txt("negative_points_not_allowed"));
									}
								}
								else
								{
									$points = 0.0;
								}
								$this->object->setSingleAnswerPoints($matches[1], $matches[2], $points);
								$this->object->setSingleAnswerState($matches[1], $matches[2], 1);
							}
						}
					}
				}
				else
				{
					// Display errormessage: You've tried to set an gap value to an empty string!
				}
			}

			if (preg_match("/selectgap_(\d+)_(\d+)/", $key, $matches))
			{
				$answer_array = $this->object->getGap($matches[1]);
				if (strlen($value) > 0)
				{
					// Only change gap values <> empty string
					if (array_key_exists($matches[2], $answer_array))
					{
						if (strcmp($value, $answer_array[$matches[2]]->getAnswertext()) != 0)
						{
							if ($a_apply_text)
							{
								$this->object->setAnswertext(
									ilUtil::stripSlashes($matches[1]),
									ilUtil::stripSlashes($matches[2]),
									ilUtil::stripSlashes($value)
								);
							}
						}
						if (preg_match("/\d+/", $_POST["points_$matches[1]_$matches[2]"]))
						{
							$points = $_POST["points_$matches[1]_$matches[2]"];
							if ($points < 0)
							{
								$result = 1;
								$this->setErrorMessage($this->lng->txt("negative_points_not_allowed"));
							}
						}
						else
						{
							$points = 0.0;
						}
						$this->object->setSingleAnswerPoints($matches[1], $matches[2], $points);
						$this->object->setSingleAnswerState($matches[1], $matches[2], 1);
					}
				}
				else
				{
					// Display errormessage: You've tried to set an gap value to an empty string!
				}
			}
		}
		return $result;
	}

	function setShuffleState()
	{
		foreach ($_POST as $key => $value)
		{
			// Set select gap shuffle state
			if (preg_match("/^shuffle_(\d+)$/", $key, $matches))
			{
				$this->object->setGapShuffle($matches[1], $value);
			}
		}
	}

	/**
	* create gaps
	*/
	function createGaps()
	{
		$this->writePostData();

		$this->setGapValues(false);
		$this->setShuffleState();
		$this->object->updateAllGapParams();
		$this->editQuestion();
	}

	function getResultOutput($test_id, &$ilUser, $pass = NULL)
	{
		$question_html = $this->outQuestionPage("", FALSE, $test_id);
		// remove the question title heading
		$question_html = preg_replace("/.*?(<div[^<]*?ilc_Question.*?<\/div>).*/", "\\1", $question_html);
		if ($test_id)
		{
			$solutions =& $this->object->getSolutionValues($test_id, $ilUser, $pass);
			if (is_array($solutions)) 
			{
				foreach ($solutions as $idx => $solution_value)
				{
					// replace text gaps
					$repl_str = "dummy=\"tgap_".$solution_value["value1"]."\"";
					//$repl_with = "[" . $solution_value["value2"] . "]";
					if (strlen($solution_value["value2"]))
					{
						$repl_with = "<span class=\"solutionbox\">" . $solution_value["value2"] . "</span>";
					}
					else
					{
						$repl_with = "<span class=\"solutionbox\">&nbsp;</span>";
					}
					$question_html = preg_replace("/(<input[^>]*".$repl_str."[^>]*>)/" , $repl_with, $question_html);
					// replace select gaps
					$repl_str = "dummy=\"sgap_".$solution_value["value1"]."_".$solution_value["value2"]."\"";
					$repl_with = "<span class=\"solutionbox\">&nbsp;</span>";
					if (preg_match("/<option[^>]*" . $repl_str . "[^>]*>(.*?)<\/option>/", $question_html, $matches))
					{
						$repl_with = "<span class=\"solutionbox\">" . $matches[1] . "</span>";
					}
					$question_html = preg_replace("/(<select.*".$repl_str.".*?\/select>)/" , $repl_with, $question_html);
				}
			}
		}
		return $question_html;
	}

	/**
	* Creates the question output form for the learner
	*
	* Creates the question output form for the learner
	*
	* @param integer $test_id Database ID of a test which contains the question
	* @param boolean $is_postponed True if the question is a postponed question ("Postponed" added to the title)
	* @param boolean $showsolution Forces the output of the users solution if set to true
	* @param boolean $show_question_page Forces the output of the question only (without the surrounding page) when set to false. Default is true.
	* @param boolean $show_solution_only Forces the output of the correct question solution only when set to true. Default is false
	* @param object  $ilUser The user object of the user who answered the question
	* @param integer $pass The pass of the question which should be displayed
	* @param boolean $mixpass Mixes test passes (takes the last pass of the question) when set to true. Default is false.
	* @access public
	*/
	function outWorkingForm(
		$test_id = "", 
		$is_postponed = false, 
		$showsolution = 0, 
		$show_question_page = true, 
		$show_solution_only = false, 
		$ilUser = null, 
		$pass = NULL, 
		$mixpass = false
	)
	{
		if (!is_object($ilUser)) 
		{
			global $ilUser;
		}
		
		$output = $this->outQuestionPage(($show_solution_only)?"":"CLOZE_TEST", $is_postponed, $test_id);
		
		if ($showsolution && !$show_solution_only)
		{
			$solutionintroduction = "<p>" . $this->lng->txt("tst_your_answer_was") . "</p>";
			$output = preg_replace("/(<div[^<]*?ilc_PageTitle.*?<\/div>)/", "\\1" . $solutionintroduction, $output);
		}
		$solutionoutput = preg_replace("/.*?(<div[^<]*?ilc_Question.*?<\/div>).*/", "\\1", $output);
		$solutionoutput = preg_replace("/\"tgap/", "\"solution_tgap", $solutionoutput);
		$solutionoutput = preg_replace("/\"sgap/", "\"solution_sgap", $solutionoutput);
		$solutionoutput = preg_replace("/name=\"gap/", "name=\"solution_gap", $solutionoutput);
		
		// if wants question only then strip everything around question element
		if (!$show_question_page) 
		{
			$output = preg_replace("/.*?(<div[^<]*?ilc_Question.*?<\/div>).*/", "\\1", $output);
		}
		
		// if wants solution only then strip the question element from output
		if ($show_solution_only) 
		{
			$output = preg_replace("/(<div[^<]*?ilc_Question[^>]*>.*?<\/div>)/", "", $output);
		}

		// set solutions
		if ($test_id)
		{
			$solutions = NULL;
			include_once "./assessment/classes/class.ilObjTest.php";
			if ((!$showsolution) && ilObjTest::_getHidePreviousResults($test_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($ilUser->id, $test_id);
			}
			if ($mixpass) $pass = NULL;
			$solutions =& $this->object->getSolutionValues($test_id, $ilUser, $pass);
	
			if (is_array($solutions)) 
			{
				foreach ($solutions as $idx => $solution_value)
				{
					// text gaps
					
					$repl_str = "dummy=\"tgap_".$solution_value["value1"]."\"";
					//
					if (!$show_question_page)
					{
						$output = $this->replaceInputElements($repl_str, $solution_value["value2"], $output,"[","]");						
					}
					else 
						$output = str_replace($repl_str, $repl_str." value=\"".$solution_value["value2"]."\"", $output);
					
					// select gaps
					$repl_str = "dummy=\"sgap_".$solution_value["value1"]."_".$solution_value["value2"]."\"";
					
					if (!$show_question_page) 
					{
						$output = $this->replaceSelectElements("gap_".$solution_value["value1"], $repl_str, $output,"[","]"); 
					} else 
						$output = str_replace($repl_str, $repl_str." selected=\"selected\"", $output);
					//echo "<br>".$repl_str;
				}
			}
			// now replace all empty inputs and selects with an []
			if (!$show_question_page) 
			{
				$output = $this->removeFormElements($output);
			}
		}

		if($showsolution)
		{			
						
			foreach ($this->object->gaps as $idx => $gap)
			{
				$solution_value = "";
				if (is_array($solutions)) 
				{
					foreach ($solutions as $solidx => $solvalue)
					{
						if ($solvalue["value1"] == $idx)
						{
							$solution_value = $solvalue["value2"];
						}
					}
				}
				if ($gap[0]->getClozeType() == CLOZE_SELECT)
				{
					$maxpoints = 0;
					$maxindex = -1;
					$sol_points = array();
					foreach ($gap as $answeridx => $answer)
					{
						if ($answer->getPoints() > $maxpoints)
						{
							$maxpoints = $answer->getPoints();
							$maxindex = $answeridx;
						}
						if ($show_solution_only && $answer->getPoints()>0) 
						{							
							$regexp = "/<select name=\"solution_gap_$idx\">.*?<option[^>]*dummy=\"solution_sgap_".$idx."_".$answeridx."\">(.*?)<\/option>.*?<\/select>/";
							preg_match ($regexp, $solutionoutput, $matches);
							$sol_points [] = $matches[1]." <em>(".$answer->getPoints()." ".$this->lng->txt("points_short").")</em>";
						}
					}
															
					if ($this->object->suggested_solutions[$idx])
					{
						if ($showsolution)
						{
							$href = $this->object->_getInternalLinkHref($this->object->suggested_solutions[$idx]["internal_link"]);
							$output = preg_replace("/(<select name\=\"gap_$idx\">.*?<\/select>)/is", "\\1" . " [<a href=\"$href\" target=\"_blank\">".$this->lng->txt("solution_hint")."</a>] " , $output);
						}
					}
					
					if (count ($sol_points)<=1)
						$solutionoutput = preg_replace("/(<select name\=\"solution_gap_$idx\">.*?<\/select>)/is", "\\1" . " <em>(" . $maxpoints . " " . $this->lng->txt("points") . ")</em> " , $solutionoutput);
					
					if ($maxindex > -1)
					{
						$repl_str = "dummy=\"solution_sgap_$idx" . "_$maxindex\"";
						if (!$show_solution_only)
							$solutionoutput = str_replace($repl_str, $repl_str." selected=\"selected\"", $solutionoutput);
						else 
						{
							if (count ($sol_points)>1) 
							{
								$solutionoutput = preg_replace ("/<select[^>]*name=\"solution_gap_$idx\">.*?<\/select>/i","<span class=\"textanswer\">[".join($sol_points,", ")."]</span>",$solutionoutput); 
							} else 
								$solutionoutput = $this->replaceSelectElements("solution_gap_$idx",$repl_str, $solutionoutput,"[","]" );
						}
					}
					
				}
				else
				{
					
					$repl_str = "dummy=\"solution_tgap_$idx\"";					
					$pvals = array();
					foreach ($gap as $answeridx => $answer)
					{
						array_push($pvals, $answer->getAnswertext());
					}
					$possible_values = join($pvals, " " . $this->lng->txt("or") . " ");
					
					$solutionoutput = preg_replace("/(<input[^<]*?dummy\=\"solution_tgap_$idx\"" . "[^>]*?>)/i", "\\1" . " <em>(" . $gap[0]->getPoints() . " " . $this->lng->txt("points") . ")</em> ", $solutionoutput);
					
					if ($this->object->suggested_solutions[$idx])
					{
						if ($showsolution)
						{
							$href = $this->object->_getInternalLinkHref($this->object->suggested_solutions[$idx]["internal_link"]);
							$output = preg_replace("/(<input[^<]*?dummy\=\"tgap_$idx\"" . "[^>]*?>)/is", "\\1" . " [<a href=\"$href\" target=\"_blank\">".$this->lng->txt("solution_hint")."</a>] " , $output);
						}
					}
					 
					if (!$show_solution_only)
						$solutionoutput = str_replace($repl_str, $repl_str." value=\"$possible_values\"", $solutionoutput);
					else 
						$solutionoutput = $this->replaceInputElements($repl_str, $possible_values, $solutionoutput,"[","]");					
				}
			}
	
			if (!$show_solution_only)
			{
				$solutionoutput = "<p>" . $this->lng->txt("correct_solution_is") . ":</p><p>$solutionoutput</p>";
			}

			if ($test_id) 
			{
				$reached_points = $this->object->getReachedPoints($ilUser->id, $test_id);
				$received_points = "<p>" . sprintf($this->lng->txt("you_received_a_of_b_points"), $reached_points, $this->object->getMaximumPoints());
				$count_comment = "";
				if ($reached_points == 0)
				{
					$count_comment = $this->object->getSolutionCommentCountSystem($test_id);
					if (strlen($count_comment))
					{
						if (strlen($mc_comment) == 0)
						{
							$count_comment = "<span class=\"asterisk\">*</span><br /><br /><span class=\"asterisk\">*</span>$count_comment";
						}
						else
						{
							$count_comment = "<br /><span class=\"asterisk\">*</span>$count_comment";
						}
					}
				}
				$received_points .= $count_comment;
				$received_points .= "</p>";
			}
			if ($show_solution_only== true) 
			{
				$received_points = "";
			}
		}
		if (!$showsolution) 
		{
			$solutionoutput="";
			$received_points = "";
		}
		
		$this->tpl->setVariable("CLOZE_TEST", $output.$solutionoutput.$received_points);
		return;
	}

	/**
	* check input fields
	*/
	function checkInput()
	{
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["clozetext"]))
		{
			return false;
		}
		return true;
	}


	function addSuggestedSolution()
	{
		$addForGap = -1;
		if (array_key_exists("cmd", $_POST))
		{
			foreach ($_POST["cmd"] as $key => $value)
			{
				if (preg_match("/addSuggestedSolution_(\d+)/", $key, $matches))
				{
					$addForGap = $matches[1];
				}
			}
		}
		if ($addForGap > -1)
		{
			if ($this->writePostData())
			{
				sendInfo($this->getErrorMessage());
				$this->editQuestion();
				return;
			}
			if (!$this->checkInput())
			{
				sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
				$this->editQuestion();
				return;
			}
			$_POST["internalLinkType"] = $_POST["internalLinkType_$addForGap"];
			$_SESSION["subquestion_index"] = $addForGap;
		}
		$this->object->saveToDb();
		$_GET["q_id"] = $this->object->getId();
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->getQuestionTemplate("qt_cloze");
		parent::addSuggestedSolution();
	}

	function removeSuggestedSolution()
	{
		$removeFromGap = -1;
		foreach ($_POST["cmd"] as $key => $value)
		{
			if (preg_match("/removeSuggestedSolution_(\d+)/", $key, $matches))
			{
				$removeFromGap = $matches[1];
			}
		}
		if ($removeFromGap > -1)
		{
			unset($this->object->suggested_solutions[$removeFromGap]);
		}
		$this->object->saveToDb();
		$this->editQuestion();
	}

}
?>