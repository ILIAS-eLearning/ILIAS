<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConsultationHourGroup 
{

	private $group_id = 0;
	private $usr_id = 0;
	private $num_assignments = 1;
	private $title = '';
	
	/**
	 * Constructor
	 * @param int $a_group_id
	 */
	public function __construct($a_group_id = 0)
	{
		$this->group_id = $a_group_id;
		$this->read();
	}
	
	public function getGroupId()
	{
		return $this->group_id;
	}
	
	public function setUserId($a_id)
	{
		$this->usr_id = $a_id;
	}
	
	public function getUserId()
	{
		return $this->usr_id;
	}
	
	public function setMaxAssignments($a_num)
	{
		$this->num_assignments = $a_num;
	}
	
	public function getMaxAssignments()
	{
		return $this->num_assignments;
	}
	
	
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
		
	/**
	 * Save new group to db
	 * @global type $ilDB
	 * @return int new group id
	 */
	public function save()
	{
		global $ilDB;
		
		$this->group_id = $ilDB->nextId('cal_ch_group');
		$query = 'INSERT INTO cal_ch_group (grp_id,usr_id,multiple_assignments,title) '.
				'VALUES ( '.
				$ilDB->quote($this->getGroupId(),'integer').', '.
				$ilDB->quote($this->getUserId(),'integer').', '.
				$ilDB->quote($this->getMaxAssignments(),'integer').', '.
				$ilDB->quote($this->getTitle(),'text').
				')';
		$ilDB->manipulate($query);
		return $this->getGroupId();
	}
	
	/**
	 * Update group information
	 * @global type $ilDB
	 * @return boolean
	 */
	public function update()
	{
		global $ilDB;
		
		$query = 'UPDATE cal_ch_group SET '.
				'usr_id = '.$ilDB->quote($this->getUserId(),'integer').', '.
				'multiple_assignments = '.$ilDB->quote($this->getMaxAssignments(),'integer').', '.
				'title = '.$ilDB->quote($this->getTitle(),'text').' '.
				'WHERE grp_id = '.$ilDB->quote($this->getGroupId(),'integer');
		$ilDB->manipulate($query);
		return true;
	}
	
	public function delete()
	{
		global $ilDB;
		
		$query = 'DELETE FROM cal_ch_group '.
				'WHERE grp_id = '.$ilDB->quote($this->getGroupId(),'integer');
		$ilDB->manipulate($query);
		
		include_once './Services/Booking/classes/class.ilBookingEntry.php';
		ilBookingEntry::resetGroup($this->getGroupId());
	}
	
	
	/**
	 * 
	 * @global type $ilDB
	 * @return boolean
	 */
	protected function read()
	{
		global $ilDB;
		
		if(!$this->getGroupId())
		{
			return false;
		}
		$query = 'SELECT * FROM cal_ch_group '.
				'WHERE grp_id = '.$ilDB->quote($this->getGroupId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setUserId($row->usr_id);
			$this->setTitle($row->title);
			$this->setMaxAssignments($row->multiple_assignments);
		}
		return true;
	}
	
}
?>
