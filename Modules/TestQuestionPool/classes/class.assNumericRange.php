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
* Class for numeric ranges of questions
* 
* assNumericRange is a class for numeric ranges of questions
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @see assNumeric
*/

class assNumericRange 
{
 /**
	* The lower limit of the range
	* 
	* A double value containing the lower limit of the range
	*
	* @var double
	*/
  var $lowerlimit;

 /**
	* The upper limit of the range
	* 
	* A double value containing the upper limit of the range
	*
	* @var double
	*/
  var $upperlimit;
	
 /**
	* The points for entering a number in the correct range
	* 
	* The points for entering a number in the correct range
	*
	* @var double
	*/
  var $points;

 /**
	* The order of the range in the container question
	* 
	* The order of the range in the container question
	*
	* @var integer
	*/
  var $order;

	
/**
* assNumericRange constructor
*
* The constructor takes possible arguments an creates an instance of the assNumericRange object.
*
* @param double $lowerlimit The lower limit of the range
* @param double $upperlimit The upper limit of the range
* @param double $points The number of points given for the correct range
* @param integer $order A nonnegative value representing a possible display or sort order
* @access public
*/
  function assNumericRange (
    $lowerlimit = 0.0,
    $upperlimit = 0.0,
    $points = 0.0,
    $order = 0
  )
  {
		$this->lowerlimit = $lowerlimit;
		$this->upperlimit = $upperlimit;
		$this->points = $points;
		$this->order = $order;
  }


 /**
	* Get the lower limit
	*
	* Returns the lower limit of the range
	* @return double The lower limit
	* @access public
	* @see $lowerlimit
	*/
  function getLowerLimit() 
	{
    return $this->lowerlimit;
  }

 /**
	* Get the upper limit
	*
	* Returns the upper limit of the range
	
	* @return double The upper limit
	* @access public
	* @see $upperlimit
	*/
  function getUpperLimit() 
	{
    return $this->upperlimit;
  }

 /**
	* Get the points
	*
	* Returns the points of the range
	* @return double The points
	* @access public
	* @see $points
	*/
  function getPoints() 
	{
    return $this->points;
  }

 /**
	* Get the order of the range
	*
	* Returns the order of the range
	* @return integer order
	* @access public
	* @see $order
	*/
  function getOrder() 
	{
    return $this->order;
  }

 /**
	* Set the lower limit
	*
	* Sets the lower limit of the range
	* @param double $limit The lower limit
	* @access public
	* @see $lowerlimit
	*/
  function setLowerLimit($limit) 
	{
    $this->lowerlimit = $limit;
  }

 /**
	* Set the upper limit
	*
	* Sets the upper limit of the range
	* @param double $limit The upper limit
	* @access public
	* @see $upperlimit
	*/
  function setUpperLimit($limit) 
	{
    $this->upperlimit = $limit;
  }

 /**
	* Set the points
	*
	* Sets the points of the range
	
	* @param double $points The points
	* @access public
	* @see $points
	*/
  function setPoints($points) 
	{
    $this->points = $points;
  }

 /**
	* Set the order
	*
	* Sets the order of the range
	* @param integer $order The order
	* @access public
	* @see $order
	*/
  function setOrder($order) 
	{
    $this->order = $order;
  }
	
 /**
	* Checks for a given value within the range
	*
	* Checks for a given value within the range
	*
	* @param double $value The value to check
	* @return boolean TRUE if the value is in the range, FALSE otherwise
	* @access public
	* @see $upperlimit
	* @see $lowerlimit
	*/
  function contains($value) 
	{
		include_once "./Services/Math/classes/class.EvalMath.php";
		$eval = new EvalMath();
		$eval->suppress_errors = TRUE;
		$result = $eval->e($value);
		if ($result === FALSE) return FALSE;
		if (($result >= $eval->e($this->lowerlimit)) && ($result <= $eval->e($this->upperlimit)))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
  }
}

?>
