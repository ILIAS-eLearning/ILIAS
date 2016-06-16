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
