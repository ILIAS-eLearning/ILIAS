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

include_once('./Services/Membership/classes/class.ilWaitingList.php');

/**
* Waiting list for groups
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesGroup
*/

class ilGroupWaitingList extends ilWaitingList
{
	/**
	 * Add to waiting list and raise event
	 * @param int $a_usr_id
	 */
	public function addToList($a_usr_id)
	{
		global $ilAppEventHandler, $ilLog;
		
		if(!parent::addToList($a_usr_id))
		{
			return FALSE;
		}
		
		$ilLog->write(__METHOD__.': Raise new event: Modules/Group addToList');
		$ilAppEventHandler->raise(
				"Modules/Group", 
				'addToWaitingList', 
				array(
					'obj_id' => $this->getObjId(),
					'usr_id' => $a_usr_id
				)
			);
		return TRUE;
	}
	
}
?>