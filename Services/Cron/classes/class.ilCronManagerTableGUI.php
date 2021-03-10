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
    /** @var bool */
    private $mayWrite;

    /**
     * ilCronManagerTableGUI constructor.
     * @param object $a_parent_obj
     * @param $a_parent_cmd
     * @param \ILIAS\DI\Container|null $dic
     * @param bool $mayWrite
     */
    public function __construct(
        object $a_parent_obj,
        $a_parent_cmd,
        \ILIAS\DI\Container $dic = null,
        bool $mayWrite = false
    ) {
        if ($dic === null) {
            global $DIC;
            $dic = $DIC;
        }

        $this->language = $dic->language();
        $this->controller = $dic->ctrl();
        $this->mayWrite = $mayWrite;

        $this->setId('crnmng'); // #14526 / #16391
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        if ($this->mayWrite) {
            $this->addColumn("", "", 1);
        }
        $this->addColumn($this->lng->txt('cron_job_id'), 'title');
        $this->addColumn($this->lng->txt('cron_component'), 'component');
        $this->addColumn($this->lng->txt('cron_schedule'), 'schedule');
        $this->addColumn($this->lng->txt('cron_status'), 'status');
        $this->addColumn($this->lng->txt('cron_status_info'), '');
        $this->addColumn($this->lng->txt('cron_result'), 'result');
        $this->addColumn($this->lng->txt('cron_result_info'), '');
        $this->addColumn($this->lng->txt('cron_last_run'), 'last_run');
        if ($this->mayWrite) {
            $this->addColumn($this->lng->txt('actions'), '');
        }

        $this->setTitle($this->lng->txt('cron_jobs'));
        $this->setDefaultOrderField('title');

        if ($this->mayWrite) {
            $this->setSelectAllCheckbox('mjid');
            $this->addMultiCommand('activate', $this->language->txt('cron_action_activate'));
            $this->addMultiCommand('deactivate', $this->language->txt('cron_action_deactivate'));
            $this->addMultiCommand('reset', $this->language->txt('cron_action_reset'));
        }
                        
        $this->setRowTemplate('tpl.cron_job_row.html', 'Services/Cron');
        $this->setFormAction($this->controller->getFormAction($a_parent_obj, $a_parent_cmd));
    }

    /**
     * @param ilCronJobEntity $entity
     * @param array $row
     * @return string
     */
    private function formatSchedule(ilCronJobEntity $entity, array $row) : string
    {
        $schedule = '';
        switch ($entity->getEffectiveScheduleType()) {
            case ilCronJob::SCHEDULE_TYPE_DAILY:
                $schedule = $this->language->txt('cron_schedule_daily');
                break;

            case ilCronJob::SCHEDULE_TYPE_WEEKLY:
                $schedule = $this->language->txt('cron_schedule_weekly');
                break;

            case ilCronJob::SCHEDULE_TYPE_MONTHLY:
                $schedule = $this->language->txt('cron_schedule_monthly');
                break;

            case ilCronJob::SCHEDULE_TYPE_QUARTERLY:
                $schedule = $this->language->txt('cron_schedule_quarterly');
                break;

            case ilCronJob::SCHEDULE_TYPE_YEARLY:
                $schedule = $this->language->txt('cron_schedule_yearly');
                break;

            case ilCronJob::SCHEDULE_TYPE_IN_MINUTES:
                $schedule = sprintf(
                    $this->language->txt('cron_schedule_in_minutes'),
                    $entity->getEffectiveScheduleValue()
                );
                break;

            case ilCronJob::SCHEDULE_TYPE_IN_HOURS:
                $schedule = sprintf(
                    $this->language->txt('cron_schedule_in_hours'),
                    $entity->getEffectiveScheduleValue()
                );
                break;

            case ilCronJob::SCHEDULE_TYPE_IN_DAYS:
                $schedule = sprintf(
                    $this->language->txt('cron_schedule_in_days'),
                    $entity->getEffectiveScheduleValue()
                );
                break;
        }

        return $schedule;
    }

    /**
     * @param ilCronJobEntity $entity
     * @return string
     * @throws ilDateTimeException
     */
    private function formatStatusInfo(ilCronJobEntity $entity) : string
    {
        $status_info = [];
        if ($entity->getJobStatusTimestamp()) {
            $status_info[] = ilDatePresentation::formatDate(
                new ilDateTime($entity->getJobStatusTimestamp(), IL_CAL_UNIX)
            );
        }

        if ($entity->getJobStatusType()) {
            $status_info[] = ilUserUtil::getNamePresentation($entity->getJobStatusUsrId());
        } else {
            $status_info[] = $this->language->txt('cron_changed_by_crontab');
        }

        return implode('<br />', $status_info);
    }

    /**
     * @param ilCronJobEntity $entity
     * @return string
     */
    private function formatResult(ilCronJobEntity $entity) : string
    {
        $result = '-';
        if ($entity->getJobResultStatus()) {
            switch ($entity->getJobResultStatus()) {
                case ilCronJobResult::STATUS_INVALID_CONFIGURATION:
                    $result = $this->language->txt('cron_result_status_invalid_configuration');
                    break;

                case ilCronJobResult::STATUS_NO_ACTION:
                    $result = $this->language->txt('cron_result_status_no_action');
                    break;

                case ilCronJobResult::STATUS_OK:
                    $result = $this->language->txt('cron_result_status_ok');
                    break;

                case ilCronJobResult::STATUS_CRASHED:
                    $result = $this->language->txt('cron_result_status_crashed');
                    break;

                case ilCronJobResult::STATUS_RESET:
                    $result = $this->language->txt('cron_result_status_reset');
                    break;

                case ilCronJobResult::STATUS_FAIL:
                    $result = $this->language->txt('cron_result_status_fail');
                    break;
            }
        }

        return $result;
    }

    /**
     * @param ilCronJobEntity $entity
     * @return string
     */
    private function formatResultInfo(ilCronJobEntity $entity) : string
    {
        $result_info = [];
        if ($entity->getJobResultDuration()) {
            $result_info[] = ($entity->getJobResultDuration() / 1000) . ' sec';
        }

        // #23391 / #11866
        $resultCode = $entity->getJobResultCode();
        if (in_array($resultCode, ilCronJobResult::getCoreCodes())) {
            $result_info[] = $this->language->txt('cro_job_rc_' . $resultCode);
        } elseif ($entity->getJobResultMessage()) {
            $result_info[] = $entity->getJobResultMessage();
        }

        if (defined('DEVMODE') && DEVMODE) {
            $result_info[] = $resultCode;
        }

        if ($entity->getJobResultType()) {
            $result_info[] = ilUserUtil::getNamePresentation($entity->getJobResultUsrId());
        } else {
            $result_info[] = $this->language->txt('cron_changed_by_crontab');
        }

        return implode('<br />', $result_info);
    }

    /**
     * @param ilCronJobCollection $collection
     * @return $this
     * @throws ilDateTimeException
     */
    public function populate(ilCronJobCollection $collection) : self
    {
        $this->setData(array_map(function (ilCronJobEntity $entity) : array {
            $row = [];

            $row['schedule'] = $this->formatSchedule($entity, $row);
            $row['status'] = $this->language->txt('cron_status_inactive');
            if ($entity->getJobStatus()) {
                $row['status'] = $this->language->txt('cron_status_active');
            }
            $row['status_info'] = $this->formatStatusInfo($entity);
            $row['result'] = $this->formatResult($entity);
            $row['result_info'] = $this->formatResultInfo($entity);

            $row['last_run'] = null;
            if ($entity->getRunningTimestamp()) {
                $row['last_run'] = strtotime('+1year', $entity->getRunningTimestamp());
            } elseif ($entity->getJobResultTimestamp()) {
                $row['last_run'] = $entity->getJobResultTimestamp();
            }

            $row['job_id'] = $entity->getJobId();
            $row['component'] = $entity->getComponent();
            if ($entity->isPlugin()) {
                $row['job_id'] = 'pl__' . $row['component'] . '__' . $row['job_id'];
                $row['component'] = $this->language->txt('cmps_plugin') . '/' . $row['component'];
            }

            $row['title'] = $entity->getEffectiveTitle();
            $row['description'] = $entity->getJob()->getDescription();
            $row['is_manually_executable'] = $entity->getJob()->isManuallyExecutable();
            $row['has_settings'] = $entity->getJob()->hasCustomSettings();
            $row['job_result_status'] = $entity->getJobResultStatus();
            $row['job_status'] = $entity->getJobStatus();
            $row['alive_ts'] = $entity->getAliveTimestamp();
            $row['running_ts'] = $entity->getRunningTimestamp();

            if ($entity->getJob()->hasFlexibleSchedule()) {
                $row['editable_schedule'] = true;
                if (!$entity->getScheduleType()) {
                    ilCronManager::updateJobSchedule(
                        $entity->getJob(),
                        $entity->getEffectiveScheduleType(),
                        $entity->getEffectiveScheduleValue()
                    );
                }
            } elseif ($entity->getScheduleType()) {
                ilCronManager::updateJobSchedule($entity->getJob(), null, null);
            }
            
            return $row;
        }, $collection->toArray()));

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function fillRow($a_set)
    {
        if ($this->mayWrite) {
            $this->tpl->setVariable('VAL_JID', $a_set['job_id']);
        }
        $this->tpl->setVariable('VAL_ID', $a_set['title']);

        if ($a_set['description']) {
            $this->tpl->setVariable('VAL_DESC', $a_set['description']);
        }
        
        $this->tpl->setVariable('VAL_COMPONENT', $a_set['component']);
        $this->tpl->setVariable('VAL_SCHEDULE', $a_set['schedule']);
        $this->tpl->setVariable('VAL_STATUS', $a_set['status']);
        $this->tpl->setVariable('VAL_STATUS_INFO', $a_set['status_info']);
        $this->tpl->setVariable('VAL_RESULT', $a_set['result']);
        $this->tpl->setVariable('VAL_RESULT_INFO', $a_set['result_info']);
        if ($a_set['last_run'] > time()) {
            $a_set['last_run'] = $this->language->txt('cron_running_since') . ' ' .
                ilDatePresentation::formatDate(new ilDateTime($a_set['running_ts'], IL_CAL_UNIX));

            // job has pinged
            if ($a_set['alive_ts'] != $a_set['running_ts']) {
                $a_set['last_run'] .= '<br />(Ping: ' .
                    ilDatePresentation::formatDate(new ilDateTime($a_set['alive_ts'], IL_CAL_UNIX)) . ')';
            }
        } elseif ($a_set['last_run']) {
            $a_set['last_run'] = ilDatePresentation::formatDate(new ilDateTime($a_set['last_run'], IL_CAL_UNIX));
        }
        $this->tpl->setVariable('VAL_LAST_RUN', $a_set['last_run'] ? $a_set['last_run'] : '-');

        $actions = [];
        if ($this->mayWrite && !$a_set['running_ts']) {
            if ($a_set['job_result_status'] == ilCronJobResult::STATUS_CRASHED) {
                $actions[] = 'reset';
            } elseif (!$a_set['job_status']) {
                $actions[] = 'activate';
            } else {
                if ($a_set['is_manually_executable']) {
                    $actions[] = 'run';
                }
                $actions[] = 'deactivate';
            }

            if ($a_set['editable_schedule'] || $a_set['has_settings']) {
                $actions[] = 'edit';
            }

            $this->controller->setParameter($this->getParentObject(), 'jid', $a_set['job_id']);
            foreach ($actions as $action) {
                $this->tpl->setCurrentBlock('action_bl');
                $this->tpl->setVariable(
                    'URL_ACTION',
                    $this->controller->getLinkTarget($this->getParentObject(), $action)
                );
                $this->tpl->setVariable('TXT_ACTION', $this->language->txt('cron_action_' . $action));
                $this->tpl->parseCurrentBlock();
            }
            $this->controller->setParameter($this->getParentObject(), 'jid', '');
        }
    }
}
