<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php";

/**
* Class for answers with a binary state indicator
* 
* ASS_AnswerBinaryStateImage is a class for answers with a binary state 
* indicator (checked/unchecked, set/unset) and an image file
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @see ASS_AnswerSimple
*/
class ASS_AnswerBinaryStateImage extends ASS_AnswerBinaryState {
/**
* Image filename
* 
* Image filename
*
* @var string
*/
  var $image;

/**
* ASS_AnswerBinaryStateImage constructor
*
* The constructor takes possible arguments an creates an instance of the ASS_AnswerBinaryStateImage object.
*
* @param string $answertext A string defining the answer text
* @param double $points The number of points given for the selected answer
* @param integer $state A integer value indicating the state of the answer
* @param integer $order A nonnegative value representing a possible display or sort order
* @param string $a_image The image filename
* @access public
*/
  function ASS_AnswerBinaryStateImage(
    $answertext = "",
    $points = 0.0,
    $order = 0,
    $state = 0,
		$a_image = "",
		$id = -1
  )
  {
    $this->ASS_AnswerBinaryState($answertext, $points, $order, $state, $id);
    $this->image = $a_image;
  }


/**
* Gets the image filename
*
* Returns the image filename

* @return string The image filename
* @access public
* @see $image
*/
  function getImage() 
	{
    return $this->image;
  }

/**
* Sets the image filename
*
* Sets the image filename
*
* @param string $a_image The image filename
* @access public
* @see $image
*/
  function setImage($a_image = 0)
  {
    $this->image = $a_image;
  }

}