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
* Text survey question GUI representation
*
* The SurveyTextQuestionGUI class encapsulates the GUI representation
* for text survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestionGUI
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyTextQuestionGUI extends SurveyQuestionGUI 
{

/**
* SurveyTextQuestionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyTextQuestionGUI object.
*
* @param integer $id The database id of a text question object
* @access public
*/
  function SurveyTextQuestionGUI(
		$id = -1
  )

  {
		$this->SurveyQuestionGUI();
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyTextQuestion.php";
		$this->object = new SurveyTextQuestion();
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
			$this->object->label = $_POST['label'];
			$this->object->setAuthor($_POST["author"]);
			$this->object->setDescription($_POST["description"]);
			$questiontext = $_POST["question"];
			$this->object->setQuestiontext($questiontext);
			$this->object->setObligatory(($_POST["obligatory"]) ? 1 : 0);

			$this->object->setMaxChars((strlen($_POST["maxchars"])) ? $_POST["maxchars"] : null);
			$this->object->setTextWidth($_POST["textwidth"]);
			$this->object->setTextHeight($_POST["textheight"]);

			return 0;
		}
		else
		{
			return 1;
		}
	}
	
/**
* Creates an output of the edit form for the question
*/
	public function editQuestion() 
	{
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt($this->getQuestionType()));
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("essay");

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
		
		// maximum number of characters
		$maxchars = new ilNumberInputGUI($this->lng->txt("maxchars"), "maxchars");
		$maxchars->setRequired(false);
		$maxchars->setSize(5);
		if ($this->object->getMaxChars() > 0)
		{
			$maxchars->setValue($this->object->getMaxChars());
		}
		$maxchars->setDecimals(0);
		$form->addItem($maxchars);
		
		// textwidth
		$textwidth = new ilNumberInputGUI($this->lng->txt("width"), "textwidth");
		$textwidth->setRequired(true);
		$textwidth->setSize(3);
		$textwidth->setValue($this->object->getTextWidth());
		$textwidth->setDecimals(0);
		$textwidth->setMinValue(10);
		$form->addItem($textwidth);
		
		// textheight
		$textheight = new ilNumberInputGUI($this->lng->txt("height"), "textheight");
		$textheight->setRequired(true);
		$textheight->setSize(3);
		$textheight->setValue($this->object->getTextHeight());
		$textheight->setDecimals(0);
		$textheight->setMinValue(1);
		$form->addItem($textheight);
		
		// obligatory
		$shuffle = new ilCheckboxInputGUI($this->lng->txt("obligatory"), "obligatory");
		$shuffle->setValue(1);
		$shuffle->setChecked($this->object->getObligatory());
		$shuffle->setRequired(FALSE);
		$form->addItem($shuffle);

		$this->addCommandButtons($form);
		
		$errors = false;
	
		if ($this->isSaveCommand())
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			$form->setValuesByPost(); // again, because checkInput now performs the whole stripSlashes handling and we need this if we don't want to have duplication of backslashes
			if ($errors) $checkonly = false;
		}

		if (!$checkonly) $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		return $errors;
	}

/**
* Creates the question output form for the learner
*/
	public function getWorkingForm($working_data = "", $question_title = 1, $show_questiontext = 1, $error_message = "", $survey_id = null)
	{
		$template = new ilTemplate("tpl.il_svy_out_text.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		$template->setCurrentBlock("material_text");
		$template->setVariable("TEXT_MATERIAL", $this->getMaterialOutput());
		$template->parseCurrentBlock();

		if ($this->object->getTextHeight() == 1)
		{
			$template->setCurrentBlock("textinput");
			if (is_array($working_data))
			{
				if (strlen($working_data[0]["textanswer"]))
				{
					$template->setVariable("VALUE_ANSWER", " value=\"" . ilUtil::prepareFormOutput($working_data[0]["textanswer"]) . "\"");
				}
			}
			$template->setVariable("QUESTION_ID", $this->object->getId());
			$template->setVariable("WIDTH", $this->object->getTextWidth());
			if ($this->object->getMaxChars())
			{
				$template->setVariable("MAXLENGTH", " maxlength=\"" . $this->object->getMaxChars() . "\"");
			}
			$template->parseCurrentBlock();
		}
		else
		{
			$template->setCurrentBlock("textarea");
			if (is_array($working_data))
			{
				$template->setVariable("VALUE_ANSWER", ilUtil::prepareFormOutput($working_data[0]["textanswer"]));
			}
			$template->setVariable("QUESTION_ID", $this->object->getId());
			$template->setVariable("WIDTH", $this->object->getTextWidth());
			$template->setVariable("HEIGHT", $this->object->getTextHeight());
			$template->parseCurrentBlock();
		}
		$template->setCurrentBlock("question_data_text");
		if ($show_questiontext)
		{
			$this->outQuestionText($template);
		}
		if ($question_title)
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
		$template->setVariable("LABEL_QUESTION_ID", $this->object->getId());
		if (strcmp($error_message, "") != 0)
		{
			$template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
		}
		if ($this->object->getMaxChars())
		{
			$template->setVariable("TEXT_MAXCHARS", sprintf($this->lng->txt("text_maximum_chars_allowed"), $this->object->getMaxChars()));
		}
		$template->parseCurrentBlock();
		return $template->get();
	}

	/**
	* Creates a HTML representation of the question
	*/
	public function getPrintView($question_title = 1, $show_questiontext = 1, $survey_id = null)
	{
		$template = new ilTemplate("tpl.il_svy_qpl_text_printview.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		if ($show_questiontext)
		{
			$this->outQuestionText($template);
		}
		if ($question_title)
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
		$template->setVariable("TEXTBOX_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("textbox.png")));
		$template->setVariable("TEXTBOX", $this->lng->txt("textbox"));
		$template->setVariable("TEXTBOX_WIDTH", $this->object->getTextWidth()*16);
		$template->setVariable("TEXTBOX_HEIGHT", $this->object->getTextHeight()*16);
		$template->setVariable("QUESTION_ID", $this->object->getId());
		if ($this->object->getMaxChars())
		{
			$template->setVariable("TEXT_MAXCHARS", sprintf($this->lng->txt("text_maximum_chars_allowed"), $this->object->getMaxChars()));
		}
		return $template->get();
	}
	
/**
* Creates a preview of the question
*
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
		$this->setQuestionTabsForClass("surveytextquestiongui");
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
		
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("given_answers"));
		$textvalues = "";
		if (is_array($this->cumulated["textvalues"]))
		{
			foreach ($this->cumulated["textvalues"] as $textvalue)
			{
				$textvalues .= "<li>" . preg_replace("/\n/", "<br>", $textvalue) . "</li>";
			}
		}
		$textvalues = "<ul>$textvalues</ul>";
		$template->setVariable("TEXT_OPTION_VALUE", $textvalues);
		$template->parseCurrentBlock();

		$template->setVariable("QUESTION_TITLE", "$counter. ".$this->object->getTitle());
		return $template->get();
	}
}
?>
