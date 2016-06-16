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
	
	//
	// EXPORT
	//	

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
}