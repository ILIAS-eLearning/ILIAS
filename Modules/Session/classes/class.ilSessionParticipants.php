<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Membership/classes/class.ilParticipants.php');
include_once './Modules/Session/classes/class.ilEventParticipants.php';

/**
 * Session participation handling.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesSession
 */
class ilSessionParticipants extends ilParticipants
{
	const COMPONENT_NAME = 'Modules/Session';
	
	protected static $instances = array();

	protected $event_part = null;
	
	/**
	 * Constructor
	 *
	 * @access protected
	 * @param int ref_id of object
	 */
	public function __construct($a_ref_id)
	{
		$this->event_part = new ilEventParticipants(ilObject::_lookupObjId($a_ref_id));
		parent::__construct(self::COMPONENT_NAME,$a_ref_id);
	}
	
	
	/**
	 * Get instance
	 * @param int $a_ref_id
	 * @return ilSessionParticipants
	 */
	public static function getInstance($a_ref_id)
	{
		if(self::$instances[$a_ref_id] instanceof self)
		{
			return self::$instances[$a_ref_id];
		}
		return self::$instances[$a_ref_id] = new self(self::COMPONENT_NAME, $a_ref_id);
	}
	
	/**
	 * 
	 * @return ilEventParticipants
	 */
	public function getEventParticipants()
	{
		return $this->event_part;
	}
	
	/**
	 * Static function to check if a user is a participant of the container object
	 *
	 * @access public
	 * @param int ref_id
	 * @param int user id
	 * @static
	 */
	public static function _isParticipant($a_ref_id,$a_usr_id)
	{
		$obj_id = ilObject::_lookupObjId($a_ref_id);
		return ilEventParticipants::_isRegistered($a_usr_id, $obj_id);
	}
	
	/**
	 * read Participants
	 */
	public function readParticipants()
	{
		$this->participants = $this->members = $this->getEventParticipants()->getRegistered();
	}
	
	/**
	 * read participant status
	 */
	public function readParticipantsStatus()
	{
		$this->participants_status = array();
		foreach($this->getMembers() as $mem_uid)
		{
			$this->participants_status[$mem_uid]['blocked'] = FALSE;
			$this->participants_status[$mem_uid]['notification'] = FALSE;
			$this->participants_status[$mem_uid]['passed'] = FALSE;
		}
	}
	
	/**
	 * Add user
	 * @param type $a_usr_id
	 */
	public function add($a_usr_id, $a_role = "")
	{
		$this->getEventParticipants()->register($a_usr_id);
	}
	
	/**
	 * Unregister user
	 * @param type $a_usr_id
	 */
	public function delete($a_usr_id)
	{
		$this->getEventParticipants()->unregister($a_usr_id);
	}
}
?>