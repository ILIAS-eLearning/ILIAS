<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * System support contacts
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilSystemSupportContacts
{
	/**
	 * Get list
	 *
	 * @return string comma separated list of contacts
	 */
	static function getList()
	{
		global $ilSetting;

		return $ilSetting->get("adm_support_contacts");
	}
	
	/**
	 * Set list
	 *
	 * @param string $a_list comma separated list of contacts
	 */
	static function setList($a_list)
	{
		global $ilSetting;

		$list = explode(",", $a_list);
		$accounts = array();
		foreach ($list as $l)
		{
			if (ilObjUser::_lookupId(trim($l)) > 0)
			{
				$accounts[] = trim($l);
			}
		}

		return $ilSetting->set("adm_support_contacts", implode(",", $accounts));
	}

	/**
	 * Get valid support contacts
	 *
	 * @return array array of user IDs
	 */
	static function getValidSupportContactIds()
	{
		global $ilDB;

		$list = self::getList();
		$list = explode(",", $list);

		$set = $ilDB->query("SELECT usr_id FROM usr_data ".
			" WHERE ".$ilDB->in("login", $list, false, "text")
			);
		$ids = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$ids[] = $rec["usr_id"];
		}
		return $ids;
	}

	/**
	 * Get mailto: email
	 *
	 * @param
	 * @return
	 */
	static function getMailToAddress()
	{
		$emails = array();
		foreach (self::getValidSupportContactIds() as $id)
		{
			if (($e = ilObjUser::_lookupEmail($id)) != "")
			{
				return $e;
			}
		}
		return "";
	}
}

?>