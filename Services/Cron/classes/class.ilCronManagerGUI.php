<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilCronManagerGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilCronManagerGUI: ilPropertyFormGUI
 * @ingroup ServicesCron
 */
class ilCronManagerGUI
{
    /** @var \ilLanguage */
    protected $lng;
    /** @var \ilCtrl */
    protected $ctrl;
    /** @var \ilSetting */
    protected $settings;
    /** @var \ilTemplate */
    protected $tpl;
    /** @var Factory */
    private $uiFactory;
    /** @var Renderer */
    private $uiRenderer;
    /** @var ilUIService */
    private $uiService;
    /** @var ilCronJobRepository */
    private $repository;
    /** @var \ILIAS\DI\RBACServices */
    private $rbac;
    /** @var ilErrorHandling */
    private $error;
    /** @var \ILIAS\DI\Container */
    private $dic;

    /**
     * ilCronManagerGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->settings = $DIC->settings();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->uiService = $DIC->uiService();
        $this->dic = $DIC;
        $this->rbac = $DIC->rbac();
        $this->error = $DIC['ilErr'];
        $this->repository = new ilCronJobRepositoryImpl();

        $this->lng->loadLanguageModule('cron');
        $this->lng->loadLanguageModule('cmps');
    }

    public function executeCommand() : void
    {
        if (!$this->rbac->system()->checkAccess('visible,read', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $class = $this->ctrl->getNextClass($this);

        switch (strtolower($class)) {
            case ilPropertyFormGUI::class:
                $form = $this->initEditForm(ilUtil::stripSlashes($_REQUEST['jid']));
                $this->ctrl->forwardCommand($form);
                break;
        }

        $cmd = $this->ctrl->getCmd('render');
        $this->$cmd();
    }

    protected function render() : void
    {
        $tstamp = $this->lng->txt('cronjob_last_start_unknown');
        if ($this->settings->get('last_cronjob_start_ts')) {
            $tstamp = ilDatePresentation::formatDate(new ilDateTime($this->settings->get('last_cronjob_start_ts'), IL_CAL_UNIX));
        }

        $message = $this->uiFactory->messageBox()->info($this->lng->txt('cronjob_last_start') . ': ' . $tstamp);

        $cronJobs = $this->repository->findAll();

        $tableFilterMediator = new ilCronManagerTableFilterMediator(
            $cronJobs,
            $this->uiFactory,
            $this->uiService,
            $this->lng
        );
        $filter = $tableFilterMediator->filter($this->ctrl->getFormAction(
            $this,
            'render',
            '',
            true
        ));

        $tbl = new ilCronManagerTableGUI(
            $this,
            'render',
            $this->dic,
            $this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)
        );
        $this->tpl->setContent(implode('', [
            $this->uiRenderer->render([$message, $filter]),
            $tbl->populate(
                $tableFilterMediator->filteredJobs(
                    $filter,
                    false
                )
            )->getHTML()
        ]));
    }
    
    public function edit(ilPropertyFormGUI $a_form = null)
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $id = $_REQUEST["jid"];
        if (!$id) {
            $this->ctrl->redirect($this, "render");
        }
        
        if (!$a_form) {
            $a_form = $this->initEditForm($id);
        }
        
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * @param int $scheduleTypeId
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getScheduleTypeFormElementName(int $scheduleTypeId)
    {
        switch ($scheduleTypeId) {
            case ilCronJob::SCHEDULE_TYPE_DAILY:
                return $this->lng->txt('cron_schedule_daily');

            case ilCronJob::SCHEDULE_TYPE_WEEKLY:
                return $this->lng->txt('cron_schedule_weekly');

            case ilCronJob::SCHEDULE_TYPE_MONTHLY:
                return $this->lng->txt('cron_schedule_monthly');

            case ilCronJob::SCHEDULE_TYPE_QUARTERLY:
                return $this->lng->txt('cron_schedule_quarterly');

            case ilCronJob::SCHEDULE_TYPE_YEARLY:
                return $this->lng->txt('cron_schedule_yearly');

            case ilCronJob::SCHEDULE_TYPE_IN_MINUTES:
                return sprintf($this->lng->txt('cron_schedule_in_minutes'), 'x');

            case ilCronJob::SCHEDULE_TYPE_IN_HOURS:
                return sprintf($this->lng->txt('cron_schedule_in_hours'), 'x');

            case ilCronJob::SCHEDULE_TYPE_IN_DAYS:
                return sprintf($this->lng->txt('cron_schedule_in_days'), 'x');
        }

        throw new \InvalidArgumentException(sprintf('The passed argument %s is invalid!', var_export($scheduleTypeId, 1)));
    }

    /**
     * @param int $scheduleTypeId
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getScheduleValueFormElementName(int $scheduleTypeId)
    {
        switch ($scheduleTypeId) {
            case ilCronJob::SCHEDULE_TYPE_IN_MINUTES:
                return 'smini';

            case ilCronJob::SCHEDULE_TYPE_IN_HOURS:
                return 'shri';

            case ilCronJob::SCHEDULE_TYPE_IN_DAYS:
                return 'sdyi';
        }

        throw new \InvalidArgumentException(sprintf('The passed argument %s is invalid!', var_export($scheduleTypeId, 1)));
    }

    /**
     * @param int $scheduleTypeId
     * @return bool
     */
    protected function hasScheduleValue(int $scheduleTypeId) : bool
    {
        return in_array(
            $scheduleTypeId,
            [
                ilCronJob::SCHEDULE_TYPE_IN_MINUTES,
                ilCronJob::SCHEDULE_TYPE_IN_HOURS,
                ilCronJob::SCHEDULE_TYPE_IN_DAYS
            ]
        );
    }

