<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCronDeleteNeverLoggedInUserAccounts
 */
class ilCronDeleteNeverLoggedInUserAccounts extends \ilCronJob
{
    const DEFAULT_CREATION_THRESHOLD = 365;

    /** @var string */
    private $roleIdWhiteliste = '';
    
    /** @var int */
    private $thresholdInDays = self::DEFAULT_CREATION_THRESHOLD;

    /** @var \ilLanguage */
    protected $lng;

    /** @var \ilSetting */
    protected $settings;

    /** @var \ilRbacReview */
    protected $rbacreview;

    /** @var \ilObjectDataCache */
    protected $objectDataCache;
    
    /** @var \Psr\Http\Message\ServerRequestInterface */
    protected $request;

    /**
     * ilCronDeleteNeverLoggedInUserAccounts constructor.
     */
    public function __construct()
    {
        global $DIC;

        if ($DIC) {
            if (isset($DIC['ilSetting'])) {
                $this->settings = $DIC->settings();

                $this->roleIdWhiteliste = (string) $this->settings->get(
                    'cron_users_without_login_delete_incl_roles',
                    ''
                );

                $this->thresholdInDays = (int) $this->settings->get(
                    'cron_users_without_login_delete_threshold',
                    self::DEFAULT_CREATION_THRESHOLD
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
                $this->request = $DIC->http()->request();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return 'user_never_logged_in';
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        global $DIC;

        return $DIC->language()->txt('user_never_logged_in');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        global $DIC;

        return $DIC->language()->txt('user_never_logged_in_info');
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScheduleValue()
    {
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function hasAutoActivation()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function hasCustomSettings()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        global $DIC;

        $result = new \ilCronJobResult();

        $status = \ilCronJobResult::STATUS_NO_ACTION;
        $message = 'No user deleted';

        $userIds = ilObjUser::getUserIdsNeverLoggedIn(
            $this->thresholdInDays ?: self::DEFAULT_CREATION_THRESHOLD
        );

        $roleIdWhitelist = array_filter(array_map('intval', explode(',', $this->roleIdWhiteliste)));

        $counter = 0;
        foreach ($userIds as $userId) {
            if ($userId == ANONYMOUS_USER_ID || $userId == SYSTEM_USER_ID) {
                continue;
            }

            $user = ilObjectFactory::getInstanceByObjId($userId, false);
            if (!$user || !($user instanceof \ilObjUser)) {
                continue;
            }

            $ignoreUser = true;

            if (count($roleIdWhitelist) > 0) {
                $assignedRoleIds = array_filter(array_map('intval', (array) $this->rbacreview->assignedRoles($userId)));

                $respectedRolesToInclude = array_intersect($assignedRoleIds, $roleIdWhitelist);
                if (count($respectedRolesToInclude) > 0) {
                    $ignoreUser = false;
                }
            }

            if ($ignoreUser) {
                continue;
            }

            $DIC->logger()->usr()->info(sprintf(
                "Deleting user account with id %s (login: %s)",
                $user->getId(),
                $user->getLogin()
            ));
            $user->delete();

            $counter++;
        }

        if ($counter) {
            $status = \ilCronJobResult::STATUS_OK;
            $message = sprintf('%s user(s) deleted', $counter);
        }

        $result->setStatus($status);
        $result->setMessage($message);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function addCustomSettingsToForm(\ilPropertyFormGUI $a_form)
    {
        $roleWhiteList = new ilMultiSelectInputGUI(
            $this->lng->txt('cron_users_without_login_del_role_whitelist'),
            'role_whitelist'
        );
        $roleWhiteList->setInfo($this->lng->txt('cron_users_without_login_del_role_whitelist_info'));
        $roles = array();
        foreach ($this->rbacreview->getGlobalRoles() as $role_id) {
            if ($role_id != ANONYMOUS_ROLE_ID) {
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
        $threshold->setInfo($this->lng->txt('cron_users_without_login_del_create_date_thr_info'));
        $threshold->setValue($this->thresholdInDays);
        $threshold->setSuffix($this->lng->txt('days'));
        $threshold->setSize(4);
        $threshold->setMaxLength(4);
        $threshold->setRequired(true);
        $a_form->addItem($threshold);
    }

    /**
     * @inheritdoc
     */
    public function saveCustomSettings(\ilPropertyFormGUI $a_form)
    {
        $valid = true;

        $roleIdWhitelist = $this->request->getParsedBody()['role_whitelist'] ?? [];
        $this->roleIdWhiteliste = implode(',', array_map('intval', (is_array($roleIdWhitelist) ? $roleIdWhitelist : [])));

        $this->thresholdInDays = $this->request->getParsedBody()['threshold'] ?? '';

        if (!is_numeric($this->thresholdInDays) || $this->hasDecimals($this->thresholdInDays)) {
            $valid = false;
            $a_form->getItemByPostVar('threshold')->setAlert($this->lng->txt('user_never_logged_in_info_threshold_err_num'));
        }

        if ($valid) {
            $this->settings->set(
                'cron_users_without_login_delete_incl_roles',
                (string) $this->roleIdWhiteliste
            );
            $this->settings->set(
                'cron_users_without_login_delete_threshold',
                (int) $this->thresholdInDays
            );
            return true;
        } else {
            \ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
            return false;
        }
    }

    /**
     * @param mixed $number
     * @return bool
     */
    protected function hasDecimals($number) : bool
    {
        if (strpos($number, ',') !== false || strpos($number, '.') !== false) {
            return true;
        }

        return false;
    }
}
