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

use ILIAS\Cron\Schedule\CronJobScheduleType;

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

    private const ACTION_USER_NONE = 0;
    private const ACTION_USER_REMINDER_MAIL_SENT = 1;
    private const ACTION_USER_DELETED = 2;

    private int $delete_period;
    private int $reminder_period;
    /** @var int[] */
    private array $include_roles;
    private ilCronDeleteInactiveUserReminderMail $cron_delete_reminder_mail;
    private ilSetting $settings;
    private ilLanguage $lng;
    private ilComponentLogger $log;
    private ilRbacReview $rbac_review;
    private ilObjectDataCache $objectDataCache;
    private \ILIAS\HTTP\GlobalHttpState $http;
    private \ILIAS\Refinery\Factory $refinery;
    private ilCronJobRepository $cronRepository;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        if (isset($DIC['ilDB'])) {
            $this->cron_delete_reminder_mail = new ilCronDeleteInactiveUserReminderMail($DIC['ilDB']);
        }

        if (isset($DIC['tpl'])) {
            $this->main_tpl = $DIC['tpl'];
        }
        if (isset($DIC['http'])) {
            $this->http = $DIC['http'];
        }

        if (isset($DIC['lng'])) {
            $this->lng = $DIC['lng'];
        }

        if (isset($DIC['ilLog'])) {
            $this->log = $DIC['ilLog'];
        }

        if (isset($DIC['refinery'])) {
            $this->refinery = $DIC['refinery'];
        }

        if (isset($DIC['ilObjDataCache'])) {
            $this->objectDataCache = $DIC['ilObjDataCache'];
        }

        if (isset($DIC['rbacreview'])) {
            $this->rbac_review = $DIC['rbacreview'];
        }

        if (isset($DIC['cron.repository'])) {
            $this->cronRepository = $DIC['cron.repository'];
        }

        if (isset($DIC['ilSetting'])) {
            $this->settings = $DIC['ilSetting'];
            $this->loadSettings();
        }
    }

    private function loadSettings(): void
    {
        $include_roles = $this->settings->get(
            'cron_inactive_user_delete_include_roles',
            null
        );
        if ($include_roles === null) {
            $this->include_roles = [];
        } else {
            $this->include_roles = array_filter(array_map('intval', explode(',', $include_roles)));
        }

        $this->delete_period = (int) $this->settings->get(
            'cron_inactive_user_delete_period',
            (string) self::DEFAULT_INACTIVITY_PERIOD
        );
        $this->reminder_period = (int) $this->settings->get(
            'cron_inactive_user_reminder_period',
            (string) self::DEFAULT_REMINDER_PERIOD
        );
    }

    /**
     * @param string|int $number
     */
    protected function isDecimal($number): bool
    {
        $number_as_string = (string) $number;

        return strpos($number_as_string, ',') || strpos($number_as_string, '.');
    }

    protected function getTimeDifferenceBySchedule(CronJobScheduleType $schedule_time, int $multiplier): int
    {
        $time_difference = 0;

        switch ($schedule_time) {
            case CronJobScheduleType::SCHEDULE_TYPE_DAILY:
                $time_difference = 86400;
                break;
            case CronJobScheduleType::SCHEDULE_TYPE_IN_MINUTES:
                $time_difference = 60 * $multiplier;
                break;
            case CronJobScheduleType::SCHEDULE_TYPE_IN_HOURS:
                $time_difference = 3600 * $multiplier;
                break;
            case CronJobScheduleType::SCHEDULE_TYPE_IN_DAYS:
                $time_difference = 86400 * $multiplier;
                break;
            case CronJobScheduleType::SCHEDULE_TYPE_WEEKLY:
                $time_difference = 604800;
                break;
            case CronJobScheduleType::SCHEDULE_TYPE_MONTHLY:
                $time_difference = 2629743;
                break;
            case CronJobScheduleType::SCHEDULE_TYPE_QUARTERLY:
                $time_difference = 7889229;
                break;
            case CronJobScheduleType::SCHEDULE_TYPE_YEARLY:
                $time_difference = 31556926;
                break;
        }

        return $time_difference;
    }

    public function getId(): string
    {
        return "user_inactive";
    }

    public function getTitle(): string
    {
        return $this->lng->txt("delete_inactive_user_accounts");
    }

    public function getDescription(): string
    {
        return $this->lng->txt("delete_inactive_user_accounts_desc");
    }

    public function getDefaultScheduleType(): CronJobScheduleType
    {
        return CronJobScheduleType::SCHEDULE_TYPE_DAILY;
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
        return true;
    }

    public function hasCustomSettings(): bool
    {
        return true;
    }

    public function run(): ilCronJobResult
    {
        $status = ilCronJobResult::STATUS_NO_ACTION;
        $check_mail = $this->delete_period - $this->reminder_period;
        $usr_ids = ilObjUser::getUserIdsByInactivityPeriod($check_mail);
        $counters = [
            self::ACTION_USER_NONE => 0,
            self::ACTION_USER_REMINDER_MAIL_SENT => 0,
            self::ACTION_USER_DELETED => 0
        ];
        foreach ($usr_ids as $usr_id) {
            if ($usr_id === ANONYMOUS_USER_ID || $usr_id === SYSTEM_USER_ID) {
                continue;
            }

            foreach ($this->include_roles as $role_id) {
                if ($this->rbac_review->isAssigned($usr_id, $role_id)) {
                    $action_taken = $this->deleteUserOrSendReminderMail($usr_id);
                    $counters[$action_taken]++;
                    break;
                }
            }
        }

        if ($counters[self::ACTION_USER_REMINDER_MAIL_SENT] > 0
            || $counters[self::ACTION_USER_DELETED] > 0) {
            $status = ilCronJobResult::STATUS_OK;
        }

        $this->cron_delete_reminder_mail->removeEntriesFromTableIfLastLoginIsNewer();
        $this->log->write(
            'CRON - ilCronDeleteInactiveUserAccounts::run(), deleted '
            . "=> {$counters[self::ACTION_USER_DELETED]} User(s), sent reminder "
            . "mail to {$counters[self::ACTION_USER_REMINDER_MAIL_SENT]} User(s)"
        );

        $result = new ilCronJobResult();
        $result->setStatus($status);

        return $result;
    }

    private function deleteUserOrSendReminderMail($usr_id): int
    {
        $user = ilObjectFactory::getInstanceByObjId($usr_id);
        $timestamp_last_login = strtotime($user->getLastLogin());
        $grace_period_over = time() - ($this->delete_period * 24 * 60 * 60);

        if ($timestamp_last_login < $grace_period_over) {
            $user->delete();
            return self::ACTION_USER_DELETED;
        }

        if ($this->reminder_period > 0) {
            $timestamp_for_deletion = $timestamp_last_login - $grace_period_over;
            $account_will_be_deleted_on = $this->calculateDeletionData($timestamp_for_deletion);
            if(
                $this->cron_delete_reminder_mail->sendReminderMailIfNeeded(
                    $user,
                    $$this->reminder_period,
                    $account_will_be_deleted_on
                )
            ) {
                return self::ACTION_USER_REMINDER_MAIL_SENT;
            }
        }

        return self::ACTION_USER_NONE;
    }

    protected function calculateDeletionData(int $date_for_deletion): int
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
                CronJobScheduleType::from((int) $cron_timing[0]['schedule_type']),
                $multiplier
            );
        }
        return time() + $date_for_deletion + $time_difference;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form): void
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
        foreach ($this->rbac_review->getGlobalRoles() as $role_id) {
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

    public function saveCustomSettings(ilPropertyFormGUI $a_form): bool
    {
        $this->lng->loadLanguageModule("user");

        $valid = true;

        $cron_period = CronJobScheduleType::from($this->http->wrapper()->post()->retrieve(
            'type',
            $this->refinery->kindlyTo()->int()
        ));

        $cron_period_custom = 0;
        $delete_period = 0;
        $reminder_period = '';

        $empty_string_trafo = $this->refinery->custom()->transformation(static function ($value): string {
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

        if ($cron_period->value >= CronJobScheduleType::SCHEDULE_TYPE_IN_DAYS->value &&
            $cron_period->value <= CronJobScheduleType::SCHEDULE_TYPE_YEARLY->value && $reminder_period > 0) {
            $logic = true;
            $check_window_logic = $delete_period - $reminder_period;
            if ($cron_period === CronJobScheduleType::SCHEDULE_TYPE_IN_DAYS) {
                if ($check_window_logic < $cron_period_custom) {
                    $logic = false;
                }
            } elseif ($cron_period === CronJobScheduleType::SCHEDULE_TYPE_WEEKLY) {
                if ($check_window_logic <= 7) {
                    $logic = false;
                }
            } elseif ($cron_period === CronJobScheduleType::SCHEDULE_TYPE_MONTHLY) {
                if ($check_window_logic <= 31) {
                    $logic = false;
                }
            } elseif ($cron_period === CronJobScheduleType::SCHEDULE_TYPE_QUARTERLY) {
                if ($check_window_logic <= 92) {
                    $logic = false;
                }
            } elseif ($cron_period === CronJobScheduleType::SCHEDULE_TYPE_YEARLY) {
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
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                    $this->refinery->always([])
                ])
            ));

            $this->settings->set('cron_inactive_user_delete_include_roles', $roles);
            $this->settings->set('cron_inactive_user_delete_period', (string) $delete_period);
        }

        if ($this->reminder_period > $reminder_period) {
            $this->cron_delete_reminder_mail->flushDataTable();
        }

        $this->settings->set('cron_inactive_user_reminder_period', (string) $reminder_period);

        if (!$valid) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("form_input_not_valid"));
            return false;
        }

        return true;
    }
}
