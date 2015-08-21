<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings for LO courses
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOTestAssignments
{
	private static $instances = array();
	
	private $container_id = 0;
	private $assignments = array();
	
	private $settings = null;
	
	
	/**
	 * Constructor
	 * @param type $a_container_id
	 */
	public function __construct($a_container_id)
	{
		$this->container_id = $a_container_id;
		
		include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
		$this->settings = ilLOSettings::getInstanceByObjId($a_container_id);
		$this->readTestAssignments();
		
	}
	
	/**
	 * Get instance by container id
	 * @param type $a_container_id
	 * @return ilLOTestAssignments
	 */
	public static function getInstance($a_container_id)
	{
		if(self::$instances[$a_container_id])
		{
			return self::$instances[$a_container_id];
		}
		return self::$instances[$a_container_id] = new self($a_container_id);
	}
	
	public function getContainerId()
	{
		return $this->container_id;
	}
	
	/**
	 * get objective settings
	 * @return ilLOSettings
	 */
	public function getSettings()
	{
		return $this->settings;
	}

	/**
	 * Delete assignments by container id (obj_id of course)
	 * @global type $ilDB
	 * @param type $a_container_id
	 */
	public static function deleteByContainer($a_container_id)
	{
		global $ilDB;
		
		$query = 'DELETE FROM loc_tst_assignments '.
				'WHERE container_id = '.$ilDB->quote($a_container_id,'integer');
		$ilDB->manipulate($query);
	}
	
	public function getAssignmentsByType($a_type)
	{
		$by_type = array();
		foreach($this->assignments as $assignment)
		{
			if($assignment->getAssignmentType() == $a_type)
			{
				$by_type[] = $assignment;
			}
		}
		return $by_type;
	}

	/**
	 * 
	 * @param type $a_objective_id
	 * @param type $a_type
	 * @return int
	 */
	public function getTestByObjective($a_objective_id, $a_type)
	{
		switch($a_type)
		{
			case ilLOSettings::TYPE_TEST_INITIAL:
				if(!$this->getSettings()->hasSeparateInitialTests())
				{
					return $this->getSettings()->getInitialTest();
				}
				break;
				
			case ilLOSettings::TYPE_TEST_QUALIFIED:
				if(!$this->getSettings()->hasSeparateQualifiedTests())
				{
					return $this->getSettings()->getQualifiedTest();
				}
				break;
		}
		
		$assignment = $this->getAssignmentByObjective($a_objective_id, $a_type);
		if($assignment)
		{
			return $assignment->getTestRefId();
		}
		return 0;
	}
	
	/**
	 * Get assignment by objective
	 * @param type $a_objective_id
	 * @param type initial or final
	 * @return ilLOTestAssignment
	 */
	public function getAssignmentByObjective($a_objective_id, $a_type)
	{
		
		foreach($this->assignments as $assignment)
		{
			if(
				($assignment->getObjectiveId() == $a_objective_id) &&
				($assignment->getAssignmentType() == $a_type)
			)
			{
				return $assignment;
			}
		}
		return FALSE;
	}

	/**
	 * Read assignments
	 * @global type $ilDB
	 */
	protected function readTestAssignments()
	{
		global $ilDB;

		$query = 'SELECT assignment_id FROM loc_tst_assignments '.
				'WHERE container_id = '.$ilDB->quote($this->getContainerId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignment.php';
			$assignment = new ilLOTestAssignment($row->assignment_id);
			
			$this->assignments[] = $assignment;
		}
	}
}