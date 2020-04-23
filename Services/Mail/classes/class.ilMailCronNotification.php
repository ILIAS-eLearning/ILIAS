<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Mail notifications
 *
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilMailCronNotification extends ilCronJob
{
    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilSetting
     */
    protected $settings;

    /**
     * @var bool
     */
    protected $initDone = false;

    /**
     *
     */
    protected function init()
    {
        global $DIC;

        if (!$this->initDone) {
            $this->settings = $DIC->settings();
            $this->lng = $DIC->language();

            $this->initDone = true;
        }
    }

    public function getId()
    {
        return "mail_notification";
    }

    public function getTitle()
    {
        $this->init();
        return $this->lng->txt("cron_mail_notification");
    }
    
    public function getDescription()
    {
        $this->init();
        return  $this->lng->txt("cron_mail_notification_desc");
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
        require_once 'Services/Mail/classes/class.ilMailSummaryNotification.php';
        $msn = new ilMailSummaryNotification();
        $msn->send();

        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
    {
        $this->init();
        $cb = new ilCheckboxInputGUI($this->lng->txt("cron_mail_notification_message"), "mail_notification_message");
        $cb->setInfo($this->lng->txt("cron_mail_notification_message_info"));
        $cb->setChecked($this->settings->get("mail_notification_message"));
        $a_form->addItem($cb);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form)
    {
        $this->init();
        $this->settings->set('mail_notification_message', $_POST['mail_notification_message'] ? 1 : 0);
        return true;
    }

    public function activationWasToggled($a_currently_active)
    {
        $this->init();
        $this->settings->set('mail_notification', (bool) $a_currently_active);
    }
}
