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

define ("BOOLEAN_NO_PREFIX", 0);
define ("BOOLEAN_NOT_PREFIX", 1);

/**
* Class for true/false or yes/no answers
* 
* AnswerblockAnswer is a class encapsulating answerblock answers in enhanced question mode
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.assAnswerblockAnswer.php
* @modulegroup   Assessment
*/
class AnswerblockAnswer {
/**
* order of the answerblock answer
*
* order of the answerblock answer
*
* @var integer
*/
  var $order;

/**
* The answer id of the answerblock answer
*
* The answer id of the answerblock answer
*
* @var integer
*/
  var $answer_id;

/**
* The boolean prefix of the answerblock answer
*
* The boolean prefix of the answerblock answer
*
* @var integer
*/
  var $boolean_prefix;

/**
* AnswerblockAnswer constructor
* 
* The constructor takes possible arguments an creates an instance of the AnswerblockAnswer object.
*
* @param integer $boolean_prefix The id of the question containing the answerblock
* @access public
*/
  function AnswerblockAnswer ()
  {
		$this->order = 0;
		$this->answer_id = -1;
		$this->boolean_prefix = BOOLEAN_NO_PREFIX;
  }
  
  
/**
* Sets the order of the answerblock answer
*
* Sets the order of the answerblock answer

* @param integer $order answerblock answer order
* @access public
* @see $order
*/
  function setOrder($order = 0) 
	{
    $this->order = $order;
  }


/**
* Gets the order of the answerblock answer
*
* Gets the order of the answerblock answer

* @return integer answerblock answer order
* @access public
* @see $order
*/
  function getOrder() 
	{
    return $this->order;
  }

/**
* Sets the boolean prefix of the answerblock answer
*
* Sets the boolean prefix of the answerblock answer

* @param integer $boolean_prefix boolean prefix
* @access public
* @see $boolean_prefix
*/
  function setBooleanPrefix($boolean_prefix = BOOLEAN_NO_PREFIX) 
	{
    $this->boolean_prefix = $boolean_prefix;
  }


/**
* Gets the boolean prefix of the answerblock answer
*
* Gets the boolean prefix of the answerblock answer

* @return integer boolean prefix
* @access public
* @see $boolean_prefix
*/
  function getBooleanPrefix() 
	{
    return $this->boolean_prefix;
  }

/**
* Sets the answerblock answer id
*
* Sets the answerblock answer id

* @param integer $answer_id answerblock answer id
* @access public
* @see $answer_id
*/
  function setAnswerId($answer_id = -1) 
	{
    $this->answer_id = $answer_id;
  }


/**
* Gets the answerblock answer id
*
* Gets the answerblock answer id

* @return integer answerblock answer id
* @access public
* @see $answer_id
*/
  function getAnswerId() 
	{
    return $this->answer_id;
  }

}

?>