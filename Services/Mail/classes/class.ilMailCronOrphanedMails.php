<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * Delete orphaned mails
 *
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMails extends ilCronJob
{
    private ServerRequestInterface $httpRequest;
    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected ilDBInterface $db;
    protected ilObjUser $user;
    protected bool $initDone = false;

    
    protected function init() : void
    {
        global $DIC;

        if (!$this->initDone) {
            $this->settings = $DIC->settings();
            $this->lng = $DIC->language();
            $this->db = $DIC->database();
            $this->user = $DIC->user();
            $this->httpRequest = $DIC->http()->request();

            $this->lng->loadLanguageModule('mail');
            $this->initDone = true;
        }
    }

    public function getId() : string
    {
        return "mail_orphaned_mails";
    }

    public function getTitle() : string
    {
        $this->init();
        return $this->lng->txt("mail_orphaned_mails");
    }

    public function getDescription() : string
    {
        $this->init();
        return $this->lng->txt("mail_orphaned_mails_desc");
    }

    public function hasAutoActivation() : bool
    {
        return false;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    /**
     * @return int[]
     */
    public function getValidScheduleTypes() : array
    {
        return [
            self::SCHEDULE_TYPE_DAILY,
            self::SCHEDULE_TYPE_WEEKLY,
            self::SCHEDULE_TYPE_MONTHLY,
            self::SCHEDULE_TYPE_QUARTERLY,
            self::SCHEDULE_TYPE_YEARLY,
            self::SCHEDULE_TYPE_IN_DAYS
        ];
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue() : ?int
    {
        return 1;
    }

    public function hasCustomSettings() : bool
    {
        return true;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form) : void
    {
        $this->init();
        parent::addCustomSettingsToForm($a_form);

        $threshold = new ilNumberInputGUI($this->lng->txt('mail_threshold'), 'mail_threshold');
        $threshold->setInfo($this->lng->txt('mail_threshold_info'));
        $threshold->allowDecimals(false);
        $threshold->setSuffix($this->lng->txt('days'));
        $threshold->setMinValue(1);
        $threshold->setValue($this->settings->get('mail_threshold'));

        $a_form->addItem($threshold);
        
        $mail_folder = new ilCheckboxInputGUI(
            $this->lng->txt('only_inbox_trash'),
            'mail_only_inbox_trash'
        );
        $mail_folder->setInfo($this->lng->txt('only_inbox_trash_info'));
        $mail_folder->setChecked($this->settings->get('mail_only_inbox_trash'));
        $a_form->addItem($mail_folder);
        
        $notification = new ilNumberInputGUI(
            $this->lng->txt('mail_notify_orphaned'),
            'mail_notify_orphaned'
        );
        $notification->setInfo($this->lng->txt('mail_notify_orphaned_info'));
        $notification->allowDecimals(false);
        $notification->setSuffix($this->lng->txt('days'));
        $notification->setMinValue(0);
        
        $mail_threshold = isset($this->httpRequest->getParsedBody()['mail_threshold']) ?
            (int) $this->httpRequest->getParsedBody()['mail_threshold'] :
            $this->settings->get('mail_threshold');
        $maxvalue = $mail_threshold - 1;
        $notification->setMaxValue($maxvalue);
        $notification->setValue($this->settings->get('mail_notify_orphaned'));
        $a_form->addItem($notification);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form) : bool
    {
        $this->init();
        $this->settings->set('mail_threshold', (int) $a_form->getInput('mail_threshold'));
        $this->settings->set('mail_only_inbox_trash', (int) $a_form->getInput('mail_only_inbox_trash'));
        $this->settings->set('mail_notify_orphaned', (int) $a_form->getInput('mail_notify_orphaned'));

        if ($this->settings->get('mail_notify_orphaned') == 0) {
            //delete all mail_cron_orphaned-table entries!
            $this->db->manipulate('DELETE FROM mail_cron_orphaned');

            ilLoggerFactory::getLogger('mail')->info(sprintf(
                "Deleted all scheduled mail deletions " .
                "because a reminder should't be sent (login: %s|usr_id: %s) anymore!",
                $this->user->getLogin(),
                $this->user->getId()
            ));
        }

        return true;
    }

    public function run() : ilCronJobResult
    {
        $this->init();
        $mail_threshold = (int) $this->settings->get('mail_threshold');

        ilLoggerFactory::getLogger('mail')->info(sprintf(
            'Started mail deletion job with threshold: %s day(s)',
            var_export($mail_threshold, 1)
        ));

        if ((int) $this->settings->get('mail_notify_orphaned') >= 1 && $mail_threshold >= 1) {
            $this->processNotification();
        }

        if ((int) $this->settings->get('last_cronjob_start_ts', time()) && $mail_threshold >= 1) {
            $this->processDeletion();
        }

        $result = new ilCronJobResult();
        $status = ilCronJobResult::STATUS_OK;
        $result->setStatus($status);

        ilLoggerFactory::getLogger('mail')->info(sprintf(
            'Finished mail deletion job with threshold: %s day(s)',
            var_export($mail_threshold, 1)
        ));

        return $result;
    }

    private function processNotification() : void
    {
        $this->init();
        $collector = new ilMailCronOrphanedMailsNotificationCollector();

        $notifier = new ilMailCronOrphanedMailsNotifier(
            $collector,
            (int) $this->settings->get('mail_threshold'),
            (int) $this->settings->get('mail_notify_orphaned')
        );
        $notifier->processNotification();
    }

    private function processDeletion() : void
    {
        $this->init();
        $collector = new ilMailCronOrphanedMailsDeletionCollector();
        $processor = new ilMailCronOrphanedMailsDeletionProcessor($collector);
        $processor->processDeletion();
    }
}
