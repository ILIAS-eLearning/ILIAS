<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * This cron deletes user accounts by INACTIVITY period
 * @author Bjoern Heyser <bheyser@databay.de>
 * @author Guido Vollbach <gvollbach@databay.de>
 * @package ilias
 */
class ilCronDeleteInactiveUserAccounts extends ilCronJob
{
    private const DEFAULT_INACTIVITY_PERIOD = 365;
    private const DEFAULT_REMINDER_PERIOD = 0;

    private int $period;
    private int $reminderTimer;
    /** @var int[] */
    private array $include_roles;
    private ilSetting $settings;
    private ilLanguage $lng;
    private ilRbacReview $rbacReview;
    private ilObjectDataCache $objectDataCache;
    private \ILIAS\HTTP\GlobalHttpState $http;
    private \ILIAS\Refinery\Factory $refinery;
    private ilCronJobRepository $cronRepository;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        if ($DIC) {
            if (isset($DIC['http'])) {
                $this->http = $DIC->http();
            }

            if (isset($DIC['lng'])) {
                $this->lng = $DIC->language();
            }

            if (isset($DIC['refinery'])) {
                $this->refinery = $DIC->refinery();
            }

            if (isset($DIC['ilObjDataCache'])) {
                $this->objectDataCache = $DIC['ilObjDataCache'];
            }

            if (isset($DIC['rbacreview'])) {
                $this->rbacReview = $DIC->rbac()->review();
            }

            if (isset($DIC['cron.repository'])) {
                $this->cronRepository = $DIC->cron()->repository();
            }

            if (isset($DIC['ilSetting'])) {
                $this->settings = $DIC->settings();

                $include_roles = $DIC['ilSetting']->get(
                    'cron_inactive_user_delete_include_roles',
                    null
                );
                if ($include_roles === null) {
                    $this->include_roles = [];
                } else {
                    $this->include_roles = array_filter(array_map('intval', explode(',', $include_roles)));
                }

                $this->period = (int) $this->settings->get(
                    'cron_inactive_user_delete_period',
                    (string) self::DEFAULT_INACTIVITY_PERIOD
                );
                $this->reminderTimer = (int) $this->settings->get(
                    'cron_inactive_user_reminder_period',
                    (string) self::DEFAULT_REMINDER_PERIOD
                );
            }
        }
    }

    /**
     * @param string|int $number
     */
    protected function isDecimal($number) : bool
    {
        $number = (string) $number;

        return strpos($number, ',') || strpos($number, '.');
    }

    protected function getTimeDifferenceBySchedule(int $schedule_time, int $multiplier) : int
    {
        $time_difference = 0;

        switch ($schedule_time) {
            case ilCronJob::SCHEDULE_TYPE_DAILY:
                $time_difference = 86400;
                break;
            case ilCronJob::SCHEDULE_TYPE_IN_MINUTES:
                $time_difference = 60 * $multiplier;
                break;
            case ilCronJob::SCHEDULE_TYPE_IN_HOURS:
                $time_difference = 3600 * $multiplier;
                break;
            case ilCronJob::SCHEDULE_TYPE_IN_DAYS:
                $time_difference = 86400 * $multiplier;
                break;
            case ilCronJob::SCHEDULE_TYPE_WEEKLY:
                $time_difference = 604800;
                break;
            case ilCronJob::SCHEDULE_TYPE_MONTHLY:
                $time_difference = 2629743;
                break;
            case ilCronJob::SCHEDULE_TYPE_QUARTERLY:
                $time_difference = 7889229;
                break;
            case ilCronJob::SCHEDULE_TYPE_YEARLY:
                $time_difference = 31556926;
                break;
        }

        return $time_difference;
    }

    public function getId() : string
    {
        return "user_inactive";
    }
    
    public function getTitle() : string
    {
        return $this->lng->txt("delete_inactive_user_accounts");
    }
    
    public function getDescription() : string
    {
        return $this->lng->txt("delete_inactive_user_accounts_desc");
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
        return true;
    }
    
    public function hasCustomSettings() : bool
    {
        return true;
    }
    
    public function run() : ilCronJobResult
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        $ilLog = $DIC['ilLog'];

        $status = ilCronJobResult::STATUS_NO_ACTION;
        $reminder_time = $this->reminderTimer;
        $checkMail = $this->period - $reminder_time;
        $usr_ids = ilObjUser::getUserIdsByInactivityPeriod($checkMail);
        $counter = 0;
        $userDeleted = 0;
        $userMailsDelivered = 0;
        foreach ($usr_ids as $usr_id) {
            if ($usr_id === ANONYMOUS_USER_ID || $usr_id === SYSTEM_USER_ID) {
                continue;
            }

            $continue = true;
            foreach ($this->include_roles as $role_id) {
                if ($rbacreview->isAssigned($usr_id, $role_id)) {
                    $continue = false;
                    break;
                }
            }

            if ($continue) {
                continue;
            }

            /** @var $user ilObjUser */
            $user = ilObjectFactory::getInstanceByObjId($usr_id);
            $timestamp_last_login = strtotime($user->getLastLogin());
            $grace_period_over = time() - ($this->period * 24 * 60 * 60);
            if ($timestamp_last_login < $grace_period_over) {
                $user->delete();
                $userDeleted++;
            } elseif ($reminder_time > 0) {
                $timestamp_for_deletion = $timestamp_last_login - $grace_period_over;
                $account_will_be_deleted_on = $this->calculateDeletionData($timestamp_for_deletion);
                $mailSent = ilCronDeleteInactiveUserReminderMail::checkIfReminderMailShouldBeSend(
                    $user,
                    $reminder_time,
                    $account_will_be_deleted_on
                );
                if ($mailSent) {
                    $userMailsDelivered++;
                }
            }
            $counter++;
        }
        
        if ($counter) {
            $status = ilCronJobResult::STATUS_OK;
        }

        ilCronDeleteInactiveUserReminderMail::removeEntriesFromTableIfLastLoginIsNewer();
        $ilLog->write(
            "CRON - ilCronDeleteInactiveUserAccounts::run(), deleted " .
            "=> $userDeleted User(s), sent reminder mail to $userMailsDelivered User(s)"
        );

        $result = new ilCronJobResult();
        $result->setStatus($status);

        return $result;
    }
    
    protected function calculateDeletionData(int $date_for_deletion) : int
    {
        $cron_timing = $this->cronRepository->getCronJobData($this->getId());
        $time_difference = 0;
        $multiplier = 1;

        if (!is_array($cron_timing) || !isset($cron_timing[0]) || !is_array($cron_timing[0])) {
            return time() + $date_for_deletion + $time_difference;
        }

        if (array_key_exists('schedule_type', $cron_timing[0])) {
            if ($cron_timing[0]['schedule_value'] !== null) {
                $multiplier = (int) $cron_timing[0]['schedule_value'];
            }
            $time_difference = $this->getTimeDifferenceBySchedule(
                (int) $cron_timing[0]['schedule_type'],
                $multiplier
            );
        }
        return time() + $date_for_deletion + $time_difference;
    }
    
    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form) : void
    {
        $this->lng->loadLanguageModule("user");

        $schedule = $a_form->getItemByPostVar('type');
        $schedule->setTitle($this->lng->txt('delete_inactive_user_accounts_frequency'));
        $schedule->setInfo($this->lng->txt('delete_inactive_user_accounts_frequency_desc'));

        $sub_mlist = new ilMultiSelectInputGUI(
            $this->lng->txt('delete_inactive_user_accounts_include_roles'),
            'cron_inactive_user_delete_include_roles'
        );
        $sub_mlist->setInfo($this->lng->txt('delete_inactive_user_accounts_include_roles_desc'));
        $roles = [];
        foreach ($this->rbacReview->getGlobalRoles() as $role_id) {
            if ($role_id !== ANONYMOUS_ROLE_ID) {
                $roles[$role_id] = $this->objectDataCache->lookupTitle($role_id);
            }
        }
        $sub_mlist->setOptions($roles);
        $setting = $this->settings->get('cron_inactive_user_delete_include_roles', null);
        if ($setting === null) {
            $setting = [];
        } else {
            $setting = explode(',', $setting);
        }
        $sub_mlist->setValue($setting);
        $sub_mlist->setWidth(300);
        $a_form->addItem($sub_mlist);

        $default_setting = (string) self::DEFAULT_INACTIVITY_PERIOD;

        $sub_text = new ilNumberInputGUI(
            $this->lng->txt('delete_inactive_user_accounts_period'),
            'cron_inactive_user_delete_period'
        );
        $sub_text->allowDecimals(false);
        $sub_text->setInfo($this->lng->txt('delete_inactive_user_accounts_period_desc'));
        $sub_text->setValue($this->settings->get("cron_inactive_user_delete_period", $default_setting));
        $sub_text->setSize(4);
        $sub_text->setMaxLength(4);
        $sub_text->setRequired(true);
        $a_form->addItem($sub_text);

        $sub_period = new ilNumberInputGUI(
            $this->lng->txt('send_mail_to_inactive_users'),
            'cron_inactive_user_reminder_period'
        );
        $sub_period->allowDecimals(false);
        $sub_period->setInfo($this->lng->txt("send_mail_to_inactive_users_desc"));
        $sub_period->setValue($this->settings->get("cron_inactive_user_reminder_period", $default_setting));
        $sub_period->setSuffix($this->lng->txt("send_mail_to_inactive_users_suffix"));
        $sub_period->setSize(4);
        $sub_period->setMaxLength(4);
        $sub_period->setRequired(false);
        $sub_period->setMinValue(0);
        $a_form->addItem($sub_period);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form) : bool
    {
        $this->lng->loadLanguageModule("user");

        $valid = true;

        $cron_period = $this->http->wrapper()->post()->retrieve(
            'type',
            $this->refinery->kindlyTo()->int()
        );

        $cron_period_custom = 0;
        $delete_period = 0;
        $reminder_period = '';

        $empty_string_trafo = $this->refinery->custom()->transformation(static function ($value) : string {
            if ($value === '') {
                return '';
            }

            throw new Exception('The value to be transformed is not an empty string');
        });

        if ($this->http->wrapper()->post()->has('sdyi')) {
            $cron_period_custom = $this->http->wrapper()->post()->retrieve(
                'sdyi',
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->int(),
                    $empty_string_trafo
                ])
            );
        }

        if ($this->http->wrapper()->post()->has('cron_inactive_user_delete_period')) {
            $delete_period = $this->http->wrapper()->post()->retrieve(
                'cron_inactive_user_delete_period',
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->int(),
                    $this->refinery->in()->series([
                        $this->refinery->kindlyTo()->float(),
                        $this->refinery->kindlyTo()->int()
                    ])
                ])
            );
        }

        if ($this->http->wrapper()->post()->has('cron_inactive_user_reminder_period')) {
            $reminder_period = $this->http->wrapper()->post()->retrieve(
                'cron_inactive_user_reminder_period',
                $this->refinery->byTrying([
                    $empty_string_trafo,
                    $this->refinery->byTrying([
                        $this->refinery->kindlyTo()->int(),
                        $this->refinery->in()->series([
                            $this->refinery->kindlyTo()->float(),
                            $this->refinery->kindlyTo()->int()
                        ])
                    ])
                ])
            );
        }

        if ($this->isDecimal($delete_period)) {
            $valid = false;
            $a_form->getItemByPostVar('cron_inactive_user_delete_period')->setAlert(
                $this->lng->txt('send_mail_to_inactive_users_numbers_only')
            );
        }

        if ($this->isDecimal($reminder_period)) {
            $valid = false;
            $a_form->getItemByPostVar('cron_inactive_user_reminder_period')->setAlert(
                $this->lng->txt('send_mail_to_inactive_users_numbers_only')
            );
        }

        if ($reminder_period >= $delete_period) {
            $valid = false;
            $a_form->getItemByPostVar('cron_inactive_user_reminder_period')->setAlert(
                $this->lng->txt('send_mail_to_inactive_users_must_be_smaller_than')
            );
        }

        if ($cron_period >= ilCronJob::SCHEDULE_TYPE_IN_DAYS && $cron_period <= ilCronJob::SCHEDULE_TYPE_YEARLY && $reminder_period > 0) {
            $logic = true;
            $check_window_logic = $delete_period - $reminder_period;
            if ($cron_period === ilCronJob::SCHEDULE_TYPE_IN_DAYS) {
                if ($check_window_logic < $cron_period_custom) {
                    $logic = false;
                }
            } elseif ($cron_period === ilCronJob::SCHEDULE_TYPE_WEEKLY) {
                if ($check_window_logic <= 7) {
                    $logic = false;
                }
            } elseif ($cron_period === ilCronJob::SCHEDULE_TYPE_MONTHLY) {
                if ($check_window_logic <= 31) {
                    $logic = false;
                }
            } elseif ($cron_period === ilCronJob::SCHEDULE_TYPE_QUARTERLY) {
                if ($check_window_logic <= 92) {
                    $logic = false;
                }
            } elseif ($cron_period === ilCronJob::SCHEDULE_TYPE_YEARLY) {
                if ($check_window_logic <= 366) {
                    $logic = false;
                }
            }

            if (!$logic) {
                $valid = false;
                $a_form->getItemByPostVar('cron_inactive_user_reminder_period')->setAlert(
                    $this->lng->txt('send_mail_reminder_window_too_small')
                );
            }
        }

        if ($delete_period > 0) {
            $roles = implode(',', $this->http->wrapper()->post()->retrieve(
                'cron_inactive_user_delete_include_roles',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            ));

            $this->settings->set('cron_inactive_user_delete_include_roles', $roles);
            $this->settings->set('cron_inactive_user_delete_period', (string) $delete_period);
        }

        if ($this->reminderTimer > $reminder_period) {
            ilCronDeleteInactiveUserReminderMail::flushDataTable();
        }

        $this->settings->set('cron_inactive_user_reminder_period', (string) $reminder_period);

        if (!$valid) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("form_input_not_valid"));
            return false;
        }

        return true;
    }
}
