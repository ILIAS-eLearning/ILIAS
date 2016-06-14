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
	
	
	
	
	
	/**
	* Creates a the cumulated results data for the question
	*
	* @return array Data
	*/
	public function getCumulatedResultData($survey_id, $counter, $finished_ids)
	{				
		$cumulated =& $this->calculateCumulatedResults($survey_id, $finished_ids);
		$questiontext = preg_replace("/\<[^>]+?>/ims", "", $this->getQuestiontext());
		
		include_once "./Services/Utilities/classes/class.ilStr.php";
		$maxlen = 75;
		if (strlen($questiontext) > $maxlen + 3)
		{			
			$questiontext = ilStr::substr($questiontext, 0, $maxlen) . "...";
		}
		
		$result = array();
		$row = array(
			'counter' => $counter,
			'title' => $counter.'. '.$this->getTitle(),
			'question' => $questiontext,
			'users_answered' => $cumulated['TOTAL']['USERS_ANSWERED'],
			'users_skipped' => $cumulated['TOTAL']['USERS_SKIPPED'],
			'question_type' => $this->lng->txt($cumulated['TOTAL']['QUESTION_TYPE']),
			'mode' => $cumulated['TOTAL']['MODE'],
			'mode_nr_of_selections' => $cumulated['TOTAL']['MODE_NR_OF_SELECTIONS'],
			'median' => $cumulated['TOTAL']['MEDIAN'],
			'arithmetic_mean' => $cumulated['TOTAL']['ARITHMETIC_MEAN']
		);
		array_push($result, $row);
		$maxlen -= 3;
		foreach ($cumulated as $key => $value)
		{
			if (is_numeric($key))
			{
				if (strlen($value['ROW']) > $maxlen + 3)
				{
					$value['ROW'] = ilStr::substr($value['ROW'], 0, $maxlen) . "...";
				}
				
				$row = array(
					'title' => '',
					'question' => ($key+1) . ". " . $value['ROW'],
					'users_answered' => $value['USERS_ANSWERED'],
					'users_skipped' => $value['USERS_SKIPPED'],
					'question_type' => '',
					'mode' => $value["MODE"],
					'mode_nr_of_selections' => $value["MODE_NR_OF_SELECTIONS"],
					'median' => $value["MEDIAN"],
					'arithmetic_mean' => $value["ARITHMETIC_MEAN"]
				);
				array_push($result, $row);
			}
		}
		return $result;
	}
	
	/**
	* Returns the cumulated results for a given row
	*
	* @param integer $row The index of the row
	* @param integer $survey_id The database ID of the survey
	* @return integer The number of users who took part in the survey
	* @access public
	*/
	function &getCumulatedResultsForRow($rowindex, $survey_id, $nr_of_users, $finished_ids)
	{
		global $ilDB;
		
		$question_id = $this->getId();
		
		$result_array = array();
		$cumulated = array();		
		
		$sql = "SELECT svy_answer.* FROM svy_answer".
			" JOIN svy_finished ON (svy_finished.finished_id = svy_answer.active_fi)".
			" WHERE svy_answer.question_fi = ".$ilDB->quote($question_id, "integer").
			" AND svy_answer.rowvalue = ".$ilDB->quote($rowindex, "integer").
			" AND svy_finished.survey_fi = ".$ilDB->quote($survey_id, "integer");		
		if($finished_ids)
		{
			$sql .= " AND ".$ilDB->in("svy_finished.finished_id", $finished_ids, "", "integer");
		}
		$result = $ilDB->query($sql);		
		
		switch ($this->getSubtype())
		{
			case 0:
			case 1:
				while ($row = $ilDB->fetchAssoc($result))
				{
					$cumulated[$row["value"]]++;
					
					// add text value to result array
					if ($row["textanswer"])
					{
						$result_array["textanswers"][$row["value"]][] = $row["textanswer"];
					}
				}
				// sort textanswers by value
				if (is_array($result_array["textanswers"]))
				{
					ksort($result_array["textanswers"], SORT_NUMERIC);
				}
				asort($cumulated, SORT_NUMERIC);
				end($cumulated);
				break;
		}
		$numrows = $result->numRows();
		$result_array["USERS_ANSWERED"] = $this->getNrOfUsersAnswered($survey_id, $finished_ids, $rowindex);
		$result_array["USERS_SKIPPED"] = $nr_of_users - $result_array["USERS_ANSWERED"];

		if(sizeof($cumulated))
		{
			$prefix = "";
			if (strcmp(key($cumulated), "") != 0)
			{
				$prefix = (key($cumulated)+1) . " - ";
			}
			$cat = $this->getColumnForScale(key($cumulated)+1);
			$result_array["MODE"] =  $prefix . $cat->title;
			$result_array["MODE_VALUE"] =  key($cumulated)+1;
			$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
		}
		for ($key = 0; $key < $this->getColumnCount(); $key++)
		{
			$cat = $this->getColumn($key);
			$scale = $cat->scale-1;
			
			$percentage = 0;
			if ($numrows > 0)
			{
				$percentage = (float)((int)$cumulated[$scale]/$numrows);
			}

			$result_array["variables"][$key] = array("title" => $cat->title, "selected" => (int)$cumulated[$scale], "percentage" => $percentage);
		}
		ksort($cumulated, SORT_NUMERIC);
		$median = array();
		$total = 0;
		foreach ($cumulated as $value => $key)
		{
			$total += $key;
			for ($i = 0; $i < $key; $i++)
			{
				array_push($median, $value+1);
			}
		}
		if ($total > 0)
		{
			if (($total % 2) == 0)
			{
				$median_value = 0.5 * ($median[($total/2)-1] + $median[($total/2)]);
				if (round($median_value) != $median_value)
				{
					$cat = $this->getColumnForScale((int)floor($median_value));
					$cat2 = $this->getColumnForScale((int)ceil($median_value));
					$median_value = $median_value . "<br />" . "(" . $this->lng->txt("median_between") . " " . (floor($median_value)) . "-" . $cat->title . " " . $this->lng->txt("and") . " " . (ceil($median_value)) . "-" . $cat2->title . ")";
				}
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
		$result_array["ARITHMETIC_MEAN"] = "";
		$result_array["MEDIAN"] = $median_value;
		$result_array["QUESTION_TYPE"] = "SurveyMatrixQuestion";
		$result_array["ROW"] = $this->getRow($rowindex)->title;		
		return $result_array;
	}

	/**
	* Returns the cumulated results for the question
	*
	* @param integer $survey_id The database ID of the survey
	* @return integer The number of users who took part in the survey
	* @access public
	*/
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
		
		switch ($this->getSubtype())
		{
			case 0:
			case 1:
				while ($row = $ilDB->fetchAssoc($result))
				{
					$cumulated[$row["value"]]++;
				}
				asort($cumulated, SORT_NUMERIC);
				end($cumulated);
				break;
		}
		$numrows = $result->numRows();
		$result_array["USERS_ANSWERED"] = $this->getNrOfUsersAnswered($survey_id, $finished_ids);
		$result_array["USERS_SKIPPED"] = $nr_of_users - $this->getNrOfUsersAnswered($survey_id, $finished_ids);

		if(sizeof($cumulated))
		{
			$prefix = "";
			if (strcmp(key($cumulated), "") != 0)
			{
				$prefix = (key($cumulated)+1) . " - ";
			}
			$cat = $this->getColumnForScale(key($cumulated)+1);
			$result_array["MODE"] =  $prefix . $cat->title;
			$result_array["MODE_VALUE"] =  key($cumulated)+1;
			$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
		}
		for ($key = 0; $key < $this->getColumnCount(); $key++)
		{
			$cat = $this->getColumn($key);
			$scale = $cat->scale-1;
			
			$percentage = 0;
			if ($numrows > 0)
			{
				$percentage = (float)((int)$cumulated[$scale]/$numrows);
			}

			$result_array["variables"][$key] = array("title" => $cat->title, "selected" => (int)$cumulated[$scale], "percentage" => $percentage);
		}
		ksort($cumulated, SORT_NUMERIC);
		$median = array();
		$total = 0;
		foreach ($cumulated as $value => $key)
		{
			$total += $key;
			for ($i = 0; $i < $key; $i++)
			{
				array_push($median, $value+1);
			}
		}
		if ($total > 0)
		{
			if (($total % 2) == 0)
			{
				$median_value = 0.5 * ($median[($total/2)-1] + $median[($total/2)]);
				if (round($median_value) != $median_value)
				{
					$cat = $this->getColumnForScale((int)floor($median_value));
					$cat2 = $this->getColumnForScale((int)ceil($median_value));
					$median_value = $median_value . "<br />" . "(" . $this->lng->txt("median_between") . " " . (floor($median_value)) . "-" . $cat->title . " " . $this->lng->txt("and") . " " . (ceil($median_value)) . "-" . $cat2->title . ")";
				}
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
		$result_array["ARITHMETIC_MEAN"] = "";
		$result_array["MEDIAN"] = $median_value;
		$result_array["QUESTION_TYPE"] = "SurveyMatrixQuestion";
		
		$cumulated_results = array();
		$cumulated_results["TOTAL"] = $result_array;
		for ($i = 0; $i < $this->getRowCount(); $i++)
		{
			$rowresult =& $this->getCumulatedResultsForRow($i, $survey_id, $nr_of_users, $finished_ids);
			$cumulated_results[$i] = $rowresult;
		}
		return $cumulated_results;
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
		$sql .= " ORDER BY rowvalue, value";		
		$result = $ilDB->query($sql);	
		
		while ($row = $ilDB->fetchAssoc($result))
		{
			$column = $this->getColumnForScale($row["value"]+1);
			if (!is_array($answers[$row["active_fi"]])) $answers[$row["active_fi"]] = array();
			$rowobj = $this->getRow($row["rowvalue"]);
			array_push($answers[$row["active_fi"]], $rowobj->title . (($rowobj->other) ? (" " . $row["textanswer"]) : "") . ": " . ($row["value"] + 1) . " - " . $column->title);
		}
		foreach ($answers as $key => $value)
		{
			$answers[$key] = implode("<br />", $value);
		}
		return $answers;
	}
		
	/**
	* Returns the number of users that answered the question for a given survey
	*
	* @param integer $survey_id The database ID of the survey
	* @return integer The number of users
	* @access public
	*/
	function getNrOfUsersAnswered($survey_id, $finished_ids = null, $rowindex = null)
	{
		global $ilDB;
		
		$sql = "SELECT svy_answer.active_fi, svy_answer.question_fi".
			" FROM svy_answer".
			" JOIN svy_finished ON (svy_finished.finished_id = svy_answer.active_fi)".
			" WHERE svy_answer.question_fi = ".$ilDB->quote($this->getId(), "integer").
			" AND svy_finished.survey_fi = ".$ilDB->quote($survey_id, "integer");		
		if($finished_ids)
		{
			$sql .= " AND ".$ilDB->in("svy_finished.finished_id", $finished_ids, "", "integer");
		}		
		if($rowindex)
		{
			$sql .= " AND rowvalue = ".$ilDB->quote($rowindex, "integer");
		}
		
		$result = $ilDB->query($sql);
		$found = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			$found[$row["active_fi"].$row["question_fi"]] = 1;
		}
		return count($found);
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
