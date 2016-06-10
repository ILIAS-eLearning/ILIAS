<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/SurveyQuestionPool/classes/class.SurveyQuestionEvaluation.php";

/**
 * Survey mc evaluation 
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesSurveyQuestionPool
 */
class SurveyMultipleChoiceQuestionEvaluation extends SurveyQuestionEvaluation
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
		$numrows = $result->numRows();
		
		// count the answers for every answer value
		$textanswers = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			$cumulated[$row["value"]]++;
			
			// add text value to result array
			if ($row["textanswer"])
			{
				$textanswers[$row["value"]][] = $row["textanswer"];
			}
		}
		// sort textanswers by value
		if (is_array($textanswers))
		{
			ksort($textanswers, SORT_NUMERIC);
		}
		asort($cumulated, SORT_NUMERIC);
		end($cumulated);
		
		$sql = "SELECT svy_answer.answer_id, svy_answer.question_fi, svy_answer.active_fi".
			" FROM svy_answer".
			" JOIN svy_finished ON (svy_finished.finished_id = svy_answer.active_fi)".
			" WHERE svy_answer.question_fi = ".$ilDB->quote($question_id, "integer").
			" AND svy_finished.survey_fi = ".$ilDB->quote($survey_id, "integer");		
		if($finished_ids)
		{
			$sql .= " AND ".$ilDB->in("svy_finished.finished_id", $finished_ids, "", "integer");
		}
		
		$mcmr_result = $ilDB->query($sql);	
		$found = array();
		while ($row = $ilDB->fetchAssoc($mcmr_result))
		{
			$found[$row["question_fi"] . "_" . $row["active_fi"]] = 1;
		}
		$result_array["USERS_ANSWERED"] = count($found);
		$result_array["USERS_SKIPPED"] = $nr_of_users - count($found);
		$numrows = count($found);

		$result_array["MEDIAN"] = "";
		$result_array["ARITHMETIC_MEAN"] = "";
		if(sizeof($cumulated))
		{
			$prefix = "";
			if (strcmp(key($cumulated), "") != 0)
			{
				$prefix = (key($cumulated)+1) . " - ";
			}
			$category = $this->categories->getCategoryForScale(key($cumulated)+1);
			$result_array["MODE"] =  $prefix . $category->title;
			$result_array["MODE_VALUE"] =  key($cumulated)+1;
			$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
		}
		$result_array["QUESTION_TYPE"] = "SurveyMultipleChoiceQuestion";
		$maxvalues = 0;
		for ($key = 0; $key < $this->categories->getCategoryCount(); $key++)
		{
			$cat = $this->categories->getCategory($key);
			$maxvalues += $cumulated[$cat->scale-1];
		}
		for ($key = 0; $key < $this->categories->getCategoryCount(); $key++)
		{
			$cat = $this->categories->getCategory($key);
			$percentage = 0;
			if ($numrows > 0)
			{
				if ($maxvalues > 0)
				{
					$percentage = ($maxvalues > 0) ? (float)((int)$cumulated[$cat->scale-1]/$maxvalues) : 0;
				}
			}
			if(isset($textanswers[$cat->scale-1]))
			{
				// #12138
				$result_array["textanswers"][$key] = $textanswers[$cat->scale-1];
			}
			$result_array["variables"][$key] = array("title" => $cat->title, "selected" => (int)$cumulated[$cat->scale-1], "percentage" => $percentage);
		}
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
	
		$a_excel->setCell($row, 0, $this->lng->txt("mode"));
		$a_excel->setCell($row++, 1, $a_eval_data["MODE_VALUE"]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("mode_text"));
		$a_excel->setCell($row++, 1, $a_eval_data["MODE"]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("mode_nr_of_selections"));
		$a_excel->setCell($row++, 1, (int)$a_eval_data["MODE_NR_OF_SELECTIONS"]);
		
		// "subtitles"
		$a_excel->setColors("B".$row.":E".$row, ilSurveyEvaluationGUI::EXCEL_SUBTITLE);
		$a_excel->setCell($row, 0, $this->lng->txt("categories"));
		$a_excel->setCell($row, 1, $this->lng->txt("title"));
		$a_excel->setCell($row, 2, $this->lng->txt("value"));
		$a_excel->setCell($row, 3, $this->lng->txt("category_nr_selected"));
		$a_excel->setCell($row++, 4, $this->lng->txt("svy_fraction_of_selections"));

		foreach($a_eval_data["variables"] as $key => $value)
		{			
			$category = $this->categories->getCategory($key);
			
			$a_excel->setCell($row, 1, $value["title"]);
			$a_excel->setCell($row, 2, (int)$category->scale);
			$a_excel->setCell($row, 3, (int)$value["selected"]);
			$a_excel->setCell($row++, 4, ($value["percentage"]*100)."%");
		}
		
		// add text answers to detailed results
		if(is_array($a_eval_data["textanswers"]))
		{
			$a_excel->setColors("B".$row.":C".$row, ilSurveyEvaluationGUI::EXCEL_SUBTITLE);
			$a_excel->setCell($row, 0, $this->lng->txt("freetext_answers"));
			$a_excel->setCell($row, 1, $this->lng->txt("title"));
			$a_excel->setCell($row++, 2, $this->lng->txt("answer"));
			
			foreach($a_eval_data["textanswers"] as $key => $answers)
			{
				$title = $a_eval_data["variables"][$key]["title"];
				foreach($answers as $answer)
				{
					$a_excel->setCell($row, 1, $title);
					$a_excel->setCell($row++, 2, $answer);
				}
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
		
		for ($index = 0; $index < $this->categories->getCategoryCount(); $index++)
		{
			$category = $this->categories->getCategory($index);
			$title = $category->title;
			
			if(!$a_use_label || $a_substitute)
			{
				array_push($a_array, $title);
			}
			else
			{
				array_push($a_array, "");
			}
			
			// optionally add headers for text answers
			if ($category->other)
			{
				if(!$a_use_label || $a_substitute)
				{
					array_push($a_array, $title . " - ". $this->lng->txt("other"));
				}
				else
				{
					array_push($a_array, "");
				}
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
			for ($index = 0; $index < $this->categories->getCategoryCount(); $index++)
			{
				$category = $this->categories->getCategory($index);		
				$incoming_value = $category->scale ? $category->scale-1 : $index;		
				
				$found = 0;
				$textanswer = "";				
				foreach ($resultset["answers"][$this->getId()] as $answerdata)
				{
					if (strcmp($incoming_value, $answerdata["value"]) == 0)
					{
						$found = $answerdata["value"]+1;
						$textanswer = $answerdata["textanswer"];
					}
				}
				if ($found)
				{
					array_push($a_array, $found);
				}
				else
				{
					array_push($a_array, "0");
				}				
				if ($category->other)
				{
					array_push($a_array, $textanswer);
				}
			}
		}
		else
		{
			array_push($a_array, $this->getSkippedValue());
			for ($index = 0; $index < $this->categories->getCategoryCount(); $index++)
			{
				array_push($a_array, "");
				
				// add empty text answers for skipped question
				$category = $this->categories->getCategory($index);
				if ($category->other)
				{
					array_push($a_array, "");
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
		
		$result = $ilDB->query($sql);		
		while ($row = $ilDB->fetchAssoc($result))
		{
			$category = $this->categories->getCategoryForScale($row["value"]+1);
			if (!is_array($answers[$row["active_fi"]]))
			{
				$answers[$row["active_fi"]] = array();
			}
			$title = $row["value"] + 1 . " - " . $category->title;
			if ($category->other) $title .= ": " . $row["textanswer"];
			$catindex = $this->categories->getIndex($category);
			if ($catindex !== null)
			{
				$answers[$row["active_fi"]][$catindex] = $title;
			}
			else
			{
				array_push($answers[$row["active_fi"]], $title);
			}
			ksort($answers[$row["active_fi"]], SORT_NUMERIC);
		}
		return $answers;
	}
	
	
	//
	// EVALUTION
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
		$template->setVariable("TEXT_OPTION", $this->lng->txt("categories"));	
		$table = array();
		$idx = $selsum = 0;
		if (is_array($this->cumulated["variables"]))
		{
			foreach ($this->cumulated["variables"] as $key => $value)
			{
				$table[] = array(
					(++$idx).".",
					$value["title"], 
					$value["selected"], 
					sprintf("%.2f", 100*$value["percentage"])."%"
				);			
				$selsum += (int)$value["selected"];
			}
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
		if (is_array($this->cumulated["textanswers"]))
		{
			$template->setCurrentBlock("detail_row");
			$template->setVariable("TEXT_OPTION", $this->lng->txt("freetext_answers"));	
			$html = "";		
			foreach ($this->cumulated["textanswers"] as $key => $answers)
			{
				$html .= $this->cumulated["variables"][$key]["title"] ."\n";
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
		$template->setVariable("TEXT_OPTION_VALUE", $this->renderChart("svy_ch_".$this->object->getId(), $this->cumulated["variables"]));
		$template->parseCurrentBlock();
				
		$template->setVariable("QUESTION_TITLE", "$counter. ".$this->object->getTitle());
		return $template->get();
	}


}