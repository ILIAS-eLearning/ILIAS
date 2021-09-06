<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Mail notifications
 *
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilMailCronNotification extends ilCronJob
{
    private ServerRequestInterface $httpRequest;
    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected bool $initDone = false;

    
    protected function init() : void
    {
        global $DIC;

        if (!$this->initDone) {
            $this->settings = $DIC->settings();
            $this->lng = $DIC->language();
            $this->httpRequest = $DIC->http()->request();

            $this->initDone = true;
        }
    }

    public function getId() : string
    {
        return "mail_notification";
    }

    public function getTitle() : string
    {
        $this->init();
        return $this->lng->txt("cron_mail_notification");
    }

    public function getDescription() : string
    {
        $this->init();
        return  $this->lng->txt("cron_mail_notification_desc");
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }
    

    public function getDefaultScheduleValue() : ?int
    {
        return null;
    }
    
    public function hasAutoActivation() : bool
    {
        return false;
    }

    public function hasFlexibleSchedule() : bool
    {
        return false;
    }

    public function hasCustomSettings() : bool
    {
        return true;
    }

    public function run() : ilCronJobResult
    {
        require_once 'Services/Mail/classes/class.ilMailSummaryNotification.php';
        $msn = new ilMailSummaryNotification();
        $msn->send();

        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form) : void
    {
        $this->init();
        $cb = new ilCheckboxInputGUI($this->lng->txt("cron_mail_notification_message"), "mail_notification_message");
        $cb->setInfo($this->lng->txt("cron_mail_notification_message_info"));
        $cb->setChecked($this->settings->get("mail_notification_message"));
        $a_form->addItem($cb);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form) : bool
    {
        $this->init();
        $this->settings->set('mail_notification_message', $this->httpRequest->getParsedBody()['mail_notification_message'] ? 1 : 0);
        return true;
    }

    public function activationWasToggled(bool $a_currently_active) : void
    {
        $this->init();
        $this->settings->set('mail_notification', $a_currently_active);
    }
}
