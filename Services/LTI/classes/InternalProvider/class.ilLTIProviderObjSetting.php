<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class illTIProviderObjectSetting
{
	/**
	 * @var ilLogger
	 */
	private $log = null;
	
	/**
	 * @var ilDBInterface
	 */
	private $db = null;
	
	private $obj_id = 0;
	private $consumer_id = 0;
	private $enabled = false;
	private $admin = false;
	private $tutor = false;
	private $member = false;
	
	/**
	 * Constructor
	 * @global type $DIC
	 */
	public function __construct($a_obj_id, $a_consumer_id)
	{
		global $DIC;
		
		$this->log = $DIC->logger()->lti();
		$this->db = $DIC->database();
		
		$this->obj_id = $a_obj_id;
		$this->consumer_id = $a_consumer_id;
		
		$this->read();
	}
	
	public function setEnabled($a_status)
	{
		$this->enabled = $a_status;
	}
	
	/**
	 * is enabled
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}
	
	public function enableAdminAssignment($a_enabled)
	{
		$this->admin = $a_enabled;
	}
	
	public function isAdminAssignmentEnabled()
	{
		return $this->admin;
	}
	
	public function enableTutorAssignment($a_enabled)
	{
		$this->tutor = $a_enabled;
	}
	
	public function isTutorAssignmentEnabled()
	{
		return $this->tutor;
	}
	
	public function enableMemberAssignment($a_enabled)
	{
		$this->member = $a_enabled;
	}
	
	public function isMemberAssignmentEnabled()
	{
		return $this->member;
	}
	
	/**
	 * Delete obj setting
	 */
	public function delete()
	{
		$query = 'DELETE FROM lti_int_provider_obj '.
			'WHERE obj_id = '.$this->db->quote($this->obj_id, 'integer').' '.
			'AND consumer_id = '.$this->db->quote($this->consumer_id, 'integer');
		$this->db->manipulate($query);
	}
	
	public function save()
	{
		$this->delete();
		
		$query = 'INSERT INTO lti_int_provider_obj '.
			'(obj_id,consumer_id,enabled,admin,tutor,member) VALUES( '.
			$this->db->quote($this->obj_id, 'integer').', '.
			$this->db->quote($this->consumer_id, 'integer').', '.
			$this->db->quote($this->isEnabled(),'integer').', '.
			$this->db->quote($this->isAdminAssignmentEnabled(), 'integer').', '.
			$this->db->quote($this->isTutorAssignmentEnabled(), 'integer').', '.
			$this->db->quote($this->isMemberAssignmentEnabled(), 'integer').
			' )';
		$this->db->manipulate($query);
	}

	/**
	 * Read object settings
	 * @return boolean
	 */
	protected function read()
	{
		if(!$this->obj_id)
		{
			return false;
		}
		
		$query = 'SELECT * FROM lti_int_provider_obj '.
			'WHERE obj_id = '.$this->db->quote($this->obj_id, 'integer').' '.
			'AND consumer_id = '.$this->db->quote($this->consumer_id, 'integer');
		
		$res = $this->db->query($query);
		while($row = $res->fetchObject())
		{
			$this->obj_id = $row->obj_id;
			$this->consumer_id = $row->consumer_id;
			$this->enabled = $row->enabled;
			$this->admin = $row->admin;
			$this->tutor = $row->tutor;
			$this->member = $row->member;
		}
	}
}
?>