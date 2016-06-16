<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Survey/classes/class.ilSurveyEvaluationResults.php";

/**
 * Survey question evaluation 
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesSurveyQuestionPool
 */
abstract class SurveyQuestionEvaluation
{
	protected $question; // [SurveyQuestion]
	protected $finished_ids; // [array]
	
	/**
	 * Constructor
	 * 
	 * @param SurveyQuestion $a_question
	 * @param array $a_finished_ids
	 * @return self
	 */
	public function __construct(SurveyQuestion $a_question, array $a_finished_ids = null)
	{		
		$this->question = $a_question;
		$this->finished_ids = $a_finished_ids;
	}	
	
	
	//
	// RESULTS
	//
	
	/**
	 * Get results
	 * 
	 * @return ilSurveyEvaluationResults|array
	 */
	public function getResults()
	{
		$results = new ilSurveyEvaluationResults($this->question);	
		$answers = $this->getAnswerData();
										
		$this->parseResults(
			$results, 
			$answers[0], 
			method_exists($this->question, "getCategories")
				? $this->question->getCategories()
				: null
		);
		
		return $results;
	}
		
	/**
	 * Parse answer data into results instance
	 * 
	 * @param ilSurveyEvaluationResults $a_results
	 * @param array $a_answers
	 * @param SurveyCategories $a_categories
	 */
	protected function parseResults(ilSurveyEvaluationResults $a_results, array $a_answers, SurveyCategories $a_categories = null)
	{
		$num_users_answered = sizeof($a_answers);			

		$a_results->setUsersAnswered($num_users_answered);
		$a_results->setUsersSkipped($this->getNrOfParticipants()-$num_users_answered);
		
		// parse answers
		$has_multi = false;
		$selections = array();
		foreach($a_answers as $active_id => $answers)
		{
			// :TODO: 
			if(sizeof($answers) > 1)
			{
				$has_multi = true;
			}							
			foreach($answers as $answer)
			{					
				// map selection value to scale/category
				if($a_categories && 
					$answer["value"] != "")
				{
					$scale = $a_categories->getCategoryForScale($answer["value"]+1);
					if($scale instanceof ilSurveyCategory)
					{
						$answer["value"] = $scale->scale;
					}
				}				
				
				$parsed = new ilSurveyEvaluationResultsAnswer(
					$active_id, 
					$answer["value"], 
					$answer["text"]
				);		
				$a_results->addAnswer($parsed);

				if($answer["value"] != "")
				{
					$selections[$answer["value"]]++;
				}				
			}			
		}
		
		$total = array_sum($selections);

		if($total)
		{
			// mode
			$mode_nr = max($selections);		
			$tmp_mode = $selections;
			asort($tmp_mode, SORT_NUMERIC);
			$mode = array_keys($tmp_mode, $mode_nr);
			$a_results->setMode($mode, $mode_nr);
			
			if(!$has_multi)
			{							
				// median			
				ksort($selections, SORT_NUMERIC);
				$median = array();
				foreach($selections as $value => $count)
				{
					for($i = 0; $i < $count; $i++)
					{
						$median[] = $value;
					}
				}
				if($total % 2 == 0)
				{
					$lower = $median[($total/2)-1];
					$upper = $median[($total/2)];
					$median_value = 0.5 * ($lower + $upper);
					if($a_categories &&
						round($median_value) != $median_value)
					{
						// mapping calculated value to scale values
						$median_value = array($lower, $upper);
					}
				}
				else
				{
					$median_value = $median[(($total+1)/2)-1];
				}
				$a_results->setMedian($median_value);
			}
		}
		
		if($a_categories)
		{
			// selections by category 
			for ($c = 0; $c < $a_categories->getCategoryCount(); $c++)
			{
				$cat = $a_categories->getCategory($c);
				$scale = $cat->scale;

				$var = new ilSurveyEvaluationResultsVariable(
						$cat,
						$selections[$scale],
						$total
							? $selections[$scale]/$total
							: null
					);			
				$a_results->addVariable($var);
			}		
		}
	}
	
	
	//
	// DETAILS
	//
	
	/**
	 * Get grid data
	 * 
	 * @param ilSurveyEvaluationResults|array $a_results
	 * @return array
	 */
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
		
		$vars = $a_results->getVariables();
		if($vars)
		{
			foreach($vars as $var)
			{				
				$res["rows"][] = array(
					$var->cat->title,
					$var->abs,
					$var->perc
						? sprintf("%.2f", $var->perc*100)."%"
						: null
				);								
			}
		}	
		
