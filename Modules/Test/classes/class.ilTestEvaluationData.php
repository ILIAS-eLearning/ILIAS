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
		return array('questionTitles', 'participants', 'statistics', 'filterby', 'filtertext', 'datasets');
	}

	/**
	* Constructor
	*
	* @access	public
	*/
	function ilTestEvaluationData()
	{
		$this->participants = array();
		$this->questionTitles = array();
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

	private function getMembershipByType($a_usr_id,$a_type)
	{
		global $ilDB;
		
		$query = "SELECT DISTINCT obd.obj_id,obr.ref_id FROM rbac_ua AS ua ".
			"JOIN rbac_fa AS fa ON ua.rol_id = fa.rol_id ".
			"JOIN tree AS t1 ON t1.child = fa.parent ".
			"JOIN object_reference AS obr ON t1.parent = obr.ref_id ".
			"JOIN object_data AS obd ON obr.obj_id = obd.obj_id ".
			"WHERE obd.type = ".$ilDB->quote($a_type)." ".
			"AND fa.assign = 'y' ".
			"AND ua.usr_id = ".$ilDB->quote($a_usr_id)." ";
		$res = $ilDB->query($query);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ref_ids[] = $row->obj_id;
		}
		
		return $ref_ids ? $ref_ids : array();			
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
					$query = sprintf("SELECT obj_id FROM object_data WHERE type = 'grp' AND title LIKE %s",
						$ilDB->quote("%$filtertext%")
					);
					$result = $ilDB->query($query);
					while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
					{
						array_push($groupids, $row["obj_id"]);
					}
					break;
				case "course":
					$query = sprintf("SELECT obj_id FROM object_data WHERE type = 'crs' AND title LIKE %s",
						$ilDB->quote("%$filtertext%")
					);
					$result = $ilDB->query($query);
					while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
					{
						array_push($courseids, $row["obj_id"]);
					}
					break;
			}
			foreach ($this->participants as $active_id => $participant)
			{
				$remove = FALSE;
				switch ($this->filterby)
				{
					case "name":
						if (strpos(strtolower($participant->getName()), strtolower($this->filtertext)) !== FALSE) $remove = TRUE;
						break;
					case "group":
						$groups = $this->getMembershipByType($participant->getUserID(), "grp");
						$foundfilter = FALSE;
						if (count(array_intersect($groupids, $groups))) $foundfilter = TRUE;
						if (!$foundfilter) $remove = TRUE;
						break;
					case "course":
						$courses = $this->getMembershipByType($participant->getUserID(), "crs");
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
