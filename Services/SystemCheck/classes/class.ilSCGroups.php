<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/SystemCheck/classes/class.ilSCGroup.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCGroups 
{

	/**
	 * @var ilSCGroup
	 */
	private static $instance = null;
	
	private $groups = array();
	
	/**
	 * Singleton constructor
	 */
	private function __construct()
	{
		$this->read();
	}
	
	/**
	 * Get singleton instance
	 * @return ilSCGroups
	 */
	public static function getInstance()
	{
		if(self::$instance == NULL)
		{
			return self::$instance = new self();
		}
		return self::$instance;
	}
	
	
	/**
	 * Get groups
	 * @return ilSCGroup[]
	 */
	public function getGroups()
	{
		return (array) $this->groups;
	}
	
	/**
	 * read groups
	 */
	protected function read()
	{
		global $ilDB;
		
		$query = 'SELECT id FROM sysc_groups '.
				'ORDER BY id ';
		$res = $ilDB->query($query);
		
		$this->groups = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->groups[] = new ilSCGroup($row->id);
		}
	}
}
?>
