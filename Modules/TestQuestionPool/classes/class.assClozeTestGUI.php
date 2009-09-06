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
	*/
	private $gapIndex;
	
	/**
	* assClozeTestGUI constructor
	*
	* @param integer $id The database id of a image map question object
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
		if (preg_match("/^(removegap|addgap)_(\d+)$/", $cmd, $matches))
		{
			$cmd = $matches[1];
			$this->gapIndex = $matches[2];
		}
		return $cmd;
	}

	/**
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	function writePostData($always = false)
	{
		$hasErrors = (!$always) ? $this->editQuestion(true) : false;
		if (!$hasErrors)
		{
			$this->object->flushGaps();
			$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
			$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
			$this->object->setTextgapRating($_POST["textgap_rating"]);
			$this->object->setIdenticalScoring($_POST["identical_scoring"]);
			if ($this->getSelfAssessmentEditingMode())
			{
				$this->setNrOfTries($_POST['nr_of_tries']);
			}
			$this->object->setFixedTextLength($_POST["fixedTextLength"]);
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$cloze_text = ilUtil::stripSlashes($_POST["question"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
			$this->object->setClozeText($cloze_text);
			$this->object->setEstimatedWorkingTime(
				ilUtil::stripSlashes($_POST["Estimated"]["hh"]),
				ilUtil::stripSlashes($_POST["Estimated"]["mm"]),
				ilUtil::stripSlashes($_POST["Estimated"]["ss"])
			);

			if (is_array($_POST['gap']))
			{
				if (strcmp($this->ctrl->getCmd(), 'createGaps') != 0) $this->object->clearGapAnswers();
				foreach ($_POST['gap'] as $idx => $hidden)
				{
					$clozetype = $_POST['clozetype_' . $idx];
					$this->object->setGapType($idx, $clozetype);
					if (array_key_exists('shuffle_' . $idx, $_POST))
					{
						$this->object->setGapShuffle($idx, $_POST['shuffle_' . $idx]);
					}

					if (strcmp($this->ctrl->getCmd(), 'createGaps') != 0) 
					{
						if (is_array($_POST['gap_' . $idx]['answer']))
						{
							foreach ($_POST['gap_' . $idx]['answer'] as $order => $value)
							{
								$this->object->addGapAnswer($idx, $order, ilUtil::stripSlashes($value, FALSE));
							}
						}
					}
					if (array_key_exists('gap_' . $idx . '_numeric', $_POST))
					{
						if (strcmp($this->ctrl->getCmd(), 'createGaps') != 0) $this->object->addGapAnswer($idx, 0, ilUtil::stripSlashes(str_replace(",", ".", $_POST['gap_' . $idx . '_numeric']), FALSE));
						$this->object->setGapAnswerLowerBound($idx, 0, ilUtil::stripSlashes(str_replace(",", ".", $_POST['gap_' . $idx . '_numeric_lower']), FALSE));
						$this->object->setGapAnswerUpperBound($idx, 0, ilUtil::stripSlashes(str_replace(",", ".", $_POST['gap_' . $idx . '_numeric_upper']), FALSE));
						$this->object->setGapAnswerPoints($idx, 0, ilUtil::stripSlashes($_POST['gap_' . $idx . '_numeric_points']));
					}
					if (is_array($_POST['gap_' . $idx]['points']))
					{
						foreach ($_POST['gap_' . $idx]['points'] as $order => $value)
						{
							$this->object->setGapAnswerPoints($idx, $order, ilUtil::stripSlashes($value));
						}
					}
				}
				if (strcmp($this->ctrl->getCmd(), 'createGaps') != 0) $this->object->updateClozeTextFromGaps();
			}
			return 0;
		}
		else
		{
			return 1;
		}
	}

	/**
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	public function editQuestion($checkonly = FALSE)
	{
		$save = ((strcmp($this->ctrl->getCmd(), "save") == 0) || (strcmp($this->ctrl->getCmd(), "saveEdit") == 0)) ? TRUE : FALSE;
		$this->getQuestionTemplate();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("assclozetest");

		// title, author, description, question, working time (assessment mode)
		$this->addBasicQuestionFormProperties($form);
		$q_item = $form->getItemByPostVar("question");
		$q_item->setInfo($this->lng->txt("close_text_hint"));
		$q_item->setTitle($this->lng->txt("cloze_text"));
	
		// text rating
		if (!$this->getSelfAssessmentEditingMode())
		{
			$textrating = new ilSelectInputGUI($this->lng->txt("text_rating"), "textgap_rating");
			$text_options = array(
				"ci" => $this->lng->txt("cloze_textgap_case_insensitive"),
				"cs" => $this->lng->txt("cloze_textgap_case_sensitive"),
				"l1" => sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "1"),
				"l2" => sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "2"),
				"l3" => sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "3"),
				"l4" => sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "4"),
				"l5" => sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "5")
			);
			$textrating->setOptions($text_options);
			$textrating->setValue($this->object->getTextgapRating());
			$form->addItem($textrating);
	
			// text field length
			$fixedTextLength = new ilNumberInputGUI($this->lng->txt("cloze_fixed_textlength"), "fixedTextLength");
			$fixedTextLength->setValue(ilUtil::prepareFormOutput($this->object->getFixedTextLength()));
			$fixedTextLength->setMinValue(0);
			$fixedTextLength->setSize(3);
			$fixedTextLength->setMaxLength(6);
			$fixedTextLength->setInfo($this->lng->txt('cloze_fixed_textlength_description'));
			$fixedTextLength->setRequired(false);
			$form->addItem($fixedTextLength);
	
			// identical scoring
			$identical_scoring = new ilCheckboxInputGUI($this->lng->txt("identical_scoring"), "identical_scoring");
			$identical_scoring->setValue(1);
			$identical_scoring->setChecked($this->object->getIdenticalScoring());
			$identical_scoring->setInfo($this->lng->txt('identical_scoring_desc'));
			$identical_scoring->setRequired(FALSE);
			$form->addItem($identical_scoring);
		}

		for ($i = 0; $i < $this->object->getGapCount(); $i++)
		{
			$gap = $this->object->getGap($i);
			$header = new ilFormSectionHeaderGUI();
			$header->setTitle($this->lng->txt("gap") . " " . ($i+1));
			$form->addItem($header);

			$gapcounter = new ilHiddenInputGUI("gap[$i]");
			$gapcounter->setValue($i);
			$form->addItem($gapcounter);
			
			$gaptype = new ilSelectInputGUI($this->lng->txt('type'), "clozetype_$i");
			$options = array(
				0 => $this->lng->txt("text_gap"),
				1 => $this->lng->txt("select_gap"),
				2 => $this->lng->txt("numeric_gap")
			);
			$gaptype->setOptions($options);
			$gaptype->setValue($gap->getType());
			$form->addItem($gaptype);
			
			if ($gap->getType() == CLOZE_TEXT)
			{
				// Choices
				include_once "./Modules/TestQuestionPool/classes/class.ilAnswerWizardInputGUI.php";
				include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
				$values = new ilAnswerWizardInputGUI($this->lng->txt("values"), "gap_" . $i . "");
				$values->setRequired(true);
				$values->setQuestionObject($this->object);
				$values->setSingleline(true);
				$values->setAllowMove(false);
				if (count($gap->getItemsRaw()) == 0) $gap->addItem(new assAnswerCloze("", 0, 0));
				$values->setValues($gap->getItemsRaw());
				$form->addItem($values);
			}
			else if ($gap->getType() == CLOZE_SELECT)
			{
				include_once "./Modules/TestQuestionPool/classes/class.ilAnswerWizardInputGUI.php";
				include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
				$values = new ilAnswerWizardInputGUI($this->lng->txt("values"), "gap_" . $i . "");
				$values->setRequired(true);
				$values->setQuestionObject($this->object);
				$values->setSingleline(true);
				$values->setAllowMove(false);
				if (count($gap->getItemsRaw()) == 0) $gap->addItem(new assAnswerCloze("", 0, 0));
				$values->setValues($gap->getItemsRaw());
				$form->addItem($values);

				// shuffle
				$shuffle = new ilCheckboxInputGUI($this->lng->txt("shuffle_answers"), "shuffle_" . $i . "");
				$shuffle->setValue(1);
				$shuffle->setChecked($gap->getShuffle());
				$shuffle->setRequired(FALSE);
				$form->addItem($shuffle);
			}
			else if ($gap->getType() == CLOZE_NUMERIC)
			{
				if (count($gap->getItemsRaw()) == 0) $gap->addItem(new assAnswerCloze("", 0, 0));
				foreach ($gap->getItemsRaw() as $item)
				{
					$value = new ilFormulaInputGUI($this->lng->txt('value'), "gap_" . $i . "_numeric");
					$value->setSize(10);
					$value->setValue(ilUtil::prepareFormOutput($item->getAnswertext()));
					$value->setRequired(true);
					$value->setInlineStyle('text-align: right;');
					$form->addItem($value);

					$lowerbound = new ilFormulaInputGUI($this->lng->txt('range_lower_limit'), "gap_" . $i . "_numeric_lower");
					$lowerbound->setSize(10);
					$lowerbound->setRequired(true);
					$lowerbound->setValue(ilUtil::prepareFormOutput($item->getLowerBound()));
					$lowerbound->setInlineStyle('text-align: right;');
					$form->addItem($lowerbound);

					$upperbound = new ilFormulaInputGUI($this->lng->txt('range_upper_limit'), "gap_" . $i . "_numeric_upper");
					$upperbound->setSize(10);
					$upperbound->setRequired(true);
					$upperbound->setValue(ilUtil::prepareFormOutput($item->getUpperBound()));
					$upperbound->setInlineStyle('text-align: right;');
					$form->addItem($upperbound);

					$points = new ilNumberInputGUI($this->lng->txt('points'), "gap_" . $i . "_numeric_points");
					$points->setSize(3);
					$points->setRequired(true);
					$points->setValue(ilUtil::prepareFormOutput($item->getPoints()));
					$form->addItem($points);
				}
			}
		}
		
		$form->addCommandButton("save", $this->lng->txt("save"));
		if (!$this->getSelfAssessmentEditingMode()) $form->addCommandButton("saveEdit", $this->lng->txt("save_edit"));
		$form->addCommandButton('createGaps', $this->lng->txt('create_gaps'));
	
		$errors = false;
	
		if ($save)
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			if ($errors) $checkonly = false;
		}

		if (!$checkonly) $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
		return $errors;
	}

	/**
	* Create gaps from cloze text
	*/
	public function createGaps()
	{
		$this->writePostData(true);
		$this->editQuestion();
	}

	/**
	* Remove a gap answer
	*/
	function removegap()
	{
		$this->writePostData(true);
		$this->object->deleteAnswerText($this->gapIndex, key($_POST['cmd']['removegap_' . $this->gapIndex]));
		$this->editQuestion();
	}

	/**
	* Add a gap answer
	*/
	function addgap()
	{
		$this->writePostData(true);
		$this->object->addGapAnswer($this->gapIndex, key($_POST['cmd']['addgap_' . $this->gapIndex])+1, "");
		$this->editQuestion();
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
	* Get the question solution output
	*
	* @param integer $active_id The active user id
	* @param integer $pass The test pass
	* @param boolean $graphicalOutput Show visual feedback for right/wrong answers
	* @param boolean $result_output Show the reached points for parts of the question
	* @param boolean $show_question_only Show the question without the ILIAS content around
	* @param boolean $show_feedback Show the question feedback
	* @param boolean $show_correct_solution Show the correct solution instead of the user solution
	* @param boolean $show_manual_scoring Show specific information for the manual scoring output
	* @return The solution output of the question as HTML code
	*/
	function getSolutionOutput(
		$active_id,
		$pass = NULL,
		$graphicalOutput = FALSE,
		$result_output = FALSE,
		$show_question_only = TRUE,
		$show_feedback = FALSE,
		$show_correct_solution = FALSE,
		$show_manual_scoring = FALSE
	)
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
					$details = $this->object->calculateReachedPoints($active_id, $pass, TRUE);
					$check = $details[$gap_index];
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
		$commands = $_POST["cmd"];
		if (is_array($commands))
		{
			foreach ($commands as $key => $value)
			{
				if (preg_match("/^removegap_.*/", $key, $matches) || 
					preg_match("/^addgap_.*/", $key, $matches)
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
				array("editQuestion", "save", "createGaps", "saveEdit"),
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