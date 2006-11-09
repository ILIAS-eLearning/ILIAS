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

include_once "./Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for cloze question answers
* 
* ASS_AnswerCloze is a class for cloze questions answers used in cloze questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @see ASS_AnswerBinaryState
*/
class ASS_AnswerCloze extends ASS_AnswerBinaryState {
/**
* Type of answer (text or select gap answer)
* 
* An integer value indicating the type of the answer.
* 0 == text gap answer, 1 == select gap answer
*
* @var int
*/
  var $cloze_type;

/**
* Name of answer gap 
* 
* A string value defining a name for the gap
* which contains the answer
*
* @var string
*/
  var $name;
  
	var $shuffle;
	
/**
* ASS_AnswerCloze constructor
* 
* The constructor takes possible arguments an creates an instance of the ASS_AnswerCloze object.
*
* @param string $answertext A string defining the answer text
* @param double $points The number of points given for the selected answer
* @param boolean $correctness A boolean value indicating the correctness of the answer
* @param integer $order A nonnegative value representing a possible display or sort order
* @param integer $cloze_type An integer representing the answer type
* @access public
*/
  function ASS_AnswerCloze (
    $answertext = "",
    $points = 0.0,
    $order = 0,
    $state = 0,
		$cloze_type = 0,
		$name = "",
		$shuffle = 1,
		$id = -1
  )
  {
    $this->ASS_AnswerBinaryState($answertext, $points, $order, $state, $id);
    $this->cloze_type = $cloze_type;
		$this->name = $name;
		$this->shuffle = $shuffle;
  }
  
  
/**
* Gets the cloze type
* 
* Returns the answer type
*
* @return integer answer type
* @access public
* @see $cloze_type
*/
  function getClozeType() {
    return $this->cloze_type;
  }
  
	function getShuffle() {
		return $this->shuffle;
	}
	
/**
* Sets the answer type
* 
* Sets the answer type
*
* @param integer $cloze_type Answer type
* @access public
* @see $correctness
*/
  function setClozeType($cloze_type = 0) {
    $this->cloze_type = $cloze_type;
  }

/**
* Gets the gap name
* 
* Returns the gap name
*
* @return string gap name
* @access public
* @see $name
*/
  function getName() {
    return $this->name;
  }
  
/**
* Sets the gap name
* 
* Sets the gap name
*
* @param string $name Gap name
* @access public
* @see $name
*/
  function setName($name = 0) {
    $this->name = $name;
  }
	
	function setShuffle($shuffle = 1) {
		$this->shuffle = $shuffle;
	}
}

?>