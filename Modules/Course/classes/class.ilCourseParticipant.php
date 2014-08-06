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

include_once('./Services/Membership/classes/class.ilParticipant.php');

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ModulesCourse 
*/

class ilCourseParticipant extends ilParticipant
{
	const COMPONENT_NAME = 'Modules/Course';
	
	protected static $instances = array();
	
	/**
	 * Singleton constructor
	 *
	 * @access protected
	 * @param int obj_id of container
	 */
	public function __construct($a_obj_id,$a_usr_id)
	{
		$this->type = 'crs';
		
		$this->NOTIFY_DISMISS_SUBSCRIBER = 1;
		$this->NOTIFY_ACCEPT_SUBSCRIBER = 2;
		$this->NOTIFY_DISMISS_MEMBER = 3;
		$this->NOTIFY_BLOCK_MEMBER = 4;
		$this->NOTIFY_UNBLOCK_MEMBER = 5;
		$this->NOTIFY_ACCEPT_USER = 6;
		$this->NOTIFY_ADMINS = 7;
		$this->NOTIFY_STATUS_CHANGED = 8;
		$this->NOTIFY_SUBSCRIPTION_REQUEST = 9;
		
		$this->NOTIFY_REGISTERED = 10;
		$this->NOTIFY_UNSUBSCRIBE = 11;
		$this->NOTIFY_WAITING_LIST = 12; 
		
		parent::__construct(self::COMPONENT_NAME,$a_obj_id,$a_usr_id);
	}

	/**
	 * Get singleton instance
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 * @return ilCourseParticipant
	 */
	public static function _getInstanceByObjId($a_obj_id,$a_usr_id)
	{
		if(self::$instances[$a_obj_id][$a_usr_id])
		{
			return self::$instances[$a_obj_id][$a_usr_id];
		}
		return self::$instances[$a_obj_id][$a_usr_id] = new ilCourseParticipant($a_obj_id,$a_usr_id);
	}
	
}
?>