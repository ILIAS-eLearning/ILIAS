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

include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* MultipleChoice survey question GUI representation
*
* The SurveyMultipleChoiceQuestionGUI class encapsulates the GUI representation
* for multiple choice survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestionGUI
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyMultipleChoiceQuestionGUI extends SurveyQuestionGUI 
{

/**
* SurveyMultipleChoiceQuestionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyMultipleChoiceQuestionGUI object.
*
* @param integer $id The database id of a multiple choice question object
* @access public
*/
  function SurveyMultipleChoiceQuestionGUI(
		$id = -1
  )

  {
		$this->SurveyQuestionGUI();
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyMultipleChoiceQuestion.php";
		$this->object = new SurveyMultipleChoiceQuestion();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
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
			$this->object->setTitle($_POST["title"]);
			$this->object->setAuthor($_POST["author"]);
			$this->object->setDescription($_POST["description"]);
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$questiontext = $_POST["question"];
			$this->object->setQuestiontext($questiontext);
			$this->object->setObligatory(($_POST["obligatory"]) ? 1 : 0);
			$this->object->setOrientation($_POST["orientation"]);
			$this->object->use_other_answer = ($_POST['use_other_answer']) ? 1 : 0;
			$this->object->other_answer_label = ($this->object->use_other_answer) ? $_POST['other_answer_label'] : null;
			$this->object->use_min_answers = ($_POST['use_min_answers']) ? true : false;
			$this->object->nr_min_answers = ($_POST['nr_min_answers'] > 0) ? $_POST['nr_min_answers'] : null;
			$this->object->nr_max_answers = ($_POST['nr_max_answers'] > 0) ? $_POST['nr_max_answers'] : null;
			$this->object->label = $_POST['label'];

	    $this->object->categories->flushCategories();

			foreach ($_POST['answers']['answer'] as $key => $value) 
			{
				if (strlen($value)) $this->object->getCategories()->addCategory($value, $_POST['answers']['other'][$key], 0, null, $_POST['answers']['scale'][$key]);
			}
			if (strlen($_POST['answers']['neutral']))
			{
				$this->object->getCategories()->addCategory($_POST['answers']['neutral'], 0, 1, null, $_POST['answers_neutral_scale']);
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
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt($this->getQuestionType()));
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("multiplechoice");

		// title
		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setValue($this->object->getTitle());
		$title->setRequired(TRUE);
		$form->addItem($title);
		
		// label
		$label = new ilTextInputGUI($this->lng->txt("label"), "label");
		$label->setValue($this->object->label);
		$label->setInfo($this->lng->txt("label_info"));
		$label->setRequired(false);
		$form->addItem($label);

		// author
		$author = new ilTextInputGUI($this->lng->txt("author"), "author");
		$author->setValue($this->object->getAuthor());
		$author->setRequired(TRUE);
		$form->addItem($author);
		
		// description
		$description = new ilTextInputGUI($this->lng->txt("description"), "description");
		$description->setValue($this->object->getDescription());
		$description->setRequired(FALSE);
		$form->addItem($description);
		
		// questiontext
		$question = new ilTextAreaInputGUI($this->lng->txt("question"), "question");
		$question->setValue($this->object->prepareTextareaOutput($this->object->getQuestiontext()));
		$question->setRequired(TRUE);
		$question->setRows(10);
		$question->setCols(80);
		$question->setUseRte(TRUE);
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$question->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("survey"));
		$question->addPlugin("latex");
		$question->addButton("latex");
		$question->addButton("pastelatex");
		$question->removePlugin("ibrowser");
		$question->setRTESupport($this->object->getId(), "spl", "survey");
		$form->addItem($question);
		
		// obligatory
		$shuffle = new ilCheckboxInputGUI($this->lng->txt("obligatory"), "obligatory");
		$shuffle->setValue(1);
		$shuffle->setChecked($this->object->getObligatory());
		$shuffle->setRequired(FALSE);
		$form->addItem($shuffle);

		// orientation
		$orientation = new ilRadioGroupInputGUI($this->lng->txt("orientation"), "orientation");
		$orientation->setRequired(false);
		$orientation->setValue($this->object->getOrientation());
		$orientation->addOption(new ilRadioOption($this->lng->txt('vertical'), 0));
		$orientation->addOption(new ilRadioOption($this->lng->txt('horizontal'), 1));
		$form->addItem($orientation);

		// minimum answers
		$minanswers = new ilCheckboxInputGUI($this->lng->txt("use_min_answers"), "use_min_answers");
		$minanswers->setValue(1);
		$minanswers->setOptionTitle($this->lng->txt("use_min_answers_option"));
		$minanswers->setChecked($this->object->use_min_answers);
		$minanswers->setRequired(FALSE);
		$nranswers = new ilNumberInputGUI($this->lng->txt("nr_min_answers"), "nr_min_answers");
		$nranswers->setSize(5);
		$nranswers->setDecimals(0);
		$nranswers->setRequired(false);
		$nranswers->setMinValue(1);
		$nranswers->setValue($this->object->nr_min_answers);
		$minanswers->addSubItem($nranswers);
		$nrmaxanswers = new ilNumberInputGUI($this->lng->txt("nr_max_answers"), "nr_max_answers");
		$nrmaxanswers->setSize(5);
		$nrmaxanswers->setDecimals(0);
		$nrmaxanswers->setRequired(false);
		$nrmaxanswers->setMinValue(1);
		$nrmaxanswers->setValue($this->object->nr_max_answers);
		$minanswers->addSubItem($nrmaxanswers);
		$form->addItem($minanswers);

		// Answers
		include_once "./Modules/SurveyQuestionPool/classes/class.ilCategoryWizardInputGUI.php";
		$answers = new ilCategoryWizardInputGUI($this->lng->txt("answers"), "answers");
		$answers->setRequired(false);
		$answers->setAllowMove(true);
		$answers->setShowWizard(false);
		$answers->setShowSavePhrase(false);
		$answers->setUseOtherAnswer(true);
		$answers->setShowNeutralCategory(true);
		$answers->setNeutralCategoryTitle($this->lng->txt('matrix_neutral_answer'));
		if (!$this->object->getCategories()->getCategoryCount())
		{
			$this->object->getCategories()->addCategory("");
		}
		$answers->setValues($this->object->getCategories());
		$answers->setDisabledScale(false);
		$form->addItem($answers);

		$form->addCommandButton("saveReturn", $this->lng->txt("save_return"));
		$form->addCommandButton("save", $this->lng->txt("save"));
	
		$errors = false;

		if ($this->isSaveCommand())
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			$form->setValuesByPost(); // again, because checkInput now performs the whole stripSlashes handling and we need this if we don't want to have duplication of backslashes
			if ($nranswers->getValue() > $answers->getCategoryCount())
			{
				$nrmaxanswers->setAlert($this->lng->txt('err_minvalueganswers'));
				if (!$errors)
				{
					ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
				}
				$errors = true;
			}
			if ($nrmaxanswers->getValue() > 0 && ($nrmaxanswers->getValue() > $answers->getCategoryCount() || $nrmaxanswers->getValue() < $nranswers->getValue()))
			{
				$nrmaxanswers->setAlert($this->lng->txt('err_maxvaluegeminvalue'));
				if (!$errors)
				{
					ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
				}
				$errors = true;
			}
			if ($errors) $checkonly = false;
		}

		if (!$checkonly) $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		return $errors;
	}

	/**
	* Add a new answer
	*/
	public function addanswers()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['addanswers']);
		$this->object->getCategories()->addCategoryAtPosition("", $position+1);
		$this->editQuestion();
	}

	/**
	* Remove an answer
	*/
	public function removeanswers()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['removeanswers']);
		$this->object->getCategories()->removeCategory($position);
		$this->editQuestion();
	}

	/**
	* Move an answer up
	*/
	public function upanswers()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['upanswers']);
		$this->object->getCategories()->moveCategoryUp($position);
		$this->editQuestion();
	}

	/**
	* Move an answer down
	*/
	public function downanswers()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['downanswers']);
		$this->object->getCategories()->moveCategoryDown($position);
		$this->editQuestion();
	}

