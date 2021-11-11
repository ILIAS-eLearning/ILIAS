<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This cron deletes user accounts by INACTIVATION period
 * @author Bjoern Heyser <bheyser@databay.de>
 * @package Services/User
 */
class ilCronDeleteInactivatedUserAccounts extends ilCronJob
{
    private const DEFAULT_INACTIVITY_PERIOD = 365;
    private int $period;
    /** @var int[]|null */
    private ?array $include_roles = null;
    
    public function __construct()
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        if (is_object($ilSetting)) {
            $this->include_roles = $ilSetting->get(
                'cron_inactivated_user_delete_include_roles',
                null
            );
            if ($this->include_roles === null) {
                $this->include_roles = [];
            } else {
                $this->include_roles = array_filter(array_map('intval', explode(',', $this->include_roles)));
            }

            $this->period = (int) $ilSetting->get(
                'cron_inactivated_user_delete_period',
                (string) self::DEFAULT_INACTIVITY_PERIOD
            );
        }
    }
    
    public function getId() : string
    {
        return "user_inactivated";
    }
    
    public function getTitle() : string
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        return $lng->txt("delete_inactivated_user_accounts");
    }
    
    public function getDescription() : string
    {
        global $DIC;

        $lng = $DIC['lng'];

        return sprintf(
            $lng->txt("delete_inactivated_user_accounts_desc"),
            $this->period
        );
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
    
    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form) : void
    {
        global $DIC;

        $lng = $DIC->language();
        $rbacreview = $DIC->rbac()->review();
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilSetting = $DIC->settings();

        $sub_mlist = new ilMultiSelectInputGUI(
            $lng->txt('delete_inactivated_user_accounts_include_roles'),
            'cron_inactivated_user_delete_include_roles'
        );
        $sub_mlist->setInfo($lng->txt('delete_inactivated_user_accounts_include_roles_desc'));
        $roles = [];
        foreach ($rbacreview->getGlobalRoles() as $role_id) {
            if ($role_id !== ANONYMOUS_ROLE_ID) {
                $roles[$role_id] = $ilObjDataCache->lookupTitle($role_id);
            }
        }
        $sub_mlist->setOptions($roles);
        $setting = $ilSetting->get('cron_inactivated_user_delete_include_roles', null);
        if ($setting === null) {
            $setting = [];
        } else {
            $setting = explode(',', $setting);
        }
        $sub_mlist->setValue($setting);
        $sub_mlist->setWidth(300);
        $a_form->addItem($sub_mlist);

        $sub_text = new ilNumberInputGUI(
            $lng->txt('delete_inactivated_user_accounts_period'),
            'cron_inactivated_user_delete_period'
        );
        $sub_text->allowDecimals(false);
        $sub_text->setInfo($lng->txt('delete_inactivated_user_accounts_period_desc'));
        $sub_text->setValue((string) $ilSetting->get('cron_inactivated_user_delete_period', (string) self::DEFAULT_INACTIVITY_PERIOD));
        $sub_text->setSize(4);
        $sub_text->setMaxLength(4);
        $sub_text->setRequired(true);
        $a_form->addItem($sub_text);
    }
    
    public function saveCustomSettings(ilPropertyFormGUI $a_form) : bool
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $setting = implode(',', (string) ($_POST['cron_inactivated_user_delete_include_roles'] ?? ''));
        $ilSetting->set('cron_inactivated_user_delete_include_roles', $setting);
        $ilSetting->set(
            'cron_inactivated_user_delete_period',
            (string) ($_POST['cron_inactivated_user_delete_period'] ?? self::DEFAULT_INACTIVITY_PERIOD)
        );

        return true;
    }
}
