<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/SurveyQuestionPool/classes/class.SurveyQuestionEvaluation.php";

/**
 * Survey matrix evaluation 
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesSurveyQuestionPool
 */
class SurveyMatrixQuestionEvaluation extends SurveyQuestionEvaluation
{
	//
	// RESULTS	
	//
	
	public function getResults()
	{
		$results = array();
		
		$answers = $this->getAnswerData();
		
		// parse rows
		for ($r = 0; $r < $this->question->getRowCount(); $r++)
		{											
			$row_results = new ilSurveyEvaluationResults($this->question);	
					
			$this->parseResults(
				$row_results, 
				(array)$answers[$r], 
				$this->question->getColumns()
			);
				
			$results[] = array(
				$this->question->getRow($r)->title,
				$row_results
			);
		}
		
		return $results;
	}
	
	
	//
	// DETAILS
	//
	
	
	public function getGrid($a_results)
	{
		global $lng;
		
		$res = array(
			"cols" => array(),
			"rows" => array()
		);
		
		$tmp = $a_results;
		$tmp = array_shift($tmp);
		$vars = $tmp[1]->getVariables();
		if($vars)
		{
			foreach($vars as $var)
			{
				$res["cols"][] = $var->cat->title;
			}
		}
		
		foreach($a_results as $results_row)
		{																				
			$parsed_row = array(
				$results_row[0]
			);
			
			$vars = $results_row[1]->getVariables();
			if($vars)
			{
				foreach($vars as $var)
				{
					$parsed_row[] = array(
						$var->abs,
						$var->perc
							? sprintf("%.2f", $var->perc*100)."%"
							: null
					);
				}
			}
			
			$res["rows"][] = $parsed_row;
		}		
		
		return $res;
	}
	
	public function getTextAnswers($a_results)
	{
		$res = array();
		
		foreach($a_results as $results_row)
		{		
			$texts = $results_row[1]->getMappedTextAnswers();
			if($texts)
			{		
				$idx = $results_row[0];
				foreach($texts as $answers)
				{									
					foreach($answers as $answer)
					{
						$res[$idx][] = $answer;
					}
				}
			}
		}
		
		return $res;
	}
	
	public function getChart($a_results)
	{
		global $lng;
		
		include_once "Services/Chart/classes/class.ilChart.php";
		$chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $a_results[0][1]->getQuestion()->getId());
		$chart->setsize(700, 400);
		$chart->setStacked(true);

		$legend = new ilChartLegend();
		$chart->setLegend($legend);	
		$chart->setXAxisToInteger(true);
		
		$data = array();
		
		$row_idx = sizeof($a_results);
				
		foreach($a_results as $row)
		{
			$row_idx--;
			
			$row_title = $row[0];
			$row_results = $row[1];
			
			$labels[$row_idx] = $row_title;
			
			$vars = $row_results->getVariables();
			if($vars)
			{								
				foreach($vars as $idx => $var)
				{																	
					if(!array_key_exists($idx, $data))
					{
						$data[$idx] = $chart->getDataInstance(ilChartGrid::DATA_BARS);
						$data[$idx]->setLabel($var->cat->title);
						$data[$idx]->setBarOptions(0.5, "center", true);
					}
					$data[$idx]->addPoint($var->abs, $row_idx);											
				}				
			}
		}
		
		foreach($data as $var)
		{
			$chart->addData($var);			
		}
		
		$chart->setTicks(false, $labels, true);
		
