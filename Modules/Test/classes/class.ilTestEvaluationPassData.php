<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilTestEvaluationPassData
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @defgroup ModulesTest Modules/Test
* @extends ilObject
*/

class ilTestEvaluationPassData
{
	/**
	* Answered questions
	*
	* @var array
	*/
	var $answeredQuestions;
	
	/**
	* Test pass
	*
	* @var integer
	*/
	var $pass;
	
	public function __sleep()
	{
		return array('answeredQuestions', 'pass');
	}

	/**
	* Constructor
	*
	* @access	public
	*/
	function ilTestEvaluationPassData()
	{
		$this->answeredQuestions = array();
	}
	
	function getPass()
	{
		return $this->pass;
	}
	
	function setPass($a_pass)
	{
		$this->pass = $a_pass;
	}
	
	function getAnsweredQuestions()
	{
		return $this->answeredQuestions;
	}
	
	function addAnsweredQuestion($question_id, $max_points, $reached_points, $sequence = NULL)
	{
		array_push($this->answeredQuestions, array("id" => $question_id, "points" => $max_points, "reached" => $reached_points, "sequence" => $sequence));
	}
	
	function &getAnsweredQuestion($index)
	{
		if (array_key_exists($index, $this->answeredQuestions))
		{
			return $this->answeredQuestions[$index];
		}
		else
		{
			return NULL;
		}
	}
	
	function &getAnsweredQuestionByQuestionId($question_id)
	{
		foreach ($this->answeredQuestions as $question)
		{
			if ($question["id"] == $question_id)
			{
				return $question;
			}
		}
		return NULL;
	}
	
	function getAnsweredQuestionCount()
	{
		return count($this->answeredQuestions);
	}
	
} // END ilTestEvaluationPassData

?>
