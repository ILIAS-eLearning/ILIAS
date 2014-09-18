<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Stores current objective, questions and max points
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOTestRun
{
	protected $container_id = 0; 
	protected $user_id = 0;
	protected $test_id = 0;
	protected $objective_id = 0;
	
	protected $max_points = 0;
	protected $questions = array();
	
	
	public function __construct($a_crs_id, $a_user_id, $a_test_id, $a_objective_id)
	{
		$this->container_id = $a_crs_id;
		$this->user_id = $a_user_id;
		$this->test_id = $a_test_id;
		$this->objective_id = $a_objective_id;
		
		$this->read();
	}
	
	/**
	 * 
	 * @global type $ilDB
	 * @param type $a_test_obj_id
	 * @param type $a_objective_id
	 * @param type $a_user_id
	 * @return boolean
	 */
	public static function lookupRunExistsForObjective($a_test_id, $a_objective_id, $a_user_id)
	{
		global $ilDB;
		
		$query = 'SELECT * FROM loc_tst_run '.
				'WHERE test_id = '.$ilDB->quote($a_test_id,'integer').' '.
				'AND objective_id = '.$ilDB->quote($a_objective_id,'integer').' '.
				'AND user_id = '.$ilDB->quote($a_user_id,'integer');
		$res = $ilDB->query($query);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		return false;
	}
	
	public static function deleteRun($a_container_id, $a_user_id, $a_test_id)
	{
		global $ilDB;
		
		$query = 'DELETE FROM loc_tst_run '.
				'WHERE container_id = ' . $ilDB->quote($a_container_id,'integer').' '.
				'AND user_id = '.$ilDB->quote($a_user_id,'integer').' '.
				'AND test_id = '.$ilDB->quote($a_test_id,'integer').' ';
		$ilDB->manipulate($query);
	}
	
	public static function lookupObjectives($a_container_id, $a_user_id, $a_test_id)
	{
		global $ilDB;
		
		$query = 'SELECT objective_id FROM loc_tst_run '.
				'WHERE container_id = '.$ilDB->quote($a_container_id,'integer').' '.
				'AND user_id = '.$ilDB->quote($a_user_id,'integer').' '.
				'AND test_id = '.$ilDB->quote($a_test_id,'integer');
		$GLOBALS['ilLog']->write(__METHOD__.': '.$query);
		$res = $ilDB->query($query);
		$objectives = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objectives[] = $row->objective_id;
		}
		return $objectives;
	}
	
	/**
	 * 
	 * @return ilLOTestRun[] 
	 */
	public static function getRun($a_container_id, $a_user_id, $a_test_id)
	{
		global $ilDB;
		
		$query = 'SELECT objective_id FROM loc_tst_run '.
				'WHERE container_id = ' . $ilDB->quote($a_container_id,'integer').' '.
				'AND user_id = '.$ilDB->quote($a_user_id,'integer').' '.
				'AND test_id = '.$ilDB->quote($a_test_id,'integer').' ';
		$res = $ilDB->query($query);
		
		$run = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$run[] = new ilLOTestRun($a_container_id, $a_user_id, $a_test_id, $row->objective_id);
		}
		return $run;
	}

	public function getContainerId()
	{
		return $this->container_id;
	}
	
	public function getUserId()
	{
		return $this->user_id;
	}
	
	public function getTestId()
	{
		return $this->test_id;
	}
	
	public function getObjectiveId()
	{
		return $this->objective_id;
	}
	
	public function getMaxPoints()
	{
		return $this->max_points;
	}
	
	public function setMaxPoints($a_points)
	{
		$this->max_points = $a_points;
	}
	
	public function getQuestions()
	{
		return (array) $this->questions;
	}
	
	public function clearQuestions()
	{
		$this->questions = array();
	}
	
	public function addQuestion($a_id)
	{
		$this->questions[$a_id] = 0;
	}
	
	public function questionExists($a_question_id)
	{
		return array_key_exists($a_question_id,(array) $this->questions);
	}
	
	public function setQuestionResult($a_qst_id, $a_points)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': '.$a_qst_id.' '.$a_points);
		
		
		if($this->questions[$a_qst_id] < $a_points)
		{
			$this->questions[$a_qst_id] = $a_points;
		}
		$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($this->questions,true));
	}
	
	/**
	 * Get result for objective run
	 */
	public function getResult()
	{
		$sum_points = 0;
		foreach($this->questions as $qid => $points)
		{
			$sum_points += (int) $points;
		}
		
		$percentage = 
			($this->getMaxPoints() > 0) ?
			($sum_points / $this->getMaxPoints() * 100) :
			100;
		
		return array(
			'max' => $this->getMaxPoints(),
			'reached' => $sum_points,
			'percentage' => $percentage
		);
	}

	
	/**
	 * Delete all entries
	 * @global type $ilDB
	 */
	public function delete()
	{
		global $ilDB;
		
		$query = 'DELETE FROM loc_tst_run '.
				'WHERE container_id = ' . $ilDB->quote($this->getContainerId(),'integer').' '.
				'AND user_id = '.$ilDB->quote($this->getUserId(),'integer').' '.
				'AND test_id = '.$ilDB->quote($this->getTestId(),'integer').' '.
				'AND objective_id = '.$ilDB->quote($this->getObjectiveId(),'integer');
		$ilDB->manipulate($query);
	}
	
	public function create()
	{
		global $ilDB;
		
		$query = 'INSERT INTO loc_tst_run '.
				'(container_id, user_id, test_id, objective_id,max_points,questions) '.
				'VALUES( '.
				$ilDB->quote($this->getContainerId(),'integer').', '.
				$ilDB->quote($this->getUserId(),'integer').', '.
				$ilDB->quote($this->getTestId(),'integer').', '.
				$ilDB->quote($this->getObjectiveId(),'integer').', '.
				$ilDB->quote($this->getMaxPoints(),'integer').', '.
				$ilDB->quote(serialize($this->getQuestions()),'text').' '.
				')';
		$ilDB->manipulate($query);
	}
	
	public function update()
	{
		global $ilDB;
		
		$query = 'UPDATE loc_tst_run SET '.
				'max_points = '.$ilDB->quote($this->getMaxPoints(),'integer').', '.
				'questions = '.$ilDB->quote(serialize($this->getQuestions()),'text').' '.
				'WHERE container_id = '.$ilDB->quote($this->container_id,'integer').' '.
				'AND user_id = '.$ilDB->quote($this->getUserId(),'integer').' '.
				'AND test_id = '.$ilDB->quote($this->getTestId(),'integer').' '.
				'AND objective_id = '.$ilDB->quote($this->getObjectiveId(),'integer').' ';
		$ilDB->manipulate($query);
	}
	
	/**
	 * 
	 * @global type $ilDB
	 */
	public function read()
	{
		global $ilDB;
		
		$query = 'SELECT * FROM loc_tst_run '.
				'WHERE container_id = ' . $ilDB->quote($this->getContainerId(),'integer').' '.
				'AND user_id = '.$ilDB->quote($this->getUserId(),'integer').' '.
				'AND test_id = '.$ilDB->quote($this->getTestId(),'integer').' '.
				'AND objective_id = '.$ilDB->quote($this->getObjectiveId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->max_points = $row->max_points;
			if($row->questions)
			{
				$this->questions = unserialize($row->questions);
			}
		}
				
	}
}
?>
