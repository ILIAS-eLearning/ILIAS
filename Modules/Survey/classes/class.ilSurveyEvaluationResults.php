<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Survey evaluation answers
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesSurvey
 */
class ilSurveyEvaluationResults
{
	protected $question; // [SurveyQuestion]
	protected $users_answered; // [int]
	protected $users_skipped; // [int]
	protected $mode_value; // [int]
	protected $mode_nr_of_selections; // [int]
	protected $arithmetic_mean; // [float]
	protected $median; // [string|float]
	protected $variables = array(); // [array] 	
	protected $answers = array(); // [array]
	
	public function __construct(SurveyQuestion $a_question)
	{		
		$this->question = $a_question;
	}
	
	public function getQuestion()
	{
		return $this->question;
	}
	
	public function setUsersAnswered($a_value)
	{
		$this->users_answered = (int)$a_value;
	}
	
	public function getUsersAnswered()
	{
		return $this->users_answered;
	}
	
	public function setUsersSkipped($a_value)
	{
		$this->users_skipped = (int)$a_value;
	}
	
	public function getUsersSkipped()
	{
		return $this->users_skipped;
	}
	
	public function setMode($a_value, $a_nr_of_selections)
	{
		$this->mode_value = $a_value;
		$this->mode_nr_of_selections = (int)$a_nr_of_selections;
	}
	
	public function getModeValue()
	{
		return $this->mode_value;
	}
	
	public function getModeNrOfSelections()
	{
		return $this->mode_nr_of_selections;
	}
	
	public function setMean($a_mean)
	{
		$this->arithmetic_mean = (float)$a_mean;
	}
	
	public function getMean()
	{
		return $this->arithmetic_mean;
	}
	
	public function setMedian($a_value)
	{
		$this->median = trim($a_value);
	}	
	
	public function getMedian()
	{
		return $this->median;
	}	
	
	public function addVariable(ilSurveyEvaluationResultsVariable $a_variable)
	{
		$this->variables[] = $a_variable;
	}
	
	public function addAnswer(ilSurveyEvaluationResultsAnswer $a_answer)
	{
		$this->answers[] = $a_answer;
	}	
}

class ilSurveyEvaluationResultsVariable
{
	protected $cat; // [SurveyCategory]
	protected $abs; // [int]
	protected $perc; // [float]
	
	public function __construct(SurveyCategory $a_cat, $a_abs, $a_perc)
	{
		$this->cat = $a_cat;
		$this->abs = (int)$a_abs;		
		$this->perc = (float)$a_perc;		
	}
}

class ilSurveyEvaluationResultsAnswer
{
	protected $active_id; // [int]
	protected $value; // [int|float]
	protected $text; // [string]
	
	public function __construct($a_active_id, $a_value, $a_text)
	{
		$this->active_id = (int)$a_active_id;
		$this->value = $a_value;
		$this->text = trim($a_text);
	}
}