		return $chart->getHTML();				
	}
	

	
	//
	// EXPORT
	//
	
	public function exportResults($a_results, $a_do_title, $a_do_label)
	{
		$question = $a_results[0][1]->getQuestion();
		
		$rows = array();		
		$row = array();
		
		if($a_do_title)
		{
			$row[] = $question->getTitle();
		}
		if($a_do_label)
		{
			$row[] = $question->label;
		}
		
		$row[] = $question->getQuestiontext();		
		$row[] = SurveyQuestion::_getQuestionTypeName($question->getQuestionType());
		
		$row[] = (int)$a_results[0][1]->getUsersAnswered();
		$row[] = (int)$a_results[0][1]->getUsersSkipped();
		$row[] = null;
		$row[] = null;
		$row[] = null;
		$row[] = null;
		$row[] = null;
		
		$rows[] = $row;		
		
		foreach($a_results as $row_result)
		{						
			$row_title = $row_result[0];
			$row_res = $row_result[1];
		
			$row = array();
			
			if($a_do_title)
			{
				$row[] = null;
			}
			if($a_do_label)
			{
				$row[] = null;
			}

			$row[] = $row_title;
			$row[] = null;

			$row[] = null;
			$row[] = null;
			
			// :TODO:
			$row[] = is_array($row_res->getModeValue())
				? implode(", ", $row_res->getModeValue())
				: $row_res->getModeValue();
			
			$row[] = $row_res->getModeValueAsText();
			$row[] = (int)$row_res->getModeNrOfSelections();

			// :TODO:
			$row[] = $row_res->getMedianAsText();

			$row[] = $row_res->getMean();
			
			$rows[] = $row;					
		}
		
		return $rows;		
	}
	
	/**
	* Adds the entries for the title row of the user specific results
	*
	* @param array $a_array An array which is used to append the title row entries
	* @access public
	*/
	function addUserSpecificResultsExportTitles(&$a_array, $a_use_label = false, $a_substitute = true)
	{		
		parent::addUserSpecificResultsExportTitles($a_array, $a_use_label, $a_substitute);
	
		for ($i = 0; $i < $this->getRowCount(); $i++)
		{
			// create row title according label, add 'other column'
			$row = $this->getRow($i);
			
			if(!$a_use_label)
			{
				$title = $row->title;	
			}
			else
			{
				if($a_substitute)
				{
					$title = $row->label ? $row->label : $row->title;
				}
				else
				{
					$title = $row->label;
				}
			}				
			array_push($a_array, $title);

			if ($row->other)
			{
				if(!$a_use_label || $a_substitute)
				{
					array_push($a_array, $title. ' - '. $this->lng->txt('other'));	
				}
				else
				{
					array_push($a_array, "");
				}
			}
			
			switch ($this->getSubtype())
			{
				case 0:	
					break;
				case 1:
					for ($index = 0; $index < $this->getColumnCount(); $index++)
					{
						$col = $this->getColumn($index);
						if(!$a_use_label || $a_substitute)
						{
							array_push($a_array, ($index+1) . " - " . $col->title);
						}
						else
						{
							array_push($a_array, "");
						}
					}
					break;
			}
		}
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
			array_push($a_array, "");
			switch ($this->getSubtype())
			{
				case 0:
					for ($i = 0; $i < $this->getRowCount(); $i++)
					{
						// add textanswer column for single choice mode
						$row = $this->getRow($i);
						$textanswer = "";						
						$checked = FALSE;
						foreach ($resultset["answers"][$this->getId()] as $result)
						{
							if ($result["rowvalue"] == $i)
							{								
								$checked = TRUE;
								array_push($a_array, $result["value"] + 1);
								
								if ($result["textanswer"])
								{
									$textanswer = $result["textanswer"];
								}						
							}
						}
						if (!$checked)
						{
							array_push($a_array, $this->getSkippedValue());
						}
						if ($row->other)
						{
							array_push($a_array, $textanswer);	
						}
					}
					break;
				case 1:
					for ($i = 0; $i < $this->getRowCount(); $i++)
					{
						// add textanswer column for multiple choice mode
						$row = $this->getRow($i);
						$textanswer = "";						
						$checked = FALSE;
						$checked_values = array();
						foreach ($resultset["answers"][$this->getId()] as $result)
						{
							if ($result["rowvalue"] == $i)
							{
								$checked = TRUE;
								array_push($checked_values, $result["value"] + 1);
								
								if ($result["textanswer"])
								{
									$textanswer = $result["textanswer"];
								}	
							}
						}
						if (!$checked)
						{
							array_push($a_array, $this->getSkippedValue());
						}
						else
						{
							array_push($a_array, "");
						}
						if ($row->other)
						{
							array_push($a_array, $textanswer);	
						}
						for ($index = 0; $index < $this->getColumnCount(); $index++)
						{
							if (!$checked)
							{
								array_push($a_array, "");
							}
							else
							{
								$cat = $this->getColumn($index);
								$scale = $cat->scale;								
								if (in_array($scale, $checked_values))
								{
									array_push($a_array, $scale);
								}
								else
								{
									array_push($a_array, 0);
								}
							}
						}
					}
					break;
			}
		}
		else
		{
			array_push($a_array, $this->getSkippedValue());
			for ($i = 0; $i < $this->getRowCount(); $i++)
			{
				array_push($a_array, "");
				
				// add empty "other" column if not answered
				$row = $this->getRow($i);
				if ($row->other)
				{
					array_push($a_array, "");	
				}
				
				switch ($this->getSubtype())
				{
					case 0:
						break;
					case 1:
						for ($index = 0; $index < $this->getColumnCount(); $index++)
						{
							array_push($a_array, "");
						}
						break;
				}
			}
		}
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
		
		$cumulated_count = 0;
		foreach ($this->cumulated as $key => $value)
		{
			if (is_numeric($key))	
			{
				$cumulated_count++;							
			}
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
		$template->setVariable("TEXT_OPTION_VALUE", $this->lng->txt($this->getQuestionType()).
			" (".$cumulated_count." ".$this->lng->txt("rows").")");
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("users_answered"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["USERS_ANSWERED"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("users_skipped"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["USERS_SKIPPED"]);
		$template->parseCurrentBlock();
		/*
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("mode"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["MODE"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("mode_nr_of_selections"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["MODE_NR_OF_SELECTIONS"]);		
	    $template->parseCurrentBlock();
		 */
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("median"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["MEDIAN"]);
		$template->parseCurrentBlock();
		
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("categories"));
		$table = array();
		$idx = $selsum = 0;
		foreach ($this->cumulated["TOTAL"]["variables"] as $key => $value)
		{
			$table[] = array(
				(++$idx).".",
				$value["title"], 
				$value["selected"], 
				sprintf("%.2f", 100*$value["percentage"])."%"
			);
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
				
		// total chart 
		$template->setCurrentBlock("detail_row");				
		$template->setVariable("TEXT_OPTION", $this->lng->txt("chart"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->renderChart("svy_ch_".$this->object->getId()."_total", $this->cumulated["TOTAL"]["variables"]));
		$template->parseCurrentBlock();
		
		$template->setVariable("QUESTION_TITLE", "$counter. ".$this->object->getTitle());		
		
		$output .= $template->get();
		
		foreach ($this->cumulated as $key => $value)
		{
			if (is_numeric($key))	
			{
				$template = new ilTemplate("tpl.il_svy_svy_cumulated_results_detail.html", TRUE, TRUE, "Modules/Survey");	
				
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("users_answered"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["USERS_ANSWERED"]);
				$template->parseCurrentBlock();
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("users_skipped"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["USERS_SKIPPED"]);
				$template->parseCurrentBlock();				
				/*
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("mode"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["MODE"]);
				$template->parseCurrentBlock();				
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("mode_nr_of_selections"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["MODE_NR_OF_SELECTIONS"]);
				$template->parseCurrentBlock();
				*/
				$template->setCurrentBlock("detail_row");				
				$template->setVariable("TEXT_OPTION", $this->lng->txt("median"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["MEDIAN"]);
				$template->parseCurrentBlock();
				
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("categories"));
				$table = array();
				$idx = $selsum = 0;
				foreach ($value["variables"] as $cvalue)
				{					
					$table[] = array(
						(++$idx).".",
						$cvalue["title"], 
						$cvalue["selected"], 
						sprintf("%.2f", 100*$cvalue["percentage"])."%"
					);
					$selsum += (int)$cvalue["selected"];
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
				
				// add text answers to detailed results
				if (is_array($value["textanswers"]))
				{
					$template->setCurrentBlock("detail_row");
					$template->setVariable("TEXT_OPTION", $this->lng->txt("freetext_answers"));	
					$html = "";		
					foreach ($value["textanswers"] as $tkey => $answers)
					{
						$html .= $value["variables"][$tkey]["title"] ."\n";
						$html .= "<ul>\n";
						foreach ($answers as $answer)
						{
							$html .= "<li>" . preg_replace("/\n/", "<br>\n", $answer) . "</li>\n";
						}
						$html .= "</ul>\n";
					}
					$template->setVariable("TEXT_OPTION_VALUE", $html);
					$template->parseCurrentBlock();
				}			
			
				// chart 
				$template->setCurrentBlock("detail_row");				
				$template->setVariable("TEXT_OPTION", $this->lng->txt("chart"));
				$template->setVariable("TEXT_OPTION_VALUE", $this->renderChart("svy_ch_".$this->object->getId()."_".$key, $value["variables"]));
				$template->parseCurrentBlock();
				
				$template->setVariable("QUESTION_SUBTITLE", $counter.".".($key+1)." ".
					$this->object->prepareTextareaOutput($value["ROW"], TRUE));
				
				$output .= $template->get();
			}
		}

		return $output;
	}		
}
