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
include_once "./assessment/classes/inc.AssessmentConstants.php";

/**
* Class for true/false or yes/no answers
* 
* ASS_AnswerTrueFalse is a class for true/false or yes/no answers used for example in multiple choice tests.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.assAnswerTrueFalse.php
* @modulegroup   Assessment
* @see ASS_AnswerSimple
*/
class ASS_AnswerTrueFalse extends ASS_AnswerSimple {
/**
* Correctness of the answer
* 
* A boolean value indicating the correctness of the answer. Either the answer is correct (TRUE) or incorrect (FALSE)
*
* @var boolean
*/
  var $correctness;

/**
* ASS_AnswerTrueFalse constructor
*
* The constructor takes possible arguments an creates an instance of the ASS_AnswerTrueFalse object.
*
* @param string $answertext A string defining the answer text
* @param double $points The number of points given for the selected answer
* @param boolean $correctness A boolean value indicating the correctness of the answer
* @param integer $order A nonnegative value representing a possible display or sort order
* @access public
*/
  function ASS_AnswerTrueFalse (
    $answertext = "",
    $points = 0.0,
    $order = 0,
    $correctness = FALSE
  )
  {
    $this->ASS_AnswerSimple($answertext, $points, $order);
	// force $this->correctness to be a string
	// ilDB->quote makes 1 from true and saving it to ENUM('1','0') makes that '0'!!!
	// (maybe that only happens for certain mysql versions)
    $this->correctness = $correctness."";
  }


/**
* Gets the correctness
*
* Returns the correctness of the answer

* @return boolean correctness
* @access public
* @see $correctness
*/
  function getCorrectness() {
    return $this->correctness;
  }

/**
* Gets the correctness
*
* Returns TRUE if the answer is correct

* @return boolean correctness
* @access public
* @see $correctness
*/
  function isCorrect() {
    return $this->correctness;
  }

/**
* Gets the correctness
*
* Returns TRUE if the answer is correct

* @return boolean correctness
* @access public
* @see $correctness
*/
  function isTrue() {
    return $this->correctness;
  }

/**
* Gets the correctness
*
* Returns TRUE if the answer is incorrect

* @return boolean correctness
* @access public
* @see $correctness
*/
  function isIncorrect() {
    return !$this->correctness;
  }

/**
* Gets the correctness
*
* Returns TRUE if the answer is incorrect

* @return boolean correctness
* @access public
* @see $correctness
*/
  function isFalse() {
    return !$this->correctness;
  }


/**
* Sets the correctness
*
* Sets the correctness of the answer using TRUE or FALSE values to indicate that the answer is correct or incorrect.
*
* @param boolean $correctness A boolean value indicating the correctness of the answer
* @access public
* @see $correctness
*/
  function setCorrectness($correctness = FALSE)
  {
  	// force $this->correctness to be a string
	// ilDB->quote makes 1 from true and saving it to ENUM('1','0') makes that '0'!!!
	// (maybe that only happens for certain mysql versions)
    $this->correctness = $correctness."";
  }

/**
* Sets the answer as a correct answer
*
* Sets the correctness value of the answer to TRUE
*
* @access public
* @see $correctness
*/
  function setTrue() {
    $this->correctness = "1";
  }

/**
* Sets the answer as a incorrect answer
*
* Sets the correctness value of the answer to FALSE
*
* @access public
* @see $correctness
*/
  function setFalse() {
    $this->correctness = "0";
  }
}

?>
