<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. | 
   +----------------------------------------------------------------------------+
*/

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
		$this->ASS_AnswerSimple($answertext, $points, $order, -1);
		$this->lowerBound = NULL;
		$this->upperBound = NULL;
	}

	function setLowerBound($bound)
	{
		$bound = str_replace(",", ".", $bound);
		if ($bound > $this->getAnswertext()) $bound = $this->getAnswertext();
		$this->lowerBound = is_numeric($bound) ? $bound : NULL;
	}
	
	function setUpperBound($bound)
	{
		$bound = str_replace(",", ".", $bound);
		if ($bound < $this->getAnswertext()) $bound = $this->getAnswertext();
		$this->upperBound = is_numeric($bound) ? $bound : NULL;
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

?>