/**
* Creates the question output form for the learner
*
* @access public
*/
	function getWorkingForm($working_data = "", $question_title = 1, $show_questiontext = 1, $error_message = "", $survey_id = null)
	{
		$template = new ilTemplate("tpl.il_svy_out_mc.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		$template->setCurrentBlock("material");
		$template->setVariable("TEXT_MATERIAL", $this->getMaterialOutput());
		$template->parseCurrentBlock();
		switch ($this->object->getOrientation())
		{
			case 0:
				// vertical orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$cat = $this->object->categories->getCategory($i);
					if ($cat->other)
					{
						$template->setCurrentBlock("other_row");
						if (strlen($cat->title))
						{
							$template->setVariable("OTHER_LABEL", $cat->title);
						}
						$template->setVariable("VALUE_MC", ($cat->scale) ? ($cat->scale - 1) : $i);
						$template->setVariable("QUESTION_ID", $this->object->getId());
						if (is_array($working_data))
						{
							foreach ($working_data as $value)
							{
								if (strlen($value["value"]))
								{
									if ($value["value"] == $cat->scale-1)
									{
										$template->setVariable("OTHER_VALUE", ' value="' . ilUtil::prepareFormOutput($value['textanswer']) . '"');
										if (!$value['uncheck'])
										{
											$template->setVariable("CHECKED_MC", " checked=\"checked\"");
										}
									}
								}
							}
						}
						$template->parseCurrentBlock();
					}
					else
					{
						$template->setCurrentBlock("mc_row");
						if ($cat->neutral) $template->setVariable('ROWCLASS', ' class="neutral"');
						$template->setVariable("TEXT_MC", ilUtil::prepareFormOutput($cat->title));
						$template->setVariable("VALUE_MC", ($cat->scale) ? ($cat->scale - 1) : $i);
						$template->setVariable("QUESTION_ID", $this->object->getId());
						if (is_array($working_data))
						{
							foreach ($working_data as $value)
							{
								if (strlen($value["value"]))
								{
									if ($value["value"] == $cat->scale-1)
									{
										if (!$value['uncheck'])
										{
											$template->setVariable("CHECKED_MC", " checked=\"checked\"");
										}
									}
								}
							}
						}
						$template->parseCurrentBlock();
					}
					$template->touchBlock('outer_row');
				}
				break;
			case 1:
				// horizontal orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$cat = $this->object->categories->getCategory($i);
					$template->setCurrentBlock("checkbox_col");
					if ($cat->neutral) $template->setVariable('COLCLASS', ' neutral');
					$template->setVariable("VALUE_MC", ($cat->scale) ? ($cat->scale - 1) : $i);
					$template->setVariable("QUESTION_ID", $this->object->getId());
					if (is_array($working_data))
					{
						foreach ($working_data as $value)
						{
							if (strlen($value["value"]))
							{
								if ($value["value"] == $cat->scale-1)
								{
									if (!$value['uncheck'])
									{
										$template->setVariable("CHECKED_MC", " checked=\"checked\"");
									}
								}
							}
						}
					}
					$template->parseCurrentBlock();
				}
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$cat = $this->object->categories->getCategory($i);
					if ($cat->other)
					{
						$template->setCurrentBlock("text_other_col");
						$template->setVariable("VALUE_MC", ($cat->scale) ? ($cat->scale - 1) : $i);
						$template->setVariable("QUESTION_ID", $this->object->getId());
						if (strlen($cat->title))
						{
							$template->setVariable("OTHER_LABEL", $cat->title);
						}
						if (is_array($working_data))
						{
							foreach ($working_data as $value)
							{
								if (strlen($value["value"]))
								{
									if ($value["value"] == $cat->scale-1)
									{
										$template->setVariable("OTHER_VALUE", ' value="' . ilUtil::prepareFormOutput($value['textanswer']) . '"');
									}
								}
							}
						}
						$template->parseCurrentBlock();
					}
					else
					{
						$category = $this->object->categories->getCategory($i);
						$template->setCurrentBlock("text_col");
						if ($cat->neutral) $template->setVariable('COLCLASS', ' neutral');
						$template->setVariable("VALUE_MC", ($cat->scale) ? ($cat->scale - 1) : $i);
						$template->setVariable("TEXT_MC", ilUtil::prepareFormOutput($cat->title));
						$template->setVariable("QUESTION_ID", $this->object->getId());
						$template->parseCurrentBlock();
					}
					$template->touchBlock('text_outer_col');
				}
				break;
		}
		
		$template->setCurrentBlock("question_data");
		if ($this->object->use_min_answers)
		{
			$template->setCurrentBlock('min_max_msg');
			if ($this->object->nr_min_answers > 0 && $this->object->nr_max_answers > 0)
			{
				if ($this->object->nr_min_answers == $this->object->nr_max_answers)
				{
					$template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_min_max_exact_answers'), $this->object->nr_min_answers));
				}
				else
				{
					$template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_min_max_nr_answers'), $this->object->nr_min_answers, $this->object->nr_max_answers));
				}
			}
			else if ($this->object->nr_min_answers > 0)
			{
				$template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_min_nr_answers'), $this->object->nr_min_answers));
			}
			else if ($this->object->nr_max_answers > 0)
			{
				$template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_max_nr_answers'), $this->object->nr_max_answers));
			}
			$template->parseCurrentBlock();
		}
		if (strcmp($error_message, "") != 0)
		{
			$template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
		}
		if ($show_questiontext)
		{
			$this->outQuestionText($template);
		}
		if ($question_title)
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$template->parseCurrentBlock();
		return $template->get();
	}

	/**
	* Creates a HTML representation of the question
	*
	* @access private
	*/
	function getPrintView($question_title = 1, $show_questiontext = 1, $survey_id = null)
	{
		$template = new ilTemplate("tpl.il_svy_qpl_mc_printview.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		switch ($this->object->getOrientation())
		{
			case 0:
				// vertical orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$cat = $this->object->categories->getCategory($i);
					if ($cat->other)
					{
						$template->setCurrentBlock("other_row");
						$template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.gif")));
						$template->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
						$template->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
						$template->setVariable("OTHER_LABEL", ilUtil::prepareFormOutput($cat->title));
						$template->setVariable("OTHER_ANSWER", "&nbsp;");
						$template->parseCurrentBlock();
					}
					else
					{
						$template->setCurrentBlock("mc_row");
						$template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.gif")));
						$template->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
						$template->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
						$template->setVariable("TEXT_MC", ilUtil::prepareFormOutput($cat->title));
						$template->parseCurrentBlock();
					}
				}
				break;
			case 1:
				// horizontal orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$template->setCurrentBlock("checkbox_col");
					$template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.gif")));
					$template->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
					$template->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
					$template->parseCurrentBlock();
				}
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$cat = $this->object->categories->getCategory($i);
					if ($cat->other)
					{
						$template->setCurrentBlock("other_text_col");
						$template->setVariable("OTHER_LABEL", ilUtil::prepareFormOutput($this->object->other_answer_label));
						$template->setVariable("OTHER_ANSWER", "&nbsp;");
						$template->parseCurrentBlock();
					}
					else
					{
						$template->setCurrentBlock("text_col");
						$template->setVariable("TEXT_MC", ilUtil::prepareFormOutput($category));
						$template->parseCurrentBlock();
					}
				}
				break;
		}
		
		if ($this->object->use_min_answers)
		{
			$template->setCurrentBlock('min_max_msg');
			if ($this->object->nr_min_answers > 0 && $this->object->nr_max_answers > 0)
			{
				$template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_min_max_nr_answers'), $this->object->nr_min_answers, $this->object->nr_max_answers));
			}
			else if ($this->object->nr_min_answers > 0)
			{
				$template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_min_nr_answers'), $this->object->nr_min_answers));
			}
			else if ($this->object->nr_max_answers > 0)
			{
				$template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_max_nr_answers'), $this->object->nr_max_answers));
			}
			$template->parseCurrentBlock();
		}
		if ($show_questiontext)
		{
			$this->outQuestionText($template);
		}
		if ($question_title)
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$template->parseCurrentBlock();
		return $template->get();
	}
	
