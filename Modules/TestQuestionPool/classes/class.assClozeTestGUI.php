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

include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";

/**
* Cloze test question GUI representation
*
* The assClozeTestGUI class encapsulates the GUI representation
* for cloze test questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assClozeTestGUI extends assQuestionGUI
{
	/**
	* assClozeTestGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assClozeTestGUI object.
	*
	* @param integer $id The database id of a image map question object
	* @access public
	*/
	function assClozeTestGUI(
			$id = -1
	)
	{
		$this->assQuestionGUI();
		include_once "./Modules/TestQuestionPool/classes/class.assClozeTest.php";
		$this->object = new assClozeTest();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
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
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$internallinks = array(
			"lm" => $this->lng->txt("obj_lm"),
			"st" => $this->lng->txt("obj_st"),
			"pg" => $this->lng->txt("obj_pg"),
			"glo" => $this->lng->txt("glossary_term")
		);
		//$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->getQuestionTemplate();
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_cloze_question.html", "Modules/TestQuestionPool");
		for ($i = 0; $i < $this->object->getGapCount(); $i++)
		{
			$gap = $this->object->getGap($i);
			if ($gap[0]->getClozeType() == CLOZE_TEXT)
			{
				$this->tpl->setCurrentBlock("textgap_value");
				foreach ($gap as $key => $value)
				{
					$this->tpl->setVariable("TEXT_VALUE", $this->lng->txt("value"));
					$this->tpl->setVariable("VALUE_TEXT_GAP", ilUtil::prepareFormOutput($value->getAnswertext()));
					$this->tpl->setVariable("VALUE_GAP_COUNTER", "$i" . "_" . "$key");
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("VALUE_INDEX", $key);
					$this->tpl->setVariable("VALUE_STATUS_COUNTER", $key);
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
					$this->tpl->setVariable("VALUE_TEXT_GAP_POINTS", $value->getPoints());
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
					$href = assQuestion::_getInternalLinkHref($solution_array["internal_link"]);
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
			if ($gap[0]->getClozeType() == CLOZE_NUMERIC)
			{
				$this->tpl->setCurrentBlock("numericgap_value");
				foreach ($gap as $key => $value)
				{
					$this->tpl->setVariable("TEXT_VALUE", $this->lng->txt("value"));
					$this->tpl->setVariable("VALUE_TEXT_GAP", ilUtil::prepareFormOutput($value->getAnswertext()));
					$this->tpl->setVariable("VALUE_GAP_COUNTER", "$i" . "_" . "$key");
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("VALUE_INDEX", $key);
					$this->tpl->setVariable("VALUE_STATUS_COUNTER", $key);
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
					$this->tpl->setVariable("VALUE_TEXT_GAP_POINTS", $value->getPoints());
					$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
					$this->tpl->parseCurrentBlock();
				}

				foreach ($internallinks as $key => $value)
				{
					$this->tpl->setCurrentBlock("numericgap_internallink");
					$this->tpl->setVariable("TYPE_INTERNAL_LINK", $key);
					$this->tpl->setVariable("TEXT_INTERNAL_LINK", $value);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("numericgap_suggested_solution");
				$this->tpl->setVariable("TEXT_SOLUTION_HINT", $this->lng->txt("solution_hint"));
				if (array_key_exists($i, $this->object->suggested_solutions))
				{
					$solution_array = $this->object->getSuggestedSolution($i);
					$href = assQuestion::_getInternalLinkHref($solution_array["internal_link"]);
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
				$this->tpl->setCurrentBlock("numericgap");
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
					$this->tpl->setVariable("VALUE_SELECT_GAP", ilUtil::prepareFormOutput($value->getAnswertext()));
					$this->tpl->setVariable("VALUE_GAP_COUNTER", "$i" . "_" . "$key");
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("VALUE_INDEX", $key);
					$this->tpl->setVariable("VALUE_STATUS_COUNTER", $key);
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
					$this->tpl->setVariable("VALUE_SELECT_GAP_POINTS", $value->getPoints());
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
					$href = assQuestion::_getInternalLinkHref($solution_array["internal_link"]);
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
			switch ($gap[0]->getClozeType())
			{
				case CLOZE_TEXT:
					$this->tpl->setVariable("SELECTED_TEXT_GAP", " selected=\"selected\"");
					break;
				case CLOZE_SELECT:
					$this->tpl->setVariable("SELECTED_SELECT_GAP", " selected=\"selected\"");
					break;
				case CLOZE_NUMERIC:
					$this->tpl->setVariable("SELECTED_NUMERIC_GAP", " selected=\"selected\"");
					break;
			}
			$this->tpl->setVariable("TEXT_TEXT_GAP", $this->lng->txt("text_gap"));
			$this->tpl->setVariable("TEXT_SELECT_GAP", $this->lng->txt("select_gap"));
			$this->tpl->setVariable("TEXT_NUMERIC_GAP", $this->lng->txt("numeric_gap"));
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
		$this->tpl->setVariable("VALUE_CLOZE_TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$this->tpl->setVariable("VALUE_CLOZE_COMMENT", ilUtil::prepareFormOutput($this->object->getComment()));
		$this->tpl->setVariable("VALUE_CLOZE_AUTHOR", ilUtil::prepareFormOutput($this->object->getAuthor()));
		$cloze_text = $this->object->getClozeText();
		$this->tpl->setVariable("VALUE_CLOZE_TEXT", $this->object->prepareTextareaOutput($cloze_text));
		$this->tpl->setVariable("TEXT_CREATE_GAPS", $this->lng->txt("create_gaps"));
		$this->tpl->setVariable("CLOZE_ID", $this->object->getId());

		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_CLOZE_TEXT", $this->lng->txt("cloze_text"));
		$this->tpl->setVariable("TEXT_CLOSE_HINT", ilUtil::prepareFormOutput($this->lng->txt("close_text_hint")));
		$this->tpl->setVariable("TEXTGAP_RATING", $this->lng->txt("cloze_textgap_rating"));
		$this->tpl->setVariable("TEXT_GAP_DEFINITION", $this->lng->txt("gap_definition"));
		$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
		$this->tpl->setVariable("SAVE_EDIT", $this->lng->txt("save_edit"));
		$this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
		$this->ctrl->setParameter($this, "sel_question_types", "assClozeTest");
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("assClozeTest"));
		$this->tpl->setVariable("ACTION_CLOZE_TEST", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->parseCurrentBlock();

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");

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
		include_once "./classes/class.ilObjAdvancedEditing.php";
		$cloze_text = ilUtil::stripSlashes($_POST["clozetext"], true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
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
							$points = $_POST["points_$matches[1]_$matches[2]"];
							$this->object->setSingleAnswerPoints($matches[1], $matches[2], $points);
							$this->object->setSingleAnswerState($matches[1], $matches[2], 1);
						}
						else
						{
							if (strcmp($value, $answer_array[$matches[2]]->getAnswertext()) == 0)
							{
								$points = $_POST["points_$matches[1]_$matches[2]"];
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
						$points = $_POST["points_$matches[1]_$matches[2]"];
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

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		$this->tpl->setVariable("FORMACTION", $formaction);
	}

	function getSolutionOutput($active_id, $pass = NULL, $graphicalOutput = FALSE, $result_output = FALSE, $show_question_only = TRUE)
	{
		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if ($active_id)
		{
			$user_solution =& $this->object->getSolutionValues($active_id, $pass);
			if (!is_array($user_solution)) 
			{
				$user_solution = array();
			}
		}
		else
		{
			for ($i = 0; $i < $this->object->getGapCount(); $i++)
			{
				$gap = $this->object->getGap($i);
				if ($gap[0]->getClozeType() == CLOZE_SELECT)
				{
					$maxpoints = 0;
					$foundindex = -1;
					foreach ($gap as $index => $answer)
					{
						if ($answer->getPoints() > $maxpoints)
						{
							$maxpoints = $answer->getPoints();
							$foundindex = $index;
						}
					}
					array_push($user_solution, array("value1" => $i, "value2" => $foundindex));
				}
				else
				{
					$best_solutions = array();
					foreach ($gap as $index => $answer)
					{
						if (is_array($best_solutions[$answer->getPoints()]))
						{
							array_push($best_solutions[$answer->getPoints()], "&quot;".$answer->getAnswertext()."&quot;");
						}
						else
						{
							$best_solutions[$answer->getPoints()] = array();
							array_push($best_solutions[$answer->getPoints()], "&quot;".$answer->getAnswertext()."&quot;");
						}
					}
					krsort($best_solutions, SORT_NUMERIC);
					reset($best_solutions);
					$found = current($best_solutions);
					array_push($user_solution, array("value1" => $i, "value2" => join(" " . $this->lng->txt("or") . " ", $found)));
				}
			}
		}
		
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_cloze_question_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$cloze =& $this->object->createCloseTextArray();
		//print_r($cloze);
		$cloze_text = $cloze["delimiters"];
		//print_r($cloze);
		$counter = 0;
		foreach ($cloze_text as $delimiter)
		{
			$template->setCurrentBlock("cloze_text");
			$template->setVariable("CLOZE_TEXT", $this->object->prepareTextareaOutput($delimiter[0], TRUE));
			$template->parseCurrentBlock();
			$gap = $this->object->getGap($counter);
			foreach ($user_solution as $solution)
			{
				if (strcmp($solution["value1"], $counter) == 0)
				{
					if ($active_id)
					{
						if ($graphicalOutput)
						{
							// output of ok/not ok icons for user entered solutions
							$check = $this->object->testGapSolution($solution["value2"], $gap);
							if ($check["best"])
							{
								$template->setCurrentBlock("icon_ok");
								$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.gif"));
								$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
								$template->parseCurrentBlock();
							}
							else
							{
								$template->setCurrentBlock("icon_ok");
								if ($check["positive"])
								{
									$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.gif"));
									$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
								}
								else
								{
									$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.gif"));
									$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
								}
								$template->parseCurrentBlock();
							}
						}
					}
					$template->setCurrentBlock("solution");
					if ((strlen($solution["value2"])) && ($gap[0]->getClozeType() == CLOZE_SELECT))
					{
						$template->setVariable("SOLUTION", $gap[$solution["value2"]]->getAnswertext());
					}
					else
					{
						$template->setVariable("SOLUTION", $solution["value2"]);
					}
					if ($result_output)
					{
						$points = $this->object->getMaximumGapPoints($solution["value1"]);
						$resulttext = ($points == 1) ? "(%d " . $this->lng->txt("point") . ")" : "(%d " . $this->lng->txt("points") . ")"; 
						$template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
					}
				}
			}
			$template->parseCurrentBlock();
			$template->touchBlock("cloze_part");
			$counter++;
		}
		$questionoutput = $template->get();
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get(); 
		if (!$show_question_only)
		{
			// get page object output
			$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id);
			$pageoutput = preg_replace("/\<div class\=\"ilc_PageTitle\">.*?\<\/div>/ims", "", $pageoutput);
			$solutionoutput = preg_replace("/(\<div( xmlns:xhtml\=\"http:\/\/www.w3.org\/1999\/xhtml\"){0,1} class\=\"ilc_Question\">\<\/div>)/ims", $solutionoutput, $pageoutput);
		}
		return $solutionoutput;
	}

	function getPreview()
	{
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_cloze_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$cloze =& $this->object->createCloseTextArray();
		$cloze_text = $cloze["delimiters"];
		$counter = 0;
		foreach ($cloze_text as $delimiter)
		{
			$template->setCurrentBlock("cloze_text");
			$template->setVariable("CLOZE_TEXT", $this->object->prepareTextareaOutput($delimiter[0], TRUE));
			$template->parseCurrentBlock();
			$gap = $this->object->getGap($counter);
			if ($gap)
			{
				if ($gap[0]->getClozeType() == CLOZE_SELECT)
				{
					// shuffle output
					$gkeys = array_keys($gap);
					if ($gap[0]->getShuffle())
					{
						$gkeys = $this->object->pcArrayShuffle($gkeys);
					}

					// add answers
					foreach ($gkeys as $index)
					{
						$answer = $gap[$index];
						$template->setCurrentBlock("select_gap_option");
						$template->setVariable("SELECT_GAP_VALUE", $index);
						$template->setVariable("SELECT_GAP_TEXT", $answer->getAnswertext());
						$template->parseCurrentBlock();
					}
					$template->setCurrentBlock("select_gap");
					$template->setVariable("GAP_COUNTER", $counter);
					$template->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("text_gap");
					$template->setVariable("GAP_COUNTER", $counter);
					$template->setVariable("TEXT_GAP_SIZE", $this->object->getColumnSize($gap));
					$template->parseCurrentBlock();
				}
			}
			$template->touchBlock("cloze_part");
			$counter++;
		}
		$questionoutput = $template->get();
		$questionoutput = preg_replace("/\<div[^>]*?>(.*)\<\/div>/is", "\\1", $questionoutput);
		return $questionoutput;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		// get page object output
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id);

		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if ($active_id)
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$user_solution =& $this->object->getSolutionValues($active_id, $pass);
			if (!is_array($user_solution)) 
			{
				$user_solution = array();
			}
		}
		
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_cloze_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$cloze =& $this->object->createCloseTextArray();
		//print_r($cloze);
		$cloze_text = $cloze["delimiters"];
		//print_r($cloze);
		$counter = 0;
		foreach ($cloze_text as $delimiter)
		{
			$template->setCurrentBlock("cloze_text");
			$template->setVariable("CLOZE_TEXT", $this->object->prepareTextareaOutput($delimiter[0], TRUE));
			$template->parseCurrentBlock();
			$gap = $this->object->getGap($counter);
			if ($gap)
			{
				if ($gap[0]->getClozeType() == CLOZE_SELECT)
				{
					// shuffle output
					$gkeys = array_keys($gap);
					if ($gap[0]->getShuffle())
					{
						$gkeys = $this->object->pcArrayShuffle($gkeys);
					}

					// add answers
					foreach ($gkeys as $index)
					{
						$answer = $gap[$index];
						$template->setCurrentBlock("select_gap_option");
						$template->setVariable("SELECT_GAP_VALUE", $index);
						$template->setVariable("SELECT_GAP_TEXT", $answer->getAnswertext());
						foreach ($user_solution as $solution)
						{
							if (strcmp($solution["value1"], $counter) == 0)
							{
								if (strcmp($solution["value2"], $index) == 0)
								{
									$template->setVariable("SELECT_GAP_SELECTED", " selected=\"selected\"");
								}
							}
						}
						$template->parseCurrentBlock();
					}
					$template->setCurrentBlock("select_gap");
					$template->setVariable("GAP_COUNTER", $counter);
					$template->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("text_gap");
					$template->setVariable("GAP_COUNTER", $counter);
					foreach ($user_solution as $solution)
					{
						if (strcmp($solution["value1"], $counter) == 0)
						{
							$template->setVariable("VALUE_GAP", " value=\"" . ilUtil::prepareFormOutput($solution["value2"]) . "\"");
						}
					}
					$template->setVariable("TEXT_GAP_SIZE", $this->object->getColumnSize($gap));
					$template->parseCurrentBlock();
				}
			}
			$template->touchBlock("cloze_part");
			$counter++;
		}
		$questionoutput = $template->get();
		$questionoutput = preg_replace("/(\<div( xmlns:xhtml\=\"http:\/\/www.w3.org\/1999\/xhtml\"){0,1} class\=\"ilc_Question\">\<\/div>)/ims", $questionoutput, $pageoutput);
		return $questionoutput;
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
		$this->getQuestionTemplate();
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

	/**
	* Saves the feedback for a single choice question
	*
	* Saves the feedback for a single choice question
	*
	* @access public
	*/
	function saveFeedback()
	{
		include_once "./classes/class.ilObjAdvancedEditing.php";
		$this->object->saveFeedbackGeneric(0, ilUtil::stripSlashes($_POST["feedback_incomplete"], TRUE, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->saveFeedbackGeneric(1, ilUtil::stripSlashes($_POST["feedback_complete"], TRUE, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->cleanupMediaObjectUsage();
		$this->feedback();
	}

	/**
	* Creates the output of the feedback page for a single choice question
	*
	* Creates the output of the feedback page for a single choice question
	*
	* @access public
	*/
	function feedback()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "feedback", "tpl.il_as_qpl_cloze_question_feedback.html", "Modules/TestQuestionPool");
		$this->tpl->setVariable("FEEDBACK_TEXT", $this->lng->txt("feedback"));
		$this->tpl->setVariable("FEEDBACK_COMPLETE", $this->lng->txt("feedback_complete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_COMPLETE", $this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(1)), FALSE);
		$this->tpl->setVariable("FEEDBACK_INCOMPLETE", $this->lng->txt("feedback_incomplete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_INCOMPLETE", $this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(0)), FALSE);
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");
	}
}
?>