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

include_once "./assessment/classes/class.assAnswerSimple.php";

/**
* Class for true/false or yes/no answers
* 
* ASS_AnswerBinaryState is a class for answers with a binary state indicator (checked/unchecked, set/unset)
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.assAnswerBinaryState.php
* @modulegroup   Assessment
* @see ASS_AnswerSimple
*/
class ASS_AnswerBinaryState extends ASS_AnswerSimple {
/**
* State of the answer
* 
* An integer value indicating the state of the answer. Either the answer is checked/set (=1) or unchecked/unset (=0)
*
* @var integer
*/
  var $state;

/**
* ASS_AnswerBinaryState constructor
*
* The constructor takes possible arguments an creates an instance of the ASS_AnswerBinaryState object.
*
* @param string $answertext A string defining the answer text
* @param double $points The number of points given for the selected answer
* @param integer $state A integer value indicating the state of the answer
* @param integer $order A nonnegative value representing a possible display or sort order
* @access public
*/
  function ASS_AnswerBinaryState (
    $answertext = "",
    $points = 0.0,
    $order = 0,
    $state = 0,
		$id = -1
  )
  {
    $this->ASS_AnswerSimple($answertext, $points, $order, $id);
    $this->state = $state;
  }


/**
* Gets the state
*
* Returns the state of the answer

* @return boolean state
* @access public
* @see $state
*/
  function getState() {
    return $this->state;
  }

/**
* Gets the state
*
* Returns the answer state

* @return boolean state
* @access public
* @see $state
*/
  function isStateChecked() {
    return $this->state;
  }

/**
* Gets the state
*
* Returns the answer state

* @return boolean state
* @access public
* @see $state
*/
  function isStateSet() {
    return $this->state;
  }

/**
* Gets the state
*
* Returns the answer state

* @return boolean state
* @access public
* @see $state
*/
  function isStateUnset() {
    return !$this->state;
  }

/**
* Gets the state
*
* Returns the answer state

* @return boolean state
* @access public
* @see $state
*/
  function isStateUnchecked() {
    return !$this->state;
  }

/**
* Sets the state
*
* Sets the state of the answer using 1 or 0 as indicator
*
* @param boolean $state A integer value indicating the state of the answer
* @access public
* @see $state
*/
  function setState($state = 0)
  {
    $this->state = $state;
  }

/**
* Sets the answer as a checked answer
*
* Sets the state value of the answer to 1
*
* @access public
* @see $state
*/
  function setChecked() {
    $this->state = 1;
  }

/**
* Sets the answer as a set answer
*
* Sets the state value of the answer to 1
*
* @access public
* @see $state
*/
  function setSet() {
    $this->state = 1;
  }

/**
* Sets the answer as a unset answer
*
* Sets the state value of the answer to 0
*
* @access public
* @see $state
*/
  function setUnset() {
    $this->state = 0;
  }

/**
* Sets the answer as a unchecked answer
*
* Sets the state value of the answer to 0
*
* @access public
* @see $state
*/
  function setUnchecked() {
    $this->state = 0;
  }
}

?>
