<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Reminders for consultation hours
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConsultationHourCron extends ilCronJob
{
    public function getId()
    {
        return "cal_consultation";
    }
    
    public function getTitle()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule('dateplaner');
        return $lng->txt("cal_ch_cron_reminder");
    }
    
    public function getDescription()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule('dateplaner');
        return $lng->txt("cal_ch_cron_reminder_info");
    }
    
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }
    
    public function getDefaultScheduleValue()
    {
        return;
    }
    
    public function hasAutoActivation()
    {
        return false;
    }
    
    public function hasFlexibleSchedule()
    {
        return false;
    }
    
    public function hasCustomSettings()
    {
        return true;
    }
    
    public function run()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $ilDB = $DIC['ilDB'];
        
        $status = ilCronJobResult::STATUS_NO_ACTION;
        
        $days_before = $ilSetting->get('ch_reminder_days');
        $now = new ilDateTime(time(), IL_CAL_UNIX);
        
        $limit = clone $now;
        $limit->increment(IL_CAL_DAY, $days_before);
        
        $counter = 0;
        
        $query = 'SELECT * FROM booking_user ' .
                'JOIN cal_entries ON entry_id = cal_id ' .
                'WHERE notification_sent = ' . $ilDB->quote(0, 'integer') . ' ' .
                'AND starta > ' . $ilDB->quote($now->get(IL_CAL_DATETIME, '', ilTimeZone::UTC), 'timestamp') . ' ' .
                'AND starta <= ' . $ilDB->quote($limit->get(IL_CAL_DATETIME, '', ilTimeZone::UTC), 'timestamp');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            include_once 'Services/Calendar/classes/class.ilCalendarMailNotification.php';
            $mail = new ilCalendarMailNotification();
            $mail->setAppointmentId($row->entry_id);
            $mail->setRecipients(array($row->user_id));
            $mail->setType(ilCalendarMailNotification::TYPE_BOOKING_REMINDER);
            $mail->send();
            
            // update notification
            $query = 'UPDATE booking_user ' .
                    'SET notification_sent = ' . $ilDB->quote(1, 'integer') . ' ' .
                    'WHERE user_id = ' . $ilDB->quote($row->user_id, 'integer') . ' ' .
                    'AND entry_id = ' . $ilDB->quote($row->entry_id, 'integer');
            $ilDB->manipulate($query);
            
            $counter++;
        }
                        
        if ($counter) {
            $status = ilCronJobResult::STATUS_OK;
        }
        $result = new ilCronJobResult();
        $result->setStatus($status);
        return $result;
    }
    
    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilSetting = $DIC['ilSetting'];
        
        $lng->loadLanguageModule('dateplaner');

        $consultation_days = new ilNumberInputGUI($lng->txt('cal_ch_cron_reminder_days'), 'ch_reminder_days');
        $consultation_days->setMinValue(1);
        $consultation_days->setMaxLength(2);
        $consultation_days->setSize(2);
        $consultation_days->setValue($ilSetting->get('ch_reminder_days', 2));
        $consultation_days->setRequired(true);
        $a_form->addItem($consultation_days);
    }
    
    public function saveCustomSettings(ilPropertyFormGUI $a_form)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $ilSetting->set('ch_reminder_days', $a_form->getInput('ch_reminder_days'));
        
        return true;
    }
}
