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
	* Test id
	*
	* @var object
	*/
	private $test;

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
	* Filter type
	*
	* @var string
	*/
	var $filterby;

	/**
	* Filter text
	*
	* @var string
	*/
	var $filtertext;
	
	/**
	*
	* @var integer
	*/
	var $datasets;

	public function __sleep()
	{
		return array('questionTitles', 'participants', 'statistics', 'filterby', 'filtertext', 'datasets', 'test');
	}

	/**
	* Constructor
	*
	* @access	public
	*/
	function ilTestEvaluationData($test = "")
	{
		$this->participants = array();
		$this->questionTitles = array();
		if (is_object($test)) $this->test =& $test;
		if (is_object($test))
		{
			$this->generateOverview();
		}
	}
	
	function generateOverview()
	{
		$this->participants = array();
		global $ilDB;
		include_once "./Modules/Test/classes/class.ilTestEvaluationPassData.php";
		include_once "./Modules/Test/classes/class.ilTestEvaluationUserData.php";
		$query = sprintf("SELECT usr_data.usr_id, usr_data.firstname, usr_data.lastname, usr_data.title, usr_data.login, " .
			"tst_pass_result.* FROM tst_pass_result, tst_active " .
			"LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id " .
			"WHERE tst_active.active_id = tst_pass_result.active_fi " .
			"AND tst_active.test_fi = %s " .
			"ORDER BY usr_data.lastname, usr_data.firstname, active_id, pass, TIMESTAMP",
			$ilDB->quote($this->getTest()->getTestId() . "")
		);
		$result = $ilDB->query($query);
		$pass = NULL;
		$checked = array();
		$thissets = 0;
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$thissets++;
			$remove = FALSE;
			if (!$this->participantExists($row["active_fi"]))
			{
				$this->addParticipant($row["active_fi"], new ilTestEvaluationUserData($this->getTest()->getPassScoring()));
				$this->getParticipant($row["active_fi"])->setName($this->getTest()->buildName($row["usr_id"], $row["firstname"], $row["lastname"], $row["title"]));
				$this->getParticipant($row["active_fi"])->setLogin($row["login"]);
				$this->getParticipant($row["active_fi"])->setUserID($row["usr_id"]);
			}
			if (!is_object($this->getParticipant($row["active_fi"])->getPass($row["pass"])))
			{
				$pass = new ilTestEvaluationPassData();
				$pass->setPass($row["pass"]);
				$this->getParticipant($row["active_fi"])->addPass($row["pass"], $pass);
			}
			$this->getParticipant($row["active_fi"])->getPass($row["pass"])->setReachedPoints($row["points"]);
			$this->getParticipant($row["active_fi"])->getPass($row["pass"])->setMaxPoints($row["maxpoints"]);
			$this->getParticipant($row["active_fi"])->getPass($row["pass"])->setQuestionCount($row["questioncount"]);
			$this->getParticipant($row["active_fi"])->getPass($row["pass"])->setNrOfAnsweredQuestions($row["answeredquestions"]);
			$this->getParticipant($row["active_fi"])->getPass($row["pass"])->setWorkingTime($row["workingtime"]);
		}
	}
	
	function getTest()
	{
		return $this->test;
	}
	
	function setTest($test)
	{
		$this->test =& $test;
	}
	
	function setDatasets($datasets)
	{
		$this->datasets = $datasets;
	}
	
	function getDatasets()
	{
		return $this->datasets;
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
		if (strlen($this->filterby) && strlen($this->filtertext))
		{
			$filteredParticipants = array();
			$courseids = array();
			$groupids = array();
			global $ilDB;
			switch ($this->filterby)
			{
				case "group":
					$ids = ilObject::_getIdsForTitle($this->filtertext, 'grp', true);
					$groupids = array_merge($groupids, $ids);
					break;
				case "course":
					$ids = ilObject::_getIdsForTitle($this->filtertext, 'crs', true);
					$courseids = array_merge($courseids, $ids);
					break;
			}
			foreach ($this->participants as $active_id => $participant)
			{
				$remove = FALSE;
				switch ($this->filterby)
				{
					case "name":
						if (!(strpos(strtolower($participant->getName()), strtolower($this->filtertext)) !== FALSE)) $remove = TRUE;
						break;
					case "group":
						include_once "./Services/Membership/classes/class.ilParticipants.php";
						$groups = ilParticipants::_getMembershipByType($participant->getUserID(), "grp");
						$foundfilter = FALSE;
						if (count(array_intersect($groupids, $groups))) $foundfilter = TRUE;
						if (!$foundfilter) $remove = TRUE;
						break;
					case "course":
						include_once "./Services/Membership/classes/class.ilParticipants.php";
						$courses = ilParticipants::_getMembershipByType($participant->getUserID(), "crs");
						$foundfilter = FALSE;
						if (count(array_intersect($courseids, $courses))) $foundfilter = TRUE;
						if (!$foundfilter) $remove = TRUE;
						break;
				}
				if (!$remove) $filteredParticipants[$active_id] = $participant;
			}
			return $filteredParticipants;
		}
		else
		{
			return $this->participants;
		}
	}
	
	function resetFilter()
	{
		$this->filterby = "";
		$this->filtertext = "";
	}
	
	/*
	* Set an output filter for getParticipants
	*
	* @param string $by name, course, group
	* @param string $text Filter text
	*/
	function setFilter($by, $text)
	{
		$this->filterby = $by;
		$this->filtertext = $text;
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
	
	function removeParticipant($active_id)
	{
		unset($this->participants[$active_id]);
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
