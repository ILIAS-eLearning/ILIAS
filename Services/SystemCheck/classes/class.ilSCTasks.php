<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/SystemCheck/classes/class.ilSCTask.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTasks
{

	/**
	 * @var ilSCGroup
	 */
	private static $instances = array();
	
	private $grp_id = 0;
	private $tasks = array();
	
	/**
	 * Singleton constructor
	 */
	private function __construct($a_grp_id)
	{
		$this->grp_id = $a_grp_id;
		$this->read();
	}
	
	/**
	 * Get singleton instance
	 * @return ilSCTasks
	 */
	public static function getInstanceByGroupId($a_group_id)
	{
		if(!array_key_exists($a_group_id, self::$instances))
		{
			return self::$instances[$a_group_id] = new self($a_group_id);
		}
		return self::$instances[$a_group_id];
	}
	
	/**
	 * Get groups
	 * @return ilSCGroup[]
	 */
	public function getTasks()
	{
		return (array) $this->tasks;
	}
	
	/**
	 * read groups
	 */
	protected function read()
	{
		global $ilDB;
		
		$query = 'SELECT id FROM sysc_tasks '.
				'ORDER BY id ';
		$res = $ilDB->query($query);
		
		$this->tasks = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->tasks[] = new ilSCTask($row->id);
		}
	}
}
?>
