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
	* A temporary variable to store gap indexes of ilCtrl commands in the getCommand method
	*
	*/
	var $gapIndex;

	/**
	* A temporary variable to store answer indexes of ilCtrl commands in the getCommand method
	*
	*/
	var $answerIndex;
	
	/**
	* assClozeTestGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assClozeTestGUI object.
	*
	* @param integer $id The database id of a image map question object
	* @access public
	*/
	function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assClozeTest.php";
		$this->object = new assClozeTest();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	function getCommand($cmd)
	{
		if (preg_match("/^(addGapText|addSelectGapText)_(\d+)$/", $cmd, $matches))
		{
			$cmd = $matches[1];
			$this->gapIndex = $matches[2];
		}
		else if (preg_match("/^(delete)_(\d+)_(\d+)$/", $cmd, $matches))
		{
			$cmd = $matches[1];
			$this->gapIndex = $matches[2];
			$this->answerIndex = $matches[3];
		}
		return $cmd;
	}

	/**
	* Create editable gaps from the question text
	*/
	function createGaps()
	{
		$this->writePostData();
		$this->editQuestion();
	}

	/**
	* Change the type of a gap
	*/
	function changeGapType()
	{
		$this->writePostData();
		$this->editQuestion();
	}

	/**
	* Checks the obligatory fields from a POST in the edit form
	*/
	function checkInput()
	{
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["clozetext"]))
		{
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	* Sets the gap types from the editing form
	*
	* Sets the gap types from the editing form
	*
	* @access private
	*/
	function setGapTypes()
	{
		foreach ($_POST as $key => $value)
		{
			// Set the cloze type of the gap
			if (preg_match("/clozetype_(\d+)/", $key, $matches))
			{
				$this->object->setGapType($matches[1], $value);
			}
		}
	}

	/**
	* Sets the shuffle state of gaps from the editing form
	*
	* Sets the shuffle state of gaps from the editing form
	*
	* @access private
	*/
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
	* Sets the answers for the gaps from the editing form
	*
	* Sets the answers for the gaps from the editing form
	*
	* @access private
	*/
	function setGapAnswers()
	{
		$error = FALSE;
		$this->object->clearGapAnswers();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^(textgap|selectgap|numericgap)_(\d+)_(\d+)$/", $key, $matches))
			{
				// text gap answer
				$gap = $matches[2];
				$order = $matches[3];
				$this->object->addGapAnswer($gap, $order, ilUtil::stripSlashes($value, FALSE));
				$gapObj = $this->object->getGap($gap);
				if (is_object($gapObj))
				{
					if ($gapObj->getType() == CLOZE_NUMERIC)
					{
						include_once "./Services/Math/classes/class.EvalMath.php";
						$eval = new EvalMath();
						$eval->suppress_errors = TRUE;
						if ($eval->e(str_replace(",", ".", ilUtil::stripSlashes($value, FALSE))) === FALSE)
						{
							$error = TRUE;
							$this->addErrorMessage($this->lng->txt("error_cloze_not_numeric"));
						}
					}
				}
			}
		}
		return $error;
	}

	/**
	* Sets the points for the gaps from the editing form
	*
	* Sets the points for the gaps from the editing form
	*
	* @access private
	*/
	function setGapPoints()
	{
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/points_(\d+)_(\d+)/", $key, $matches))
			{
				$gap = $matches[1];
				$order = $matches[2];
				$this->object->setGapAnswerPoints($gap, $order, ilUtil::stripSlashes($value));
			}
		}
	}

	/**
	* Sets the bounds for the gaps from the editing form
	*
	* Sets the bounds for the gaps from the editing form
	*
	* @access private
	*/
	function setGapBounds()
	{
		$error = FALSE;
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/numericgap_(\d+)_(\d+)_lower/", $key, $matches))
			{
				$gap = $matches[1];
				$order = $matches[2];
				$gapObj = $this->object->getGap($gap);
				if (is_object($gapObj))
				{
					if ($gapObj->getType() == CLOZE_NUMERIC)
					{
						include_once "./Services/Math/classes/class.EvalMath.php";
						$eval = new EvalMath();
						$eval->suppress_errors = TRUE;
						if ($eval->e(str_replace(",", ".", ilUtil::stripSlashes($value, FALSE))) === FALSE)
						{
							if (is_object($gapObj->getItem($order)))
							{
								if ($eval->e($gapObj->getItem($order)->getAnswertext()) !== FALSE)
								{
									$value = $gapObj->getItem($order)->getAnswertext();
								}
								else
								{
									$error = TRUE;
								}
							}
							else
							{
								$error = TRUE;
							}
							if ($error) $this->addErrorMessage($this->lng->txt("error_no_lower_limit"));
						}
					}
				}
				$this->object->setGapAnswerLowerBound($gap, $order, $value);
			}
			if (preg_match("/numericgap_(\d+)_(\d+)_upper/", $key, $matches))
			{
				$gap = $matches[1];
				$order = $matches[2];
				$gapObj = $this->object->getGap($gap);
				if (is_object($gapObj))
				{
					if ($gapObj->getType() == CLOZE_NUMERIC)
					{
						include_once "./Services/Math/classes/class.EvalMath.php";
						$eval = new EvalMath();
						$eval->suppress_errors = TRUE;
						if ($eval->e(str_replace(",", ".", ilUtil::stripSlashes($value, FALSE))) === FALSE)
						{
							if (is_object($gapObj->getItem($order)))
							{
								if ($eval->e($gapObj->getItem($order)->getAnswertext()) !== FALSE)
								{
									$value = $gapObj->getItem($order)->getAnswertext();
								}
								else
								{
									$error = TRUE;
								}
							}
							else
							{
								$error = TRUE;
							}
							if ($error) $this->addErrorMessage($this->lng->txt("error_no_upper_limit"));
						}
					}
				}
				$this->object->setGapAnswerUpperBound($gap, $order, $value);
			}
		}
		return $error;
	}

	/**
	* Adds a new answer text value to a text gap
	*
	* Adds a new answer text value to a text gap
	*
	* @access public
	*/
	function addGapText()
	{
		$this->writePostData();
		$this->object->addGapText($this->gapIndex);
		$this->editQuestion();
	}

	/**
	* Adds a new answer text value to a select gap
	*
	* Adds a new answer text value to a select gap
	*
	* @access public
	*/
	function addSelectGapText()
	{
		$this->writePostData();
		$this->object->addGapText($this->gapIndex);
		$this->editQuestion();
	}

	/**
	* Deletes answer text from a gap
	*
	* Deletes answer text from a gap
	*
	* @access public
	*/
	function delete()
	{
		$this->writePostData();
		$this->object->deleteAnswerText($this->gapIndex, $this->answerIndex);
		$this->editQuestion();
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
		$this->setErrorMessage("");

		if (!$this->checkInput())
		{
			$this->setErrorMessage($this->lng->txt("fill_out_all_required_fields"));
			$result = 1;
		}

/*		if (($result) and ($_POST["cmd"]["add"]))
		{
			// You cannot create gaps before you enter the required data
			ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields_create_gaps"));
			$_POST["cmd"]["add"] = "";
		}
*/
		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		$this->object->setTextgapRating($_POST["textgap_rating"]);
		$this->object->setIdenticalScoring($_POST["identical_scoring"]);
		$this->object->setFixedTextLength($_POST["fixedTextLength"]);
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$cloze_text = ilUtil::stripSlashes($_POST["clozetext"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
		$this->object->setClozeText($cloze_text);
		// adding estimated working time
		$saved = $saved | $this->writeOtherPostData($result);

		if (strcmp($this->ctrl->getCmd(), "createGaps") == 0)
		{
			// on createGaps the gaps are created from the entered cloze text
			// but synchronized with existing gap form values if an answer
			// already exists for a gap
			$this->setGapTypes();
			$this->setShuffleState();
			$this->setGapPoints();
			$this->setGapBounds();
		}
		else
		{
			$this->setGapTypes();
			$this->setShuffleState();
			$error = $this->setGapAnswers();
			if ($error) $result = 1;
			$this->setGapPoints();
			$error = $this->setGapBounds();
			if ($error) $result = 1;
			$this->object->updateClozeTextFromGaps();
		}
		if ($saved)
		{
			// If the question was saved automatically before an upload, we have to make
			// sure, that the state after the upload is saved. Otherwise the user could be
			// irritated, if he presses cancel, because he only has the question state before
			// the upload process.
			$this->object->saveToDb();
			$this->ctrl->setParameter($this, "q_id", $this->object->getId());
		}
		return $result;
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
		$this->getQuestionTemplate();
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_cloze_question.html", "Modules/TestQuestionPool");
		for ($i = 0; $i < $this->object->getGapCount(); $i++)
		{
			$gap = $this->object->getGap($i);
			if ($gap->getType() == CLOZE_TEXT)
			{
				$this->tpl->setCurrentBlock("textgap_value");
				foreach ($gap->getItemsRaw() as $item)
				{
					$this->tpl->setVariable("TEXT_VALUE", $this->lng->txt("value"));
					$this->tpl->setVariable("VALUE_TEXT_GAP", $item->getAnswertext());
					$this->tpl->setVariable("VALUE_GAP_COUNTER", "$i" . "_" . $item->getOrder());
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("VALUE_INDEX", $item->getOrder());
					$this->tpl->setVariable("VALUE_STATUS_COUNTER", $item->getOrder());
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
					$this->tpl->setVariable("VALUE_TEXT_GAP_POINTS", $item->getPoints());
					$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("textgap");
				$this->tpl->setVariable("ADD_TEXT_GAP", $this->lng->txt("add_gap"));
				$this->tpl->setVariable("VALUE_GAP_COUNTER", "$i");
				$this->tpl->parseCurrentBlock();
			}
			
			else if ($gap->getType() == CLOZE_NUMERIC)
			{
				$this->tpl->setCurrentBlock("numericgap_value");
				foreach ($gap->getItemsRaw() as $item)
				{
					$this->tpl->setVariable("TEXT_VALUE", $this->lng->txt("value"));
					$this->tpl->setVariable("TEXT_LOWER_LIMIT", $this->lng->txt("range_lower_limit"));
					$this->tpl->setVariable("TEXT_UPPER_LIMIT", $this->lng->txt("range_upper_limit"));
					$this->tpl->setVariable("VALUE_NUMERIC_GAP", $item->getAnswertext());
					$this->tpl->setVariable("VALUE_GAP_COUNTER", "$i" . "_" . $item->getOrder());
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("VALUE_INDEX", $item->getOrder());
					$this->tpl->setVariable("VALUE_STATUS_COUNTER", $item->getOrder());
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
					$this->tpl->setVariable("VALUE_NUMERIC_GAP_POINTS", $item->getPoints());
					$this->tpl->setVariable("VALUE_LOWER_LIMIT", $item->getLowerBound());
					$this->tpl->setVariable("VALUE_UPPER_LIMIT", $item->getUpperBound());
					$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("numericgap");
				$this->tpl->parseCurrentBlock();
			}
			
			else if ($gap->getType() == CLOZE_SELECT)
			{
				$this->tpl->setCurrentBlock("selectgap_value");
				foreach ($gap->getItemsRaw() as $item)
				{
					$this->tpl->setVariable("TEXT_VALUE", $this->lng->txt("value"));
					$this->tpl->setVariable("VALUE_SELECT_GAP", $item->getAnswertext());
					$this->tpl->setVariable("VALUE_GAP_COUNTER", "$i" . "_" . $item->getOrder());
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("VALUE_INDEX", $item->getOrder());
					$this->tpl->setVariable("VALUE_STATUS_COUNTER", $item->getOrder());
					$this->tpl->setVariable("VALUE_GAP", $i);
					$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
					$this->tpl->setVariable("VALUE_SELECT_GAP_POINTS", $item->getPoints());
					$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("selectgap");
				$this->tpl->setVariable("ADD_SELECT_GAP", $this->lng->txt("add_gap"));
				$this->tpl->setVariable("TEXT_SHUFFLE_ANSWERS", $this->lng->txt("shuffle_answers"));
				$this->tpl->setVariable("VALUE_GAP_COUNTER", "$i");
				if ($gap->getShuffle())
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
			$name = $this->lng->txt("gap") . " " . ($i+1);
			$this->tpl->setVariable("TEXT_GAP_NAME", $name);
			$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("type"));
			$this->tpl->setVariable("TEXT_CHANGE", $this->lng->txt("change"));
			switch ($gap->getType())
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

		// out automatical selection of the best text input field (javascript)
		$this->tpl->setCurrentBlock("HeadContent");
		$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");
		$javascript = "<script type=\"text/javascript\">ilAddOnLoad(initialSelect);\n".
			"function initialSelect() {\n%s\n}</script>";
		if (preg_match("/addGapText_(\d+)/", $this->ctrl->getCmd(), $matches))
		{
			$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_cloze_test.textgap_" . $matches[1] . "_" .(is_object($this->object->getGap($matches[1])) ? $this->object->getGap($matches[1])->getItemCount() - 1 : 0) .".focus(); document.frm_cloze_test.textgap_" . $matches[1] . "_" . (is_object($this->object->getGap($matches[1])) ? $this->object->getGap($matches[1])->getItemCount() - 1 : 0) .".scrollIntoView(\"true\");"));
		}
		else if (preg_match("/addSelectGapText_(\d+)/", $this->ctrl->getCmd(), $matches))
		{
			$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_cloze_test.selectgap_" . $matches[1] . "_" .(is_object($this->object->getGap($matches[1])) ? $this->object->getGap($matches[1])->getItemCount() - 1 : 0) .".focus(); document.frm_cloze_test.selectgap_" . $matches[1] . "_" . (is_object($this->object->getGap($matches[1])) ? $this->object->getGap($matches[1])->getItemCount() - 1 : 0) .".scrollIntoView(\"true\");"));
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
		$this->tpl->setVariable("FIXED_TEXTLENGTH", $this->lng->txt("cloze_fixed_textlength"));
		$this->tpl->setVariable("FIXED_TEXTLENGTH_DESCRIPTION", $this->lng->txt("cloze_fixed_textlength_description"));
		if ($this->object->getFixedTextLength())
		{
			$this->tpl->setVariable("VALUE_FIXED_TEXTLENGTH", " value=\"" . ilUtil::prepareFormOutput($this->object->getFixedTextLength()) . "\"");
		}
		$this->tpl->setVariable("VALUE_CLOZE_TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$this->tpl->setVariable("VALUE_CLOZE_COMMENT", ilUtil::prepareFormOutput($this->object->getComment()));
		$this->tpl->setVariable("VALUE_CLOZE_AUTHOR", ilUtil::prepareFormOutput($this->object->getAuthor()));
		$cloze_text = $this->object->getClozeText();
		$this->tpl->setVariable("VALUE_CLOZE_TEXT", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($cloze_text)));
		$this->tpl->setVariable("TEXT_CREATE_GAPS", $this->lng->txt("create_gaps"));
		$identical_scoring = $this->object->getIdenticalScoring();
		if ($identical_scoring) $this->tpl->setVariable("CHECKED_IDENTICAL_SCORING", " checked=\"checked\"");
		$this->tpl->setVariable("TEXT_IDENTICAL_SCORING", $this->lng->txt("identical_scoring"));
		$this->tpl->setVariable("TEXT_IDENTICAL_SCORING_DESCRIPTION", $this->lng->txt("identical_scoring_desc"));
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
		$this->ctrl->setParameter($this, "sel_question_types", $this->object->getQuestionType());
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->outQuestionType());
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->parseCurrentBlock();

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex"); $rte->addButton("pastelatex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");

		$this->tpl->setCurrentBlock("adm_content");
		//$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"initialSelect();\""); 
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Creates an output of the question for a test
	*
	* Creates an output of the question for a test
	*
	* @param string $formaction The form action for the test output
	* @param integer $active_id The active id of the current user from the tst_active database table
	* @param integer $pass The test pass of the current user
	* @param boolean $is_postponed The information if the question is a postponed question or not
	* @param boolean $use_post_solutions Fills the question output with answers from the previous post if TRUE, otherwise with the user results from the database
	* @access public
	*/
	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		$this->tpl->setVariable("FORMACTION", $formaction);
	}

	/**
	* Creates a preview output of the question
	*
	* Creates a preview output of the question
	*
	* @return string HTML code which contains the preview output of the question
	* @access public
	*/
	function getPreview($show_question_only = FALSE)
	{
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_cloze_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$output = $this->object->getClozeText();
		foreach ($this->object->getGaps() as $gap_index => $gap)
		{
			switch ($gap->getType())
			{
				case CLOZE_TEXT:
					$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_text.html", TRUE, TRUE, "Modules/TestQuestionPool");
					$gaptemplate->setVariable("TEXT_GAP_SIZE", $this->object->getFixedTextLength() ? $this->object->getFixedTextLength() : $gap->getMaxWidth());
					$gaptemplate->setVariable("GAP_COUNTER", $gap_index);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
				case CLOZE_SELECT:
					$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_select.html", TRUE, TRUE, "Modules/TestQuestionPool");
					foreach ($gap->getItems() as $item)
					{
						$gaptemplate->setCurrentBlock("select_gap_option");
						$gaptemplate->setVariable("SELECT_GAP_VALUE", $item->getOrder());
						$gaptemplate->setVariable("SELECT_GAP_TEXT", ilUtil::prepareFormOutput($item->getAnswerText()));
						$gaptemplate->parseCurrentBlock();
					}
					$gaptemplate->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
					$gaptemplate->setVariable("GAP_COUNTER", $gap_index);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
				case CLOZE_NUMERIC:
					$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_numeric.html", TRUE, TRUE, "Modules/TestQuestionPool");
					$gaptemplate->setVariable("TEXT_GAP_SIZE", $this->object->getFixedTextLength() ? $this->object->getFixedTextLength() : $gap->getMaxWidth());
					$gaptemplate->setVariable("GAP_COUNTER", $gap_index);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
			}
		}
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($output, TRUE));
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		return $questionoutput;
	}

	/**
	* Creates a solution output of the question
	*
	* Creates a solution output of the question
	*
	* @param integer $active_id The active id of the current user from the tst_active database table
	* @param integer $pass The test pass of the current user
	* @param boolean $graphicalOutput If TRUE, additional graphics (checkmark, cross) are shown to indicate wrong or right answers
	* @param boolean $result_output If TRUE, the resulting points are shown for every answer
	* @return string HTML code which contains the solution output of the question
	* @access public
	*/
	function getSolutionOutput($active_id, $pass = NULL, $graphicalOutput = FALSE, $result_output = FALSE, $show_question_only = TRUE, $show_feedback = FALSE, $show_correct_solution = FALSE)
	{
		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if ($active_id)
		{
			// get the solutions of a user
			$user_solution =& $this->object->getSolutionValues($active_id, $pass);
			if (!is_array($user_solution)) 
			{
				$user_solution = array();
			}
		}

		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_cloze_question_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$output = $this->object->getClozeText();
		foreach ($this->object->getGaps() as $gap_index => $gap)
		{
			$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_output_solution_gap.html", TRUE, TRUE, "Modules/TestQuestionPool");
			$found = array();
			foreach ($user_solution as $solutionarray)
			{
				if ($solutionarray["value1"] == $gap_index) $found = $solutionarray;
			}

			if ($active_id)
			{
				if ($graphicalOutput)
				{
					// output of ok/not ok icons for user entered solutions
					$check = $this->object->testGapSolution($found["value2"], $gap_index);
					if ($check["best"])
					{
						$gaptemplate->setCurrentBlock("icon_ok");
						$gaptemplate->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.gif"));
						$gaptemplate->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
						$gaptemplate->parseCurrentBlock();
					}
					else
					{
						$gaptemplate->setCurrentBlock("icon_not_ok");
						if ($check["positive"])
						{
							$gaptemplate->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.gif"));
							$gaptemplate->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
						}
						else
						{
							$gaptemplate->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.gif"));
							$gaptemplate->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
						}
						$gaptemplate->parseCurrentBlock();
					}
				}
			}
			if ($result_output)
			{
				$points = $this->object->getMaximumGapPoints($gap_index);
				$resulttext = ($points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")"; 
				$gaptemplate->setCurrentBlock("result_output");
				$gaptemplate->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
				$gaptemplate->parseCurrentBlock();
			}
			switch ($gap->getType())
			{
				case CLOZE_TEXT:
					$solutiontext = "";
					if (($active_id > 0) && (!$show_correct_solution))
					{
						if ((count($found) == 0) || (strlen(trim($found["value2"])) == 0))
						{
							for ($chars = 0; $chars < $gap->getMaxWidth(); $chars++)
							{
								$solutiontext .= "&nbsp;";
							}
						}
						else
						{
							$solutiontext = ilUtil::prepareFormOutput($found["value2"]);
						}
					}
					else
					{
						$solutiontext = ilUtil::prepareFormOutput($gap->getBestSolutionOutput());
					}
					$gaptemplate->setVariable("SOLUTION", $solutiontext);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
				case CLOZE_SELECT:
					$solutiontext = "";
					if (($active_id > 0) && (!$show_correct_solution))
					{
						if ((count($found) == 0) || (strlen(trim($found["value2"])) == 0))
						{
							for ($chars = 0; $chars < $gap->getMaxWidth(); $chars++)
							{
								$solutiontext .= "&nbsp;";
							}
						}
						else
						{
							$item = $gap->getItem($found["value2"]);
							if (is_object($item))
							{
								$solutiontext = ilUtil::prepareFormOutput($item->getAnswertext());
							}
							else
							{
								for ($chars = 0; $chars < $gap->getMaxWidth(); $chars++)
								{
									$solutiontext .= "&nbsp;";
								}
							}
						}
					}
					else
					{
						$solutiontext = ilUtil::prepareFormOutput($gap->getBestSolutionOutput());
					}
					$gaptemplate->setVariable("SOLUTION", $solutiontext);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
				case CLOZE_NUMERIC:
					$solutiontext = "";
					if (($active_id > 0) && (!$show_correct_solution))
					{
						if ((count($found) == 0) || (strlen(trim($found["value2"])) == 0))
						{
							for ($chars = 0; $chars < $gap->getMaxWidth(); $chars++)
							{
								$solutiontext .= "&nbsp;";
							}
						}
						else
						{
							$solutiontext = ilUtil::prepareFormOutput($found["value2"]);
						}
					}
					else
					{
						$solutiontext = ilUtil::prepareFormOutput($gap->getBestSolutionOutput());
					}
					$gaptemplate->setVariable("SOLUTION", $solutiontext);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
			}
		}
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($output, TRUE));

		// generate the question output
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$questionoutput = $template->get();
		$feedback = ($show_feedback) ? $this->getAnswerFeedbackOutput($active_id, $pass) : "";
		if (strlen($feedback)) $solutiontemplate->setVariable("FEEDBACK", $feedback);
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get(); 
		if (!$show_question_only)
		{
			// get page object output
			$solutionoutput = $this->getILIASPage($solutionoutput);
		}
		return $solutionoutput;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
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
		$output = $this->object->getClozeText();
		foreach ($this->object->getGaps() as $gap_index => $gap)
		{
			switch ($gap->getType())
			{
				case CLOZE_TEXT:
					$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_text.html", TRUE, TRUE, "Modules/TestQuestionPool");
					$gaptemplate->setVariable("TEXT_GAP_SIZE", $this->object->getFixedTextLength() ? $this->object->getFixedTextLength() : $gap->getMaxWidth());
					$gaptemplate->setVariable("GAP_COUNTER", $gap_index);
					foreach ($user_solution as $solution)
					{
						if (strcmp($solution["value1"], $gap_index) == 0)
						{
							$gaptemplate->setVariable("VALUE_GAP", " value=\"" . ilUtil::prepareFormOutput($solution["value2"]) . "\"");
						}
					}
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
				case CLOZE_SELECT:
					$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_select.html", TRUE, TRUE, "Modules/TestQuestionPool");
					foreach ($gap->getItems() as $item)
					{
						$gaptemplate->setCurrentBlock("select_gap_option");
						$gaptemplate->setVariable("SELECT_GAP_VALUE", $item->getOrder());
						$gaptemplate->setVariable("SELECT_GAP_TEXT", ilUtil::prepareFormOutput($item->getAnswerText()));
						foreach ($user_solution as $solution)
						{
							if (strcmp($solution["value1"], $gap_index) == 0)
							{
								if (strcmp($solution["value2"], $item->getOrder()) == 0)
								{
									$gaptemplate->setVariable("SELECT_GAP_SELECTED", " selected=\"selected\"");
								}
							}
						}
						$gaptemplate->parseCurrentBlock();
					}
					$gaptemplate->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
					$gaptemplate->setVariable("GAP_COUNTER", $gap_index);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
				case CLOZE_NUMERIC:
					$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_numeric.html", TRUE, TRUE, "Modules/TestQuestionPool");
					$gaptemplate->setVariable("TEXT_GAP_SIZE", $this->object->getFixedTextLength() ? $this->object->getFixedTextLength() : $gap->getMaxWidth());
					$gaptemplate->setVariable("GAP_COUNTER", $gap_index);
					foreach ($user_solution as $solution)
					{
						if (strcmp($solution["value1"], $gap_index) == 0)
						{
							$gaptemplate->setVariable("VALUE_GAP", " value=\"" . ilUtil::prepareFormOutput($solution["value2"]) . "\"");
						}
					}
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
			}
		}
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($output, TRUE));
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
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
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$this->object->saveFeedbackGeneric(0, ilUtil::stripSlashes($_POST["feedback_incomplete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->saveFeedbackGeneric(1, ilUtil::stripSlashes($_POST["feedback_complete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->cleanupMediaObjectUsage();
		parent::saveFeedback();
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
		$this->tpl->setVariable("VALUE_FEEDBACK_COMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(1)), FALSE));
		$this->tpl->setVariable("FEEDBACK_INCOMPLETE", $this->lng->txt("feedback_incomplete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_INCOMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(0)), FALSE));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex"); $rte->addButton("pastelatex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");
	}

	/**
	* Sets the ILIAS tabs for this question type
	*
	* Sets the ILIAS tabs for this question type
	*
	* @access public
	*/
	function setQuestionTabs()
	{
		global $rbacsystem, $ilTabs;
		
		$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $_GET["q_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$q_type = $this->object->getQuestionType();

		if (strlen($q_type))
		{
			$classname = $q_type . "GUI";
			$this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
			$this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
		}

		if ($_GET["q_id"])
		{
			if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
			{
				// edit page
				$ilTabs->addTarget("edit_content",
					$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "edit"),
					array("edit", "insert", "exec_pg"),
					"", "", $force_active);
			}
	
			// edit page
			$ilTabs->addTarget("preview",
				$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "preview"),
				array("preview"),
				"ilPageObjectGUI", "", $force_active);
		}

		$force_active = false;
		$commands = $_POST["cmd"];
		if (is_array($commands))
		{
			foreach ($commands as $key => $value)
			{
				if (preg_match("/^delete_.*/", $key, $matches) || 
					preg_match("/^addSelectGapText_.*/", $key, $matches) ||
					preg_match("/^addGapText_.*/", $key, $matches) ||
					preg_match("/^upload_.*/", $key, $matches)
					)
				{
					$force_active = true;
				}
			}
		}
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$url = "";
			if ($classname) $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "cancel", 
					 "createGaps", "saveEdit", "changeGapType"),
				$classname, "", $force_active);
		}

		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("feedback",
				$this->ctrl->getLinkTargetByClass($classname, "feedback"),
				array("feedback", "saveFeedback"),
				$classname, "");
		}
		
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("solution_hint",
				$this->ctrl->getLinkTargetByClass($classname, "suggestedsolution"),
				array("suggestedsolution", "saveSuggestedSolution", "outSolutionExplorer", "cancel", 
				"addSuggestedSolution","cancelExplorer", "linkChilds", "removeSuggestedSolution"
				),
				$classname, 
				""
			);
		}

		// Assessment of questions sub menu entry
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("statistics",
				$this->ctrl->getLinkTargetByClass($classname, "assessment"),
				array("assessment"),
				$classname, "");
		}
		
		if (($_GET["calling_test"] > 0) || ($_GET["test_ref_id"] > 0))
		{
			$ref_id = $_GET["calling_test"];
			if (strlen($ref_id) == 0) $ref_id = $_GET["test_ref_id"];
			$ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id");
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt("qpl"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
		}
	}
}
?>