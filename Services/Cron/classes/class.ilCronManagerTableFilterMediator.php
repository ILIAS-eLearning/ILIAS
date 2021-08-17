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
    private const FILTER_PROPERTY_NAME_TITLE = 'title';
    private const FILTER_PROPERTY_NAME_COMPONENT = 'component';
    private const FILTER_PROPERTY_NAME_SCHEDULE = 'schedule';
    private const FILTER_PROPERTY_NAME_STATUS = 'status';
    private const FILTER_PROPERTY_NAME_RESULT = 'result';

    private const FILTER_STATUS_ACTIVE = 1;
    private const FILTER_STATUS_INACTIVE = 2;

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
    public function filter(string $action) : Standard
    {
        $componentOptions = array_unique(array_map(function (ilCronJobEntity $entity) : string {
            if ($entity->isPlugin()) {
                return $this->lng->txt('cmps_plugin') . '/' . $entity->getComponent();
            }

            return $entity->getComponent();
        }, $this->items->toArray()));
        asort($componentOptions);

        $title = $this->uiFactory->input()->field()->text($this->lng->txt('title'));
        $components = $this->uiFactory->input()->field()->select(
            $this->lng->txt('cron_component'),
            array_combine($componentOptions, $componentOptions)
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
                self::FILTER_STATUS_ACTIVE => $this->lng->txt('cron_status_active'),
                self::FILTER_STATUS_INACTIVE => $this->lng->txt('cron_status_inactive'),
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

        $fields = [
            self::FILTER_PROPERTY_NAME_TITLE => $title,
            self::FILTER_PROPERTY_NAME_COMPONENT => $components,
            self::FILTER_PROPERTY_NAME_SCHEDULE => $schedule,
            self::FILTER_PROPERTY_NAME_STATUS => $status,
            self::FILTER_PROPERTY_NAME_RESULT => $result,
        ];

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

    /**
     * @param Standard $filter
     * @return ilCronJobCollection
     */
    public function filteredJobs(Standard $filter) : ilCronJobCollection
    {
        $filterValues = $this->uiService->filter()->getData($filter);

        return $this->items->filter(function (ilCronJobEntity $entity) use ($filterValues) : bool {
            if (
                isset($filterValues[self::FILTER_PROPERTY_NAME_TITLE]) &&
                is_string($filterValues[self::FILTER_PROPERTY_NAME_TITLE]) &&
                strlen($filterValues[self::FILTER_PROPERTY_NAME_TITLE]) > 0
            ) {
                $titleFilterValue = $filterValues[self::FILTER_PROPERTY_NAME_TITLE];
                if (ilStr::strIPos($entity->getEffectiveTitle(), $titleFilterValue) === false) {
                    return false;
                }
            }

            if (
                isset($filterValues[self::FILTER_PROPERTY_NAME_COMPONENT]) &&
                is_string($filterValues[self::FILTER_PROPERTY_NAME_COMPONENT]) &&
                strlen($filterValues[self::FILTER_PROPERTY_NAME_COMPONENT]) > 0
            ) {
                $component = $entity->getComponent();
                if ($entity->isPlugin()) {
                    $component = $this->lng->txt('cmps_plugin') . '/' . $component;
                }

                if ($filterValues[self::FILTER_PROPERTY_NAME_COMPONENT] !== $component) {
                    return false;
                }
            }

            if (
                isset($filterValues[self::FILTER_PROPERTY_NAME_SCHEDULE]) &&
                is_string($filterValues[self::FILTER_PROPERTY_NAME_SCHEDULE]) &&
                strlen($filterValues[self::FILTER_PROPERTY_NAME_SCHEDULE]) > 0
            ) {
                if ((int) $filterValues[self::FILTER_PROPERTY_NAME_SCHEDULE] !== $entity->getEffectiveScheduleType()) {
                    return false;
                }
            }

            if (
                isset($filterValues[self::FILTER_PROPERTY_NAME_STATUS]) &&
                is_string($filterValues[self::FILTER_PROPERTY_NAME_STATUS]) &&
                strlen($filterValues[self::FILTER_PROPERTY_NAME_STATUS]) > 0
            ) {
                if (
                    (int) $filterValues[self::FILTER_PROPERTY_NAME_STATUS] === self::FILTER_STATUS_ACTIVE &&
                    !$entity->getJobStatus()
                ) {
                    return false;
                } elseif (
                    (int) $filterValues[self::FILTER_PROPERTY_NAME_STATUS] === self::FILTER_STATUS_INACTIVE &&
                    $entity->getJobStatus()
                ) {
                    return false;
                }
            }

            if (
                isset($filterValues[self::FILTER_PROPERTY_NAME_RESULT]) &&
                is_string($filterValues[self::FILTER_PROPERTY_NAME_RESULT]) &&
                strlen($filterValues[self::FILTER_PROPERTY_NAME_RESULT]) > 0
            ) {
                if ((int) $filterValues[self::FILTER_PROPERTY_NAME_RESULT] !== $entity->getJobResultStatus()) {
                    return false;
                }
            }

            return true;
        });
    }
}