    protected function initEditForm($a_job_id)
    {
        $job = ilCronManager::getJobInstanceById($a_job_id);
        if (!$job) {
            $this->ctrl->redirect($this, "render");
        }

        $this->ctrl->setParameter($this, "jid", $a_job_id);

        $jobData = ilCronManager::getCronJobData($job->getId());
        $data = array_pop($jobData);

        include_once("Services/Cron/classes/class.ilCronJob.php");
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "update"));
        $form->setTitle($this->lng->txt("cron_action_edit") . ': "' . $job->getTitle() . '"');

        if ($job->hasFlexibleSchedule()) {
            $type = new ilRadioGroupInputGUI($this->lng->txt('cron_schedule_type'), 'type');
            $type->setRequired(true);
            $type->setValue($data['schedule_type']);

            foreach ($job->getAllScheduleTypes() as $typeId) {
                if (!in_array($typeId, $job->getValidScheduleTypes())) {
                    continue;
                }

                $option = new ilRadioOption(
                    $this->getScheduleTypeFormElementName($typeId),
                    $typeId
                );
                $type->addOption($option);

                if (in_array($typeId, $job->getScheduleTypesWithValues())) {
                    $scheduleValue = new ilNumberInputGUI(
                        $this->lng->txt('cron_schedule_value'),
                        $this->getScheduleValueFormElementName($typeId)
                    );
                    $scheduleValue->allowDecimals(false);
                    $scheduleValue->setRequired(true);
                    $scheduleValue->setSize(5);
                    if ($data['schedule_type'] == $typeId) {
                        $scheduleValue->setValue($data['schedule_value']);
                    }
                    $option->addSubItem($scheduleValue);
                }
            }

            $form->addItem($type);
        }
        
        if ($job->hasCustomSettings()) {
            $job->addCustomSettingsToForm($form);
        }
        
        $form->addCommandButton("update", $this->lng->txt("save"));
        $form->addCommandButton("render", $this->lng->txt("cancel"));
        
        return $form;
    }
    
    public function update()
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $id = $_REQUEST["jid"];
        if (!$id) {
            $this->ctrl->redirect($this, "render");
        }

        $form = $this->initEditForm($id);
        if ($form->checkInput()) {
            $job = ilCronManager::getJobInstanceById($id);
            if ($job) {
                $valid = true;
                if ($job->hasCustomSettings() &&
                    !$job->saveCustomSettings($form)) {
                    $valid = false;
                }
                
                if ($valid && $job->hasFlexibleSchedule()) {
                    $type = $form->getInput("type");
                    switch (true) {
                        case $this->hasScheduleValue($type):
                            $value = $form->getInput($this->getScheduleValueFormElementName($type));
                            break;

                        default:
                            $value = null;
                            break;
                    }

                    ilCronManager::updateJobSchedule($job, $type, $value);
                }
                if ($valid) {
                    ilUtil::sendSuccess($this->lng->txt("cron_action_edit_success"), true);
                    $this->ctrl->redirect($this, "render");
                }
            }
        }
        
        $form->setValuesByPost();
        $this->edit($form);
    }
        
    public function run()
    {
        $this->confirm("run");
    }
    
    public function confirmedRun()
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $job_id = $_GET["jid"];
        if ($job_id) {
            if (ilCronManager::runJobManual($job_id)) {
                ilUtil::sendSuccess($this->lng->txt("cron_action_run_success"), true);
            } else {
                ilUtil::sendFailure($this->lng->txt("cron_action_run_fail"), true);
            }
        }

        $this->ctrl->redirect($this, "render");
    }
    
    public function activate()
    {
        $this->confirm("activate");
    }
    
    public function confirmedActivate()
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $jobs = $this->getMultiActionData();
        if ($jobs) {
            foreach ($jobs as $job) {
                if (ilCronManager::isJobInactive($job->getId())) {
                    ilCronManager::resetJob($job);
                    ilCronManager::activateJob($job, true);
                }
            }
            
            ilUtil::sendSuccess($this->lng->txt("cron_action_activate_success"), true);
        }

        $this->ctrl->redirect($this, "render");
    }
    
    public function deactivate()
    {
        $this->confirm("deactivate");
    }
    
    public function confirmedDeactivate()
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $jobs = $this->getMultiActionData();
        if ($jobs) {
            foreach ($jobs as $job) {
                if (ilCronManager::isJobActive($job->getId())) {
                    ilCronManager::deactivateJob($job, true);
                }
            }
            
            ilUtil::sendSuccess($this->lng->txt("cron_action_deactivate_success"), true);
        }

        $this->ctrl->redirect($this, "render");
    }
    
    public function reset()
    {
        $this->confirm("reset");
    }
    
    public function confirmedReset()
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $jobs = $this->getMultiActionData();
        if ($jobs) {
            foreach ($jobs as $job) {
                ilCronManager::resetJob($job);
            }
            ilUtil::sendSuccess($this->lng->txt("cron_action_reset_success"), true);
        }

        $this->ctrl->redirect($this, "render");
    }
    
    protected function getMultiActionData()
    {
        $res = array();
        
        if ($_REQUEST["jid"]) {
            $job_id = trim($_REQUEST["jid"]);
            $job = ilCronManager::getJobInstanceById($job_id);
            if ($job) {
                $res[$job_id] = $job;
            }
        } elseif (is_array($_REQUEST["mjid"])) {
            foreach ($_REQUEST["mjid"] as $job_id) {
                $job = ilCronManager::getJobInstanceById($job_id);
                if ($job) {
                    $res[$job_id] = $job;
                }
            }
        }
    
        return $res;
    }
    
    protected function confirm($a_action)
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $jobs = $this->getMultiActionData();
        if (!$jobs) {
            $this->ctrl->redirect($this, "render");
        }

        if ('run' == $a_action) {
            // Filter jobs which are not indented to be executed manually
            $jobs = array_filter($jobs, function ($job) {
                /**
                 * @var $job ilCronJob
                 */
                return $job->isManuallyExecutable();
            });

            if (0 == count($jobs)) {
                ilUtil::sendFailure($this->lng->txt('cron_no_executable_job_selected'), true);
                $this->ctrl->redirect($this, 'render');
            }
        }

        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        
        if (sizeof($jobs) == 1) {
            $jobKeys = array_keys($jobs);
            $job_id = array_pop($jobKeys);
            $job = array_pop($jobs);
            $title = $job->getTitle();
            if (!$title) {
                $title = preg_replace("[^A-Za-z0-9_\-]", "", $job->getId());
            }

            $cgui->setHeaderText(sprintf(
                $this->lng->txt("cron_action_" . $a_action . "_sure"),
                $title
            ));

            $this->ctrl->setParameter($this, "jid", $job_id);
        } else {
            $cgui->setHeaderText($this->lng->txt("cron_action_" . $a_action . "_sure_multi"));
            
            foreach ($jobs as $job_id => $job) {
                $cgui->addItem("mjid[]", $job_id, $job->getTitle());
            }
        }
        
        $cgui->setFormAction($this->ctrl->getFormAction($this, "confirmed" . ucfirst($a_action)));
        $cgui->setCancel($this->lng->txt("cancel"), "render");
        $cgui->setConfirm($this->lng->txt("cron_action_" . $a_action), "confirmed" . ucfirst($a_action));

        $this->tpl->setContent($cgui->getHTML());
    }
    
    public function addToExternalSettingsForm($a_form_id)
    {
        $fields = array();
        
        $data = ilCronManager::getCronJobData();
        foreach ($data as $item) {
            $job = ilCronManager::getJobInstance(
                $item["job_id"],
                $item["component"],
                $item["class"],
                $item["path"]
            );
            
            if (method_exists($job, "addToExternalSettingsForm")) {
                $job->addToExternalSettingsForm($a_form_id, $fields, $item["job_status"]);
            }
        }
        
        if (sizeof($fields)) {
            return array("cron_jobs" => array("jumpToCronJobs", $fields));
        }
    }
}
