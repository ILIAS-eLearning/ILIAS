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
	//
	// RESULTS
	//
	
	protected function parseResults(ilSurveyEvaluationResults $a_results, array $a_answers, SurveyCategories $a_categories = null)
	{
		parent::parseResults($a_results, $a_answers);
		
		// add arithmetic mean
		$total = $sum = 0;
		foreach($a_answers as $answers)
		{	
			foreach($answers as $answer)
			{								
				$total++;
				$sum += $answer["value"]; 				
			}				
		}
		if($total > 0)
		{
			$a_results->setMean($sum/$total);
		}		
	}
	
	
	//
	// DETAILS
	//
	
	public function getGrid($a_results)
	{
		global $lng;
		
		$res = array(
			"cols" => array(
				$lng->txt("category_nr_selected"),
				$lng->txt("svy_fraction_of_selections")
			),
			"rows" => array()
		);
		
		// as we have no variables build rows from answers directly
		$total = sizeof($a_results->getAnswers());
		if($total > 0)
		{	
			$cumulated = array();
			foreach($a_results->getAnswers() as $answer)
			{										
				$cumulated[$answer->value]++;												
			}																
			foreach($cumulated as $value => $count)
			{
				$res["rows"][] = array(
					$value,
					$count,
					sprintf("%.2f", $count/$total*100)."%"
				);
			}
		}			
		
		return $res;
	}
	
	public function getChart($a_results)
	{
		global $lng; 
		
		include_once "Services/Chart/classes/class.ilChart.php";
		$chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $a_results->getQuestion()->getId());
		$chart->setsize(700, 400);

		$legend = new ilChartLegend();
		$chart->setLegend($legend);	
		$chart->setYAxisToInteger(true);
		
		$data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
		$data->setLabel($lng->txt("category_nr_selected"));
		$data->setBarOptions(0.5, "center");
		
		$total = sizeof($a_results->getAnswers());
		if($total > 0)
		{	
			$cumulated = array();
			foreach($a_results->getAnswers() as $answer)
			{										
				$cumulated[$answer->value]++;												
			}			
			
			$labels = array();
			foreach($cumulated as $value => $count)
			{				
				$data->addPoint($value, $count);		
				$labels[$value] = $value;
			}
			$chart->addData($data);

			$chart->setTicks($labels, false, true);
		
			return $chart->getHTML();		
		}		
	}

	
	//
	// EXPORT
	// 
	
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
}