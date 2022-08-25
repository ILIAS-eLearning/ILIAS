<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Reminders for consultation hours
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConsultationHourCron extends ilCronJob
{
    protected ilLanguage $lng;
    protected ilDBInterface $db;
    protected ilSetting $setting;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('dateplaner');
        $this->db = $DIC->database();
        $this->setting = $DIC->settings();
    }

    public function getId(): string
    {
        return "cal_consultation";
    }

    public function getTitle(): string
    {
        return $this->lng->txt("cal_ch_cron_reminder");
    }

    public function getDescription(): string
    {
        return $this->lng->txt("cal_ch_cron_reminder_info");
    }

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return null;
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return false;
    }

    public function hasCustomSettings(): bool
    {
        return true;
    }

    public function run(): ilCronJobResult
    {
        $status = ilCronJobResult::STATUS_NO_ACTION;

        $days_before = (int) $this->setting->get('ch_reminder_days');
        $now = new ilDateTime(time(), IL_CAL_UNIX);
        $limit = clone $now;
        $limit->increment(IL_CAL_DAY, $days_before);

        $counter = 0;

        $query = 'SELECT * FROM booking_user ' .
            'JOIN cal_entries ON entry_id = cal_id ' .
            'WHERE notification_sent = ' . $this->db->quote(0, 'integer') . ' ' .
            'AND starta > ' . $this->db->quote($now->get(IL_CAL_DATETIME, '', ilTimeZone::UTC), 'timestamp') . ' ' .
            'AND starta <= ' . $this->db->quote($limit->get(IL_CAL_DATETIME, '', ilTimeZone::UTC), 'timestamp');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $mail = new ilCalendarMailNotification();
            $mail->setAppointmentId($row->entry_id);
            $mail->setRecipients(array($row->user_id));
            $mail->setType(ilCalendarMailNotification::TYPE_BOOKING_REMINDER);
            $mail->send();

            // update notification
            $query = 'UPDATE booking_user ' .
                'SET notification_sent = ' . $this->db->quote(1, 'integer') . ' ' .
                'WHERE user_id = ' . $this->db->quote($row->user_id, 'integer') . ' ' .
                'AND entry_id = ' . $this->db->quote($row->entry_id, 'integer');
            $this->db->manipulate($query);
            $counter++;
        }

        if ($counter) {
            $status = ilCronJobResult::STATUS_OK;
        }
        $result = new ilCronJobResult();
        $result->setStatus($status);
        return $result;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form): void
    {
        $consultation_days = new ilNumberInputGUI($this->lng->txt('cal_ch_cron_reminder_days'), 'ch_reminder_days');
        $consultation_days->setMinValue(1);
        $consultation_days->setMaxLength(2);
        $consultation_days->setSize(2);
        $consultation_days->setValue($this->setting->get('ch_reminder_days', '2'));
        $consultation_days->setRequired(true);
        $a_form->addItem($consultation_days);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form): bool
    {
        $this->setting->set('ch_reminder_days', $a_form->getInput('ch_reminder_days'));
        return true;
    }
}
