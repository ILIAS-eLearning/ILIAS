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
include_once "./assessment/classes/inc.AssessmentConstants.php";

/**
* Ordering question GUI representation
*
* The ASS_OrderingQuestionGUI class encapsulates the GUI representation
* for ordering questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
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
		include_once "./assessment/classes/class.assOrderingQuestion.php";
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
		$this->getQuestionTemplate("qt_ordering");
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_ordering.html", true);

		// Output of existing answers
		for ($i = 0; $i < $this->object->getAnswerCount(); $i++)
		{
			$this->tpl->setCurrentBlock("deletebutton");
			$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("ANSWER_ORDER", $i);
			$this->tpl->parseCurrentBlock();

			$thisanswer = $this->object->getAnswer($i);
			if ($this->object->getOrderingType() == OQ_PICTURES)
			{
				$this->tpl->setCurrentBlock("order_pictures");
				$this->tpl->setVariable("ANSWER_ORDER", $i);
				$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $thisanswer->getOrder() + 1);
				$this->tpl->setVariable("TEXT_ANSWER_PICTURE", $this->lng->txt("answer_picture"));

				$filename = $thisanswer->getAnswertext();
				$extension = "jpg";
				if (preg_match("/.*\.(png|jpg|gif|jpeg)$/", $filename, $matches))
				{
					$extension = $matches[1];
				}
				if ($filename)
				{
					$imagepath = $this->object->getImagePathWeb() . $thisanswer->getAnswertext();
					$this->tpl->setVariable("UPLOADED_IMAGE", "<img src=\"$imagepath.thumb.jpg\" alt=\"" . $thisanswer->getAnswertext() . "\" border=\"\" />");
					$this->tpl->setVariable("IMAGE_FILENAME", htmlspecialchars($thisanswer->getAnswertext()));
					$this->tpl->setVariable("VALUE_ANSWER", "");
					//$thisanswer->getAnswertext()
				}
				$this->tpl->setVariable("UPLOAD", $this->lng->txt("upload"));
			}
			elseif ($this->object->getOrderingType() == OQ_TERMS)
			{
				$this->tpl->setCurrentBlock("order_terms");
				$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $thisanswer->getOrder() + 1);
				$this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
				$this->tpl->setVariable("ANSWER_ORDER", $i);
				$this->tpl->setVariable("VALUE_ANSWER", htmlspecialchars($thisanswer->getAnswertext()));
			}
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("answers");
			$anchor = "#answer_" . ($thisanswer->getOrder() + 1);
			$this->tpl->setVariable("TEXT_SOLUTION_ORDER", $this->lng->txt("solution_order"));
			$this->tpl->setVariable("ANSWER_ORDER", $thisanswer->getOrder());
			$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
			$this->tpl->setVariable("VALUE_ORDER", $thisanswer->getSolutionOrder());
			$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
			$this->tpl->setVariable("VALUE_ORDERING_POINTS", sprintf("%d", $thisanswer->getPoints()));
			$this->tpl->parseCurrentBlock();
		}

		if (($this->ctrl->getCmd() == "addItem") and ($ok))
		{
			if ($this->object->getOrderingType() == OQ_PICTURES)
			{
				$this->tpl->setCurrentBlock("order_pictures");
				$this->tpl->setVariable("ANSWER_ORDER", $this->object->getAnswerCount());
				$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $this->object->getAnswerCount() + 1);
				$this->tpl->setVariable("VALUE_ANSWER", "");
				$this->tpl->setVariable("UPLOAD", $this->lng->txt("upload"));
				$this->tpl->setVariable("TEXT_ANSWER_PICTURE", $this->lng->txt("answer_picture"));
			}
			elseif ($this->object->getOrderingType() == OQ_TERMS)
			{
				$this->tpl->setCurrentBlock("order_terms");
				$this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
				$this->tpl->setVariable("VALUE_ANSWER_COUNTER", $this->object->getAnswerCount() + 1);
				$this->tpl->setVariable("ANSWER_ORDER", $this->object->getAnswerCount());
				$this->tpl->setVariable("VALUE_ASNWER", "");
			}
			$this->tpl->parseCurrentBlock();

			// Create an empty answer
			$this->tpl->setCurrentBlock("answers");
			//$this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
			$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
			$anchor = "#answer_" . ($this->object->getAnswerCount() + 1);
			$this->tpl->setVariable("TEXT_SOLUTION_ORDER", $this->lng->txt("solution_order"));
			$this->tpl->setVariable("ANSWER_ORDER", $this->object->getAnswerCount());
			$this->tpl->setVariable("VALUE_ORDER", $this->object->getMaxSolutionOrder() + 1);
			$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
			$this->tpl->setVariable("VALUE_ORDERING_POINTS", sprintf("%d", 0));
			$this->tpl->parseCurrentBlock();
		}
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
		$javascript = "<script type=\"text/javascript\">function initialSelect() {\n%s\n}</script>";
		switch ($this->ctrl->getCmd())
		{
			case "addItem":
				$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_ordering.answer_".($this->object->getAnswerCount()).".focus(); document.frm_ordering.answer_".($this->object->getAnswerCount()).".scrollIntoView(\"true\");"));
				break;
			default:
				$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_ordering.title.focus();"));
				break;
		}
		$this->tpl->parseCurrentBlock();
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
		$questiontext = $this->object->getQuestion();
		$questiontext = preg_replace("/<br \/>/", "\n", $questiontext);
		$this->tpl->setVariable("VALUE_QUESTION", htmlspecialchars($questiontext));
		$this->tpl->setVariable("VALUE_ADD_ANSWER", $this->lng->txt("add_answer"));
		$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("type"));
		$this->tpl->setVariable("TEXT_TYPE_PICTURES", $this->lng->txt("order_pictures"));
		$this->tpl->setVariable("TEXT_TYPE_TERMS", $this->lng->txt("order_terms"));
		if ($this->object->getOrderingType() == OQ_TERMS)
		{
			$this->tpl->setVariable("SELECTED_TERMS", " selected=\"selected\"");
		}
		elseif ($this->object->getOrderingType() == OQ_PICTURES)
		{
			$this->tpl->setVariable("SELECTED_PICTURES", " selected=\"selected\"");
		}

		$this->tpl->setVariable("TEXT_SOLUTION_HINT", $this->lng->txt("solution_hint"));
		if (count($this->object->suggested_solutions))
		{
			$solution_array = $this->object->getSuggestedSolution(0);
			include_once "./assessment/classes/class.assQuestion.php";
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

		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("SAVE_EDIT", $this->lng->txt("save_edit"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->ctrl->setParameter($this, "sel_question_types", "qt_ordering");
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("qt_ordering"));
		$this->tpl->setVariable("ACTION_ORDERING_QUESTION",	$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->parseCurrentBlock();

		if ($this->error)
		{
			sendInfo($this->error);
		}
		$this->checkAdvancedEditor();
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"initialSelect();\""); 
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
				$this->object->deleteAnswer($matches[1]);
			}
		}
		//$this->ctrl->redirect($this, "editQuestion"); works only on save
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
		$this->object->flushAnswers();

		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		include_once "./classes/class.ilObjAssessmentFolder.php";
		$questiontext = ilUtil::stripSlashes($_POST["question"], true, ilObjAssessmentFolder::_getUsedHTMLTagsAsString());
		$questiontext = preg_replace("/\n/", "<br />", $questiontext);
		$this->object->setQuestion($questiontext);
		$this->object->setSuggestedSolution($_POST["solution_hint"], 0);
		$this->object->setShuffle($_POST["shuffle"]);

		// adding estimated working time
		$saved = $saved | $this->writeOtherPostData($result);
		$this->object->setOrderingType($_POST["ordering_type"]);

		// Add answers from the form
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/answer_(\d+)/", $key, $matches))
			{
				if ($this->object->getOrderingType() == OQ_PICTURES)
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
						$image_file = $_FILES[$key]["name"];
						$image_file = str_replace(" ", "_", $image_file);
						$upload_result = $this->object->setImageFile($image_file, $_FILES[$key]['tmp_name']);
						switch ($upload_result)
						{
							case 0:
								$_POST[$key] = $image_file;
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
						$result = 1;
						$this->setErrorMessage($this->lng->txt("negative_points_not_allowed"));
					}
				}
				else
				{
					$points = 0.0;
				}
				$this->object->addAnswer(
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

	function outQuestionForTest($formaction, $test_id, $user_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		$test_output = $this->getTestOutput($test_id, $user_id, $pass, $is_postponed, $use_post_solutions); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			// BEGIN: add javascript code for javascript enabled ordering questions
			$this->tpl->addBlockFile("CONTENT_BLOCK", "head_content", "tpl.il_as_execute_ordering_javascript.html", true);
			$this->tpl->setCurrentBlock("head_content");
			$this->tpl->setVariable("JS_LOCATION", "./assessment/js/toolman/");
			$this->tpl->parseCurrentBlock();
			// END: add javascript code for javascript enabled ordering questions
			
			// BEGIN: add additional stylesheet for javascript enabled ordering questions
			$this->tpl->setCurrentBlock("AdditionalStyle");
			$this->tpl->setVariable("LOCATION_ADDITIONAL_STYLESHEET", "./assessment/templates/default/test_javascript.css");
			$this->tpl->parseCurrentBlock();
			// END: add additional stylesheet for javascript enabled ordering questions
			
			// BEGIN: onsubmit form action for javascript enabled ordering questions
			$this->tpl->setVariable("ON_SUBMIT", "return saveOrder('orderlist');");
			// END: onsubmit form action for javascript enabled ordering questions
		}
		$this->tpl->setVariable("FORMACTION", $formaction);
	}

	function getSolutionOutput($test_id, $user_id, $pass = NULL)
	{
		// shuffle output
		$keys = array_keys($this->object->answers);

		// get page object output
		$pageoutput = $this->outQuestionPage("", $is_postponed, $test_id);

		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_ordering_output_solution.html", TRUE, TRUE, TRUE);

		// get the solution of the user for the active pass or from the last pass if allowed
		$solutions = array();
		if ($test_id)
		{
			$solutions =& $this->object->getSolutionValues($test_id, $user_id, $pass);
		}
		else
		{
			foreach ($this->object->answers as $index => $answer)
			{
				array_push($solutions, array("value1" => $index, "value2" => $answer->getSolutionOrder()));
			}
		}
		foreach ($keys as $idx)
		{
			$answer = $this->object->answers[$idx];
			if ($this->object->getOrderingType() == OQ_PICTURES)
			{
				$template->setCurrentBlock("ordering_row_standard_pictures");
				$template->setVariable("THUMB_HREF", $this->object->getImagePathWeb() . $answer->getAnswertext() . ".thumb.jpg");
				$template->setVariable("THUMB_ALT", $this->lng->txt("thumbnail"));
				$template->setVariable("THUMB_TITLE", $this->lng->txt("enlarge"));
				$template->parseCurrentBlock();
			}
			else
			{
				$template->setCurrentBlock("ordering_row_standard_text");
				$template->setVariable("ANSWER_TEXT", $answer->getAnswertext());
				$template->parseCurrentBlock();
			}
			$template->setCurrentBlock("ordering_row_standard");
			foreach ($solutions as $solution)
			{
				if (strcmp($solution["value1"], $idx) == 0)
				{
					$template->setVariable("ANSWER_ORDER", $solution["value2"]);
				}
			}
			$template->parseCurrentBlock();
		}
		$template->setVariable("QUESTIONTEXT", $this->object->getQuestion());
		$questionoutput = $template->get();
		$questionoutput = str_replace("<div xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" class=\"ilc_Question\"></div>", $questionoutput, $pageoutput);
		$questionoutput = preg_replace("/<div class\=\"ilc_PageTitle\"\>.*?\<\/div\>/", "", $questionoutput);

		return $questionoutput;
	}
	
	function getTestOutput($test_id, $user_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		// shuffle output
		$keys = array_keys($this->object->answers);
		if ($this->object->getShuffle())
		{
			$keys = $this->object->pcArrayShuffle($keys);
		}

		// get page object output
		$pageoutput = $this->outQuestionPage("", $is_postponed, $test_id);

		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_ordering_output.html", TRUE, TRUE, TRUE);

		// get the solution of the user for the active pass or from the last pass if allowed
		if ($test_id)
		{
			$solutions = NULL;
			include_once "./assessment/classes/class.ilObjTest.php";
			if (ilObjTest::_getHidePreviousResults($test_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($user_id, $test_id);
			}
			if ($use_post_solutions) 
			{
				$solutions = array();
				foreach ($_POST as $key => $value)
				{
					if (preg_match("/order_(\d+)/", $key, $matches))
					{
						array_push($solutions, array("value1" => $matches[1], "value2" => $value));
					}
				}
			}
			else
			{
				$solutions =& $this->object->getSolutionValues($test_id, $user_id, $pass);
			}

			if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT)
			{
				$solution_script .= "";
				$jssolutions = array();
				foreach ($solutions as $idx => $solution_value)
				{
					if ((strcmp($solution_value["value2"], "") != 0) && (strcmp($solution_value["value1"], "") != 0))
					{
						$jssolutions[$solution_value["value2"]] = $solution_value["value1"];
					}
				}
				if (count($jssolutions))
				{
					ksort($jssolutions);
					$js = "";
					foreach ($jssolutions as $key => $value)
					{
						$js .= "initialorder.push($value);";
					}
					$js .= "restoreInitialOrder();";
				}
				if (strlen($js))
				{
					$template->setCurrentBlock("javascript_restore_order");
					$template->setVariable("RESTORE_ORDER", $js);
					$template->parseCurrentBlock();
				}
			}
		}
		
		if ($this->object->getOutputType() != OUTPUT_JAVASCRIPT)
		{
			foreach ($keys as $idx)
			{
				$answer = $this->object->answers[$idx];
				if ($this->object->getOrderingType() == OQ_PICTURES)
				{
					$template->setCurrentBlock("ordering_row_standard_pictures");
					$template->setVariable("PICTURE_HREF", $this->object->getImagePathWeb() . $answer->getAnswertext());
					$template->setVariable("THUMB_HREF", $this->object->getImagePathWeb() . $answer->getAnswertext() . ".thumb.jpg");
					$template->setVariable("THUMB_ALT", $this->lng->txt("thumbnail"));
					$template->setVariable("THUMB_TITLE", $this->lng->txt("enlarge"));
					$template->setVariable("ANSWER_ID", $idx);
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("ordering_row_standard_text");
					$template->setVariable("ANSWER_TEXT", $answer->getAnswertext());
					$template->setVariable("ANSWER_ID", $idx);
					$template->parseCurrentBlock();
				}
				$template->setCurrentBlock("ordering_row_standard");
				$template->setVariable("ANSWER_ID", $idx);
				if (is_array($solutions))
				{
					foreach ($solutions as $solution)
					{
						if (($solution["value1"] == $idx) && (strlen($solution["value2"])))
						{
							$template->setVariable("ANSWER_ORDER", " value=\"" . $solution["value2"] . "\"");
						}
					}
				}
				$template->parseCurrentBlock();
			}
		}
		else
		{
			foreach ($keys as $idx)
			{
				$answer = $this->object->answers[$idx];
				if ($this->object->getOrderingType() == OQ_PICTURES)
				{
					$template->setCurrentBlock("ordering_row_javascript_pictures");
					$template->setVariable("PICTURE_HREF", $this->object->getImagePathWeb() . $answer->getAnswertext());
					$template->setVariable("THUMB_HREF", $this->object->getImagePathWeb() . $answer->getAnswertext() . ".thumb.jpg");
					$template->setVariable("THUMB_ALT", $this->lng->txt("thumbnail"));
					$template->setVariable("THUMB_TITLE", $this->lng->txt("thumbnail"));
					$template->setVariable("ENLARGE_HREF", ilUtil::getImagePath("enlarge.gif", FALSE));
					$template->setVariable("ENLARGE_ALT", $this->lng->txt("enlarge"));
					$template->setVariable("ENLARGE_TITLE", $this->lng->txt("enlarge"));
					$template->setVariable("ANSWER_ID", $idx);
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("ordering_row_javascript_text");
					$template->setVariable("ANSWER_TEXT", $answer->getAnswertext());
					$template->setVariable("ANSWER_ID", $idx);
					$template->parseCurrentBlock();
				}
			}
			$template->setCurrentBlock("ordering_with_javascript");
			if ($this->object->getOrderingType() == OQ_PICTURES)
			{
				$template->setVariable("RESET_POSITIONS", $this->lng->txt("reset_pictures"));
			}
			else
			{
				$template->setVariable("RESET_POSITIONS", $this->lng->txt("reset_definitions"));
			}
			$template->parseCurrentBlock();
		}
		$template->setVariable("QUESTIONTEXT", $this->object->getQuestion());
		$questionoutput = $template->get();
		$questionoutput = str_replace("<div xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" class=\"ilc_Question\"></div>", $questionoutput, $pageoutput);

		return $questionoutput;
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
		$ilUser = NULL, 
		$pass = NULL, 
		$mixpass = false,
		$use_post_solutions = false
	)
	{
		if (!is_object($ilUser)) 
		{
			global $ilUser;
		}
		
		$output = $this->outQuestionPage(($show_solution_only)?"":"ORDERING_QUESTION", $is_postponed,$test_id);
		$output = preg_replace("/&#123;/", "{", $output);
		$output = preg_replace("/&#125;/", "}", $output);
		
		if ($showsolution && !$show_solution_only)
		{
			$solutionintroduction = "<p>" . $this->lng->txt("tst_your_answer_was") . "</p>";
			$output = preg_replace("/(<div[^<]*?ilc_PageTitle.*?<\/div>)/", "\\1" . $solutionintroduction, $output);
		}
		$solutionoutput = preg_replace("/.*?(<div[^<]*?ilc_Question.*?<\/div>).*/", "\\1", $output);
		$solutionoutput = preg_replace("/\"ord/", "\"solution_ord", $solutionoutput);
		$solutionoutput = preg_replace("/name\=\"order_/", "name=\"solution_order_", $solutionoutput);
		
		
		if (!$show_question_page)
			$output = preg_replace("/.*?(<div[^<]*?ilc_Question.*?<\/div>).*/", "\\1", $output);
			
		// if wants solution only then strip the question element from output
		if ($show_solution_only) 
		{
			$output = preg_replace("/(<div[^<]*?ilc_Question[^>]*>.*?<\/div>)/", "", $output);
		}
		
			
		// set solutions
		$solution_script = "";
		if ($test_id)
		{
			$solutions = NULL;
			include_once "./assessment/classes/class.ilObjTest.php";
			if ((!$showsolution) && ilObjTest::_getHidePreviousResults($test_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($ilUser->id, $test_id);
			}
			if ($mixpass) $pass = NULL;
			if ($use_post_solutions) 
			{
				$solutions = array();
				foreach ($_POST as $key => $value)
				{
					if (preg_match("/order_(\d+)/", $key, $matches))
					{
						array_push($solutions, array("value1" => $matches[1], "value2" => $value));
					}
				}
			}
			else
			{
				$solutions =& $this->object->getSolutionValues($test_id, $ilUser->getId(), $pass);
			}
			$solution_script .= "";//"resetValues();\n";
			$jssolutions = array();
			foreach ($solutions as $idx => $solution_value)
			{
				if ($this->object->getOutputType() == OUTPUT_HTML || !$show_question_page)
				{
					$repl_str = "dummy=\"ord".$solution_value["value1"]."\"";
	//echo "<br>".$repl_str;
					if (!$show_question_page)
						$output = $this->replaceInputElements($repl_str, $solution_value["value2"], $output, "[","]"); 
					else 
						$output = str_replace($repl_str, $repl_str." value=\"".$solution_value["value2"]."\"", $output);
				}
				else
				{
					if ((strcmp($solution_value["value2"], "") != 0) && (strcmp($solution_value["value1"], "") != 0))
					{
						$jssolutions[$solution_value["value2"]] = $solution_value["value1"];
					}
				}
			}
			if (!$show_question_page) 
			{
				//echo htmlentities ($output);
				$output = $this->removeFormElements($output);
			}
			if (count($jssolutions))
			{
				ksort($jssolutions);
				$js = "";
				foreach ($jssolutions as $key => $value)
				{
					$js .= "initialorder.push($value);";
				}
				$js .= "restoreInitialOrder();";
				$output = str_replace("/*solution*/", $js, $output);
			}
		}

		if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			if ($this->object->getOrderingType() == OQ_PICTURES)
			{
				foreach ($this->object->answers as $key => $answer)
				{
					$extension = "jpg";
					if (preg_match("/.*\.(png|jpg|gif|jpeg)$/", $answer->getAnswertext(), $matches))
					{
						$extension = $matches[1];
					}
					$sizethumb = GetImageSize ($this->object->getImagePath() . $answer->getAnswertext() . ".thumb.jpg");
					$sizeorig = GetImageSize ($this->object->getImagePath() . $answer->getAnswertext());
					if ($sizethumb[0] >= $sizeorig[0])
					{
						// thumbnail is larger than original -> remove enlarge image
						$output = preg_replace("/<a[^>]*?>\s*<img[^>]*?enlarge[^>]*?>\s*<\/a>/", "", $output);
					}
					// add the image size to the thumbnails
					$output = preg_replace("/(<img[^>]*?".$answer->getAnswertext()."[^>]*?)(\/{0,1}\s*)?>/", "\\1 " . $sizethumb[3] . "\\2", $output);
				}
			}
			// $output = str_replace("// solution_script", "", $output);

			// BEGIN: add javascript code for javascript enabled ordering questions
			$this->tpl->addBlockFile("CONTENT_BLOCK", "head_content", "tpl.il_as_execute_ordering_javascript.html", true);
			$this->tpl->setCurrentBlock("head_content");
			$this->tpl->setVariable("JS_LOCATION", "./assessment/js/toolman/");
			$this->tpl->parseCurrentBlock();
			// END: add javascript code for javascript enabled ordering questions
			
			// BEGIN: add additional stylesheet for javascript enabled ordering questions
			$this->tpl->setCurrentBlock("AdditionalStyle");
			$this->tpl->setVariable("LOCATION_ADDITIONAL_STYLESHEET", "./assessment/templates/default/test_javascript.css");
			$this->tpl->parseCurrentBlock();
			// END: add additional stylesheet for javascript enabled ordering questions
			
			// BEGIN: onsubmit form action for javascript enabled ordering questions
			$this->tpl->setVariable("ON_SUBMIT", "return saveOrder('orderlist');");
			// END: onsubmit form action for javascript enabled ordering questions
			
			//$this->tpl->setVariable("JS_INITIALIZE", "<script type=\"text/javascript\">\nfunction show_solution() {\n$solution_script\n}\n</script>\n");
			//$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"show_solution();\"");
		}
		
		foreach ($this->object->answers as $idx => $answer)
		{
			$repl_str = "dummy=\"solution_ord$idx\"";
			$solutionoutput = str_replace($repl_str, $repl_str." value=\"" . $answer->getSolutionOrder() . "\"", $solutionoutput);
			$solutionoutput = preg_replace("/(<tr.*?dummy=\"solution_ord$idx" . "[^\d].*?)<\/tr>/", "\\1<td>" . "<em>(" . $answer->getPoints() . " " . $this->lng->txt("points") . ")</em>" . "</td></tr>", $solutionoutput);
			if ($show_solution_only)
				$solutionoutput = $this->replaceInputElements($repl_str, $answer->getSolutionOrder(), $solutionoutput , "[" , "]");
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
		if (!$showsolution)
		{
			$solutionoutput = "";
			$received_points = "";
		}
		$this->tpl->setVariable("ORDERING_QUESTION", $output.$solutionoutput.$received_points);
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
		$_SESSION["subquestion_index"] = 0;
		if ($_POST["cmd"]["addSuggestedSolution"])
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
		}
		$this->object->saveToDb();
		$_GET["q_id"] = $this->object->getId();
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->getQuestionTemplate("qt_ordering");
		parent::addSuggestedSolution();
	}
}
?>