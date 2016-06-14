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
		$a_excel->setCell($a_row, $column++, $this->lng->txt($a_eval_data["TOTAL"]["QUESTION_TYPE"]));
		$a_excel->setCell($a_row, $column++, (int)$a_eval_data["TOTAL"]["USERS_ANSWERED"]);
		$a_excel->setCell($a_row, $column++, (int)$a_eval_data["TOTAL"]["USERS_SKIPPED"]);
		$a_excel->setCell($a_row, $column++, $a_eval_data["TOTAL"]["MODE_VALUE"]);
		$a_excel->setCell($a_row, $column++, $a_eval_data["TOTAL"]["MODE"]);
		$a_excel->setCell($a_row, $column++, (int)$a_eval_data["TOTAL"]["MODE_NR_OF_SELECTIONS"]);
		$a_excel->setCell($a_row, $column++, str_replace("<br />", " ", $a_eval_data["TOTAL"]["MEDIAN"]));
		$a_excel->setCell($a_row, $column++, $a_eval_data["TOTAL"]["ARITHMETIC_MEAN"]);		
		$a_row++;
		
		$offset = ($a_export_label == 'label_only' || $a_export_label == 'title_only')
			? 0
			: 1;			
		foreach ($a_eval_data as $evalkey => $evalvalue)
		{
			if (is_numeric($evalkey))
			{
				$a_excel->setCell($a_row, 1+$offset, $evalvalue["ROW"]);
				$a_excel->setCell($a_row, 3+$offset, (int)$evalvalue["USERS_ANSWERED"]);
				$a_excel->setCell($a_row, 4+$offset, (int)$evalvalue["USERS_SKIPPED"]);
				$a_excel->setCell($a_row, 5+$offset, $evalvalue["MODE_VALUE"]);
				$a_excel->setCell($a_row, 6+$offset, $evalvalue["MODE"]);
				$a_excel->setCell($a_row, 7+$offset, (int)$evalvalue["MODE_NR_OF_SELECTIONS"]);
				$a_excel->setCell($a_row, 8+$offset, str_replace("<br />", " ", $evalvalue["MEDIAN"]));
				$a_excel->setCell($a_row, 9+$offset, $evalvalue["ARITHMETIC_MEAN"]);			
				$a_row++;
			}
		}
		
		return $a_row;
	}
	
	function setExportCumulatedCVS($eval_data, $export_label)
	{
		$result = array();
		foreach ($eval_data as $evalkey => $evalvalue)
		{
			$csvrow = array();
			if (is_numeric($evalkey))
			{
				array_push($csvrow, "");
				array_push($csvrow, $evalvalue["ROW"]);
				array_push($csvrow, "");
			}
			else
			{
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
				array_push($csvrow, strip_tags($this->getQuestiontext()));
				array_push($csvrow, $this->lng->txt($evalvalue["QUESTION_TYPE"]));
			}
			array_push($csvrow, $evalvalue["USERS_ANSWERED"]);
			array_push($csvrow, $evalvalue["USERS_SKIPPED"]);
			array_push($csvrow, $evalvalue["MODE"]);
			array_push($csvrow, $evalvalue["MODE_NR_OF_SELECTIONS"]);
			array_push($csvrow, str_replace("<br />", " ", $evalvalue["MEDIAN"])); // #17214
			array_push($csvrow, $evalvalue["ARITHMETIC_MEAN"]);
			array_push($result, $csvrow);
		}
		return $result;
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
		$a_excel->setCell($row++, 1, (int)$a_eval_data["TOTAL"]["USERS_ANSWERED"]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("users_skipped"));
		$a_excel->setCell($row++, 1, (int)$a_eval_data["TOTAL"]["USERS_SKIPPED"]);
		
		preg_match("/(.*?)\s+-\s+(.*)/", $a_eval_data["TOTAL"]["MODE"], $matches);
		
		$a_excel->setCell($row, 0, $this->lng->txt("mode"));
		$a_excel->setCell($row++, 1, $matches[1]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("mode_text"));
		$a_excel->setCell($row++, 1, $matches[2]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("mode_nr_of_selections"));
		$a_excel->setCell($row++, 1, (int)$a_eval_data["MODE_NR_OF_SELECTIONS"]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("median"));
		$a_excel->setCell($row++, 1, str_replace("<br />", " ", $a_eval_data["TOTAL"]["MEDIAN"]));
		
		// "subtitles"
		$a_excel->setColors("B".$row.":E".$row, ilSurveyEvaluationGUI::EXCEL_SUBTITLE);
		$a_excel->setCell($row, 0, $this->lng->txt("categories"));
		$a_excel->setCell($row, 1, $this->lng->txt("title"));
		$a_excel->setCell($row, 2, $this->lng->txt("value"));
		$a_excel->setCell($row, 3, $this->lng->txt("category_nr_selected"));
		$a_excel->setCell($row++, 4, $this->lng->txt("svy_fraction_of_selections"));

		foreach($a_eval_data["TOTAL"]["variables"] as $key => $value)
		{
			$a_excel->setCell($row, 1, $value["title"]);
			$a_excel->setCell($row, 2, $key+1);
			$a_excel->setCell($row, 3, (int)$value["selected"]);
			$a_excel->setCell($row++, 4, ($value["percentage"]*100)."%");			
		}
		
		foreach($a_eval_data as $evalkey => $evalvalue)
		{
			if(is_numeric($evalkey))
			{
				$a_excel->setCell($row, 0, $this->lng->txt("row"));
				$a_excel->setCell($row++, 1, (int)$evalvalue["ROW"]);
				
				$a_excel->setCell($row, 0, $this->lng->txt("users_answered"));
				$a_excel->setCell($row++, 1, (int)$evalvalue["USERS_ANSWERED"]);
				
				$a_excel->setCell($row, 0, $this->lng->txt("users_skipped"));
				$a_excel->setCell($row++, 1, (int)$evalvalue["USERS_SKIPPED"]);
				
				preg_match("/(.*?)\s+-\s+(.*)/", $evalvalue["MODE"], $matches);
				
				$a_excel->setCell($row, 0, $this->lng->txt("mode"));
				$a_excel->setCell($row++, 1, $matches[1]);

				$a_excel->setCell($row, 0, $this->lng->txt("mode_text"));
				$a_excel->setCell($row++, 1, $matches[2]);

				$a_excel->setCell($row, 0, $this->lng->txt("mode_nr_of_selections"));
				$a_excel->setCell($row++, 1, (int)$evalvalue["MODE_NR_OF_SELECTIONS"]);

				$a_excel->setCell($row, 0, $this->lng->txt("median"));
				$a_excel->setCell($row++, 1, str_replace("<br />", " ", $evalvalue["MEDIAN"]));
				
				// "subtitles"
				$a_excel->setColors("B".$row.":E".$row, ilSurveyEvaluationGUI::EXCEL_SUBTITLE);
				$a_excel->setCell($row, 0, $this->lng->txt("categories"));
				$a_excel->setCell($row, 1, $this->lng->txt("title"));
				$a_excel->setCell($row, 2, $this->lng->txt("value"));
				$a_excel->setCell($row, 3, $this->lng->txt("category_nr_selected"));
				$a_excel->setCell($row++, 4, $this->lng->txt("svy_fraction_of_selections"));

				foreach($evalvalue["variables"] as $key => $value)
				{
					$a_excel->setCell($row, 1, $value["title"]);
					$a_excel->setCell($row, 2, $key+1);
					$a_excel->setCell($row, 3, (int)$value["selected"]);
					$a_excel->setCell($row++, 4, ($value["percentage"]*100)."%");								
				}
				
				// add text answers to detailed results
				if(is_array($evalvalue["textanswers"]))
				{
					$a_excel->setColors("B".$row.":C".$row, ilSurveyEvaluationGUI::EXCEL_SUBTITLE);
					$a_excel->setCell($row, 0, $this->lng->txt("freetext_answers"));
					$a_excel->setCell($row, 1, $this->lng->txt("title"));
					$a_excel->setCell($row++, 2, $this->lng->txt("answer"));

					foreach($evalvalue["textanswers"] as $key => $answers)
					{
						$title = $evalvalue["variables"][$key]["title"];
						foreach($answers as $answer)
						{
							$a_excel->setCell($row, 1, $title);
							$a_excel->setCell($row++, 2, $answer);
						}
					}					
				}			
			}
		}
		
		$a_excel->setCell($row++, 0, $this->lng->txt("overview"));
		
		// title row with variables		
		$counter = 1;
		foreach($a_eval_data["TOTAL"]["variables"] as $variable)
		{
			$a_excel->setCell($row, $counter++, $variable["title"]);
		}				
		$a_excel->setColors("B".$row.":".$a_excel->getColumnCoord($counter-1).$row, ilSurveyEvaluationGUI::EXCEL_SUBTITLE);
		$row++;
		
		// rows with variable values
		foreach($a_eval_data as $index => $data)
		{
			if(is_numeric($index))
			{
				$a_excel->setCell($row, 0, $data["ROW"]);
				
				$counter = 1;
				foreach ($data["variables"] as $vardata)
				{
					$a_excel->setCell($row, $counter++, (int)$vardata["selected"]);
				}
				$row++;
			}
		}
		
		return $row;
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
