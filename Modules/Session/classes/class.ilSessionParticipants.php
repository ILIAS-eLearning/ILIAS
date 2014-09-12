<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('./Services/Membership/classes/class.ilParticipants.php');
include_once './Modules/Session/classes/class.ilEventParticipants.php';

/**
* 
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesGroup
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
	 * @param int obj_id of container
	 */
	public function __construct($a_obj_id)
	{
		$this->type = 'sess';
		$this->event_part = new ilEventParticipants($a_obj_id);
		parent::__construct(self::COMPONENT_NAME,$a_obj_id);
		
	}
	
	/**
	 * Get singleton instance
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 * @return object ilGroupParticipants
	 */
	public static function _getInstanceByObjId($a_obj_id)
	{
		if(isset(self::$instances[$a_obj_id]) and self::$instances[$a_obj_id])
		{
			return self::$instances[$a_obj_id];
		}
		return self::$instances[$a_obj_id] = new ilSessionParticipants($a_obj_id);
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
	public function add($a_usr_id)
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