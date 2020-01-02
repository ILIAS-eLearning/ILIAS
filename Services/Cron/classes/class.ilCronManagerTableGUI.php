<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Services/Cron/classes/class.ilCronJobResult.php';

/**
 * List all active cron jobs
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCron
 */
class ilCronManagerTableGUI extends ilTable2GUI
{
    /** @var ilLanguage */
    private $language;

    /** @var ilCtrl */
    private $controller;

    /**
     * Constructor
     *
     * @param ilObject $a_parent_obj
     * @param string $a_parent_cmd
     */
    public function __construct($a_parent_obj, $a_parent_cmd, \ILIAS\DI\Container $dic = null)
    {
        if ($dic === null) {
            global $DIC;
            $dic = $DIC;
        }

        $this->language = $dic->language();
        $this->controller = $dic->ctrl();

        $this->setId("crnmng"); // #14526 / #16391
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn("", "", 1);
        $this->addColumn($this->lng->txt("cron_job_id"), "title");
        $this->addColumn($this->lng->txt("cron_component"), "component");
        $this->addColumn($this->lng->txt("cron_schedule"), "schedule");
        $this->addColumn($this->lng->txt("cron_status"), "status");
        $this->addColumn($this->lng->txt("cron_status_info"), "");
        $this->addColumn($this->lng->txt("cron_result"), "result");
        $this->addColumn($this->lng->txt("cron_result_info"), "");
        $this->addColumn($this->lng->txt("cron_last_run"), "last_run");
        $this->addColumn($this->lng->txt("actions"), "");
        
        $this->setTitle($this->lng->txt("cron_jobs"));
        $this->setDefaultOrderField("title");
        
        $this->setSelectAllCheckbox("mjid");
        $this->addMultiCommand("activate", $this->language->txt("cron_action_activate"));
        $this->addMultiCommand("deactivate", $this->language->txt("cron_action_deactivate"));
        $this->addMultiCommand("reset", $this->language->txt("cron_action_reset"));
                        
        $this->setRowTemplate("tpl.cron_job_row.html", "Services/Cron");
        $this->setFormAction($this->controller->getFormAction($a_parent_obj, $a_parent_cmd));
                
        $this->getItems();
    }
    
