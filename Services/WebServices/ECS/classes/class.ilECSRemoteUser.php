<?php

/**
 * Storage of ecs remote user
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSRemoteUser
{
	private $eru_id = 0;
	private $sid = 0;
	private $mid = 0;
	private $usr_id = 0;
	private $remote_usr_id = '';
	
	
	/**
	 * Constructor
	 */
	public function __construct($a_eru_id = 0)
	{
		$this->eru_id = $a_eru_id;
		$this->read();
	}
	
	/**
	 * Get instance for usr_id
	 * @param type $a_usr_id
	 * @return \self|null
	 */
	public static function factory($a_usr_id)
	{
		global $ilDB;
		
		$query = 'SELECT eru_id FROM ecs_remote_user '.
				'WHERE usr_id = '.$ilDB->quote($a_usr_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return new self($row->eru_id);
		}
		return null;
	}
	
	/**
	 * Check if entry exists for user
	 */
	public function exists()
	{
		global $ilDB;
		
		$query = 'SELECT eru_id FROM ecs_remote_user '.
				'WHERE sid = '.$ilDB->quote($this->getServerId(),'integer').' '.
				'AND mid = '.$ilDB->quote($this->getMid(),'integer').' '.
				'AND usr_id = '.$ilDB->quote($this->getUserId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return (bool) $row->eru_id;
		}
		return false;
	}
	
	
	public function getId()
	{
		return $this->eru_id;
	}
	
	public function setServerId($a_sid)
	{
		$this->sid = $a_sid;
	}
	
	public function getServerId()
	{
		return $this->sid;
	}
	
	public function setMid($a_mid)
	{
		$this->mid = $a_mid;
	}
	
	public function getMid()
	{
		return $this->mid;
	}
	
	
	public function setUserId($a_usr_id)
	{
		$this->usr_id = $a_usr_id;
	}
	
	public function getUserId()
	{
		return $this->usr_id;
	}
	
	public function setRemoteUserId($a_remote_id)
	{
		$this->remote_usr_id = $a_remote_id;
	}
	
	public function getRemoteUserId()
	{
		return $this->remote_usr_id;
	}
	
	/**
	 * Update remote user entry
	 * @return boolean
	 */
	public function update()
	{
		$query = 'UPDATE ecs_remote_user SET '.
				'sid = '.$GLOBALS['ilDB']->quote($this->getServerId(),'integer').', '.
				'mid = '.$GLOBALS['ilDB']->quote($this->getMid(),'integer').', '.
				'usr_id = '.$GLOBALS['ilDB']->quote($this->getUserId(),'text').', '.
				'remote_usr_id = '.$GLOBALS['ilDB']->quote($this->getRemoteUserId(),'text').' '.
				'WHERE eru_id = '.$GLOBALS['ilDB']->quote($this->getId());
		$GLOBALS['ilDB']->manipulate($query);
		return true;
	}
	
	/**
	 * Create nerw remote user entry
	 */
	public function create()
	{
		
		$next_id = $GLOBALS['ilDB']->nextId('ecs_remote_user');
		$query = 'INSERT INTO ecs_remote_user (eru_id, sid, mid, usr_id, remote_usr_id) '.
				'VALUES( '.
				$GLOBALS['ilDB']->quote($next_id).', '.
				$GLOBALS['ilDB']->quote($this->getServerId(),'integer').', '.
				$GLOBALS['ilDB']->quote($this->getMid(),'integer').', '.
				$GLOBALS['ilDB']->quote($this->getUserId(),'text').', '.
				$GLOBALS['ilDB']->quote($this->getRemoteUserId(),'text').' '.
				')';
		$GLOBALS['ilDB']->manipulate($query);
	}
	
	/**
	 * Read data set
	 * @return boolean
	 */
	protected function read()
	{
		if(!$this->getId())
		{
			return false;
		}
		
		$query = 'SELECT * FROM ecs_remote_user '.
				'WHERE eru_id = '.$GLOBALS['ilDB']->quote($this->getId(),'integer');
		$res = $GLOBALS['ilDB']->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setServerId($row->sid);
			$this->setMid($row->mid);
			$this->setUserId($row->usr_id);
			$this->setRemoteUserId($row->remote_usr_id);
		}
	}
	
}
?>
