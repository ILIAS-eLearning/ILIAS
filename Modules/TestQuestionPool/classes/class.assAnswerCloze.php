<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/class.assAnswerSimple.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for cloze question numeric answers
* 
* assAnswerCloze is a class for cloze questions numeric answers.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @see ASS_AnswerBinaryState
*/
class assAnswerCloze extends ASS_AnswerSimple 
{
	/**
	* Name of the lower bound
	* 
	* A string value defining the lower bound
	* of a numeric value
	*
	* @var string
	*/
	var $lowerBound;

	/**
	* Name of the upper bound
	* 
	* A string value defining the upper bound
	* of a numeric value
	*
	* @var string
	*/
	var $upperBound;

	/**
	* assAnswerCloze constructor
	* 
	* The constructor takes possible arguments an creates an instance of the assAnswerCloze object.
	*
	* @param string $answertext A string defining the answer text
	* @param double $points The number of points given for the selected answer
	* @param integer $order A nonnegative value representing a possible display or sort order
	* @access public
	*/
	function assAnswerCloze($answertext = "", $points = 0.0, $order = 0)
	{
		parent::__construct($answertext, $points, $order, -1);

		$this->lowerBound = NULL;
		$this->upperBound = NULL;
	}

	/**
	 * @param $bound A string defining the lower bound of an answer for numeric gaps.
	 * @TODO: Refactor method to throw exceptions on non-numeric values to get rid of magic.
	 */
	function setLowerBound($bound)
	{
		$bound = str_replace(",", ".", $bound);	
		if ($bound > $this->getAnswertext() || strlen($bound) == 0) 
		{
			$bound = $this->getAnswertext();
		}
		
		if ( is_numeric($bound) )
		{
			$this->lowerBound = $bound;
		}
		else
		{
			// unreachable
			// $this->lowerBound = $this->getAnswertext();
		}
	}

	/**
	 * @param $bound A string defining the upper bound of an answer for numeric gaps.
	 * @TODO: Refactor method to throw exceptions on non-numeric values to get rid of magic.
	 */
	function setUpperBound($bound)
	{
		$bound = str_replace(",", ".", $bound);
		if ($bound < $this->getAnswertext() || strlen($bound) == 0)
		{
			$bound = $this->getAnswertext();
		}
		
		if ( is_numeric($bound) )
		{
			$this->upperBound = $bound;
		}
		else
		{
			$this->upperBound = $this->getAnswertext();
		}
	}
	
	function getLowerBound()
	{
		return $this->lowerBound;
	}
	
	function getUpperBound()
	{
		return $this->upperBound;
	}
}