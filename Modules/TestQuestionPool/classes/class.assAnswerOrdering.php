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
* The random id of the answer
*
* @var integer
*/
	protected $random_id;
  
/**
* ASS_AnswerOrdering constructor
* 
* The constructor takes possible arguments an creates an instance of the ASS_AnswerOrdering object.
*
* @param string $answertext A string defining the answer text
* @param integer $random_id A random ID
* @access public
*/
	function ASS_AnswerOrdering (
		$answertext = "",
		$random_id = 0
	)
	{
		$this->ASS_AnswerSimple($answertext, 0, 0);
		$this->setRandomID($random_id);
	}
  
  
/**
* Returns the random ID of the answer
*
* @return integer Random ID
* @see $random_id
*/
	public function getRandomID() 
	{
		return $this->random_id;
	}

/**
* Sets the random ID of the answer
*
* @param integer $random_id A random integer value
* @see $random_id
*/
	public function setRandomID($random_id = 0) 
	{
		$this->random_id = $random_id;
	}
}

?>