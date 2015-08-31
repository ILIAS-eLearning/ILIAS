<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Mail/classes/class.ilMailNotification.php';

/**
 * This checks if a mail has to be send after a certain INACTIVITY period
 * @author  Guido Vollbach <gvollbach@databay.de>
 * @version $Id$
 * @package Services/User
 */
class ilCronDeleteInactiveUserReminderMail
{
	const TABLE_NAME = "usr_cron_mail_reminder";

	private function mailSent($usr_id)
	{
		global $ilDB;
		$ilDB->manipulateF("INSERT INTO " . self::TABLE_NAME . " (usr_id, ts) VALUES (%s, %s)",
			array(
				"integer",
				"integer"
			),
			array(
				$usr_id,
				time()
			)
		);
	}

	private function sendReminder(ilObjUser $user, $reminderTime)
	{
		include_once 'Services/User/classes/class.ilCronDeleteInactiveUserReminderMailNotification.php';
		$mail = new ilCronDeleteInactiveUserReminderMailNotification();
		$mail->setRecipients(array($user));
		$mail->setAdditionalInformation(
			 array(
				 "www"  => ilUtil::_getHttpPath(),
				 "days" => $reminderTime,
			 )
		);
		$mail->send();
		self::mailSent($user->getId());
	}

	public static function removeEntriesFromTableIfLastLoginIsNewer()
	{
		global $ilDB;
		$query = "SELECT usr_id,ts FROM " . self::TABLE_NAME;
		$res   = $ilDB->queryF($query, array(
			'integer',
			'integer'
		), array(
			'usr_id',
			'ts'
		));
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$lastLoginUnixtime = strtotime(ilObjUser::_lookupLastLogin($row->usr_id));
			$lastReminderSent  = (int)$row->ts;
			if($lastLoginUnixtime >= $lastReminderSent)
			{
				self::removeSingleUserFromTable($row->usr_id);
			}
		}
	}

	public static function checkIfReminderMailShouldBeSend(ilObjUser $user, $reminderTime)
	{
		global $ilDB;
		$query = "SELECT ts FROM " . self::TABLE_NAME . " WHERE usr_id = %s";
		$res   = $ilDB->queryF($query, array('integer'), array($user->getId()));
		$row   = $res->fetchRow(DB_FETCHMODE_OBJECT);
		if($row->ts == null)
		{
			self::sendReminder($user, $reminderTime);
			return true;
		}
		return false;
	}

	public static function flushDataTable()
	{
		global $ilDB;
		$ilDB->manipulate("DELETE FROM " . self::TABLE_NAME);
	}

	public static function removeSingleUserFromTable($usr_id)
	{
		global $ilDB;
		$query = "DELETE FROM " . self::TABLE_NAME . " WHERE usr_id = %s";
		$ilDB->manipulateF($query, array('integer'), array($usr_id));
	}
}