    protected function parseJobToData(array $a_item, ilCronJob $job)
    {
        $res = $a_item;
        
        $res["title"] = $job->getTitle();
        $res["description"] = $job->getDescription();
        $res["has_settings"] = $job->hasCustomSettings();

        if (!$res["title"]) {
            $res["title"] = $a_item["job_id"];
        }

        // schedule
        if (!$job->hasFlexibleSchedule()) {
            // schedule type changed
            if ($a_item["schedule_type"]) {
                ilCronManager::updateJobSchedule($job, null, null);
            }

            $a_item["schedule_type"] = $job->getDefaultScheduleType();
            $a_item["schedule_value"] = $job->getDefaultScheduleValue();
            $res["editable_schedule"] = false;
        } else {
            // schedule type changed
            if (!$a_item["schedule_type"]) {
                $a_item["schedule_type"] = $job->getDefaultScheduleType();
                $a_item["schedule_value"] = $job->getDefaultScheduleValue();
                ilCronManager::updateJobSchedule(
                    $job,
                    $a_item["schedule_type"],
                    $a_item["schedule_value"]
                );
            }

            $res["editable_schedule"] = true;
        }

        switch ($a_item["schedule_type"]) {
            case ilCronJob::SCHEDULE_TYPE_DAILY:
                $schedule = $this->language->txt("cron_schedule_daily");
                break;

            case ilCronJob::SCHEDULE_TYPE_WEEKLY:
                $schedule = $this->language->txt("cron_schedule_weekly");
                break;

            case ilCronJob::SCHEDULE_TYPE_MONTHLY:
                $schedule = $this->language->txt("cron_schedule_monthly");
                break;

            case ilCronJob::SCHEDULE_TYPE_QUARTERLY:
                $schedule = $this->language->txt("cron_schedule_quarterly");
                break;

            case ilCronJob::SCHEDULE_TYPE_YEARLY:
                $schedule = $this->language->txt("cron_schedule_yearly");
                break;

            case ilCronJob::SCHEDULE_TYPE_IN_MINUTES:
                $schedule = sprintf($this->language->txt("cron_schedule_in_minutes"), $a_item["schedule_value"]);
                break;

            case ilCronJob::SCHEDULE_TYPE_IN_HOURS:
                $schedule = sprintf($this->language->txt("cron_schedule_in_hours"), $a_item["schedule_value"]);
                break;

            case ilCronJob::SCHEDULE_TYPE_IN_DAYS:
                $schedule = sprintf($this->language->txt("cron_schedule_in_days"), $a_item["schedule_value"]);
                break;
        }
        $res["schedule"] = $schedule;

        // status
        if ($a_item["job_status"]) {
            $res["status"] = $this->language->txt("cron_status_active");
        } else {
            $res["status"] = $this->language->txt("cron_status_inactive");
        }

        $status_info = array();
        if ($a_item["job_status_ts"]) {
            $status_info[] = ilDatePresentation::formatDate(new ilDateTime($a_item["job_status_ts"], IL_CAL_UNIX));
        }
        if (!$a_item["job_status_type"]) {
            $status_info[] = $this->language->txt("cron_changed_by_crontab");
        } else {
            $status_info[] = ilUserUtil::getNamePresentation($a_item["job_status_user_id"]);
        }
        $res["status_info"] = implode("<br />", $status_info);

        // result
        $result = "-";
        if ($a_item["job_result_status"]) {
            switch ($a_item["job_result_status"]) {
                case ilCronJobResult::STATUS_INVALID_CONFIGURATION:
                    $result = $this->language->txt("cron_result_status_invalid_configuration");
                    break;

                case ilCronJobResult::STATUS_NO_ACTION:
                    $result = $this->language->txt("cron_result_status_no_action");
                    break;

                case ilCronJobResult::STATUS_OK:
                    $result = $this->language->txt("cron_result_status_ok");
                    break;

                case ilCronJobResult::STATUS_CRASHED:
                    $result = $this->language->txt("cron_result_status_crashed");
                    break;

                case ilCronJobResult::STATUS_RESET:
                    $result = $this->language->txt("cron_result_status_reset");
                    break;
                
                case ilCronJobResult::STATUS_FAIL:
                    $result = $this->language->txt("cron_result_status_fail");
                    break;
            }
        }
        $res["result"] = $result;

        $result_info = array();
        if ($a_item["job_result_dur"]) {
            $result_info[] = ($a_item["job_result_dur"]/1000) . " sec";
        }

        // #23391 / #11866
        $resultCode = $a_item['job_result_code'];
        if (in_array($resultCode, ilCronJobResult::getCoreCodes())) {
            $result_info[] = $this->language->txt('cro_job_rc_' . $resultCode);
        } else {
            if ($a_item['job_result_message']) {
                $result_info[] = $a_item['job_result_message'];
            }
        }

        if (defined('DEVMODE') && DEVMODE) {
            $result_info[] = $resultCode;
        }

        if (!$a_item["job_result_type"]) {
            $result_info[] = $this->language->txt("cron_changed_by_crontab");
        } else {
            $result_info[] = ilUserUtil::getNamePresentation($a_item["job_result_user_id"]);
        }
        $res["result_info"] = implode("<br />", $result_info);

        if ($a_item["running_ts"]) {
            $res["last_run"] = strtotime("+1year", $a_item["running_ts"]);
        } elseif ($a_item["job_result_ts"]) {
            $res["last_run"] = $a_item["job_result_ts"];
        } else {
            $res["last_run"] = null;
        }
        
        $res['is_manually_executable'] = $job->isManuallyExecutable();
        
        return $res;
    }

