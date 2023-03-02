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

use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilCronManagerGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilCronManagerGUI: ilPropertyFormGUI
 * @ilCtrl_isCalledBy ilCronManagerGUI: ilAdministrationGUI
 * @ingroup ServicesCron
 */
class ilCronManagerGUI
{
    private readonly ilLanguage $lng;
    private readonly ilCtrlInterface $ctrl;
    private readonly ilSetting $settings;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly Factory $uiFactory;
    private readonly Renderer $uiRenderer;
    private readonly ilUIService $uiService;
    private readonly ilCronJobRepository $cronRepository;
    private readonly \ILIAS\DI\RBACServices $rbac;
    private readonly ilErrorHandling $error;
    private readonly WrapperFactory $httpRequest;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly ilCronManager $cronManager;
    private readonly ilObjUser $actor;

    public function __construct()
    {
        /** @var $DIC \ILIAS\DI\Container */
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->settings = $DIC->settings();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->uiService = $DIC->uiService();
        $this->rbac = $DIC->rbac();
        $this->error = $DIC['ilErr'];
        $this->httpRequest = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->actor = $DIC->user();
        $this->cronRepository = $DIC->cron()->repository();
        $this->cronManager = $DIC->cron()->manager();

        $this->lng->loadLanguageModule('cron');
        $this->lng->loadLanguageModule('cmps');
    }

    /**
     * @param mixed $default
     * @return mixed|null
     */
    protected function getRequestValue(
        string $key,
        \ILIAS\Refinery\Transformation $trafo,
        bool $forceRetrieval = false,
        $default = null
    ) {
        $exc = null;

        try {
            if ($forceRetrieval || $this->httpRequest->query()->has($key)) {
                return $this->httpRequest->query()->retrieve($key, $trafo);
            }
        } catch (OutOfBoundsException $e) {
            $exc = $e;
        }

        try {
            if ($forceRetrieval || $this->httpRequest->post()->has($key)) {
                return $this->httpRequest->post()->retrieve($key, $trafo);
            }
        } catch (OutOfBoundsException $e) {
            $exc = $e;
        }

        if ($forceRetrieval && $exc) {
            throw $exc;
        }

        return $default ?? null;
    }

    public function executeCommand(): void
    {
        if (!$this->rbac->system()->checkAccess('visible,read', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $class = $this->ctrl->getNextClass($this);

        /** @noinspection PhpSwitchStatementWitSingleBranchInspection */
        switch (strtolower($class)) {
            case strtolower(ilPropertyFormGUI::class):
                $job_id = $this->getRequestValue('jid', $this->refinery->kindlyTo()->string());
                $form = $this->initEditForm(ilUtil::stripSlashes($job_id));
                $this->ctrl->forwardCommand($form);
                break;
        }

        $cmd = $this->ctrl->getCmd('render');
        $this->$cmd();
    }

    protected function render(): void
    {
        $tstamp = $this->lng->txt('cronjob_last_start_unknown');
        if ($this->settings->get('last_cronjob_start_ts')) {
            $tstamp = ilDatePresentation::formatDate(
                new ilDateTime(
                    $this->settings->get('last_cronjob_start_ts'),
                    IL_CAL_UNIX
                )
            );
        }

        $message = $this->uiFactory->messageBox()->info($this->lng->txt('cronjob_last_start') . ': ' . $tstamp);

        $cronJobs = $this->cronRepository->findAll();

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
            $this->cronRepository,
            'render',
            $this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)
        );
        $this->tpl->setContent(implode('', [
            $this->uiRenderer->render([$message, $filter]),
            $tbl->populate(
                $tableFilterMediator->filteredJobs(
                    $filter
                )
            )->getHTML()
        ]));
    }

    public function edit(ilPropertyFormGUI $a_form = null): void
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $job_id = $this->getRequestValue('jid', $this->refinery->kindlyTo()->string());
        if (!$job_id) {
            $this->ctrl->redirect($this, 'render');
        }

