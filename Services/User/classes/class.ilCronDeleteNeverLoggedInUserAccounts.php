<?php

declare(strict_types=1);

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

use ILIAS\Refinery\ConstraintViolationException;

class ilCronDeleteNeverLoggedInUserAccounts extends \ilCronJob
{
    private const DEFAULT_CREATION_THRESHOLD = 365;

    private string $roleIdWhiteliste = '';
    private int $thresholdInDays = self::DEFAULT_CREATION_THRESHOLD;
    private ilLanguage $lng;
    private ilSetting $settings;
    private ilRbacReview $rbacreview;
    private ilObjectDataCache $objectDataCache;
    private \ILIAS\HTTP\GlobalHttpState $http;
    private \ILIAS\Refinery\Factory $refinery;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        if ($DIC) {
            if (isset($DIC['ilSetting'])) {
                $this->settings = $DIC->settings();

                $this->roleIdWhiteliste = (string) $this->settings->get(
                    'cron_users_without_login_delete_incl_roles',
                    ''
                );

                $this->thresholdInDays = (int) $this->settings->get(
                    'cron_users_without_login_delete_threshold',
                    (string) self::DEFAULT_CREATION_THRESHOLD
                );
            }

            if (isset($DIC['lng'])) {
                $this->lng = $DIC->language();
                $this->lng->loadLanguageModule('usr');
            }

            if (isset($DIC['rbacreview'])) {
                $this->rbacreview = $DIC->rbac()->review();
            }

            if (isset($DIC['ilObjDataCache'])) {
                $this->objectDataCache = $DIC['ilObjDataCache'];
            }

            if (isset($DIC['http'])) {
                $this->http = $DIC->http();
            }

            if (isset($DIC['refinery'])) {
                $this->refinery = $DIC->refinery();
            }
        }
    }

    public function getId(): string
    {
        return 'user_never_logged_in';
    }

    public function getTitle(): string
    {
        global $DIC;

        return $DIC->language()->txt('user_never_logged_in');
    }

    public function getDescription(): string
    {
        global $DIC;

        return $DIC->language()->txt('user_never_logged_in_info');
    }

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue(): int
    {
        return 1;
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
        global $DIC;

        $result = new ilCronJobResult();

        $status = ilCronJobResult::STATUS_NO_ACTION;
        $message = 'No user deleted';

        $userIds = ilObjUser::getUserIdsNeverLoggedIn(
            $this->thresholdInDays ?: self::DEFAULT_CREATION_THRESHOLD
        );

        $roleIdWhitelist = array_filter(array_map('intval', explode(',', $this->roleIdWhiteliste)));

        $counter = 0;
        foreach ($userIds as $userId) {
            if ($userId === ANONYMOUS_USER_ID || $userId === SYSTEM_USER_ID) {
                continue;
            }

            $user = ilObjectFactory::getInstanceByObjId($userId, false);
            if (!($user instanceof ilObjUser)) {
                continue;
            }

            $ignoreUser = true;

            if (count($roleIdWhitelist) > 0) {
                $assignedRoleIds = array_filter(array_map('intval', $this->rbacreview->assignedRoles($userId)));

                $respectedRolesToInclude = array_intersect($assignedRoleIds, $roleIdWhitelist);
                if (count($respectedRolesToInclude) > 0) {
                    $ignoreUser = false;
                }
            }

            if ($ignoreUser) {
                continue;
            }

            $DIC->logger()->user()->info(sprintf(
                "Deleting user account with id %s (login: %s)",
                $user->getId(),
                $user->getLogin()
            ));
            $user->delete();

            $counter++;
        }

        if ($counter) {
            $status = ilCronJobResult::STATUS_OK;
            $message = sprintf('%s user(s) deleted', $counter);
        }

        $result->setStatus($status);
        $result->setMessage($message);

        return $result;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form): void
    {
        $roleWhiteList = new ilMultiSelectInputGUI(
            $this->lng->txt('cron_users_without_login_del_role_whitelist'),
            'role_whitelist'
        );
        $roleWhiteList->setInfo($this->lng->txt('cron_users_without_login_del_role_whitelist_info'));
        $roles = array();
        foreach ($this->rbacreview->getGlobalRoles() as $role_id) {
            if ($role_id !== ANONYMOUS_ROLE_ID) {
                $roles[$role_id] = $this->objectDataCache->lookupTitle($role_id);
            }
        }
        $roleWhiteList->setOptions($roles);
        $roleWhiteList->setValue(array_filter(array_map('intval', explode(',', $this->roleIdWhiteliste))));
        $roleWhiteList->setWidth(300);
        $a_form->addItem($roleWhiteList);

        $threshold = new ilNumberInputGUI(
            $this->lng->txt('cron_users_without_login_del_create_date_thr'),
            'threshold'
        );
        $threshold->allowDecimals(false);
        $threshold->setInfo($this->lng->txt('cron_users_without_login_del_create_date_thr_info'));
        $threshold->setValue((string) $this->thresholdInDays);
        $threshold->setSuffix($this->lng->txt('days'));
        $threshold->setSize(4);
        $threshold->setMaxLength(4);
        $threshold->setRequired(true);
        $a_form->addItem($threshold);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form): bool
    {
        $valid = true;

        $this->roleIdWhiteliste = implode(',', $this->http->wrapper()->post()->retrieve(
            'role_whitelist',
            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
        ));

        try {
            $this->thresholdInDays = $this->http->wrapper()->post()->retrieve(
                'threshold',
                $this->refinery->kindlyTo()->int()
            );
        } catch (ConstraintViolationException $e) {
            $valid = false;
            $a_form->getItemByPostVar('threshold')->setAlert($this->lng->txt('user_never_logged_in_info_threshold_err_num'));
        }

        if ($valid) {
            $this->settings->set(
                'cron_users_without_login_delete_incl_roles',
                $this->roleIdWhiteliste
            );
            $this->settings->set(
                'cron_users_without_login_delete_threshold',
                (string) $this->thresholdInDays
            );
            return true;
        }

        $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        return false;
    }
}
