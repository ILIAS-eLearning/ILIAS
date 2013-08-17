<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once "./Modules/TestQuestionPool/classes/class.assAnswerSimple.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for true/false or yes/no answers
* 
* ASS_AnswerTrueFalse is a class for true/false or yes/no answers used for example in multiple choice tests.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
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
