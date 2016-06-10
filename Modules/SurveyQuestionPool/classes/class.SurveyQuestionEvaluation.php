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
			$this->question->getCategories()
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
				$parsed = new ilSurveyEvaluationResultsAnswer(
					$active_id, 
					$answer["value"], 
					$answer["text"]
				);		
				$a_results->addAnswer($parsed);

				if($answer["value"])
				{
					$selections[$answer["value"]]++;
				}				
			}			
		}
		
		$total = array_sum($selections);

		if($total)
		{
			// mode
			$tmp_mode = $selections;
			asort($tmp_mode, SORT_NUMERIC);
			$mode = array_pop(array_keys($tmp_mode));
			$mode_nr = array_pop($tmp_mode);
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
						$median[] = $value+1;
					}
				}
				if($total % 2 == 0)
				{
					$median_value = 0.5 * ($median[($total/2)-1] + $median[($total/2)]);
					if(round($median_value) != $median_value)
					{
						$median_value = array(
							$a_categories->getCategoryForScale((int)floor($median_value)),
							$a_categories->getCategoryForScale((int)ceil($median_value))
						);
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
				$scale = $cat->scale-1;

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
			" WHERE survey_fi = ".$ilDB->quote($this->question->getSurveyId(), "integer"));		
		return $set->numRows();
	}
	
	protected function getAnswerData()
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT svy_answer.* FROM svy_answer".
			" JOIN svy_finished ON (svy_finished.finished_id = svy_answer.active_fi)".
			" WHERE svy_answer.question_fi = ".$ilDB->quote($this->question->getId(), "integer").
			" AND svy_finished.survey_fi = ".$ilDB->quote($this->question->getSurveyId(), "integer");		
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

	/**
	* Adds the values for the user specific results export for a given user
	*
	* @param array $a_array An array which is used to append the values
	* @param array $resultset The evaluation data for a given user
	* @access public
	*/
	function addUserSpecificResultsData(&$a_array, &$resultset)
	{
		// overwrite in inherited classes
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
		// overwrite in inherited classes
		return array();
	}

	
	protected function &calculateCumulatedResults($survey_id, $finished_ids)
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
			if($nr_of_users)
			{
				$this->cumulated =& $this->getCumulatedResults($survey_id, $nr_of_users, $finished_ids);
			}
		}
		return $this->cumulated;
	}

	/**
	* Creates a the cumulated results data for the question
	*
	* @return array Data
	*/
	public function getCumulatedResultData($survey_id, $counter, $finished_ids)
	{		
		$cumulated =& $this->calculateCumulatedResults($survey_id, $finished_ids);
		$questiontext = preg_replace("/\<[^>]+?>/ims", "", $this->getQuestiontext());
		
		$maxlen = 75;
		include_once "./Services/Utilities/classes/class.ilStr.php";
		if (ilStr::strlen($questiontext) > $maxlen + 3)
		{
			$questiontext = ilStr::substr($questiontext, 0, $maxlen) . "...";
		}
		
		$result = array(
			'counter' => $counter,
			'title' => $counter.'. '.$this->getTitle(),
			'question' => $questiontext,
			'users_answered' => $cumulated['USERS_ANSWERED'],
			'users_skipped' => $cumulated['USERS_SKIPPED'],
			'question_type' => $this->lng->txt($cumulated["QUESTION_TYPE"]),
			'mode' => $cumulated["MODE"],
			'mode_nr_of_selections' => $cumulated["MODE_NR_OF_SELECTIONS"],
			'median' => $cumulated["MEDIAN"],
			'arithmetic_mean' => $cumulated["ARITHMETIC_MEAN"]
		);
		return $result;
	}

	
	//
	// EVALUATION
	//
	
	// :TODO:
	protected function renderChart($a_id, $a_variables)
	{
		include_once "Services/Chart/classes/class.ilChart.php";
		$chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $a_id);
		$chart->setsize(700, 400);

		$legend = new ilChartLegend();
		$chart->setLegend($legend);	
		$chart->setYAxisToInteger(true);
		
		$data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
		$data->setLabel($this->lng->txt("category_nr_selected"));
		$data->setBarOptions(0.5, "center");
		
		$max = 5;
		
		if(sizeof($a_variables) <= $max)
		{
			if($a_variables)
			{
				$labels = array();
				foreach($a_variables as $idx => $points)
				{			
					$data->addPoint($idx, $points["selected"]);		
					$labels[$idx] = ($idx+1).". ".ilUtil::prepareFormOutput($points["title"]);
				}
				$chart->addData($data);

				$chart->setTicks($labels, false, true);
			}

			return "<div style=\"margin:10px\">".$chart->getHTML()."</div>";		
		}
		else
		{
			$chart_legend = array();			
			$labels = array();
			foreach($a_variables as $idx => $points)
			{			
				$data->addPoint($idx, $points["selected"]);		
				$labels[$idx] = ($idx+1).".";				
				$chart_legend[($idx+1)] = ilUtil::prepareFormOutput($points["title"]);
			}
			$chart->addData($data);
						
			$chart->setTicks($labels, false, true);
			
			$legend = "<table>";
			foreach($chart_legend as $number => $caption)
			{
				$legend .= "<tr valign=\"top\"><td>".$number.".</td><td>".$caption."</td></tr>";
			}
			$legend .= "</table>";

			return "<div style=\"margin:10px\"><table><tr valign=\"bottom\"><td>".
				$chart->getHTML()."</td><td class=\"small\" style=\"padding-left:15px\">".
				$legend."</td></tr></table></div>";					
		}				
	}
	
	
	
	/**
	* Creates the Excel output for the cumulated results of this question
	*
	* @param ilExcel $a_excel Reference to the excel worksheet
	* @param array $a_eval_data Cumulated evaluation data
	* @param integer $a_row Actual row in the worksheet
	* @param integer $a_export_label 
	* @return integer The next row which should be used for the export
	* @access public
	*/
	function setExportCumulatedXLS(ilExcel $a_excel, array $a_eval_data, $a_row, $a_export_label)
	{
		$column = 0;
		
		switch ($a_export_label)
		{
			case 'label_only':
				$a_excel->setCell($a_row, $column++, $this->label);
				break;
			
			case 'title_only':
				$a_excel->setCell($a_row, $column++, $this->getTitle());
				break;
			
			default:
				$a_excel->setCell($a_row, $column++, $this->getTitle());
				$a_excel->setCell($a_row, $column++, $this->label);
				break;
		}
		
		$a_excel->setCell($a_row, $column++, $this->getQuestiontext());
		$a_excel->setCell($a_row, $column++, $this->lng->txt($a_eval_data["QUESTION_TYPE"]));
		$a_excel->setCell($a_row, $column++, (int)$a_eval_data["USERS_ANSWERED"]);
		$a_excel->setCell($a_row, $column++, (int)$a_eval_data["USERS_SKIPPED"]);
		$a_excel->setCell($a_row, $column++, $a_eval_data["MODE_VALUE"]);
		$a_excel->setCell($a_row, $column++, $a_eval_data["MODE"]);
		$a_excel->setCell($a_row, $column++, (int)$a_eval_data["MODE_NR_OF_SELECTIONS"]);
		$a_excel->setCell($a_row, $column++, str_replace("<br />", " ", $a_eval_data["MEDIAN"]));
		$a_excel->setCell($a_row, $column++, $a_eval_data["ARITHMETIC_MEAN"]);
		
		return $a_row+1;
	}
	
	/**
	* Creates the CSV output for the cumulated results of this question
	*
	* @param array $eval_data Cumulated evaluation data
	* @param integer $export_label 
	* @access public
	*/
	function setExportCumulatedCVS($eval_data, $export_label)
	{
		$csvrow = array();
		switch ($export_label)
		{
			case 'label_only':
				array_push($csvrow, $this->label);
				break;
			case 'title_only':
				array_push($csvrow, $this->getTitle());
				break;
			default:
				array_push($csvrow, $this->getTitle());
				array_push($csvrow, $this->label);
				break;
		}
		array_push($csvrow, strip_tags($this->getQuestiontext())); // #12942
		array_push($csvrow, $this->lng->txt($eval_data["QUESTION_TYPE"]));
		array_push($csvrow, $eval_data["USERS_ANSWERED"]);
		array_push($csvrow, $eval_data["USERS_SKIPPED"]);
		array_push($csvrow, $eval_data["MODE"]);
		array_push($csvrow, $eval_data["MODE_NR_OF_SELECTIONS"]);
		array_push($csvrow, str_replace("<br />", " ", $eval_data["MEDIAN"])); // #17214
		array_push($csvrow, $eval_data["ARITHMETIC_MEAN"]);
		$result = array();
		array_push($result, $csvrow);
		return $result;
	}
	
	/**
	* Creates an Excel worksheet for the detailed cumulated results of this question
	*
	* @param ilExcel $a_excel Reference to the parent excel workbook
	* @param array $a_eval_data Cumulated evaluation data
	* @param integer $a_export_label 
	* @access public
	*/
	function setExportDetailsXLS(ilExcel $a_excel, $a_eval_data, $a_export_label)
	{
		// overwrite in inherited classes
	}
	
}
