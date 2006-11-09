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
* Class for ordering question answers
* 
* ASS_AnswerOrdering is a class for ordering question answers used in ordering questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @see ASS_AnswerSimple
*/
class ASS_AnswerOrdering extends ASS_AnswerSimple {
/**
* The order of the correct solution
* 
* An integer value indicating the order of the correct solution. This value must be unique in a set of
* ASS_AnwerOrdering objects use by an ordering question.
*
* @var integer
*/
  var $solution_order;
  
/**
* ASS_AnswerOrdering constructor
* 
* The constructor takes possible arguments an creates an instance of the ASS_AnswerOrdering object.
*
* @param string $answertext A string defining the answer text
* @param double $points The number of points given for the selected answer
* @param boolean $correctness A boolean value indicating the correctness of the answer
* @param integer $order A nonnegative value representing a possible display or sort order
* @param integer $solution_order An integer value representing the correct order of that answer in the solution of a question
* @access public
*/
  function ASS_AnswerOrdering (
    $answertext = "",
    $points = 0.0,
    $order = 0,
    $solution_order = 0
  )
  {
    $this->ASS_AnswerSimple($answertext, $points, $order);
    $this->solution_order = $solution_order;
  }
  
  
/**
* Gets the solution order
* 
* Returns the solution order of the answer

* @return integer The solution order value
* @access public
* @see $solution_order
*/
  function getSolutionOrder() {
    return $this->solution_order;
  }
  
/**
* Sets the solution order
* 
* Sets the solution order of the answer using an integer value
*
* @param integer $solution_order An integer value representing the correct order of that answer in the solution of a question
* @access public
* @see $solution_order
*/
  function setSolutionOrder($solution_order = 0) {
    $this->solution_order = $solution_order;
  }
}

?>