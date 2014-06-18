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
  	protected function initObject()
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyMetricQuestion.php";
		$this->object = new SurveyMetricQuestion();		
	}

	
	// 
	// EDITOR
	//
	
	public function setQuestionTabs()
	{
		$this->setQuestionTabsForClass("surveymetricquestiongui");
	}
	
	protected function addFieldsToEditForm(ilPropertyFormGUI $a_form)
	{		
		// subtype
		$subtype = new ilRadioGroupInputGUI($this->lng->txt("subtype"), "type");
		$subtype->setRequired(true);		
		$a_form->addItem($subtype);
				
		// #10652
		$opt = new ilRadioOption($this->lng->txt('non_ratio'), SurveyMetricQuestion::SUBTYPE_NON_RATIO, $this->lng->txt("metric_subtype_description_interval"));
		$subtype->addOption($opt);
		
		// minimum value
		$minimum1 = new ilNumberInputGUI($this->lng->txt("minimum"), "minimum3");		
		$minimum1->setRequired(false);
		$minimum1->setSize(6);		
		$opt->addSubItem($minimum1);
		
		// maximum value
		$maximum1 = new ilNumberInputGUI($this->lng->txt("maximum"), "maximum3");			
		$maximum1->setRequired(false);
		$maximum1->setSize(6);
		$opt->addSubItem($maximum1);
		
		$opt = new ilRadioOption($this->lng->txt('ratio_non_absolute'), SurveyMetricQuestion::SUBTYPE_RATIO_NON_ABSOLUTE, $this->lng->txt("metric_subtype_description_rationonabsolute"));
		$subtype->addOption($opt);
		
		// minimum value
		$minimum2 = new ilNumberInputGUI($this->lng->txt("minimum"), "minimum4");		
		$minimum2->setRequired(false);
		$minimum2->setSize(6);	
		$minimum2->setMinValue(0);		
		$opt->addSubItem($minimum2);
		
		// maximum value
		$maximum2 = new ilNumberInputGUI($this->lng->txt("maximum"), "maximum4");		
		$maximum2->setRequired(false);
		$maximum2->setSize(6);
		$opt->addSubItem($maximum2);
		
		$opt = new ilRadioOption($this->lng->txt('ratio_absolute'), SurveyMetricQuestion::SUBTYPE_RATIO_ABSOLUTE, $this->lng->txt("metric_subtype_description_ratioabsolute"));
		$subtype->addOption($opt);	
		
		// minimum value
		$minimum3 = new ilNumberInputGUI($this->lng->txt("minimum"), "minimum5");		
		$minimum3->setRequired(false);
		$minimum3->setSize(6);		
		$minimum3->setMinValue(0);		
		$minimum3->setDecimals(0);		
		$opt->addSubItem($minimum3);
		
		// maximum value
		$maximum3 = new ilNumberInputGUI($this->lng->txt("maximum"), "maximum5");
		$maximum3->setDecimals(0);		
		$maximum3->setRequired(false);
		$maximum3->setSize(6);
		$opt->addSubItem($maximum3);		
		
		
		// values
		$subtype->setValue($this->object->getSubtype());
		
		switch($this->object->getSubtype())
		{
			case SurveyMetricQuestion::SUBTYPE_NON_RATIO:
				$minimum1->setValue($this->object->getMinimum());
				$maximum1->setValue($this->object->getMaximum());
				break;
			
			case SurveyMetricQuestion::SUBTYPE_RATIO_NON_ABSOLUTE:
				$minimum2->setValue($this->object->getMinimum());
				$maximum2->setValue($this->object->getMaximum());
				break;
			
			case SurveyMetricQuestion::SUBTYPE_RATIO_ABSOLUTE:
				$minimum3->setValue($this->object->getMinimum());
				$maximum3->setValue($this->object->getMaximum());
				break;
		}		
	}
	
	protected function importEditFormValues(ilPropertyFormGUI $a_form)
	{
		$type = (int)$a_form->getInput("type");
		$this->object->setOrientation($a_form->getInput("orientation"));
		$this->object->setSubtype($type);
		$this->object->setMinimum($a_form->getInput("minimum".$type));
		$this->object->setMaximum($a_form->getInput("maximum".$type));
	}	
	
	public function getParsedAnswers(array $a_working_data = null, $a_only_user_anwers = false)
	{
		$res = array();
		
		if(is_array($a_working_data))
		{			
			$res[] = array("value" => $a_working_data[0]["value"]);			
		}	
		
		return $res;
	}
	
	/**
	* Creates a HTML representation of the question
	*
	* Creates a HTML representation of the question
	*
	* @access private
	*/
	function getPrintView($question_title = 1, $show_questiontext = 1, $survey_id = null, array $a_working_data = null)
	{		
		$user_answer = null;
		if($a_working_data)
		{
			$user_answer = $this->getParsedAnswers($a_working_data);
			$user_answer = $user_answer[0]["value"];
		}
				
		$template = new ilTemplate("tpl.il_svy_qpl_metric_printview.html", TRUE, TRUE, "Modules/SurveyQuestionPool");
		$template->setVariable("MIN_MAX", $this->object->getMinMaxText());

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

		if(!is_array($a_working_data) || !trim($user_answer))
		{			
			$solution_text = "";
			$len = 10;
			for ($i = 0; $i < 10; $i++) $solution_text .= "&#160;";
		}	
		else
		{
			$solution_text = $user_answer;
		}		
		$template->setVariable("TEXT_SOLUTION", $solution_text);

		$template->parseCurrentBlock();
		return $template->get();
	}
	
	
	//
	// EXECUTION
	//

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

	
	// 
	// EVALUATION
	//

	/**
	* Creates the detailed output of the cumulated results for the question
	*
	* @param integer $survey_id The database ID of the survey
	* @param integer $counter The counter of the question position in the survey
	* @return string HTML text with the cumulated results
	* @access private
	*/
	function getCumulatedResultsDetails($survey_id, $counter, $finished_ids)
	{
		if (count($this->cumulated) == 0)
		{
			if(!$finished_ids)
			{
				include_once "./Modules/Survey/classes/class.ilObjSurvey.php";			
				$nr_of_users = ilObjSurvey::_getNrOfParticipants($survey_id);
			}
			else
			{
				$nr_of_users = sizeof($finished_ids);
			}
			$this->cumulated =& $this->object->getCumulatedResults($survey_id, $nr_of_users, $finished_ids);
		}
		
		$output = "";
		include_once "./Services/UICore/classes/class.ilTemplate.php";
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
			case SurveyMetricQuestion::SUBTYPE_NON_RATIO:
				$template->setVariable("TEXT_OPTION_VALUE", $this->lng->txt("non_ratio"));
				break;
			case SurveyMetricQuestion::SUBTYPE_RATIO_NON_ABSOLUTE:
				$template->setVariable("TEXT_OPTION_VALUE", $this->lng->txt("ratio_non_absolute"));
				break;
			case SurveyMetricQuestion::SUBTYPE_RATIO_ABSOLUTE:
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
		$chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $a_id);
		$chart->setsize(700, 400);
		
		$legend = new ilChartLegend();
		$chart->setLegend($legend);	

		$data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
		$data->setLabel($this->lng->txt("users_answered"));
		$data->setBarOptions(0.1, "center");
		
		if($a_values)
		{
			$labels = array();
			foreach($a_values as $idx => $answer)
			{			
				$data->addPoint($answer["value"], $answer["selected"]);		
				$labels[$answer["value"]] = $answer["value"];
			}
			$chart->addData($data);

			$chart->setTicks($labels, false, true);
		}

		return "<div style=\"margin:10px\">".$chart->getHTML()."</div>";				
	}
}

?>