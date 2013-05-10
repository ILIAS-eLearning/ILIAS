<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConsultationHourCron 
{

	/**
	 * Start cron task
	 */
	public static function start()
	{
		global $ilSetting, $ilDB;
		
		$days_before = $ilSetting->get('ch_reminder_days');
		$now = new ilDateTime(time(),IL_CAL_UNIX);
		
		$limit = clone $now;
		$limit->increment(IL_CAL_DAY,$days_before);
		
		$query = 'SELECT * FROM booking_user '.
				'JOIN cal_entries ON entry_id = cal_id '.
				'WHERE notification_sent = '.$ilDB->quote(0,'integer').' '.
				'AND starta > '.$ilDB->quote($now->get(IL_CAL_DATETIME,'',  ilTimeZone::UTC),'timestamp'). ' '.
				'AND starta <= '.$ilDB->quote($limit->get(IL_CAL_DATETIME, '', ilTimeZone::UTC),'timestamp');
		
		$GLOBALS['ilLog']->write(__METHOD__.': ' . $query);
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			include_once 'Services/Calendar/classes/class.ilCalendarMailNotification.php';
			$mail = new ilCalendarMailNotification();
			$mail->setAppointmentId($row->entry_id);
			$mail->setRecipients(array($row->user_id));
			$mail->setType(ilCalendarMailNotification::TYPE_BOOKING_REMINDER);
			$mail->send();
			
			// update notification
			$query = 'UPDATE booking_user '.
					'SET notification_sent = '.$ilDB->quote(1,'integer').' '.
					'WHERE user_id = '.$ilDB->quote($row->user_id,'integer').' '.
					'AND entry_id = '.$ilDB->quote($row->entry_id,'integer');
			$ilDB->manipulate($query);
		}
		return true;
	}
}
?>