/**
* Creates a preview of the question
*
* @access private
*/
	function preview()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_preview.html", "Modules/SurveyQuestionPool");
		$question_output = $this->getWorkingForm();
		$this->tpl->setVariable("QUESTION_OUTPUT", $question_output);
	}

	function setQuestionTabs()
	{
		$this->setQuestionTabsForClass("surveymultiplechoicequestiongui");
	}

/**
* Creates the detailed output of the cumulated results for the question
*
* @param integer $survey_id The database ID of the survey
* @param integer $counter The counter of the question position in the survey
* @return string HTML text with the cumulated results
* @access private
*/
	function getCumulatedResultsDetails($survey_id, $counter)
	{
		if (count($this->cumulated) == 0)
		{
			include_once "./Modules/Survey/classes/class.ilObjSurvey.php";
			$nr_of_users = ilObjSurvey::_getNrOfParticipants($survey_id);
			$this->cumulated =& $this->object->getCumulatedResults($survey_id, $nr_of_users);
		}
		
		$output = "";
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_svy_svy_cumulated_results_detail.html", TRUE, TRUE, "Modules/Survey");

		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("question"));
		$questiontext = $this->object->getQuestiontext();
		$template->setVariable("TEXT_OPTION_VALUE", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("question_type"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->lng->txt($this->getQuestionType()));
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("users_answered"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["USERS_ANSWERED"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("users_skipped"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["USERS_SKIPPED"]);
		$template->parseCurrentBlock();
		/*
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("mode"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["MODE"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("mode_nr_of_selections"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["MODE_NR_OF_SELECTIONS"]);
		$template->parseCurrentBlock();
		*/
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("categories"));
		$categories = "";
		if (is_array($this->cumulated["variables"]))
		{
			foreach ($this->cumulated["variables"] as $key => $value)
			{
				$categories .= "<li>" . $value["title"] . ": n=" . $value["selected"] . 
					" (" . sprintf("%.2f", 100*$value["percentage"]) . "%)</li>";
			}
		}
		$categories = "<ol>$categories</ol>";
		$template->setVariable("TEXT_OPTION_VALUE", $categories);
		$template->parseCurrentBlock();
		
		
		// chart 
		$template->setCurrentBlock("detail_row");				
		$template->setVariable("TEXT_OPTION", $this->lng->txt("chart"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->renderChart("svy_ch_".$this->object->getId(), $this->cumulated["variables"]));
		$template->parseCurrentBlock();
		
		
		$template->setVariable("QUESTION_TITLE", "$counter. ".$this->object->getTitle());
		return $template->get();
	}

		
}
?>
