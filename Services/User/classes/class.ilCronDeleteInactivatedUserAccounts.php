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

/**
 * This cron deletes user accounts by INACTIVATION period
 * @author Bjoern Heyser <bheyser@databay.de>
 * @package Services/User
 */
class ilCronDeleteInactivatedUserAccounts extends ilCronJob
{
    private const DEFAULT_INACTIVITY_PERIOD = 365;
    private int $period;
    /** @var int[] */
    private array $include_roles;
    private ilSetting $settings;
    private ilLanguage $lng;
    private ilRbacReview $rbacReview;
    private ilObjectDataCache $objectDataCache;
    private \ILIAS\HTTP\GlobalHttpState $http;
    private \ILIAS\Refinery\Factory $refinery;

    public function __construct()
    {
        global $DIC;

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

            $rbacreview = $DIC->rbac()->review();
            $ilObjDataCache = $DIC['ilObjDataCache'];

            if ($DIC['ilSetting']) {
                $this->settings = $DIC->settings();

                $include_roles = $this->settings->get(
                    'cron_inactivated_user_delete_include_roles',
                    null
                );
                if ($include_roles === null) {
                    $this->include_roles = [];
                } else {
                    $this->include_roles = array_filter(array_map('intval', explode(',', $include_roles)));
                }

                $this->period = (int) $this->settings->get(
                    'cron_inactivated_user_delete_period',
                    (string) self::DEFAULT_INACTIVITY_PERIOD
                );
            }
        }
    }

    public function getId(): string
    {
        return "user_inactivated";
    }

    public function getTitle(): string
    {
        return $this->lng->txt("delete_inactivated_user_accounts");
    }

    public function getDescription(): string
    {
        return sprintf(
            $this->lng->txt("delete_inactivated_user_accounts_desc"),
            $this->period
        );
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
        return true;
    }

    public function hasCustomSettings(): bool
    {
        return true;
    }

    public function run(): ilCronJobResult
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();

        $status = ilCronJobResult::STATUS_NO_ACTION;

        $usr_ids = ilObjUser::_getUserIdsByInactivationPeriod($this->period);

        $counter = 0;
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

            $user = ilObjectFactory::getInstanceByObjId($usr_id);
            $user->delete();

            $counter++;
        }

        if ($counter > 0) {
            $status = ilCronJobResult::STATUS_OK;
        }

        $result = new ilCronJobResult();
        $result->setStatus($status);

        return $result;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form): void
    {
        $sub_mlist = new ilMultiSelectInputGUI(
            $this->lng->txt('delete_inactivated_user_accounts_include_roles'),
            'cron_inactivated_user_delete_include_roles'
        );
        $sub_mlist->setInfo($this->lng->txt('delete_inactivated_user_accounts_include_roles_desc'));
        $roles = [];
        foreach ($this->rbacReview->getGlobalRoles() as $role_id) {
            if ($role_id !== ANONYMOUS_ROLE_ID) {
                $roles[$role_id] = $this->objectDataCache->lookupTitle($role_id);
            }
        }
        $sub_mlist->setOptions($roles);
        $setting = $this->settings->get('cron_inactivated_user_delete_include_roles', null);
        if ($setting === null) {
            $setting = [];
        } else {
            $setting = explode(',', $setting);
        }
        $sub_mlist->setValue($setting);
        $sub_mlist->setWidth(300);
        $a_form->addItem($sub_mlist);

        $sub_text = new ilNumberInputGUI(
            $this->lng->txt('delete_inactivated_user_accounts_period'),
            'cron_inactivated_user_delete_period'
        );
        $sub_text->allowDecimals(false);
        $sub_text->setInfo($this->lng->txt('delete_inactivated_user_accounts_period_desc'));
        $sub_text->setValue(
            $this->settings->get(
                'cron_inactivated_user_delete_period',
                (string) self::DEFAULT_INACTIVITY_PERIOD
            )
        );
        $sub_text->setSize(4);
        $sub_text->setMaxLength(4);
        $sub_text->setRequired(true);
        $a_form->addItem($sub_text);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form): bool
    {
        $roles = implode(',', $this->http->wrapper()->post()->retrieve(
            'cron_inactivated_user_delete_include_roles',
            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
        ));

        $pertiod = null;
        try {
            $pertiod = $this->http->wrapper()->post()->retrieve(
                'cron_inactivated_user_delete_period',
                $this->refinery->kindlyTo()->int()
            );
        } catch (ConstraintViolationException $e) {
        }

        $this->settings->set('cron_inactivated_user_delete_include_roles', $roles);
        $this->settings->set('cron_inactivated_user_delete_period', (string) ($pertiod ?? self::DEFAULT_INACTIVITY_PERIOD));

        return true;
    }
}
