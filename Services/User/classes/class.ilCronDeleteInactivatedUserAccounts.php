<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * This cron deletes user accounts by INACTIVATION period
 *
 * @author Bjoern Heyser <bheyser@databay.de>
 * @version $Id$
 *
 * @package Services/User
 */
class ilCronDeleteInactivatedUserAccounts extends ilCronJob
{
    const DEFAULT_INACTIVITY_PERIOD = 365;

    private $period = null;

    private $include_roles = null;
    
    public function __construct()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        if (is_object($ilSetting)) {
            $this->include_roles = $ilSetting->get(
                'cron_inactivated_user_delete_include_roles',
                null
            );
            if ($this->include_roles === null) {
                $this->include_roles = array();
            } else {
                $this->include_roles = explode(',', $this->include_roles);
            }

            $this->period = $ilSetting->get(
                'cron_inactivated_user_delete_period',
                self::DEFAULT_INACTIVITY_PERIOD
            );
        }
    }
    
    public function getId()
    {
        return "user_inactivated";
    }
    
    public function getTitle()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        return $lng->txt("delete_inactivated_user_accounts");
    }
    
    public function getDescription()
    {
        global $DIC;

        $lng = $DIC['lng'];

        return sprintf(
            $lng->txt("delete_inactivated_user_accounts_desc"),
            $this->period
        );
    }
    
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }
    
    public function getDefaultScheduleValue()
    {
        return;
    }
    
    public function hasAutoActivation()
    {
        return false;
    }
    
    public function hasFlexibleSchedule()
    {
        return true;
    }
    
    public function hasCustomSettings()
    {
        return true;
    }
    
    public function run()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        
        $status = ilCronJobResult::STATUS_NO_ACTION;
                
        $usr_ids = ilObjUser::_getUserIdsByInactivationPeriod($this->period);

        $counter = 0;
        foreach ($usr_ids as $usr_id) {
            if ($usr_id == ANONYMOUS_USER_ID || $usr_id == SYSTEM_USER_ID) {
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
        
        if ($counter) {
            $status = ilCronJobResult::STATUS_OK;
        }
        $result = new ilCronJobResult();
        $result->setStatus($status);
        return $result;
    }
    
    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $rbacreview = $DIC['rbacreview'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilSetting = $DIC['ilSetting'];

        include_once('Services/Form/classes/class.ilMultiSelectInputGUI.php');
        $sub_mlist = new ilMultiSelectInputGUI(
            $lng->txt('delete_inactivated_user_accounts_include_roles'),
            'cron_inactivated_user_delete_include_roles'
        );
        $sub_mlist->setInfo($lng->txt('delete_inactivated_user_accounts_include_roles_desc'));
        $roles = array();
        foreach ($rbacreview->getGlobalRoles() as $role_id) {
            if ($role_id != ANONYMOUS_ROLE_ID) {
                $roles[$role_id] = $ilObjDataCache->lookupTitle($role_id);
            }
        }
        $sub_mlist->setOptions($roles);
        $setting = $ilSetting->get('cron_inactivated_user_delete_include_roles', null);
        if ($setting === null) {
            $setting = array();
        } else {
            $setting = explode(',', $setting);
        }
        $sub_mlist->setValue($setting);
        $sub_mlist->setWidth(300);
        #$sub_mlist->setHeight(100);
        $a_form->addItem($sub_mlist);

        $default_setting = self::DEFAULT_INACTIVITY_PERIOD;
        $sub_text = new ilNumberInputGUI(
            $lng->txt('delete_inactivated_user_accounts_period'),
            'cron_inactivated_user_delete_period'
        );
        $sub_text->allowDecimals(false);
        $sub_text->setInfo($lng->txt('delete_inactivated_user_accounts_period_desc'));
        $sub_text->setValue($ilSetting->get("cron_inactivated_user_delete_period", $default_setting));
        $sub_text->setSize(4);
        $sub_text->setMaxLength(4);
        $sub_text->setRequired(true);
        $a_form->addItem($sub_text);
    }
    
    public function saveCustomSettings(ilPropertyFormGUI $a_form)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $setting = implode(',', $_POST['cron_inactivated_user_delete_include_roles']);
        if (!strlen($setting)) {
            $setting = null;
        }
        $ilSetting->set('cron_inactivated_user_delete_include_roles', $setting);
        $ilSetting->set('cron_inactivated_user_delete_period', $_POST['cron_inactivated_user_delete_period']);
        
        return true;
    }
}
