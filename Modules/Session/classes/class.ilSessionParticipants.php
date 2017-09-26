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
	public static function _getInstanceByObjId($a_obj_id)
	{
		$refs = ilObject::_getAllReferences($a_obj_id);
		return self::getInstance(array_pop($refs));
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
		return self::$instances[$a_ref_id] = new self($a_ref_id);
	}
	
	/**
	 * Get event particpants object
	 * @return ilEventParticipants
	 */
	public function getEventParticipants()
	{
		return $this->event_part;
	}
	
	/**
	 * no last admin restrictions for sessions
	 * @param int[] $a_usr_ids
	 * @return boolean
	 */
	public function checkLastAdmin($a_usr_ids)
	{
		return false;
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
	 * Add user to session member role. Additionally the status registered or participated must be set manually
	 * @param int $a_usr_id
	 * @param int $a_role
	 */
	public function add($a_usr_id, $a_role = "")
	{
		if(parent::add($a_usr_id, $a_role))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Register user
	 * @param int $a_usr_id
	 * @return boolean
	 */
	public function register($a_usr_id)
	{
		$this->logger->debug('Registering user: ' . $a_usr_id. ' for session: ' . $this->getObjId());
		$this->add($a_usr_id, IL_SESS_MEMBER);
		// in any (already participant since status attended) case register user.
		$this->getEventParticipants()->register($a_usr_id);
		return true;
	}
	
	/**
	 * Unregister user
	 * @param int $a_usr_id
	 * @return boolean
	 */
	public function unregister($a_usr_id)
	{
		// participated users are not dropped from role
		if($this->getEventParticipants()->hasParticipated($a_usr_id))
		{
			$this->getEventParticipants()->unregister($a_usr_id);
			return true;
		}
		else
		{
			$this->delete($a_usr_id);
			$this->getEventParticipants()->unregister($a_usr_id);
			return true;
		}
		return false;
	}

}
?>