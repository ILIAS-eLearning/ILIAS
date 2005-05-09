<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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


/**
* Class ilObjTestAccess
*
* This class contains methods that check object specific conditions
* for accessing test objects.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package AccessControl
*/
class ilObjTestAccess extends ilObjectAccess
{
	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here.
	*
	* @param	string		$a_cmd		command (same as in rbac)
	* @param	int			$a_ref_id	reference id
	* @param	int			$a_obj_id	object id
	* @param	int			$a_user_id	user id (if not provided, current user is taken)
	*
	* @return	mixed		true, if everything is ok, message (string) when
	*						access is not granted
	*/
	function _checkAccess($a_cmd, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		return true;
	}

	/**
	* check condition
	*
	* this method is called by ilConditionHandler
	*/
	function _checkCondition($a_obj_id, $a_operator, $a_value)
	{
		global $ilias;

		switch($a_operator)
		{
			case 'passed':
				$result = ilObjTest::_getTestResult($ilias->account->getId(), $a_exc_id);
				if ($result["test"]["passed"])
				{
					return true;
				}
				else
				{
					return false;
				}
				break;

			case 'finished':
				return ilObjTest::_hasFinished($ilias->account->getId(),$a_exc_id);

			case 'not_finished':
				return !ilObjTest::_hasFinished($ilias->account->getId(),$a_exc_id);

			default:
				return true;
		}
		return true;
	}

}

?>