        if ($a_form === null) {
            $a_form = $this->initEditForm($job_id);
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    protected function getScheduleTypeFormElementName(int $scheduleTypeId): string
    {
        return match ($scheduleTypeId) {
            ilCronJob::SCHEDULE_TYPE_DAILY => $this->lng->txt('cron_schedule_daily'),
            ilCronJob::SCHEDULE_TYPE_WEEKLY => $this->lng->txt('cron_schedule_weekly'),
            ilCronJob::SCHEDULE_TYPE_MONTHLY => $this->lng->txt('cron_schedule_monthly'),
            ilCronJob::SCHEDULE_TYPE_QUARTERLY => $this->lng->txt('cron_schedule_quarterly'),
            ilCronJob::SCHEDULE_TYPE_YEARLY => $this->lng->txt('cron_schedule_yearly'),
            ilCronJob::SCHEDULE_TYPE_IN_MINUTES => sprintf($this->lng->txt('cron_schedule_in_minutes'), 'x'),
            ilCronJob::SCHEDULE_TYPE_IN_HOURS => sprintf($this->lng->txt('cron_schedule_in_hours'), 'x'),
            ilCronJob::SCHEDULE_TYPE_IN_DAYS => sprintf($this->lng->txt('cron_schedule_in_days'), 'x'),
            default => throw new InvalidArgumentException(sprintf(
                'The passed argument %s is invalid!',
                var_export($scheduleTypeId, true)
            )),
        };
    }

    protected function getScheduleValueFormElementName(int $scheduleTypeId): string
    {
        return match ($scheduleTypeId) {
            ilCronJob::SCHEDULE_TYPE_IN_MINUTES => 'smini',
            ilCronJob::SCHEDULE_TYPE_IN_HOURS => 'shri',
            ilCronJob::SCHEDULE_TYPE_IN_DAYS => 'sdyi',
            default => throw new InvalidArgumentException(sprintf(
                'The passed argument %s is invalid!',
                var_export($scheduleTypeId, true)
            )),
        };
    }

    protected function hasScheduleValue(int $scheduleTypeId): bool
    {
        return in_array($scheduleTypeId, [
            ilCronJob::SCHEDULE_TYPE_IN_MINUTES,
            ilCronJob::SCHEDULE_TYPE_IN_HOURS,
            ilCronJob::SCHEDULE_TYPE_IN_DAYS
        ], true);
    }

    protected function initEditForm(string $a_job_id): ilPropertyFormGUI
    {
        $job = $this->cronRepository->getJobInstanceById($a_job_id);
        if (!($job instanceof ilCronJob)) {
            $this->ctrl->redirect($this, 'render');
        }

        $this->ctrl->setParameter($this, 'jid', $a_job_id);

        $jobs_data = $this->cronRepository->getCronJobData($job->getId());
        $job_data = $jobs_data[0];

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'update'));
        $form->setTitle($this->lng->txt('cron_action_edit') . ': "' . $job->getTitle() . '"');

        if ($job->hasFlexibleSchedule()) {
            $type = new ilRadioGroupInputGUI($this->lng->txt('cron_schedule_type'), 'type');
            $type->setRequired(true);
            $type->setValue($job_data['schedule_type']);

            foreach ($job->getAllScheduleTypes() as $typeId) {
                if (!in_array($typeId, $job->getValidScheduleTypes(), true)) {
                    continue;
                }

                $option = new ilRadioOption(
                    $this->getScheduleTypeFormElementName($typeId),
                    (string) $typeId
                );
                $type->addOption($option);

                if (in_array($typeId, $job->getScheduleTypesWithValues(), true)) {
                    $scheduleValue = new ilNumberInputGUI(
                        $this->lng->txt('cron_schedule_value'),
                        $this->getScheduleValueFormElementName($typeId)
                    );
                    $scheduleValue->allowDecimals(false);
                    $scheduleValue->setRequired(true);
                    $scheduleValue->setSize(5);
                    if ((int) $job_data['schedule_type'] === $typeId) {
                        $scheduleValue->setValue($job_data['schedule_value']);
                    }
                    $option->addSubItem($scheduleValue);
                }
            }

            $form->addItem($type);
        }

        if ($job->hasCustomSettings()) {
            $job->addCustomSettingsToForm($form);
        }

        $form->addCommandButton('update', $this->lng->txt('save'));
        $form->addCommandButton('render', $this->lng->txt('cancel'));

