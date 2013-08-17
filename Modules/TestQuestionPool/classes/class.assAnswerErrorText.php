<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for error text answers
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assAnswerErrorText
{
	protected $arrData;

	/**
	* assAnswerErrorText constructor
	*
	* @param string $text_wrong Wrong text
	* @param string $text_correct Correct text
	* @param double $points Points
	*/
	function __construct($text_wrong = "", $text_correct = "", $points = 0.0)
	{
		$this->arrData = array(
			'text_wrong' => $text_wrong,
			'text_correct' => $text_correct,
			'points' => $points
		);
	}

	/**
	* Object getter
	*/
	public function __get($value)
	{
		switch ($value)
		{
			case "text_wrong":
			case "text_correct":
			case "points":
				return $this->arrData[$value];
				break;
			default:
				return null;
				break;
		}
	}

	/**
	* Object setter
	*/
	public function __set($key, $value)
	{
		switch ($key)
		{
			case "text_wrong":
			case "text_correct":
			case "points":
				$this->arrData[$key] = $value;
				break;
			default:
				break;
		}
	}
}