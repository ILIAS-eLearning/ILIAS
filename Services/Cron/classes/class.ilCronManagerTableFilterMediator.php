<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Input\Container\Filter\Standard;
use ILIAS\UI\Factory;

/**
 * Class ilCronManagerTableFilterMediator
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilCronManagerTableFilterMediator
{
    /** @var ilCronJobCollection */
    private $items;
    /** @var Factory */
    private $uiFactory;
    /** @var ilUIService */
    private $uiService;
    /** @var ilLanguage */
    private $lng;

    /**
     * ilCronManagerTableFilterMediator constructor.
     * @param ilCronJobCollection $repository
     * @param Factory $uiFactory
     * @param ilUIService $uiService
     * @param ilLanguage $lng
     */
    public function __construct(
        ilCronJobCollection $repository,
        Factory $uiFactory,
        ilUIService $uiService,
        ilLanguage $lng
    ) {
        $this->items = $repository;
        $this->uiFactory = $uiFactory;
        $this->uiService = $uiService;
        $this->lng = $lng;
    }

    /**
     * @param string $action
     * @return Standard
     */
    public function getFilter(string $action) : Standard
    {
        $title = $this->uiFactory->input()->field()->text($this->lng->txt('title'));
        $components = $this->uiFactory->input()->field()->select(
            $this->lng->txt('cron_component'),
            [
                "one" => "One",
                "two" => "Two",
                "three" => "Three"
            ]
        );
        $schedule = $this->uiFactory->input()->field()->select(
            $this->lng->txt('cron_schedule'),
            [
                ilCronJob::SCHEDULE_TYPE_DAILY => $this->lng->txt('cron_schedule_daily'),
                ilCronJob::SCHEDULE_TYPE_WEEKLY => $this->lng->txt('cron_schedule_weekly'),
                ilCronJob::SCHEDULE_TYPE_MONTHLY => $this->lng->txt('cron_schedule_monthly'),
                ilCronJob::SCHEDULE_TYPE_QUARTERLY => $this->lng->txt('cron_schedule_quarterly'),
                ilCronJob::SCHEDULE_TYPE_YEARLY => $this->lng->txt('cron_schedule_yearly'),
                ilCronJob::SCHEDULE_TYPE_IN_MINUTES => sprintf($this->lng->txt('cron_schedule_in_minutes'), 'x'),
                ilCronJob::SCHEDULE_TYPE_IN_HOURS => sprintf($this->lng->txt('cron_schedule_in_hours'), 'x'),
                ilCronJob::SCHEDULE_TYPE_IN_DAYS => sprintf($this->lng->txt('cron_schedule_in_days'), 'x')
            ]
        );
        $status = $this->uiFactory->input()->field()->select(
            $this->lng->txt('cron_status'),
            [
                1 => $this->lng->txt('cron_status_active'), // TODO: Use constant instead
                2 => $this->lng->txt('cron_status_inactive'), // TODO: Use constant instead
            ]
        );
        $result = $this->uiFactory->input()->field()->select(
            $this->lng->txt('cron_result'),
            [
                ilCronJobResult::STATUS_INVALID_CONFIGURATION => $this->lng->txt(
                    'cron_result_status_invalid_configuration'
                ),
                ilCronJobResult::STATUS_NO_ACTION => $this->lng->txt(
                    'cron_result_status_no_action'
                ),
                ilCronJobResult::STATUS_OK => $this->lng->txt(
                    'cron_result_status_ok'
                ),
                ilCronJobResult::STATUS_CRASHED => $this->lng->txt(
                    'cron_result_status_crashed'
                ),
                ilCronJobResult::STATUS_RESET => $this->lng->txt(
                    'cron_result_status_reset'
                ),
                ilCronJobResult::STATUS_FAIL => $this->lng->txt(
                    'cron_result_status_fail'
                ),
            ]
        );

        $fields = [$title, $components, $schedule, $status, $result,];

        $filter = $this->uiService->filter()->standard(
            'cron_job_adm_table',
            $action,
            $fields,
            array_fill(0, count($fields), true),
            true,
            true
        );

        return $filter;
    }
}
