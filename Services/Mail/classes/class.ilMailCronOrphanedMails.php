<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Delete orphaned mails
 *
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMails extends ilCronJob
{
    private GlobalHttpState $http;
    private Refinery $refinery;
    private ilLanguage $lng;
    private ilSetting $settings;
    private ilDBInterface $db;
    private ilObjUser $user;
    private bool $initDone = false;

    private function init() : void
    {
        global $DIC;

        if (!$this->initDone) {
            $this->settings = $DIC->settings();
            $this->lng = $DIC->language();
            $this->db = $DIC->database();
            $this->user = $DIC->user();
            $this->http = $DIC->http();
            $this->refinery = $DIC->refinery();

            $this->lng->loadLanguageModule('mail');
            $this->initDone = true;
        }
    }

    public function getId() : string
    {
        return 'mail_orphaned_mails';
    }

    public function getTitle() : string
    {
        $this->init();
        return $this->lng->txt('mail_orphaned_mails');
    }

    public function getDescription() : string
    {
        $this->init();
        return $this->lng->txt('mail_orphaned_mails_desc');
    }

    public function hasAutoActivation() : bool
    {
        return false;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

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
        $threshold->setSize(4);
        $threshold->setValue($this->settings->get('mail_threshold', ''));

        $a_form->addItem($threshold);
        
        $mail_folder = new ilCheckboxInputGUI(
            $this->lng->txt('only_inbox_trash'),
            'mail_only_inbox_trash'
        );
        $mail_folder->setValue('1');
        $mail_folder->setInfo($this->lng->txt('only_inbox_trash_info'));
        $mail_folder->setChecked((bool) $this->settings->get('mail_only_inbox_trash', '0'));
        $a_form->addItem($mail_folder);
        
        $notification = new ilNumberInputGUI(
            $this->lng->txt('mail_notify_orphaned'),
            'mail_notify_orphaned'
        );
        $notification->setInfo($this->lng->txt('mail_notify_orphaned_info'));
        $notification->allowDecimals(false);
        $notification->setSize(4);
        $notification->setSuffix($this->lng->txt('days'));
        $notification->setMinValue(0);

        if ($this->http->wrapper()->post()->has('mail_threshold')) {
            $mail_threshold = (int) $this->http->wrapper()->post()->retrieve(
                'mail_threshold',
                $this->emptyStringOrFloatOrIntToEmptyOrIntegerString()
            );
        } else {
            $mail_threshold = (int) $this->settings->get('mail_threshold');
        }
        $maxvalue = $mail_threshold - 1;
        $notification->setMaxValue($maxvalue);
        $notification->setValue($this->settings->get('mail_notify_orphaned', ''));
        $a_form->addItem($notification);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form) : bool
    {
        $this->init();

        $this->settings->set('mail_only_inbox_trash', (string) ((int) $a_form->getInput('mail_only_inbox_trash')));
        $this->settings->set(
            'mail_threshold',
            $this->emptyStringOrFloatOrIntToEmptyOrIntegerString()->transform($a_form->getInput('mail_threshold'))
        );
        $this->settings->set(
            'mail_notify_orphaned',
            $this->emptyStringOrFloatOrIntToEmptyOrIntegerString()->transform($a_form->getInput('mail_notify_orphaned'))
        );

        if ((int) $this->settings->get('mail_notify_orphaned', '0') === 0) {
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

        $mail_threshold = (int) $this->settings->get('mail_threshold', '0');

        ilLoggerFactory::getLogger('mail')->info(sprintf(
            'Started mail deletion job with threshold: %s day(s)',
            var_export($mail_threshold, true)
        ));

        if ($mail_threshold >= 1 && (int) $this->settings->get('mail_notify_orphaned', '0') >= 1) {
            $this->processNotification();
        }

        if ($mail_threshold >= 1 && (int) $this->settings->get('last_cronjob_start_ts', (string) time())) {
            $this->processDeletion();
        }

        $result = new ilCronJobResult();
        $status = ilCronJobResult::STATUS_OK;
        $result->setStatus($status);

        ilLoggerFactory::getLogger('mail')->info(sprintf(
            'Finished mail deletion job with threshold: %s day(s)',
            var_export($mail_threshold, true)
        ));

        return $result;
    }

    private function processNotification() : void
    {
        $this->init();

        $collector = new ilMailCronOrphanedMailsNotificationCollector();

        $notifier = new ilMailCronOrphanedMailsNotifier(
            $collector,
            (int) $this->settings->get('mail_threshold', '0'),
            (int) $this->settings->get('mail_notify_orphaned', '0')
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
    
    private function emptyStringOrFloatOrIntToEmptyOrIntegerString() : \ILIAS\Refinery\Transformation
    {
        $empty_string_or_null_to_stirng_trafo = $this->refinery->custom()->transformation(static function ($value) : string {
            if ($value === '' || null === $value) {
                return '';
            }

            throw new Exception('The value to be transformed is not an empty string');
        });

        return $this->refinery->in()->series([
            $this->refinery->byTrying([
                $empty_string_or_null_to_stirng_trafo,
                $this->refinery->kindlyTo()->int(),
                $this->refinery->in()->series([
                    $this->refinery->kindlyTo()->float(),
                    $this->refinery->kindlyTo()->int()
                ])
            ]),
            $this->refinery->kindlyTo()->string()
        ]);
    }
}
