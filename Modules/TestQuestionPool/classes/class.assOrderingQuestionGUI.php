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
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Ordering question GUI representation
*
* The assOrderingQuestionGUI class encapsulates the GUI representation
* for ordering questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assOrderingQuestionGUI extends assQuestionGUI
{

	/**
	* assOrderingQuestionGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assOrderingQuestionGUI object.
	*
	* @param integer $id The database id of a ordering question object
	* @access public
	*/
	function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assOrderingQuestion.php";
		$this->object = new assOrderingQuestion();
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
		if (substr($cmd, 0, 6) == "upload")
		{
			$cmd = "upload";
		}

		return $cmd;
	}

	public function changeToPictures()
	{
		$this->object->setOrderingType(OQ_PICTURES);
		$this->writePostData();
		$this->editQuestion();
	}
	
	public function changeToText()
	{
		$this->object->setOrderingType(OQ_TERMS);
		$this->writePostData();
		$this->editQuestion();
	}

	public function addanswers()
	{
		$this->writePostData();
		$position = key($_POST["cmd"]["addanswers"]);
		$this->object->addAnswer("", $position+1);
		$this->editQuestion();
	}

	public function removeanswers()
	{
		$this->writePostData();
		$position = key($_POST["cmd"]["removeanswers"]);
		$this->object->deleteAnswer($position);
		$this->editQuestion();
	}

	/**
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	public function writePostData()
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$questiontext = ilUtil::stripSlashes($_POST["question"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
		$this->object->setQuestion($questiontext);
		$this->object->setThumbGeometry($_POST["thumb_geometry"]);
		$this->object->setElementHeight($_POST["element_height"]);
		// adding estimated working time
		$this->object->setEstimatedWorkingTime(
			ilUtil::stripSlashes($_POST["Estimated"]["hh"]),
			ilUtil::stripSlashes($_POST["Estimated"]["mm"]),
			ilUtil::stripSlashes($_POST["Estimated"]["ss"])
		);
		$typechange = ((strcmp($this->ctrl->getCmd(), "changeToPictures") == 0) || (strcmp($this->ctrl->getCmd(), "changeToText") == 0)) ? TRUE : FALSE;
		if (!$typechange) $this->object->setOrderingType($_POST["ordering_type"]);
		//$this->object->setMultilineAnswerSetting($_POST["multilineAnswers"]);

		// Delete all existing answers and create new answers from the form data
		$this->object->flushAnswers();
		$saved = false;

		// add answers
		if ($this->object->getOrderingType() == OQ_TERMS)
		{
			$answers = $_POST["answers"];
			if (is_array($answers))
			{
				foreach ($answers as $index => $answer)
				{
					$this->object->addAnswer(ilUtil::stripSlashes($answer));
				}
			}
		}
		else
		{
			$pictures = $_FILES["answers"];
			if (is_array($pictures))
			{
				foreach ($pictures["name"] as $index => $name)
				{
					$picturefile = $_POST["picture_answers"][$index];
					if (strlen($name))
					{
						$uploadcheck = true;
						// remove trailing '/'
						while (substr($name, -1) == '/')
						{
							$name = substr($name, 0, -1);
						}

						$filename = $name;
						$filename_arr = pathinfo($name);
						$suffix = $filename_arr["extension"];
						$mimetype = $pictures["type"][$index];
						$size_bytes = $pictures["size"][$index];
						$temp_name = $pictures["tmp_name"][$index];
						$error = $pictures["error"][$index];
						// error handling
						if ($error > 0)
						{
							switch ($error)
							{
								case UPLOAD_ERR_INI_SIZE:
									$uploadcheck = false;
									break;

								case UPLOAD_ERR_FORM_SIZE:
									$uploadcheck = false;
									break;

								case UPLOAD_ERR_PARTIAL:
									$uploadcheck = false;
									break;

								case UPLOAD_ERR_NO_FILE:
									$uploadcheck = false;
									break;

								case UPLOAD_ERR_NO_TMP_DIR:
									$uploadcheck = false;
									break;

								case UPLOAD_ERR_CANT_WRITE:
									$uploadcheck = false;
									break;

								case UPLOAD_ERR_EXTENSION:
									$uploadcheck = false;
									break;
							}
						}

						// check suffixes
						$suffixes = array("jpg", "jpeg", "png", "gif");
						if (strlen($pictures["tmp_name"][$index]))
						{
							if (!in_array(strtolower($suffix), $suffixes))
							{
								$uploadcheck = false;
							}
						}

						// virus handling
						if ($pictures["tmp_name"][$index] != "")
						{
							$vir = ilUtil::virusHandling($temp_name, $filename);
							if ($vir[0] == false)
							{
								$uploadcheck = false;
							}
						}
						if ($uploadcheck)
						{
							// upload the new file
							if ($this->object->setImageFile($pictures["tmp_name"][$index], $this->object->getEncryptedFilename($name), $picturefile))
							{
								$picturefile = $this->object->getEncryptedFilename($name);
							}
							else
							{
								$picturefile = "";
							}
							$this->object->addAnswer($picturefile);
						}
						else
						{
							$this->object->addAnswer($picturefile);
						}
					}
					else
					{
						$this->object->addAnswer($picturefile);
					}
				}
			}
		}
		$this->object->setPoints(ilUtil::stripSlashes($_POST["points"]));
		return $this->editQuestion(TRUE);
	}
	
	/**
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	function editQuestion($checkonly = FALSE)
	{
		$save = ((strcmp($this->ctrl->getCmd(), "save") == 0) || (strcmp($this->ctrl->getCmd(), "saveEdit") == 0)) ? TRUE : FALSE;
		$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");
		$this->getQuestionTemplate();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("assOrderingQuestion"));
		$form->setMultipart(($this->object->getOrderingType() == OQ_PICTURES) ? TRUE : FALSE);
		$form->setTableWidth("100%");
		$form->setId("ordering");

		// Edit mode
		$hidden = new ilHiddenInputGUI("ordering_type");
		$hidden->setValue($this->object->getOrderingType());
		$form->addItem($hidden);
		// title
		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setValue($this->object->getTitle());
		$title->setRequired(TRUE);
		$form->addItem($title);
		// author
		$author = new ilTextInputGUI($this->lng->txt("author"), "author");
		$author->setValue($this->object->getAuthor());
		$author->setRequired(TRUE);
		$form->addItem($author);
		// description
		$description = new ilTextInputGUI($this->lng->txt("description"), "comment");
		$description->setValue($this->object->getComment());
		$description->setRequired(FALSE);
		$form->addItem($description);
		// questiontext
		$question = new ilTextAreaInputGUI($this->lng->txt("question"), "question");
		$question->setValue($this->object->prepareTextareaOutput($this->object->getQuestion()));
		$question->setRequired(TRUE);
		$question->setRows(10);
		$question->setCols(80);
		$question->setUseRte(TRUE);
		$question->addPlugin("latex");
		$question->addButton("latex");
		$question->setRTESupport($this->object->getId(), "qpl", "assessment");
		$form->addItem($question);
		// duration
		$duration = new ilDurationInputGUI($this->lng->txt("working_time"), "Estimated");
		$duration->setShowHours(TRUE);
		$duration->setShowMinutes(TRUE);
		$duration->setShowSeconds(TRUE);
		$ewt = $this->object->getEstimatedWorkingTime();
		$duration->setHours($ewt["h"]);
		$duration->setMinutes($ewt["m"]);
		$duration->setSeconds($ewt["s"]);
		$duration->setRequired(FALSE);
		$form->addItem($duration);
		$element_height = new ilNumberInputGUI($this->lng->txt("element_height"), "element_height");
		$element_height->setValue($this->object->getElementHeight());
		$element_height->setRequired(false);
		$element_height->setMaxLength(6);
		$element_height->setMinValue(20);
		$element_height->setSize(6);
		$element_height->setInfo($this->lng->txt("element_height_info"));
		$form->addItem($element_height);
		if ($this->object->getOrderingType() == OQ_PICTURES)
		{
			$geometry = new ilNumberInputGUI($this->lng->txt("thumb_geometry"), "thumb_geometry");
			$geometry->setValue($this->object->getThumbGeometry());
			$geometry->setRequired(true);
			$geometry->setMaxLength(6);
			$geometry->setMinValue(20);
			$geometry->setSize(6);
			$geometry->setInfo($this->lng->txt("thumb_geometry_info"));
			$form->addItem($geometry);
		}
		if (count($this->object->getAnswers()) == 0)
		{
			$this->object->addAnswer();
		}
		// Answers
		if ($this->object->getOrderingType() == OQ_PICTURES)
		{
			$answers = new ilImageWizardInputGUI($this->lng->txt("answers"), "answers");
			$answers->setRequired(TRUE);
			$answers->setImagePathWeb($this->object->getImagePathWeb());
			$answers->setAllowMove(TRUE);
			$answervalues = array();
			foreach ($this->object->getAnswers() as $index => $answervalue)
			{
				$answervalues[$index] = $answervalue->getAnswertext();
			}
			ksort($answervalues);
			$answers->setFilenames($answervalues);
			$form->addItem($answers);
		}
		else
		{
			$answers = new ilTextWizardInputGUI($this->lng->txt("answers"), "answers");
			$answers->setRequired(TRUE);
			$answers->setAllowMove(TRUE);
			$answervalues = array();
			foreach ($this->object->getAnswers() as $index => $answervalue)
			{
				$answervalues[$index] = $answervalue->getAnswertext();
			}
			ksort($answervalues);
			$answers->setValues($answervalues);
			$form->addItem($answers);
		}
		// points
		$points = new ilNumberInputGUI($this->lng->txt("points"), "points");
		$points->setValue($this->object->getPoints());
		$points->setRequired(TRUE);
		$points->setSize(3);
		$points->setMinValue(0.0);
		$form->addItem($points);
		
		$form->addCommandButton("save", $this->lng->txt("save"));
		$form->addCommandButton("saveEdit", $this->lng->txt("save_edit"));
		if ($this->object->getOrderingType() == OQ_PICTURES)
		{
			$form->addCommandButton("changeToText", $this->lng->txt("order_terms"));
		}
		else
		{
			$form->addCommandButton("changeToPictures", $this->lng->txt("order_pictures"));
		}
		$errors = false;
		
		if ($save)
		{
			$errors = !$form->checkInput();
		}
		
		if (!$checkonly) $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
		return $errors;
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
					if ((!$value) && ($this->object->getOrderingType() == OQ_TERMS))
					{
						$ok = false;
					}
					if ($this->object->getOrderingType() == OQ_PICTURES)
					{
						if ((!$_FILES[$key]["tmp_name"]) && (!$value))
						{
							$ok = false;
						}
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

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $user_post_solution = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $user_post_solution);
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		$this->tpl->setVariable("FORMACTION", $formaction);
	}

	function getSolutionOutput($active_id, $pass = NULL, $graphicalOutput = FALSE, $result_output = FALSE, $show_question_only = TRUE, $show_feedback = FALSE, $show_correct_solution = FALSE)
	{
		// shuffle output
		$keys = array_keys($this->object->answers);

		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_ordering_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");

		// get the solution of the user for the active pass or from the last pass if allowed
		$solutions = array();
		if (($active_id > 0) && (!$show_correct_solution))
		{
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
		}
		else
		{
			foreach ($this->object->answers as $index => $answer)
			{
				array_push($solutions, array("value1" => $index, "value2" => $index+1));
			}
		}
		foreach ($keys as $idx)
		{
			$answer = $this->object->answers[$idx];
			if (($active_id > 0) && (!$show_correct_solution))
			{
				if ($graphicalOutput)
				{
					$sol = array();
					foreach ($solutions as $solution)
					{
						$sol[$solution["value1"]] = $solution["value2"];
					}
					asort($sol);
					$sol = array_keys($sol);
					$ans = array();
					foreach ($this->object->answers as $k => $a)
					{
						$ans[$k] = $k;
					}
					asort($ans);
					$ans = array_keys($ans);
					$ok = FALSE;
					foreach ($ans as $arr_idx => $ans_idx)
					{
						if ($ans_idx == $idx)
						{
							if ($ans_idx == $sol[$arr_idx])
							{
								$ok = TRUE;
							}
						}
					}
					// output of ok/not ok icons for user entered solutions
					if ($ok)
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.gif"));
						$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
						$template->parseCurrentBlock();
					}
					else
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.gif"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
						$template->parseCurrentBlock();
					}
				}
			}
			if ($this->object->getOrderingType() == OQ_PICTURES)
			{
				$template->setCurrentBlock("ordering_row_standard_pictures");
				$template->setVariable("THUMB_HREF", $this->object->getImagePathWeb() . $answer->getAnswertext() . ".thumb.jpg");
				list($width, $height, $type, $attr) = getimagesize($this->object->getImagePath() . $answer->getAnswertext(). ".thumb.jpg");
				$template->setVariable("ATTR", $attr);
				$template->setVariable("THUMB_ALT", $this->lng->txt("thumbnail"));
				$template->setVariable("THUMB_TITLE", $this->lng->txt("enlarge"));
				$template->parseCurrentBlock();
			}
			else
			{
				$template->setCurrentBlock("ordering_row_standard_text");
				$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
				$template->parseCurrentBlock();
			}
			$template->setCurrentBlock("ordering_row_standard");
			if ($result_output)
			{
				$answer = $this->object->answers[$idx];
				$points = $answer->getPoints();
				$resulttext = ($points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")"; 
				$template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
			}
			foreach ($solutions as $solution)
			{
				if (strcmp($solution["value1"], $idx) == 0)
				{
					$template->setVariable("ANSWER_ORDER", $solution["value2"]);
				}
			}
			$template->parseCurrentBlock();
		}
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
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
	
	function getPreview($show_question_only = FALSE)
	{
		// shuffle output
		$keys = array_keys($this->object->answers);
		shuffle($keys);

		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_ordering_output.html", TRUE, TRUE, "Modules/TestQuestionPool");

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
				$template->setVariable("ANSWER_ID", $answer->getRandomID());
				$template->parseCurrentBlock();
			}
			else
			{
				$template->setCurrentBlock("ordering_row_standard_text");
				$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
				$template->setVariable("ANSWER_ID", $answer->getRandomID());
				$template->parseCurrentBlock();
			}
			$template->setCurrentBlock("ordering_row_standard");
			$template->setVariable("ANSWER_ID", $answer->getRandomID());
			$template->parseCurrentBlock();
		}

		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		return $questionoutput;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $user_post_solution = FALSE)
	{
		// shuffle output
		$keys = array();
		if (is_array($user_post_solution))
		{
			$keys = $_SESSION["ordering_keys"];
		}
		else
		{
			$keys = array_keys($this->object->answers);
			shuffle($keys);
		}
		$_SESSION["ordering_keys"] = $keys;

		// generate the question output
		include_once "./classes/class.ilTemplate.php";

		$template = new ilTemplate("tpl.il_as_qpl_ordering_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			// BEGIN: add javascript code for javascript enabled ordering questions
			$this->tpl->addBlockFile("CONTENT_BLOCK", "head_content", "tpl.il_as_qpl_ordering_output_javascript.html", "Modules/TestQuestionPool");
			$this->tpl->setCurrentBlock("head_content");
			$this->tpl->setVariable("JS_LOCATION", "./Modules/TestQuestionPool/js/toolman/");
			$this->tpl->parseCurrentBlock();
			// END: add javascript code for javascript enabled ordering questions
			
			// BEGIN: add additional stylesheet for javascript enabled ordering questions
			$this->tpl->setCurrentBlock("AdditionalStyle");
			$this->tpl->setVariable("LOCATION_ADDITIONAL_STYLESHEET", ilUtil::getStyleSheetLocation("output", "test_javascript.css", "Modules/TestQuestionPool"));
			$this->tpl->parseCurrentBlock();
			// END: add additional stylesheet for javascript enabled ordering questions
			
			// BEGIN: onsubmit form action for javascript enabled ordering questions
			$this->tpl->setVariable("ON_SUBMIT", "return saveOrder('orderlist');");
			// END: onsubmit form action for javascript enabled ordering questions
		}

		// get the solution of the user for the active pass or from the last pass if allowed
		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			if (is_array($user_post_solution)) 
			{
				$solutions = array();
				foreach ($user_post_solution as $key => $value)
				{
					if (preg_match("/order_(\d+)/", $key, $matches))
					{
						foreach ($this->object->getAnswers() as $answeridx => $answer)
						{
							if ($answer->getRandomID() == $matches[1])
							{
								array_push($solutions, array("value1" => $answeridx, "value2" => $value));
							}
						}
					}
				}
			}
			else
			{
				$solutions =& $this->object->getSolutionValues($active_id, $pass);
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
						if (is_object($this->object->getAnswer($value)))
						{
							$js .= "initialorder.push('id_" . $this->object->getAnswer($value)->getRandomID() . "');";
						}
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
					$template->setVariable("ANSWER_ID", $answer->getRandomID());
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("ordering_row_standard_text");
					$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
					$template->setVariable("ANSWER_ID", $answer->getRandomID());
					$template->parseCurrentBlock();
				}
				$template->setCurrentBlock("ordering_row_standard");
				$template->setVariable("ANSWER_ID", $answer->getRandomID());
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
					$template->setVariable("ANSWER_ID", $answer->getRandomID());
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("ordering_row_javascript_text");
					$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
					$template->setVariable("ANSWER_ID", $answer->getRandomID());
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
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
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

	function editMode()
	{
		global $ilUser;
		
		if ($this->object->getOrderingType() == OQ_TERMS)
		{
			$this->object->setMultilineAnswerSetting($_POST["multilineAnswers"]);
		}
		$this->object->setOrderingType($_POST["ordering_type"]);
		$this->writePostData();
		$this->editQuestion();
	}

	/**
	* Saves the feedback for the question
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
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$url = "";
			if ($classname) $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			$commands = $_POST["cmd"];
			if (is_array($commands))
			{
				foreach ($commands as $key => $value)
				{
					if (preg_match("/^delete_.*/", $key, $matches))
					{
						$force_active = true;
					}
				}
			}
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "cancel", "editMode", "addItem", "upload", 
					"saveEdit"),
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