<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilNotification
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjExerciseGUI.php 24003 2010-05-26 14:35:42Z akill $
*
* @ilCtrl_Calls ilNotification:
*
* @ingroup ServicesNotification
*/
class ilNotification 
{
	const TYPE_EXERCISE_SUBMISSION = 1;
	const TYPE_WIKI = 2;
	const TYPE_WIKI_PAGE = 3;
	const TYPE_BLOG = 4;
    const TYPE_DATA_COLLECTION = 5;
    const TYPE_POLL = 6;
	const TYPE_LM_BLOCKED_USERS = 7;
	
	const THRESHOLD = 180; // time between mails in minutes

	/**
	 * Check notification status for object and user
	 *
	 * @param	int		$type
	 * @param	int		$user_id
	 * @param	int		$id
	 * @return	bool
	 */
	public static function hasNotification($type, $user_id, $id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT user_id FROM notification".
				" WHERE type = ".$ilDB->quote($type, "integer").
				" AND user_id = ".$ilDB->quote($user_id, "integer").
				" AND id = ".$ilDB->quote($id, "integer"));
		return (bool)$ilDB->numRows($set);
	}

	/**
	 * Get all users for given object
	 *
	 * @param	int		$type
	 * @param	int		$id
	 * @param	int		$page_id
	 * @param	bool	$ignore_threshold
	 * @return	array
	 */
	public static function getNotificationsForObject($type, $id, $page_id = null, $ignore_threshold = false)
	{
		global $ilDB;

		$sql = "SELECT user_id FROM notification".
			" WHERE type = ".$ilDB->quote($type, "integer").
			" AND id = ".$ilDB->quote($id, "integer");
		if(!$ignore_threshold)
		{
			$sql .= " AND (last_mail < ".$ilDB->quote(date("Y-m-d H:i:s", 
				strtotime("-".self::THRESHOLD."minutes")), "timestamp").
				" OR last_mail IS NULL";
			if($page_id)
			{
				$sql .= " OR page_id <> ".$ilDB->quote($page_id, "integer");
			}
			$sql .= ")";
		}
		$user = array();
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$user[] = $row["user_id"];
		}
		return $user;
	}

	/**
	 * Set notification status for object and user
	 *
	 * @param	int		$type
	 * @param	int		$user_id
	 * @param	int		$id
	 * @param	bool	$status
	 * @return	bool
	 */
	public static function setNotification($type, $user_id, $id, $status = true)
	{
		global $ilDB;

		if(!$status)
		{
			$ilDB->query("DELETE FROM notification".
				" WHERE type = ".$ilDB->quote($type, "integer").
				" AND user_id = ".$ilDB->quote($user_id, "integer").
				" AND id = ".$ilDB->quote($id, "integer"));
		}
		else
		{
			$fields = array(
				"type" => array("integer", $type),
				"user_id" => array("integer", $user_id),
				"id" => array("integer", $id)
			);			
			$ilDB->replace("notification", $fields, array());			
		}
	}

	/**
	 * Update the last mail timestamp for given object and users
	 *
	 * @param	int		$type
	 * @param	int		$id
	 * @param	array	$user_ids
	 * @param	int		$page_id
	 */
	public static function updateNotificationTime($type, $id, array $user_ids, $page_id = false)
	{
		global $ilDB;

		$sql = "UPDATE notification".
				" SET last_mail = ".$ilDB->quote(date("Y-m-d H:i:s"), "timestamp");

		if($page_id)
		{
			$sql .= ", page_id = ".$ilDB->quote($page_id, "integer");
		}

		$sql .= " WHERE type = ".$ilDB->quote($type, "integer").
				" AND id = ".$ilDB->quote($id, "integer").
				" AND ".$ilDB->in("user_id", $user_ids, false, "integer");

		$ilDB->query($sql);
	}

	/**
	 * Remove all notifications for given object
	 *
	 * @param	int		$type
	 * @param	int		$id
	 */
	public static function removeForObject($type, $id)
	{
		global $ilDB;

		$ilDB->query("DELETE FROM notification".
				" WHERE type = ".$ilDB->quote($type, "integer").
				" AND id = ".$ilDB->quote($id, "integer"));
	}

	/**
	 * Remove all notifications for given user
	 * 
	 * @param	int		$user_id
	 */
	public static function removeForUser($user_id)
    {
		global $ilDB;

		$ilDB->query("DELETE FROM notification".
				" WHERE user_id = ".$ilDB->quote($user_id, "integer"));
	}
}

?>