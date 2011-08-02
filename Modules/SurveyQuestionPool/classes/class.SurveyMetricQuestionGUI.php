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
* Metric survey question GUI representation
*
* The SurveyMetricQuestionGUI class encapsulates the GUI representation
* for metric survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestionGUI
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyMetricQuestionGUI extends SurveyQuestionGUI 
{
/**
* SurveyMetricQuestionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyMetricQuestionGUI object.
*
* @param integer $id The database id of a metric question object
* @access public
*/
  function SurveyMetricQuestionGUI(
		$id = -1
  )

  {
		$this->SurveyQuestionGUI();
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyMetricQuestion.php";
		$this->object = new SurveyMetricQuestion();
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
			$questiontext = $_POST["question"];
			$this->object->setQuestiontext($questiontext);
			$this->object->setObligatory(($_POST["obligatory"]) ? 1 : 0);
			$this->object->setOrientation($_POST["orientation"]);
			$this->object->label = $_POST['label'];

			$this->object->setSubtype($_POST["type"]);
			$this->object->setMinimum($_POST["minimum"]);
			$this->object->setMaximum($_POST["maximum"]);
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
		
		// subtype
		$subtype = new ilRadioGroupInputGUI($this->lng->txt("subtype"), "type");
		$subtype->setRequired(true);
		$subtype->setValue($this->object->getSubtype());
		$subtype->addOption(new ilRadioOption($this->lng->txt('non_ratio'), 3, $this->lng->txt("metric_subtype_description_interval")));
		$subtype->addOption(new ilRadioOption($this->lng->txt('ratio_non_absolute'), 4, $this->lng->txt("metric_subtype_description_rationonabsolute")));
		$subtype->addOption(new ilRadioOption($this->lng->txt('ratio_absolute'), 5, $this->lng->txt("metric_subtype_description_ratioabsolute")));
		$form->addItem($subtype);

		// minimum value
		$minimum = new ilNumberInputGUI($this->lng->txt("minimum"), "minimum");
		$minimum->setValue($this->object->getMinimum());
		$minimum->setRequired(false);
		$minimum->setSize(6);
		if ($this->object->getSubtype() > 3)
		{
			$minimum->setMinValue(0);
		}
		if ($this->object->getSubtype() == 5)
		{
			$minimum->setDecimals(0);
		}
		$form->addItem($minimum);
		
		// maximum value
		$maximum = new ilNumberInputGUI($this->lng->txt("maximum"), "maximum");
		if ($this->object->getSubtype() == 5)
		{
			$maximum->setDecimals(0);
		}
		$maximum->setValue($this->object->getMaximum());
		$maximum->setRequired(false);
		$maximum->setSize(6);
		$form->addItem($maximum);
		
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
	* Creates a HTML representation of the question
	*
	* Creates a HTML representation of the question
	*
	* @access private
	*/
	function getPrintView($question_title = 1, $show_questiontext = 1, $survey_id = null)
	{
		$template = new ilTemplate("tpl.il_svy_qpl_metric_printview.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		$template->setVariable("MIN_MAX", $this->object->getMinMaxText());

		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		if ($show_questiontext)
		{
			$this->outQuestionText($template);
		}
		if ($question_title)
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
		$template->setVariable("QUESTION_ID", $this->object->getId());

		$solution_text = "";
		$len = 10;
		for ($i = 0; $i < 10; $i++) $solution_text .= "&#160;";
		$template->setVariable("TEXT_SOLUTION", $solution_text);

		$template->parseCurrentBlock();
		return $template->get();
	}
	
/**
* Creates the question output form for the learner
*
* Creates the question output form for the learner
*
* @access public
*/
	function getWorkingForm($working_data = "", $question_title = 1, $show_questiontext = 1, $error_message = "", $survey_id = null)
	{
		$template = new ilTemplate("tpl.il_svy_out_metric.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		$template->setCurrentBlock("material_metric");
		$template->setVariable("TEXT_MATERIAL", $this->getMaterialOutput());
		$template->parseCurrentBlock();
		$template->setVariable("MIN_MAX", $this->object->getMinMaxText());
		/*if (strlen($this->object->getMinimum()))
		{
			$template->setCurrentBlock("minimum");
			$template->setVariable("TEXT_MINIMUM", $this->lng->txt("minimum"));
			$template->setVariable("VALUE_MINIMUM", $this->object->getMinimum());
			$template->parseCurrentBlock();
		}
		if (strlen($this->object->getMaximum()))
		{
			$template->setCurrentBlock("maximum");
			$template->setVariable("TEXT_MAXIMUM", $this->lng->txt("maximum"));
			$template->setVariable("VALUE_MAXIMUM", $this->object->getMaximum());
			$template->parseCurrentBlock();
		}*/

		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		if ($show_questiontext)
		{
			$this->outQuestionText($template);
		}
		if ($question_title)
		{
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
		$template->setVariable("QUESTION_ID", $this->object->getId());
		if (is_array($working_data))
		{
			$template->setVariable("VALUE_METRIC", $working_data[0]["value"]);
		}

		$template->setVariable("INPUT_SIZE", 10);

		if (strcmp($error_message, "") != 0)
		{
			$template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
		}
		$template->parseCurrentBlock();
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
		$this->setQuestionTabsForClass("surveymetricquestiongui");
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
		$template->setVariable("TEXT_OPTION", $this->lng->txt("subtype"));
		switch ($this->object->getSubType())
		{
			case SUBTYPE_NON_RATIO:
				$template->setVariable("TEXT_OPTION_VALUE", $this->lng->txt("non_ratio"));
				break;
			case SUBTYPE_RATIO_NON_ABSOLUTE:
				$template->setVariable("TEXT_OPTION_VALUE", $this->lng->txt("ratio_non_absolute"));
				break;
			case SUBTYPE_RATIO_ABSOLUTE:
				$template->setVariable("TEXT_OPTION_VALUE", $this->lng->txt("ratio_absolute"));
				break;
		}
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
		$template->setVariable("TEXT_OPTION", $this->lng->txt("median"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["MEDIAN"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("arithmetic_mean"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["ARITHMETIC_MEAN"]);
		$template->parseCurrentBlock();

		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("values"));
		$values = "";
		if (is_array($this->cumulated["values"]))
		{
			foreach ($this->cumulated["values"] as $key => $value)
			{
				$values .= "<li>" . $value["value"] . ": n=" .  $value["selected"] . 
					" (" . sprintf("%.2f", 100*$value["percentage"]) . "%)</li>";
			}
		}
		$values = "<ol>$values</ol>";
		$template->setVariable("TEXT_OPTION_VALUE", $values);
		$template->parseCurrentBlock();
	
		
		// chart 
		$template->setCurrentBlock("detail_row");				
		$template->setVariable("TEXT_OPTION", $this->lng->txt("chart"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->renderChart("svy_ch_".$this->object->getId(), $this->cumulated["values"]));
		$template->parseCurrentBlock();
		
		
		$template->setVariable("QUESTION_TITLE", "$counter. ".$this->object->getTitle());
		return $template->get();
	}
	
	protected function renderChart($a_id, $a_values)
	{
		include_once "Services/Chart/classes/class.ilChart.php";
		$chart = new ilChart($a_id, 700, 400);

		$legend = new ilChartLegend();
		$chart->setLegend($legend);	

		$data = new ilChartData("bars");
		$data->setLabel($this->lng->txt("users_answered"));
		$data->setBarOptions(0.1, "center");
		
		$labels = array();
		foreach($a_values as $idx => $answer)
		{			
			$data->addPoint($answer["value"], $answer["selected"]);		
			$labels[$answer["value"]] = $answer["value"];
		}
		$chart->addData($data);
		
		$chart->setTicks($labels, false, true);

		return "<div style=\"margin:10px\">".$chart->getHTML()."</div>";				
	}
}
?>