    protected function getItems()
    {
        include_once "Services/User/classes/class.ilUserUtil.php";
        include_once "Services/Cron/classes/class.ilCronJobResult.php";
        
        // systems
        $data = ilCronManager::getCronJobData();
        foreach ($data as $idx => $item) {
            $job = ilCronManager::getJobInstance(
                $item["job_id"],
                $item["component"],
                $item["class"],
                $item["path"]
            );
            if ($job) {
                $data[$idx] = $this->parseJobToData($item, $job);
            }
        }
        
        // plugins
        $this->language->loadLanguageModule("cmps");
        foreach (ilCronManager::getPluginJobs() as $item) {
            $job = $item[0];
            $item = $item[1];
            
            $item["job_id"] = "pl__" . $item["component"] . "__" . $job->getId();
            $item["component"] = $this->language->txt("cmps_plugin") . "/" . $item["component"];

            $data[] = $this->parseJobToData($item, $job);
        }
                
        $this->setData($data);
    }

    protected function fillRow($a_set)
    {
        $this->tpl->setVariable("VAL_ID", $a_set["title"]);
        $this->tpl->setVariable("VAL_JID", $a_set["job_id"]);
        
        if ($a_set["description"]) {
            $this->tpl->setVariable("VAL_DESC", $a_set["description"]);
        }
        
        $this->tpl->setVariable("VAL_COMPONENT", $a_set["component"]);
        $this->tpl->setVariable("VAL_SCHEDULE", $a_set["schedule"]);
        $this->tpl->setVariable("VAL_STATUS", $a_set["status"]);
        $this->tpl->setVariable("VAL_STATUS_INFO", $a_set["status_info"]);
        $this->tpl->setVariable("VAL_RESULT", $a_set["result"]);
        $this->tpl->setVariable("VAL_RESULT_INFO", $a_set["result_info"]);
        if ($a_set["last_run"] > time()) {
            $a_set["last_run"] = $this->language->txt("cron_running_since") . " " .
                ilDatePresentation::formatDate(new ilDateTime($a_set["running_ts"], IL_CAL_UNIX));
            
            // job has pinged
            if ($a_set["alive_ts"] != $a_set["running_ts"]) {
                $a_set["last_run"] .= "<br />(Ping: " .
                    ilDatePresentation::formatDate(new ilDateTime($a_set["alive_ts"], IL_CAL_UNIX)) . ")";
            }
        } elseif ($a_set["last_run"]) {
            $a_set["last_run"] = ilDatePresentation::formatDate(new ilDateTime($a_set["last_run"], IL_CAL_UNIX));
        }
        $this->tpl->setVariable("VAL_LAST_RUN", $a_set["last_run"] ? $a_set["last_run"] : "-");
        
        
        // actions
        
        $actions = array();
        
        if (!$a_set["running_ts"]) {
            // reset
            if ($a_set["job_result_status"] == ilCronJobResult::STATUS_CRASHED) {
                $actions[] = "reset";
            }
            // activate
            elseif (!$a_set["job_status"]) {
                $actions[] = "activate";
            }
            // deactivate
            else {
                if ($a_set['is_manually_executable']) {
                    $actions[] = 'run';
                }
                $actions[] = "deactivate";
            }
            // edit (schedule)
            if ($a_set["editable_schedule"] || $a_set["has_settings"]) {
                $actions[] = "edit";
            }
            
            $this->controller->setParameter($this->getParentObject(), "jid", $a_set["job_id"]);
            
            foreach ($actions as $action) {
                $this->tpl->setCurrentBlock("action_bl");
                $this->tpl->setVariable(
                    "URL_ACTION",
                    $this->controller->getLinkTarget($this->getParentObject(), $action)
                );
                $this->tpl->setVariable("TXT_ACTION", $this->language->txt("cron_action_" . $action));
                $this->tpl->parseCurrentBlock();
            }
            
            $this->controller->setParameter($this->getParentObject(), "jid", "");
        }
    }
}
