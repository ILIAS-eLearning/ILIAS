<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Soft disk quota notifications
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilDAVCronDiskQuota extends ilCronJob
{
    public function getId()
    {
        return "rep_disk_quota";
    }
    
    public function getTitle()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule("file");
        return $lng->txt("repository_disk_quota");
    }
    
    public function getDescription()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule("file");
        return $lng->txt("repository_disk_quota_info");
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
        return false;
    }
    
    public function hasCustomSettings()
    {
        return true;
    }

    public function run()
    {
        require_once'./Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
        if (ilDiskQuotaActivationChecker::_isActive()) {
            require_once'./Services/WebDAV/classes/class.ilDiskQuotaChecker.php';
            ilDiskQuotaChecker::_updateDiskUsageReport();
                        
            if (ilDiskQuotaActivationChecker::_isReminderMailActive()) {
                ilDiskQuotaChecker::_sendReminderMails();
            }

            if (ilDiskQuotaActivationChecker::_isSummaryMailActive()) {
                ilDiskQuotaChecker::_sendSummaryMails();
            }
        }
    
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }
    
    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule("file");
        
        require_once 'Services/WebDAV/classes/class.ilObjDiskQuotaSettings.php';
        $disk_quota_obj = ilObjDiskQuotaSettings::getInstance();

        // Enable disk quota reminder mail
        $cb_prop_reminder = new ilCheckboxInputGUI($lng->txt("enable_disk_quota_reminder_mail"), "enable_disk_quota_reminder_mail");
        $cb_prop_reminder->setValue('1');
        $cb_prop_reminder->setChecked($disk_quota_obj->isDiskQuotaReminderMailEnabled());
        $cb_prop_reminder->setInfo($lng->txt('disk_quota_reminder_mail_desc'));
        $a_form->addItem($cb_prop_reminder);
        
        // Enable summary mail for certain users
        $cb_prop_summary= new ilCheckboxInputGUI($lng->txt("enable_disk_quota_summary_mail"), "enable_disk_quota_summary_mail");
        $cb_prop_summary->setValue(1);
        $cb_prop_summary->setChecked($disk_quota_obj->isDiskQuotaSummaryMailEnabled());
        $cb_prop_summary->setInfo($lng->txt('enable_disk_quota_summary_mail_desc'));
        $a_form->addItem($cb_prop_summary);
        
        // Edit disk quota recipients
        $summary_rcpt = new ilTextInputGUI($lng->txt("disk_quota_summary_rctp"), "disk_quota_summary_rctp");
        $summary_rcpt->setValue($disk_quota_obj->getSummaryRecipients());
        $summary_rcpt->setInfo($lng->txt('disk_quota_summary_rctp_desc'));
        $cb_prop_summary->addSubItem($summary_rcpt);
    }
    
    public function saveCustomSettings(ilPropertyFormGUI $a_form)
    {
        require_once 'Services/WebDAV/classes/class.ilObjDiskQuotaSettings.php';
        $disk_quota_obj = ilObjDiskQuotaSettings::getInstance();
        $disk_quota_obj->setDiskQuotaReminderMailEnabled($_POST['enable_disk_quota_reminder_mail'] == '1');
        $disk_quota_obj->isDiskQuotaSummaryMailEnabled($_POST['enable_disk_quota_summary_mail'] == '1');
        $disk_quota_obj->setSummaryRecipients(ilUtil::stripSlashes($_POST['disk_quota_summary_rctp']));
        $disk_quota_obj->update();
            
        return true;
    }
    
    public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule("file");
        
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_REPOSITORY:
            case ilAdministrationSettingsFormHandler::FORM_FILES_QUOTA:
            
                require_once('Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php');
                
                $subitems = array(
                    "enable_disk_quota_reminder_mail" => array(
                        ilDiskQuotaActivationChecker::_isReminderMailActive(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                        ),
                    "enable_disk_quota_summary_mail" => array(
                        ilDiskQuotaActivationChecker::_isSummaryMailActive(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                        )
                );
                $a_fields["repository_disk_quota"] = array($a_is_active ?
                    $lng->txt("enabled") :
                    $lng->txt("disabled"),
                    null, $subitems);
                break;
        }
    }
    
    public function activationWasToggled($a_currently_active)
    {
        // #12221
        $settings = new ilSetting('disk_quota');
        $settings->set('enabled', $a_currently_active);
        
        /* objDefinition is not available in setup, we cannot use ilObject
        require_once 'Services/WebDAV/classes/class.ilObjDiskQuotaSettings.php';
        $disk_quota_obj = ilObjDiskQuotaSettings::getInstance();
        $disk_quota_obj->setDiskQuotaEnabled((bool)$a_currently_active);
        $disk_quota_obj->update();
        */
    }
}
