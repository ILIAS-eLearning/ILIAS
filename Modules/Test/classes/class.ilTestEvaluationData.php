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
* Class ilTestEvaluationData
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @defgroup ModulesTest Modules/Test
* @extends ilObject
*/

class ilTestEvaluationData
{
	/**
	* Question titles
	*
	* @var array
	*/
	var $questionTitles;

	/**
	* Participants
	*
	* @var array
	*/
	var $participants;

	/**
	* Statistical data
	*
	* @var object
	*/
	var $statistics;

	/**
	* Constructor
	*
	* @access	public
	*/
	function ilTestEvaluationData($test_id)
	{
		$this->participants = array();
		$this->questionTitles = array();
	}
	
	function addQuestionTitle($question_id, $question_title)
	{
		$this->questionTitles[$question_id] = $question_title;
	}
	
	function getQuestionTitles()
	{
		return $this->questionTitles;
	}
	
	function getQuestionTitle($question_id)
	{
		if (array_key_exists($question_id, $this->questionTitles))
		{
			return $this->questionTitles[$question_id];
		}
		else
		{
			return "";
		}
	}
	
	function calculateStatistics()
	{
		include_once "./Modules/Test/classes/class.ilTestStatistics.php";
		$this->statistics = new ilTestStatistics($this);
	}

	function getParticipants()
	{
		return $this->participants;
	}
	
	function addParticipant($active_id, $participant)
	{
		$this->participants[$active_id] = $participant;
	}
	
	function &getParticipant($active_id)
	{
		return $this->participants[$active_id];
	}
	
	function participantExists($active_id)
	{
		return array_key_exists($active_id, $this->participants);
	}
	
	function &getStatistics()
	{
		return $this->statistics;
	}

	function getParticipantIds()
	{
		return array_keys($this->participants);
	}
} // END ilTestEvaluationData

?>
