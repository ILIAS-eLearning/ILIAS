<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/SurveyQuestionPool/classes/class.SurveyQuestionEvaluation.php";

/**
 * Survey metric  evaluation 
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesSurveyQuestionPool
 */
class SurveyMetricQuestionEvaluation extends SurveyQuestionEvaluation
{
	public function getResults()
	{
		
		
		
	}
	
	

		
	function &getCumulatedResults($survey_id, $nr_of_users, $finished_ids)
	{
		global $ilDB;
		
		$question_id = $this->getId();
		
		$result_array = array();
		$cumulated = array();

		$sql = "SELECT svy_answer.* FROM svy_answer".
			" JOIN svy_finished ON (svy_finished.finished_id = svy_answer.active_fi)".
			" WHERE svy_answer.question_fi = ".$ilDB->quote($question_id, "integer").
			" AND svy_finished.survey_fi = ".$ilDB->quote($survey_id, "integer");		
		if($finished_ids)
		{
			$sql .= " AND ".$ilDB->in("svy_finished.finished_id", $finished_ids, "", "integer");
		}

		$result = $ilDB->query($sql);		
		while ($row = $ilDB->fetchAssoc($result))
		{
			$cumulated[$row["value"]]++;
		}
		asort($cumulated, SORT_NUMERIC);
		end($cumulated);
		$numrows = $result->numRows();
		$result_array["USERS_ANSWERED"] = $result->numRows();
		$result_array["USERS_SKIPPED"] = $nr_of_users - $result->numRows();
		if(sizeof($cumulated))
		{
			$result_array["MODE"] = key($cumulated);
			$result_array["MODE_VALUE"] = key($cumulated);
			$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
			ksort($cumulated, SORT_NUMERIC);
		}
		$counter = 0;
		foreach ($cumulated as $value => $nr_of_users)
		{
			$percentage = 0;
			if ($numrows > 0)
			{
				$percentage = (float)($nr_of_users/$numrows);
			}
			$result_array["values"][$counter++] = array("value" => $value, "selected" => (int)$nr_of_users, "percentage" => $percentage);
		}
		$median = array();
		$total = 0;
		$x_i = 0;
		$p_i = 1;
		$x_i_inv = 0;
		$sum_part_zero = false;
		foreach ($cumulated as $value => $key)
		{
			$total += $key;
			for ($i = 0; $i < $key; $i++)
			{
				array_push($median, $value);
				$x_i += $value;
				$p_i *= $value;
				if ($value != 0)
				{
					$sum_part_zero = true;
					$x_i_inv += 1/$value;
				}
			}
		}
		if ($total > 0)
		{
			if (($total % 2) == 0)
			{
				$median_value = 0.5 * ($median[($total/2)-1] + $median[($total/2)]);
			}
			else
			{
				$median_value = $median[(($total+1)/2)-1];
			}
		}
		else
		{
			$median_value = "";
		}
		if ($total > 0)
		{
			if (($x_i/$total) == (int)($x_i/$total))
			{
				$result_array["ARITHMETIC_MEAN"] = $x_i/$total;
			}
			else
			{
				$result_array["ARITHMETIC_MEAN"] = sprintf("%.2f", $x_i/$total);
			}
		}
		else
		{
			$result_array["ARITHMETIC_MEAN"] = "";
		}
		$result_array["MEDIAN"] = $median_value;
		$result_array["QUESTION_TYPE"] = "SurveyMetricQuestion";
		return $result_array;
	}
	
