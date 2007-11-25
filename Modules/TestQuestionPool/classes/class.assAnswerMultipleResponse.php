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
* Class for true/false or yes/no answers
* 
* ASS_AnswerMultipleResponse is a class for answers with a binary state indicator (checked/unchecked, set/unset)
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @see ASS_AnswerSimple
*/
class ASS_AnswerMultipleResponse extends ASS_AnswerSimple {
/**
* The points given to the answer when the answer is not checked
* 
* The points given to the answer when the answer is not checked
*
* @var double
*/
  var $points_unchecked;

/**
* ASS_AnswerMultipleResponse constructor
*
* The constructor takes possible arguments an creates an instance of the ASS_AnswerMultipleResponse object.
*
* @param string $answertext A string defining the answer text
* @param double $points The number of points given for the selected answer
* @param double $points_unchecked The points when the answer is not checked
* @param integer $order A nonnegative value representing a possible display or sort order
* @access public
*/
  function ASS_AnswerMultipleResponse (
    $answertext = "",
    $points_checked = 0.0,
    $order = 0,
    $points_unchecked = 0,
		$id = -1
  )
  {
    $this->ASS_AnswerSimple($answertext, $points_checked, $order, $id);
    $this->points_unchecked = $points_unchecked;
  }


/**
* Returns the points for an unchecked answer
*
* Returns the points for an unchecked answer

* @return double The points for an unchecked answer
* @access public
* @see $points_unchecked
*/
  function getPointsUnchecked() {
		if (round($this->points_unchecked) == $this->points_unchecked)
		{
			return sprintf("%d", $this->points_unchecked);
		}
		else
		{
			return $this->points_unchecked;
		}
  }

/**
* Sets the points for an unchecked answer
*
* Sets the points for an unchecked answer
*
* @param double $points_unchecked The points for an unchecked answer
* @access public
* @see $state
*/
  function setPointsUnchecked($points_unchecked = 0.0)
  {
		$new_points = str_replace(",", ".", $points_unchecked);
		if ($this->checkPoints($new_points))
		{
			$this->points_unchecked = $new_points;
		}
		else
		{
			$this->points_unchecked = 0.0;
		}
  }

	function setPointsChecked($points_checked)
	{
		$this->setPoints($points_checked);
	}
	
	function getPointsChecked()
	{
		return $this->getPoints();
	}
}

?>
