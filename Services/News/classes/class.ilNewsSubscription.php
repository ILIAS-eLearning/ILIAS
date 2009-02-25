<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* This class handles news subscriptions of users.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesNews
*/
class ilNewsSubscription
{
	/**
	* Subscribe a user to an object (ref id).
	*
	* @param	int		$a_ref_id	ref id
	* @param	int		$a_user_id	user id
	*/
	public static function _subscribe($a_ref_id, $a_user_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM il_news_subscription WHERE ".
			" ref_id = ".$ilDB->quote($a_ref_id, "integer")." ".
			" AND user_id = ".$ilDB->quote($a_user_id, "integer"));
		$ilDB->manipulate("INSERT INTO il_news_subscription (ref_id, user_id) VALUES (".
			$ilDB->quote($a_ref_id, "integer").", ".
			$ilDB->quote($a_user_id, "integer").")");
	}

	/**
	* Unsubscribe a user from an object (ref id).
	*
	* @param	int		$a_ref_id	ref id
	* @param	int		$a_user_id	user id
	*/
	public static function _unsubscribe($a_ref_id, $a_user_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM il_news_subscription WHERE ref_id  = ".
			$ilDB->quote($a_ref_id, "integer")." AND user_id = ".
			$ilDB->quote($a_user_id, "integer"));
	}

	/**
	* Check whether user has subscribed to an object.
	*
	* @param	int		$a_ref_id	ref id
	* @param	int		$a_user_id	user id
	* @return	boolean	has subscribed true/false
	*/
	public static function _hasSubscribed($a_ref_id, $a_user_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_news_subscription WHERE ref_id = ".
			$ilDB->quote($a_ref_id, "integer")." AND user_id = ".
			$ilDB->quote($a_user_id, "integer");
		$set = $ilDB->query($query);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Get subscriptions of user.
	*
	* @param	int		$a_ref_id	ref id
	* @param	int		$a_user_id	user id
	* @return	boolean	has subscribed true/false
	*/
	public static function _getSubscriptionsOfUser($a_user_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_news_subscription WHERE user_id = ".
			$ilDB->quote($a_user_id, "integer");
		$set = $ilDB->query($query);
		$ref_ids = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$ref_ids[] = $rec["ref_id"];
		}

		return $ref_ids;
	}
}
?>