		return $res;
	}
	
	/**
	 * Get text answers
	 * 
	 * @param ilSurveyEvaluationResults|array $a_results
	 * @return array
	 */
	public function getTextAnswers($a_results)
	{
		return $a_results->getMappedTextAnswers();		
	}
	
	/**
	 * Get chart
	 * 
	 * @param ilSurveyEvaluationResults|array $a_results
	 * @return array
	 */
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
		
		// :TODO:
		$max = 5;
		
		$vars = $a_results->getVariables();
		
		if(sizeof($vars) <= $max)
		{
			if($vars)
			{
				$labels = array();
				foreach($vars as $idx => $var)
				{			
					$data->addPoint($idx, $var->abs);		
					$labels[$idx] = ilUtil::prepareFormOutput($var->cat->title);
				}
				$chart->addData($data);

				$chart->setTicks($labels, false, true);
			}

			return $chart->getHTML();		
		}
		else
		{
			$chart_legend = array();			
			$labels = array();
			foreach($vars as $idx => $var)
			{			
				$data->addPoint($idx, $var->abs);		
				$labels[$idx] = ($idx+1).".";				
				$chart_legend[($idx+1)] = ilUtil::prepareFormOutput($var->cat->title);
			}
			$chart->addData($data);
						
			$chart->setTicks($labels, false, true);
			
			return array(
				$chart->getHTML(),
				$chart_legend
			);					
		}				
	}
	
	
	// 
	// USER-SPECIFIC
	// 
	
	/**
	 * Get caption for skipped value
	 * 
	 * @return string
	 */	
	public function getSkippedValue()
	{
		include_once "Modules/Survey/classes/class.ilObjSurvey.php";
		return ilObjSurvey::getSurveySkippedValue();
	}
	
			
	//
	// HELPER
	// 	
	
	protected function getSurveyId()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT survey_id FROM svy_svy".
			" WHERE obj_fi = ".$ilDB->quote($this->question->getObjId(), "integer"));
		$row = $ilDB->fetchAssoc($set);
		return $row["survey_id"];
	}
	
	
	/**
	* Returns the number of participants for a survey
	*
	* @return integer The number of participants
	*/
	protected function getNrOfParticipants()
	{
		global $ilDB;
		
		if(is_array($this->finished_ids))
		{
			return sizeof($this->finished_ids);
		}
		
		$set = $ilDB->query("SELECT finished_id FROM svy_finished".
			" WHERE survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer"));		
		return $set->numRows();
	}
	
	protected function getAnswerData()
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT svy_answer.* FROM svy_answer".
			" JOIN svy_finished ON (svy_finished.finished_id = svy_answer.active_fi)".
			" WHERE svy_answer.question_fi = ".$ilDB->quote($this->question->getId(), "integer").
			" AND svy_finished.survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer");		
		if(is_array($this->finished_ids))
		{
			$sql .= " AND ".$ilDB->in("svy_finished.finished_id", $this->finished_ids, "", "integer");
		}		
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{			
			$res[(int)$row["rowvalue"]][(int)$row["active_fi"]][] = array(			
				"value" => $row["value"],			
				"text" => $row["textanswer"]
			);						
		}
		
		return $res;
	}
	
	
	//
	// EXPORT
	// 	
	
	public function exportResults($a_results, $a_do_title, $a_do_label)
	{
		$question = $a_results->getQuestion();
		
		$res = array();
		
		if($a_do_title)
		{
			$res[] = $question->getTitle();
		}
		if($a_do_label)
		{
			$res[] = $question->label;
		}
		
		$res[] = $question->getQuestiontext();		
		$res[] = SurveyQuestion::_getQuestionTypeName($question->getQuestionType());
		
		$res[] = (int)$a_results->getUsersAnswered();
		$res[] = (int)$a_results->getUsersSkipped();
		
		// :TODO:
		$res[] = is_array($a_results->getModeValue())
			? implode(", ", $a_results->getModeValue())
			: $a_results->getModeValue();
		
		$res[] = $a_results->getModeValueAsText();
		$res[] = (int)$a_results->getModeNrOfSelections();
		
		// :TODO:
		$res[] = $a_results->getMedianAsText();
		
		$res[] = $a_results->getMean();
				
		return array($res);
	}
		
	/**
	 * Get grid data
	 * 
	 * @param ilSurveyEvaluationResults|array $a_results
	 * @return array
	 */
	public function getExportGrid($a_results)
	{
		global $lng;
		
		$res = array(
			"cols" => array(
				$lng->txt("title"),
				$lng->txt("value"),
				$lng->txt("category_nr_selected"),
				$lng->txt("svy_fraction_of_selections")
			),
			"rows" => array()
		);
		
		$vars = $a_results->getVariables();
		if($vars)
		{
			foreach($vars as $var)
			{				
				$res["rows"][] = array(
					$var->cat->title,
					$var->cat->scale,
					$var->abs,
					$var->perc
						? sprintf("%.2f", $var->perc*100)."%"
						: null
				);					
			}
		}	
		
		return $res;
	}
	
	
	
	
	
	
	
	
	/**
	* Adds the entries for the title row of the user specific results
	*
	* @param array $a_array An array which is used to append the title row entries
	* @access public
	*/
	function addUserSpecificResultsExportTitles(&$a_array, $a_use_label = false, $a_substitute = true)
	{
		if(!$a_use_label)
		{
			$title = $this->title;			
		}
		else
		{
			if($a_substitute)
			{
				$title = $this->label ? $this->label : $this->title;
			}
			else
			{
				$title = $this->label;
			}
		}	
		
		array_push($a_array, $title);
		return $title;
	}
}