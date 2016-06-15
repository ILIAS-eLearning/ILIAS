<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/SurveyQuestionPool/classes/class.SurveyQuestionEvaluation.php";

/**
 * Survey sc evaluation 
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesSurveyQuestionPool
 */
class SurveySingleChoiceQuestionEvaluation extends SurveyQuestionEvaluation
{
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
		
		preg_match("/(.*?)\s+-\s+(.*)/", $a_eval_data["MODE"], $matches);
		
		$a_excel->setCell($row, 0, $this->lng->txt("mode"));
		$a_excel->setCell($row++, 1, $matches[1]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("mode_text"));
		$a_excel->setCell($row++, 1, $matches[2]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("mode_nr_of_selections"));
		$a_excel->setCell($row++, 1, (int)$a_eval_data["MODE_NR_OF_SELECTIONS"]);
		
		$a_excel->setCell($row, 0, $this->lng->txt("median"));
		$a_excel->setCell($row++, 1, str_replace("<br />", " ", $a_eval_data["MEDIAN"]));
		
		$a_excel->setCell($row, 0, $this->lng->txt("arithmetic_mean"));
		$a_excel->setCell($row++, 1, $a_eval_data["ARITHMETIC_MEAN"]);
	
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
	* overwritten addUserSpecificResultsExportTitles
	* 
	* Adds the entries for the title row of the user specific results
	*
	* @param array $a_array An array which is used to append the title row entries
	* @access public
	*/
	function addUserSpecificResultsExportTitles(&$a_array, $a_use_label = false, $a_substitute = true)
	{
		$title = parent::addUserSpecificResultsExportTitles($a_array, $a_use_label, $a_substitute);

		// optionally add header for text answer
		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$cat = $this->categories->getCategory($i);
			if ($cat->other)
			{
				if(!$a_use_label || $a_substitute)
				{
					array_push($a_array, $title. ' - '. $this->lng->txt('other'));	
				}
				else
				{
					array_push($a_array, "");
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
		// check if text answer column is needed
		$other = false;
		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$cat = $this->categories->getCategory($i);
			if ($cat->other)
			{
				$other = true;	
				break;	
			}
		}
		
		if (count($resultset["answers"][$this->getId()]))
		{
			foreach ($resultset["answers"][$this->getId()] as $key => $answer)
			{
				array_push($a_array, $answer["value"]+1);
				
				// add the text answer from the selected option
				if ($other)
				{
					array_push($a_array, $answer["textanswer"]);
				}
			}
		}
		else
		{
			array_push($a_array, $this->getSkippedValue());
			
			if ($other)
			{
				array_push($a_array, "");
			}
		}
	}
}