        return $form;
    }

    public function update(): void
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $job_id = $this->getRequestValue('jid', $this->refinery->kindlyTo()->string());
        if (!$job_id) {
            $this->ctrl->redirect($this, 'render');
        }

        $form = $this->initEditForm($job_id);
        if ($form->checkInput()) {
            $job = $this->cronRepository->getJobInstanceById($job_id);
            if ($job instanceof ilCronJob) {
                $valid = true;
                if ($job->hasCustomSettings() && !$job->saveCustomSettings($form)) {
                    $valid = false;
                }

                if ($valid && $job->hasFlexibleSchedule()) {
                    $type = (int) $form->getInput('type');
                    $value = match (true) {
                        $this->hasScheduleValue($type) => (int) $form->getInput($this->getScheduleValueFormElementName($type)),
                        default => null,
                    };

                    $this->cronRepository->updateJobSchedule($job, $type, $value);
                }

                if ($valid) {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt('cron_action_edit_success'), true);
                    $this->ctrl->redirect($this, 'render');
                }
            }
        }

        $form->setValuesByPost();
        $this->edit($form);
    }

    public function run(): void
    {
        $this->confirm('run');
    }

    public function confirmedRun(): void
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $job_id = $this->getRequestValue('jid', $this->refinery->kindlyTo()->string());
        if ($job_id) {
            if ($this->cronManager->runJobManual($job_id, $this->actor)) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('cron_action_run_success'), true);
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('cron_action_run_fail'), true);
            }
        }

        $this->ctrl->redirect($this, 'render');
    }

    public function activate(): void
    {
        $this->confirm('activate');
    }

    public function confirmedActivate(): void
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $jobs = $this->getMultiActionData();
        if ($jobs !== []) {
            foreach ($jobs as $job) {
                if ($this->cronManager->isJobInactive($job->getId())) {
                    $this->cronManager->resetJob($job, $this->actor);
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('cron_action_activate_success'), true);
        }

        $this->ctrl->redirect($this, 'render');
    }

    public function deactivate(): void
    {
        $this->confirm('deactivate');
    }

    public function confirmedDeactivate(): void
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $jobs = $this->getMultiActionData();
        if ($jobs !== []) {
            foreach ($jobs as $job) {
                if ($this->cronManager->isJobActive($job->getId())) {
                    $this->cronManager->deactivateJob($job, $this->actor, true);
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('cron_action_deactivate_success'), true);
        }

        $this->ctrl->redirect($this, 'render');
    }

    public function reset(): void
    {
        $this->confirm('reset');
    }

    public function confirmedReset(): void
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $jobs = $this->getMultiActionData();
        if ($jobs !== []) {
            foreach ($jobs as $job) {
                $this->cronManager->resetJob($job, $this->actor);
            }
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('cron_action_reset_success'), true);
        }

        $this->ctrl->redirect($this, 'render');
    }

    /**
     * @return array<string, ilCronJob>
     */
    protected function getMultiActionData(): array
    {
        $res = [];

        $job_ids = [];
        try {
            try {
                $job_ids = [$this->getRequestValue('jid', $this->refinery->kindlyTo()->string(), true)];
            } catch (\ILIAS\Refinery\ConstraintViolationException | OutOfBoundsException) {
                $job_ids = $this->getRequestValue('mjid', $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->string()
                ), false);
            }
        } catch (\ILIAS\Refinery\ConstraintViolationException | OutOfBoundsException) {
        }

        foreach ($job_ids as $job_id) {
            $job = $this->cronRepository->getJobInstanceById($job_id);
            if ($job instanceof ilCronJob) {
                $res[$job_id] = $job;
            }
        }

        return $res;
    }

    protected function confirm(string $a_action): void
    {
        if (!$this->rbac->system()->checkAccess('write', SYSTEM_FOLDER_ID)) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $jobs = $this->getMultiActionData();
        if ($jobs === []) {
            $this->ctrl->redirect($this, 'render');
        }

        if ('run' === $a_action) {
            $jobs = array_filter($jobs, static function (ilCronJob $job): bool {
                return $job->isManuallyExecutable();
            });

            if ($jobs === []) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('cron_no_executable_job_selected'), true);
                $this->ctrl->redirect($this, 'render');
            }
        }

        $cgui = new ilConfirmationGUI();

        if (1 === count($jobs)) {
            $jobKeys = array_keys($jobs);
            $job_id = array_pop($jobKeys);
            $job = array_pop($jobs);
            $title = $job->getTitle();
            if (!$title) {
                $title = preg_replace('[^A-Za-z0-9_\-]', '', $job->getId());
            }

            $cgui->setHeaderText(sprintf(
                $this->lng->txt('cron_action_' . $a_action . '_sure'),
                $title
            ));

            $this->ctrl->setParameter($this, 'jid', $job_id);
        } else {
            $cgui->setHeaderText($this->lng->txt('cron_action_' . $a_action . '_sure_multi'));

            foreach ($jobs as $job_id => $job) {
                $cgui->addItem('mjid[]', $job_id, $job->getTitle());
            }
        }

        $cgui->setFormAction($this->ctrl->getFormAction($this, 'confirmed' . ucfirst($a_action)));
        $cgui->setCancel($this->lng->txt('cancel'), 'render');
        $cgui->setConfirm($this->lng->txt('cron_action_' . $a_action), 'confirmed' . ucfirst($a_action));

        $this->tpl->setContent($cgui->getHTML());
    }

    public function addToExternalSettingsForm(int $a_form_id): array
    {
        $form_elements = [];
        $fields = [];
        $data = $this->cronRepository->getCronJobData();
        foreach ($data as $item) {
            $job = $this->cronRepository->getJobInstance(
                $item['job_id'],
                $item['component'],
                $item['class']
            );
            if (!is_null($job)) {
                $job->addToExternalSettingsForm($a_form_id, $fields, (bool) $item['job_status']);
            }
        }

        if ($fields !== []) {
            $form_elements = [
                'cron_jobs' => [
                    'jumpToCronJobs',
                    $fields
                ]
            ];
        }

        return $form_elements;
    }
}
