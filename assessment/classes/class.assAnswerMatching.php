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

require_once "./assessment/classes/class.assAnswerSimple.php";

/**
* Class for simple answers
* 
* ASS_AnswerMatching is a class for matching answers used for example in matching questions.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assAnswerMatching.php
* @modulegroup   Assessment
*/
class ASS_AnswerMatching extends ASS_AnswerSimple {
/**
* Matching text
* 
* The matching text string of the ASS_AnswerMatching object
*
* @var string
*/
  var $matchingtext;
  
/**
* Unique identifier for the matching text
* 
* Unique identifier for the matching text
*
* @var integer
*/
  var $matchingtext_order;
  
/**
* ASS_AnswerMatching constructor
* 
* The constructor takes possible arguments an creates an instance of the ASS_AnswerMatching object.
*
* @param string $answertext A string defining the answer text
* @param double $points The number of points given for the selected answer
* @param integer $order A nonnegative value representing a possible display or sort order
* @param string $matchingtext A string defining the matching text for the answer text
* @access public
*/
  function ASS_AnswerMatching (
    $answertext = "",
    $points = 0.0,
    $order = 0,
    $matchingtext = "",
    $matchingtext_order = 0
  )
  {
    $this->ASS_AnswerSimple($answertext, $points, $order);
    $this->matchingtext = $matchingtext;
    $this->matchingtext_order = $matchingtext_order;
  }
  
/**
* Gets the matching text
* 
* Returns the matching text

* @return string matching text
* @access public
* @see $matchingtext
*/
  function get_matchingtext() {
    return $this->matchingtext;
  }
  
/**
* Gets the matching text identifier
* 
* Returns the matching text identifier

* @return integer matching text identifier
* @access public
* @see $matchingtext_order
*/
  function get_matchingtext_order() {
    return $this->matchingtext_order;
  }
  
/**
* Sets the matching text
* 
* Sets the matching text
*
* @param string $matchingtext The matching text
* @access public
* @see $matchingtext
*/
  function set_matchingtext($matchingtext = "") {
    $this->matchingtext = $matchingtext;
  }

/**
* Sets the matching text identifier
* 
* Sets the matching text identifier
*
* @param integer $matchingtext_order The matching text identifier
* @access public
* @see $matchingtext_order
*/
  function set_matchingtext_order($matchingtext_order = 0) {
    $this->matchingtext_order = $matchingtext_order;
  }

}

?>