	function setExportDetailsXLS(ilExcel $a_excel, $a_eval_data, $a_export_label)
	{		
		$row = 1;						
		switch($a_export_label)
		{
			case 'label_only':				
				$a_excel->setCell($row, 0, $this->lng->txt("label"));
				$a_excel->setCell($row++, 1, $this->label);			
				break;
			
			case 'title_only':
				$a_excel->setCell($row, 0, $this->lng->txt("title"));
				$a_excel->setCell($row++, 1, $this->getTitle());							
				break;
			
			default:
				$a_excel->setCell($row, 0, $this->lng->txt("title"));
				$a_excel->setCell($row++, 1, $this->getTitle());		
				$a_excel->setCell($row, 0, $this->lng->txt("title"));
				$a_excel->setCell($row++, 1, $this->getTitle());		
				break;
		}
		
		$a_excel->setCell($row, 0, $this->lng->txt("question"));
		$a_excel->setCell($row++, 1, $this->getQuestiontext());
		
		$a_excel->setCell($row, 0, $this->lng->txt("question_type"));
		$a_excel->setCell($row++, 1, $this->lng->txt($this->getQuestionType()));
		
		$a_excel->setCell($row, 0, $this->lng->txt("users_answered"));
		$a_excel->setCell($row++, 1, (int)$a_eval_data["USERS_ANSWERED"]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("users_skipped"));
		$a_excel->setCell($row++, 1, (int)$a_eval_data["USERS_SKIPPED"]);
				
		$a_excel->setCell($row, 0, $this->lng->txt("subtype"));		
		switch ($this->getSubtype())
		{
			case self::SUBTYPE_NON_RATIO:
				$subtype_text = $this->lng->txt("non_ratio");
				break;
			
			case self::SUBTYPE_RATIO_NON_ABSOLUTE:
				$subtype_text = $this->lng->txt("ratio_non_absolute");
				break;
			
			case self::SUBTYPE_RATIO_ABSOLUTE:
				$subtype_text = $this->lng->txt("ratio_absolute");
				break;
		}
		$a_excel->setCell($row++, 1, $subtype_text);
		
		$a_excel->setCell($row, 0, $this->lng->txt("mode"));
		$a_excel->setCell($row++, 1, $a_eval_data["MODE"]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("mode_text"));
		$a_excel->setCell($row++, 1, $a_eval_data["MODE"]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("mode_nr_of_selections"));
		$a_excel->setCell($row++, 1, (int)$a_eval_data["MODE_NR_OF_SELECTIONS"]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("median"));
		$a_excel->setCell($row++, 1, $a_eval_data["MEDIAN"]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("arithmetic_mean"));
		$a_excel->setCell($row++, 1, $a_eval_data["ARITHMETIC_MEAN"]);
		
		// "subtitles"
		$a_excel->setColors("B".$row.":D".$row, ilSurveyEvaluationGUI::EXCEL_SUBTITLE);
		$a_excel->setCell($row, 0, $this->lng->txt("values"));
		$a_excel->setCell($row, 1, $this->lng->txt("value"));
		$a_excel->setCell($row, 2, $this->lng->txt("category_nr_selected"));
		$a_excel->setCell($row++, 3, $this->lng->txt("svy_fraction_of_selections"));		
				
		if(is_array($a_eval_data["values"]))
		{
			foreach($a_eval_data["values"] as $value)
			{				
				$a_excel->setCell($row, 1, (int)$value["value"]);
				$a_excel->setCell($row, 2, (int)$value["selected"]);
				$a_excel->setCell($row++, 3, ($value["percentage"]*100)."%");				
			}
		}
		
		return $row;
	}

	/**
	* Adds the values for the user specific results export for a given user
	*
	* @param array $a_array An array which is used to append the values
	* @param array $resultset The evaluation data for a given user
	* @access public
	*/
	function addUserSpecificResultsData(&$a_array, &$resultset)
	{
		if (count($resultset["answers"][$this->getId()]))
		{
			foreach ($resultset["answers"][$this->getId()] as $key => $answer)
			{
				array_push($a_array, $answer["value"]);
			}
		}
		else
		{
			array_push($a_array, $this->getSkippedValue());
		}
	}
	
	/**
	* Returns an array containing all answers to this question in a given survey
	*
	* @param integer $survey_id The database ID of the survey
	* @return array An array containing the answers to the question. The keys are either the user id or the anonymous id
	* @access public
	*/
	function &getUserAnswers($survey_id, $finished_ids)
	{
		global $ilDB;
		
		$answers = array();
		
		$sql = "SELECT svy_answer.* FROM svy_answer".
			" JOIN svy_finished ON (svy_finished.finished_id = svy_answer.active_fi)".
			" WHERE svy_answer.question_fi = ".$ilDB->quote($this->getId(), "integer").
			" AND svy_finished.survey_fi = ".$ilDB->quote($survey_id, "integer");		
		if($finished_ids)
		{
			$sql .= " AND ".$ilDB->in("svy_finished.finished_id", $finished_ids, "", "integer");
		}
		
		$result = $ilDB->query($sql);		
		while ($row = $ilDB->fetchAssoc($result))
		{
			$answers[$row["active_fi"]] = $row["value"];
		}
		return $answers;
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
		$table = array();
		$idx = $selsum = 0;
		if (is_array($this->cumulated["values"]))
		{
			foreach ($this->cumulated["values"] as $key => $value)
			{				
				$table[] = array(
					(++$idx).".",
					$value["title"], 
					$value["selected"], 
					sprintf("%.2f", 100*$value["percentage"])."%"
				);
			}
			$selsum += (int)$value["selected"];
		}
		$head = array(
			"", 
			$this->lng->txt("title"), 
			$this->lng->txt("category_nr_selected"), 
			$this->lng->txt("percentage_of_selections")
		);
		$foot = array(null, null, $selsum, null);
		$template->setVariable("TEXT_OPTION_VALUE", 
			$this->renderStatisticsDetailsTable($head, $table, $foot));	
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