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

/**
* Class for enhanced mode answerblocks
* 
* EnhancedAnswerblock is a class encapsulating answerblocks in enhanced question mode
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assEnhancedAnswerblock.php
* @modulegroup   Assessment
*/
class EnhancedAnswerblock {
/**
* database id of the answerblock
*
* database id of the answerblock
*
* @var integer
*/
  var $id;

/**
* The index counter of the answerblock
*
* The index counter of the answerblock
*
* @var integer
*/
  var $answerblock_index;

/**
* The database id of the question containing the answerblock
*
* The database id of the question containing the answerblock
*
* @var integer
*/
  var $question_id;

/**
* The points for the answerblock
*
* The points for the answerblock
*
* @var double
*/
  var $points;
  
/**
* An internal feedback link
*
* An internal feedback link
*
* @var string
*/
  var $feedback;

/**
* An array containing the combined answers for the answerblock
*
* An array containing the combined answers for the answerblock
*
* @var array
*/
  var $connections;

/**
* The subquestion index, if you have more than one question (i.e. close tests)
*
* The subquestion index, if you have more than one question (i.e. close tests)
*
* @var integer
*/
  var $subquestion_index;
  
/**
* EnhancedAnswerblock constructor
* 
* The constructor takes possible arguments an creates an instance of the EnhancedAnswerblock object.
*
* @param integer $question_id The id of the question containing the answerblock
* @access public
*/
  function EnhancedAnswerblock (
    $question_id = -1
  )
  {
		$this->question_id = $question_id;
		$this->subquestion_index = 0;
		$this->connections = array();
		$this->feedback = "";
		$this->points = 0;
		$this->answerblock_index = 0;
  }
  
  
/**
* Sets the database id of the answerblock
*
* Sets the database id of the answerblock

* @param integer $id database id
* @access public
* @see $id
*/
  function setId($id = -1) 
	{
    $this->id = $id;
  }


/**
* Gets the database id of the answerblock
*
* Gets the database id of the answerblock

* @return integer database id
* @access public
* @see $id
*/
  function getId() 
	{
    return $this->id;
  }

/**
* Sets the question id of the answerblock
*
* Sets the question id of the answerblock

* @param integer $question_id question id
* @access public
* @see $question_id
*/
  function setQuestionId($id = -1) 
	{
    $this->question_id = $question_id;
  }


/**
* Gets the question id of the answerblock
*
* Gets the question id of the answerblock

* @return integer question id
* @access public
* @see $question_id
*/
  function getQuestionId() 
	{
    return $this->question_id;
  }

/**
* Sets the answerblock index of the answerblock
*
* Sets the answerblock index of the answerblock

* @param integer $answerblock_index answerblock index
* @access public
* @see $answerblock_index
*/
  function setAnswerblockIndex($answerblock_index = -1) 
	{
    $this->answerblock_index = $answerblock_index;
  }


/**
* Gets the answerblock index of the answerblock
*
* Gets the answerblock index of the answerblock

* @return integer answerblock index
* @access public
* @see $answerblock_index
*/
  function getAnswerblockIndex() 
	{
    return $this->answerblock_index;
  }

/**
* Sets the points of the answerblock
*
* Sets the points of the answerblock

* @param integer $point points
* @access public
* @see $points
*/
  function setPoints($points = 0) 
	{
    $this->points = $points;
  }


/**
* Gets the points of the answerblock
*
* Gets the points of the answerblock

* @return integer points
* @access public
* @see $points
*/
  function getPoints() 
	{
    return $this->points;
  }

/**
* Sets the subquestion index
*
* Sets the subquestion index

* @param integer $subquestion_index subquestion index
* @access public
* @see $subquestion_index
*/
  function setSubquestionIndex($subquestion_index = 0) 
	{
    $this->subquestion_index = $subquestion_index;
  }


/**
* Gets the subquestion index
*
* Gets the subquestion index

* @return integer subquestion index
* @access public
* @see $subquestion_index
*/
  function getSubquestionIndex() 
	{
    return $this->subquestion_index;
  }
	
/**
* Sets the feedback of the answerblock
*
* Sets the feedback of the answerblock

* @param integer $feedback feedback
* @access public
* @see $feedback
*/
  function setFeedback($feedback = 0) 
	{
    $this->feedback = $feedback;
  }


/**
* Gets the feedback of the answerblock
*
* Gets the feedback of the answerblock

* @return integer feedback
* @access public
* @see $feedback
*/
  function getFeedback() 
	{
    return $this->feedback;
  }

/**
* Adds a new connection to the answerblock
*
* Adds a new connection to the answerblock

* @access public
* @see $feedback
*/
  function addConnection($answer_id, $order, $boolean_prefix) 
	{
		$connection = new AnswerblockAnswer();
		$connection->setAnswerId($answer_id);
		$connection->setOrder($order);
		$connection->setBooleanPrefix($boolean_prefix);
		$this->connections[$order] = $connection;
  }

/**
* Gets an answerblock answer of a given position
*
* Gets an answerblock answer of a given position
* @param integer $order Order of the answerblock answer
* @return object AnswerblockAnswer object of the given position
* @access public
* @see $feedback
*/
  function getConnection($order) 
	{
		return $this->connections[$order];
  }
	
/**
* Deletes all existing answerblock answers
*
* Deletes all existing answerblock answers

* @access public
* @see $connections
*/
	function flushConnections()
	{
		$this->connections = array();
	}

}

?>