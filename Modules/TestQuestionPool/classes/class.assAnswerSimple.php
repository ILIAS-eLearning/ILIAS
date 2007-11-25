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

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for simple answers
* 
* ASS_AnswerSimple is a class for simple answers used for example in cloze tests with text gap.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class ASS_AnswerSimple
{
	/**
	* Answer text
	*
	* The answer text string of the ASS_AnswerSimple object
	*
	* @var string
	*/
	var $answertext;

	/**
	* Points for selected answer
	*
	* The number of points given for the selected answer
	*
	* @var double
	*/
	var $points;

	/**
	* A sort or display order
	*
	* A nonnegative integer value indicating a sort or display order of the answer. This value can be used by objects containing ASS_AnswerSimple objects.
	*
	* @var integer
	*/
	var $order;
	
	/**
	* The database id of the answer
	*
	* The database id of the answer
	*
	* @var integer
	*/
	var $id;

	/**
	* ASS_AnswerSimple constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_AnswerSimple object.
	*
	* @param string $answertext A string defining the answer text
	* @param double $points The number of points given for the selected answer
	* @param integer $order A nonnegative value representing a possible display or sort order
	* @access public
	*/
	function ASS_AnswerSimple (
		$answertext = "",
		$points = 0.0,
		$order = 0,
		$id = -1
	)
	{
		$this->answertext = $answertext;
		$this->setPoints($points);
		$this->order = $order;
		$this->id = $id;
	}

	/**
	* Gets the answer id
	*
	* Returns the answer id

	* @return integer answer id
	* @access public
	* @see $id
	*/
	function getId()
	{
		return $this->id;
	}
	
	/**
	* Gets the answer text
	*
	* Returns the answer text

	* @return string answer text
	* @access public
	* @see $answertext
	*/
	function getAnswertext()
	{
	  //$tmpanswertext = "<![CDATA[".$this->answertext."]]>";
	  $tmpanswertext = $this->answertext;
		return $tmpanswertext;
	}

	/**
	* Gets the points
	*
	* Returns the points

	* @return double points
	* @access public
	* @see $points
	*/
	function getPoints()
	{
		if (round($this->points) == $this->points)
		{
			return sprintf("%d", $this->points);
		}
		else
		{
			return $this->points;
		}
	}
	
	/**
	* Checks, if the point value is numeric
	*
	* Checks, if the point value is numeric

	* @return boolean true if the point value is numeric, false otherwise
	* @access public
	* @see $points
	*/
	function checkPoints($a_points)
	{
		if (is_numeric($a_points))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* Gets the sort/display order
	*
	* Returns a nonnegative order value for display or sorting

	* @return integer order
	* @access public
	* @see $order
	*/
	function getOrder()
	{
		return $this->order;
	}

	/**
	* Sets the order
	*
	* Sets the nonnegative order value which can be used for sorting or displaying multiple answers
	*
	* @param integer $order A nonnegative integer
	* @access public
	* @see $order
	*/
	function setOrder($order = 0)
	{
		if ($order >= 0)
		{
			$this->order = $order;
		}
	}

	/**
	* Sets the answer id
	*
	* Sets the answer id
	*
	* @param integer $id answer id
	* @access public
	* @see $id
	*/
	function setId($id = -1)
	{
		$this->order = $order;
	}

	/**
	* Sets the answer text
	*
	* Sets the answer text
	*
	* @param string $answertext The answer text
	* @access public
	* @see $answertext
	*/
	function setAnswertext($answertext = "")
	{
		$this->answertext = $answertext;
	}

	/**
	* Sets the points
	*
	* Sets the points given for selecting the answer. You can even use negative values for wrong answers.
	*
	* @param double $points The points given for the answer
	* @access public
	* @see $points
	*/
	function setPoints($points = 0.0)
	{
		$new_points = str_replace(",", ".", $points);
		if ($this->checkPoints($new_points))
		{
			$this->points = $new_points;
		}
		else
		{
			$this->points = 0.0;
		}
	}
}

?>
