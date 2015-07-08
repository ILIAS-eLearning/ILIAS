<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Defines a system check task
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTask
{
	const STATUS_NOT_ATTEMPTED = 0;
	const STATUS_IN_PROGRESS = 1;
	const STATUS_COMPLETED = 2;
	const STATUS_FAILED = 3;
	
	
	
	private $id = 0;
	private $grp_id = 0;
	private $title = '';
	private $description = '';
	private $last_update = NULL;
	private $status = 0;
	
	
	/**
	 * Constructor
	 * @param type $a_id
	 */
	public function __construct($a_id)
	{
		$this->id = $a_id;
		$this->read();
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setGroupId($a_id)
	{
		$this->grp_id = $a_id;
	}
	
	public function getGroupId()
	{
		return $this->grp_id;
	}
	
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function setDescription($a_desc)
	{
		$this->description = $a_desc;
	}
	
	/**
	 * Get description
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}
	
	public function setComponentId($a_comp)
	{
		$this->component_id = $a_comp;
	}
	
	
	public function setLastUpdate(ilDateTime $a_update)
	{
		$this->last_update = $a_update;
	}
	
	/**
	 * Get last update date
	 * @return ilDateTime
	 */
	public function getLastUpdate()
	{
		if(!$this->last_update)
		{
			return $this->last_update = new ilDateTime();
		}
		return $this->last_update;
	}
	
	public function setStatus($a_status)
	{
		$this->status = $a_status;
	}
	
	/**
	 * Get status
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}
	
	/**
	 * Read group
	 */
	public function read()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return false;
		}
		
		$query = 'SELECT * FROM sysc_tasks '.
				'WHERE id = '.$ilDB->quote($this->getId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setGroupId($row->grp_id);
			$this->setTitle($row->title);
			$this->setDescription($row->description);
			$this->setLastUpdate(new ilDateTime($row->last_update,IL_CAL_DATETIME,'UTC'));
			$this->setStatus($row->status);
		}
		return true;
	}
	
	/**
	 * Create new group
	 */
	public function create()
	{
		global $ilDB;
		
		$this->id = $ilDB->nextId('sysyc_tasks');
		
		$query = 'INSERT INTO sysc_groups (id,grp_id,title,description,last_update,status) '.
				'VALUES ( '.
				$ilDB->quote($this->getId(),'integer').', '.
				$ilDB->quote($this->getGroupId(),'integer').', '.
				$ilDB->quote($this->getTitle(),'text').', '.
				$ilDB->quote($this->getDescription(),'text').', '.
				$ilDB->quote($this->getComponentId(),'text').', '.
				$ilDB->quote($this->getComponentType(),'text').', '.
				$ilDB->quote($this->getLastUpdate()->get(IL_CAL_DATETIME,'','UTC'),'timestamp').', '.
				$ilDB->quote($this->getStatus(),'integer').' '.
				')';
		$ilDB->manipulate($query);
		return $this->getId();
	}
}
?>
