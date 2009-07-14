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
			$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
			$this->object->setDescription(ilUtil::stripSlashes($_POST["description"]));
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$questiontext = ilUtil::stripSlashes($_POST["question"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("survey"));
			$this->object->setQuestiontext($questiontext);
			$this->object->setObligatory(($_POST["obligatory"]) ? 1 : 0);
			$this->object->setOrientation($_POST["orientation"]);

	    $this->object->categories->flushCategories();

			foreach ($_POST['answers']['answer'] as $key => $value) 
			{
				$this->object->getCategories()->addCategory(ilUtil::stripSlashes($value));
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
		$question->addPlugin("latex");
		$question->removePlugin("ibrowser");
		$question->addButton("latex");
		$question->addButton("pastelatex");
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

		// Answers
		include_once "./Modules/SurveyQuestionPool/classes/class.ilCategoryWizardInputGUI.php";
		$answers = new ilCategoryWizardInputGUI($this->lng->txt("answers"), "answers");
		$answers->setRequired(true);
		$answers->setAllowMove(true);
		if (!$this->object->getCategories()->getCategoryCount())
		{
			$this->object->getCategories()->addCategory("");
		}
		$answers->setValues($this->object->getCategories());
		$form->addItem($answers);

		$form->addCommandButton("save", $this->lng->txt("save"));
	
		$errors = false;
	
		if ($save)
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
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
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
					$category = $this->object->categories->getCategory($i);
					$template->setCurrentBlock("mc_row");
					$template->setVariable("TEXT_MC", ilUtil::prepareFormOutput($category));
					$template->setVariable("VALUE_MC", $i);
					$template->setVariable("QUESTION_ID", $this->object->getId());
					if (is_array($working_data))
					{
						foreach ($working_data as $value)
						{
							if (strlen($value["value"]))
							{
								if ($value["value"] == $i)
								{
									$template->setVariable("CHECKED_MC", " checked=\"checked\"");
								}
							}
						}
					}
					$template->parseCurrentBlock();
				}
				break;
			case 1:
				// horizontal orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$category = $this->object->categories->getCategory($i);
					$template->setCurrentBlock("checkbox_col");
					$template->setVariable("VALUE_MC", $i);
					$template->setVariable("QUESTION_ID", $this->object->getId());
					if (is_array($working_data))
					{
						foreach ($working_data as $value)
						{
							if (strlen($value["value"]))
							{
								if ($value["value"] == $i)
								{
									$template->setVariable("CHECKED_MC", " checked=\"checked\"");
								}
							}
						}
					}
					$template->parseCurrentBlock();
				}
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$category = $this->object->categories->getCategory($i);
					$template->setCurrentBlock("text_col");
					$template->setVariable("VALUE_MC", $i);
					$template->setVariable("TEXT_MC", ilUtil::prepareFormOutput($category));
					$template->setVariable("QUESTION_ID", $this->object->getId());
					$template->parseCurrentBlock();
				}
				break;
		}
		
		$template->setCurrentBlock("question_data");
		if (strcmp($error_message, "") != 0)
		{
			$template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
		}
		if ($show_questiontext)
		{
			$questiontext = $this->object->getQuestiontext();
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		}
		if (!$this->object->getObligatory($survey_id))
		{
			$template->setVariable("OBLIGATORY_TEXT", $this->lng->txt("survey_question_optional"));
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
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
					$category = $this->object->categories->getCategory($i);
					$template->setCurrentBlock("mc_row");
					$template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.gif")));
					$template->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
					$template->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
					$template->setVariable("TEXT_MC", ilUtil::prepareFormOutput($category));
					$template->parseCurrentBlock();
				}
				break;
			case 1:
				// horizontal orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$category = $this->object->categories->getCategory($i);
					$template->setCurrentBlock("checkbox_col");
					$template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.gif")));
					$template->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
					$template->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
					$template->parseCurrentBlock();
				}
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) 
				{
					$category = $this->object->categories->getCategory($i);
					$template->setCurrentBlock("text_col");
					$template->setVariable("TEXT_MC", ilUtil::prepareFormOutput($category));
					$template->parseCurrentBlock();
				}
				break;
		}
		
		if ($show_questiontext)
		{
			$questiontext = $this->object->getQuestiontext();
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		}
		if (! $this->object->getObligatory($survey_id))
		{
			$template->setVariable("OBLIGATORY_TEXT", $this->lng->txt("survey_question_optional"));
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
* Creates a the cumulated results row for the question
*
* Creates a the cumulated results row for the question
*
* @return string HTML text with the cumulated results
* @access private
*/
	function getCumulatedResultRow($counter, $css_class, $survey_id)
	{
		include_once "./classes/class.ilTemplate.php";
		if (count($this->cumulated) == 0)
		{
			include_once "./Modules/Survey/classes/class.ilObjSurvey.php";
			$nr_of_users = ilObjSurvey::_getNrOfParticipants($survey_id);
			$this->cumulated =& $this->object->getCumulatedResults($survey_id, $nr_of_users);
		}
		$template = new ilTemplate("tpl.il_svy_svy_cumulated_results_row.html", TRUE, TRUE, "Modules/Survey");
		$template->setVariable("QUESTION_TITLE", ($counter+1) . ". ".$this->object->getTitle());
		$maxlen = 37;
		$questiontext = preg_replace("/\<[^>]+?>/ims", "", $this->object->getQuestiontext());
		if (strlen($questiontext) > $maxlen + 3)
		{
			$questiontext = substr($questiontext, 0, $maxlen) . "...";
		}
		$template->setVariable("QUESTION_TEXT", $questiontext);
		$template->setVariable("USERS_ANSWERED", $this->cumulated["USERS_ANSWERED"]);
		$template->setVariable("USERS_SKIPPED", $this->cumulated["USERS_SKIPPED"]);
		$template->setVariable("QUESTION_TYPE", $this->lng->txt($this->cumulated["QUESTION_TYPE"]));
		$template->setVariable("MODE", $this->cumulated["MODE"]);
		$template->setVariable("MODE_NR_OF_SELECTIONS", $this->cumulated["MODE_NR_OF_SELECTIONS"]);
		$template->setVariable("MEDIAN", $this->cumulated["MEDIAN"]);
		$template->setVariable("ARITHMETIC_MEAN", $this->cumulated["ARITHMETIC_MEAN"]);
		$template->setVariable("COLOR_CLASS", $css_class);
		return $template->get();
	}

/**
* Creates the detailed output of the cumulated results for the question
*
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
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("mode"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["MODE"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("mode_nr_of_selections"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["MODE_NR_OF_SELECTIONS"]);
		$template->parseCurrentBlock();
		
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("categories"));
		$categories = "";
		if (is_array($this->cumulated["variables"]))
		{
			foreach ($this->cumulated["variables"] as $key => $value)
			{
				$categories .= "<li>" . $this->lng->txt("title") . ":" . "<span class=\"bold\">" . $value["title"] . "</span><br />" .
					$this->lng->txt("category_nr_selected") . ": " . "<span class=\"bold\">" . $value["selected"] . "</span><br />" .
					$this->lng->txt("percentage_of_selections") . ": " . "<span class=\"bold\">" . sprintf("%.2f", 100*$value["percentage"]) . "</span></li>";
			}
		}
		$categories = "<ol>$categories</ol>";
		$template->setVariable("TEXT_OPTION_VALUE", $categories);
		$template->parseCurrentBlock();
		
		// display chart for multiple choice question for array $eval["variables"]
		$template->setCurrentBlock("chart");
		$template->setVariable("TEXT_CHART", $this->lng->txt("chart"));
		$template->setVariable("ALT_CHART", $data["title"] . "( " . $this->lng->txt("chart") . ")");

		$charturl = "";
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$surveySetting = new ilSetting("survey");
		if ($surveySetting->get("googlechart") == 1)
		{
			$chartcolors = array("2A4BD7", "9DAFFF", "1D6914", "81C57A", "814A19", "E9DEBB", "8126C0", "AD2323", "29D0D0", "FFEE33", "FF9233", "FFCDF3", "A0A0A0", "575757", "000000");
			$selections = array();
			$values = array();
			$maxselection = 0;
			foreach ($this->cumulated["variables"] as $val)
			{
				if ($val["selected"] > $maxselection) $maxselection = $val["selected"];
				array_push($selections, $val["selected"]);
				array_push($values, str_replace(" ", "+", $val["title"]));
			}
			$chartwidth = 800;
			if ($maxselection % 2 == 0)
			{
				$selectionlabels = "0|" . ($maxselection / 2) . "|$maxselection";
			}
			else
			{
				$selectionlabels = "0|$maxselection";
			}
			$charturl = "http://chart.apis.google.com/chart?chco=" . implode("|", array_slice($chartcolors, 0, count($values))). "&cht=bvs&chs=" . $chartwidth . "x250&chd=t:" . implode(",", $selections) . "&chds=0,$maxselection&chxt=y,y&chxl=0:|".$selectionlabels."|1:||".str_replace(" ", "+", $this->lng->txt("mode_nr_of_selections"))."|" . "&chxr=1,0,$maxselection&chtt=" . str_replace(" ", "+", $this->object->getTitle()) . "&chbh=20," . round($chartwidth/(count($values)+1.5)) . "&chdl=" . implode("|", $values) . "&chdlp=b";
		}
		else
		{
			$this->ctrl->setParameterByClass("ilsurveyevaluationgui", "survey", $survey_id);
			$this->ctrl->setParameterByClass("ilsurveyevaluationgui", "question", $this->object->getId());
			$charturl = $this->ctrl->getLinkTargetByClass("ilsurveyevaluationgui", "outChart");
		}
		$template->setVariable("CHART", $charturl);
		$template->parseCurrentBlock();
		
		$template->setVariable("QUESTION_TITLE", "$counter. ".$this->object->getTitle());
		return $template->get();
	}

		
}
?>
