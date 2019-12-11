<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Cron/classes/class.ilCronJob.php";
include_once "./Services/Cron/classes/class.ilCronJobResult.php";
require_once './Services/Logging/classes/public/class.ilLoggerFactory.php';

/**
 * Delete orphaned mails
 *
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMails extends ilCronJob
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
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilObjUser
     */
    protected $user;

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
            $this->lng      = $DIC->language();
            $this->db       = $DIC->database();
            $this->user     = $DIC->user();

            $this->lng->loadLanguageModule('mail');
            $this->initDone = true;
        }
    }

    /**
     * Get id
     * @return string
     */
    public function getId()
    {
        return "mail_orphaned_mails";
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        $this->init();
        return $this->lng->txt("mail_orphaned_mails");
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $this->init();
        return $this->lng->txt("mail_orphaned_mails_desc");
    }
    
    /**
     * Is to be activated on "installation"
     * @return boolean
     */
    public function hasAutoActivation()
    {
        return false;
    }

    /**
     * Can the schedule be configured?
     * @return boolean
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getValidScheduleTypes()
    {
        return array(
            self::SCHEDULE_TYPE_DAILY,
            self::SCHEDULE_TYPE_WEEKLY,
            self::SCHEDULE_TYPE_MONTHLY,
            self::SCHEDULE_TYPE_QUARTERLY,
            self::SCHEDULE_TYPE_YEARLY,
            self::SCHEDULE_TYPE_IN_DAYS
        );
    }

    /**
     * Get schedule type
     * @return int
     */
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    /**
     * Get schedule value
     * @return int|array
     */
    public function getDefaultScheduleValue()
    {
        return 1;
    }

    /**
     * @return bool
     */
    public function hasCustomSettings()
    {
        return true;
    }

    /**
     * @param ilPropertyFormGUI $a_form
     */
    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
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
        
        $mail_folder = new ilCheckboxInputGUI($this->lng->txt('only_inbox_trash'), 'mail_only_inbox_trash');
        $mail_folder->setInfo($this->lng->txt('only_inbox_trash_info'));
        $mail_folder->setChecked($this->settings->get('mail_only_inbox_trash'));
        $a_form->addItem($mail_folder);
        
        $notification = new ilNumberInputGUI($this->lng->txt('mail_notify_orphaned'), 'mail_notify_orphaned');
        $notification->setInfo($this->lng->txt('mail_notify_orphaned_info'));
        $notification->allowDecimals(false);
        $notification->setSuffix($this->lng->txt('days'));
        $notification->setMinValue(0);
        
        $mail_threshold = isset($_POST['mail_threshold']) ? (int) $_POST['mail_threshold'] : $this->settings->get('mail_threshold');
        $maxvalue = $mail_threshold-1;
        $notification->setMaxValue($maxvalue);
        $notification->setValue($this->settings->get('mail_notify_orphaned'));
        $a_form->addItem($notification);
    }

    /**
     * @param ilPropertyFormGUI $a_form
     * @return bool
     */
    public function saveCustomSettings(ilPropertyFormGUI $a_form)
    {
        $this->init();
        $this->settings->set('mail_threshold', (int) $a_form->getInput('mail_threshold'));
        $this->settings->set('mail_only_inbox_trash', (int) $a_form->getInput('mail_only_inbox_trash'));
        $this->settings->set('mail_notify_orphaned', (int) $a_form->getInput('mail_notify_orphaned'));

        if ($this->settings->get('mail_notify_orphaned') == 0) {
            //delete all mail_cron_orphaned-table entries!
            $this->db->manipulate('DELETE FROM mail_cron_orphaned');

            ilLoggerFactory::getLogger('mail')->info(sprintf(
                "Deleted all scheduled mail deletions because a reminder should't be sent (login: %s|usr_id: %s) anymore!",
                $this->user->getLogin(),
                $this->user->getId()
            ));
        }

        return true;
    }

    /**
     * Run job
     * @return ilCronJobResult
     */
    public function run()
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

    private function processNotification()
    {
        $this->init();
        include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsNotificationCollector.php';
        $collector = new ilMailCronOrphanedMailsNotificationCollector();

        include_once'./Services/Mail/classes/class.ilMailCronOrphanedMailsNotifier.php';
        $notifier = new ilMailCronOrphanedMailsNotifier(
            $collector,
            (int) $this->settings->get('mail_threshold'),
            (int) $this->settings->get('mail_notify_orphaned')
        );
        $notifier->processNotification();
    }

    private function processDeletion()
    {
        $this->init();
        include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsDeletionCollector.php';
        $collector = new ilMailCronOrphanedMailsDeletionCollector();

        include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsDeletionProcessor.php';
        $processor = new ilMailCronOrphanedMailsDeletionProcessor($collector);
        $processor->processDeletion();
    }
}
